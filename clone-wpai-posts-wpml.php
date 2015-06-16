<?php
/*
Plugin Name: Clone WPAI posts to WPML
Plugin URI: http://www.wordpresshelsinki.fi/
Description: This plugin clones new WP All Imported posts to all enabled WPML languages.
Version: 0.1
Author: Jonni Tammisto
Author URI: https://fi.linkedin.com/in/jonnitammisto
License: GPLv2
*/


function cwpaiwpml_deactivate() {
  delete_option('cwpaiwpml_settings');
}

register_deactivation_hook(__FILE__, 'cwpaiwpml_deactivate');


add_action('pmxi_before_xml_import', 'cwpaiwpml_is_insert_or_update_import', 10, 1);

function cwpaiwpml_is_insert_or_update_import($import_id) {
	/* Update option to 'insert or save' */
	global $wpdb;
	$is_insert_only = $wpdb->get_row($wpdb->prepare("SELECT options FROM ".$wpdb->prefix."pmxi_imports WHERE id = %d", $import_id));
	$import_options = unserialize($is_insert_only->options);
	$settings = get_option( "cwpaiwpml_settings" );
	if ($import_options[create_new_records] == "1" && $import_options[is_keep_former_posts] == "yes") {
		$settings['process'] = true;
	} else {
		$settings['process'] = false;
	}
	$updated = update_option( "cwpaiwpml_settings", $settings );
}



add_action('pmxi_saved_post', 'cwpaiwpml_post_saved', 10, 1);

/* Hook to WP All Import's post saved */

function cwpaiwpml_post_saved($id) {
	/* Check first if have to do anything */
	$settings = get_option( "cwpaiwpml_settings" );
	if ($settings['process'] == true) {
		/* Save duplicates of inserted post */
		global $newposts_to_trid_update;
		global $sitepress;
		$default_language = $sitepress->get_default_language();
		$flag = 0;
		$languages = icl_get_languages('skip_missing=0&orderby=code');
		foreach ($languages as $langu) {
			$code = (string)$langu['language_code'];
			if ($code != $default_language) {
				$post_id_to_copy = $id;
				$post_format_to_copy = get_post_format( $id );
				$lang_to_post = $code;
				$post_to_get = get_post( $post_id_to_copy, ARRAY_A );
				$post_to_get['post_name'] .= "-".$lang_to_post;
				$post_to_get['guid'] = str_replace(basename($post_to_get['guid']), $post_to_get['post_name'], $post_to_get['guid']);
				unset($post_to_get['ID']);
				$new_post_id = wp_insert_post( $post_to_get, false );
				if ($new_post_id != 0) {
					if ($post_format_to_copy != false) {
						set_post_format($new_post_id, $post_format_to_copy );
					}
					$meta_to_copy = get_post_meta( $post_id_to_copy );
					foreach ($meta_to_copy as $metakey => $metavalue) {
						update_post_meta($new_post_id, $metakey, $metavalue[0]);
					}

					/* Get categories to save if have any */
					$post_categories = wp_get_post_categories( $id );
					$categories_array = array();
					foreach($post_categories as $c){
						$c_icl_converted = icl_object_id($c, 'category', false, $lang_to_post);
						if (!empty($c_icl_converted)) {
							$categories_array[] = $c_icl_converted;
						}
					}
					if (count($categories_array) > 0) {
						wp_set_post_categories( $new_post_id, $categories_array, 'category', false );
					}

					global $wpdb;
					$myTrid = $wpdb->get_row($wpdb->prepare("SELECT trid as suomitrid FROM ".$wpdb->prefix."icl_translations WHERE element_id = %d", $post_id_to_copy));
					$suomitrid = $myTrid->suomitrid;
					$newposts_to_trid_update[$new_post_id] = $suomitrid;
					if ($suomitrid != "" && $post_id_to_copy > 1 && $new_post_id > 1) {
			    		$wpdb->query( 
							$wpdb->prepare( 
								"
								UPDATE ".$wpdb->prefix."icl_translations
								SET language_code = %s
								WHERE element_id = %d
								",
							        $lang_to_post, $new_post_id 
						        )
						);
						if ($flag == 0) {
							add_action( 'shutdown', 'cwpaiwpml_perform_trid_update' );
							$flag = 1;
						}
					}
				}
			}
		}
	}
}


/* Update WPML trid in the end because WPML will otherwise override updated trid */
function cwpaiwpml_perform_trid_update() {
	global $newposts_to_trid_update;
	global $wpdb;
	if (count($newposts_to_trid_update) > 0) {
		foreach ($newposts_to_trid_update as $postid => $trid) {
    		$wpdb->query( 
				$wpdb->prepare( 
					"
					UPDATE ".$wpdb->prefix."icl_translations
					SET trid = %d
					WHERE element_id = %d
					",
				        $trid, $postid 
			        )
			);
		}
	}
}

?>