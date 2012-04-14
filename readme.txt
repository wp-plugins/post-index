=== Plugin Name ===
Contributors: iTux
Tags: post, index, overview, list, reference
Author URI: http://www.thirsch.de/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=232JYLEQYMWG2
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: 0.5
License: GPLv2 or later

Build an index of all posts found for a specific category and display it in any page or post. 


== Description ==

Here you will find a page where the plugin is in use: [BookLover](http://books.nivija.com/rezensionen/ "BookLover Blog")

***

With this plugin, you can build a alphabetically sorted index like:

A

* A post
* A second post

D

* Do you really like that?

H

* How to do something

Each post title is directly linked to the post permalink and can have different additional information.

What comes next?

* Translation from German to English
* Additional text to a link from custom fields and an admin setting to change the link name
* Or any idea, you post me :)


== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Please define your custom fields and custom titles in the settings page
1. Place `[post_index]` or `[post_index category='CategoryName']` in your page or post to display the index


== Screenshots == 

1. A sample page containing the shortcode [post-index].
2. The tag in the page editor.
3. The post editor and the new custom fields.
4. Settings page to adjust the configuration of the plugin.


== Frequently Asked Questions ==

= Why does the index not appear? =

Did you enter the tag `<!--post-index-->` in the Visual or HTML mode of the editor? You have to use the HTML mode, otherwise the tag `<!--post-index-->` does simply appear as it is on your page. 

Please use `[post_index]`, supported since Version 0.5, to avoid that!


== Upgrade Notice ==

= 0.5 =
The plugin uses shortcodes now. Please change your page or post and replace `<!--post-index-->` with `[post_index]`! Thanks to [Franz Wieser](http://www.wieser.at/ "Franz Wieser")


== Changelog ==

= 0.5 =
* Using shortcodes instead of content parsing.

= 0.3 =
* Parses the post-index hook and extracts the category name from it.

= 0.2 =
* Fixed a bug that caused the meta box to not correctly load the custom field values.

= 0.1 =
* First version - This version is available only German at the moment!