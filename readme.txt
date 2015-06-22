=== Plugin Name ===
Contributors: j0nnii
Donate link: http://www.devjonni.fi/
Tags: wpallimport, wpml, import
Requires at least: 4.0.0
Tested up to: 4.2.2
Stable tag: 4.2.2
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin clones WP All Imported posts to all enabled WPML language versions and connects them together.

== Description ==

This plugin hooks to the post_saved hook of WP All Import and does the following

*   Clones new posts to other enabled WPML languages (content, postmeta, post format)
*   If categories exist in language versions then add those as well
*   If Import has "Update existing posts with changed data in your file" enabled, then this plugin doesn't do anything.

Plugin is tested only for importing posts. I made plugin because WP All Import dropped WPML support. Developer of this plugin is not responsible for damages this might cause to your site/database. I am in no way affiliated with developers of WP All Import or WPML. I recommend you backup your database before trying this.

Tested with WP All Import Pro 4.1.6 & WPML 3.2. I have not tried with free version of WP All Import.

== Installation ==

1. Upload plugin to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. In WP All Import corresponding import's Import Settings you must set the following:
When WP All Import finds new or changed data... tick only Create new posts from records newly present in your file.


== Frequently Asked Questions ==

= Why this plugin doesn't have this or that feature =

I made it to do the job I need, not make it full of features. If you want new features, you can try to hire me.

== Screenshots ==

1. Before / After plugin installation screenshot

== Changelog ==

= 0.1 =
* Initial version

== Upgrade Notice ==

= 0.1 =
Initial version.