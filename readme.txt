=== Plugin Name ===
Contributors: iTux
Tags: post, index, overview, list, reference
Author URI: http://www.thirsch.de/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=232JYLEQYMWG2
Requires at least: 2.7
Tested up to: 4.2.2
Stable tag: 0.7.4
License: GPLv2 or later

Generates an index of your posts that can be used in any page or post. 


== Description ==

[Here](http://books.nivija.com/rezensionen/ "BookLover Blog"), you will find a page where the plugin is in use or have a look at the screenshots page to see it in action.

***

This plugin summarises all found blog posts added to a specific category or of a specific post type and lists them alphabetically or grouped by the used subcategory. Additional custom fields could be used to display links to other pages or additional information to a post.

Upcoming features:

* Customisable templates for the index entries to add any additional information:
  * additional text
  * custom field values
  * subcategories
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

Did you use an existing category or custom post type?

= How to change the grouping? =

The following groupBy clauses to be used in the shortcode `[post_index groupby=""]` are supported:

* firstLetter - Groups the posts according to their first letter
* subcategory - Uses the subcategory of each post as its group key.

= List of supported attributes for the shortcode =

The base shortcode is `[post_index]`. You can add any of the following attributes to change the output of the index. There are some options which makes no sense to combine. For example to use the category and categoryslug attribute as they will both indicate which category has to be used.

* **category**: Lists all entries that are in the given category, searched by it's name.
* **categoryslug**: Same as category but searched by the slug.
* **groupby**: Grouping as explaned in `How to change the grouping`, possible values are `firstLetter`, `subcategory`, and `custom_field`
* **post_type**: The index will be build for the given post type instead of the standard type `post`. Please see [WordPress Codex, Post Types](http://codex.wordpress.org/Post_Types "WordPress Codex, Post Types") for details.
* **columns**: The amount of columns. Default is 1.
* **show_letter**: If set to false, the grouping letter will not be generated.
* **show_list**: If set to false, the "Jump to" list will not be generated.
* **groupby_cf**: If defined, the custom field value will be used for grouping and sorting instead of the blog posts title.

== Upgrade Notice ==

= 0.7 =

* You can now split your index into columns using the attribute columns in the shortcode: `[post_index columns=2]`.
* Custom post types are now supported and can be specified in the shortcode tag: `[post_index post_type="page"]`.
* The old filter for `<!--post-index-->` has been completely removed! Replace it by using the shortcode.

= 0.5 =

The plugin uses shortcodes now. Please change your page or post and replace `<!--post-index-->` with `[post_index]`! Thanks to [Franz Wieser](http://www.wieser.at/ "Franz Wieser")


== Changelog ==

= 0.7.5 =

* Added: Use any custom field value to group your index with. Just specify it in the shortcode like `[post_index groupby_cf='Author']`. It does work together with grouping by subcategory and the default firstLetter grouping.
* Added: New Parameter show_list to explicitly hiding the "Jump to" list. Contributed by billthefarmer. (Usage: `[post_index show_list=false]`)

= 0.7.4 =

* Added: Dutch translation. Contributed by aadje93.
* Added: Portuguese translation. Contributed by luisapietrobon.
* Added: Parameter show_letter to explicitly hide the grouping letter. (Usage: `[post_index show_letter=false]`)
* Fixed: On PHP configurations having error_reporting set to include E_NOTICE, saving pages ended up with a warning page.

= 0.7.3 =

* Removed deprecation warning, caused by the use of old user levels. The call has been replaced by using a role name.

= 0.7.2 =

* Tested successfully with 4.2.2
* Added supported short codes and arguments to the settings page.

= 0.7.1 =

* Tested successfully with 3.7.1 and 3.8
* Removed short codes as some hosters do not support them.

= 0.7 =

* Added: Support for custom post types
* Added: Support for columns
* Removed: Support for `<!--post-index-->` has been removed.

= 0.6.2 =

* Fixed: Sorting now uses the sanitize_title function before extracting the first letter.
* Improved: Added alphabetical subgrouping in groupBy type "subcategory".
* Added: CategorySlug as a new filter criteria if the category name occurs more than once.
* Added an optional displayed group count. It can be activated in the settings of the plugin.

= 0.6 =

* Translated the plugin to English and added a German translation file.
* Small and capital letters are now treated as one letter
* The index can now group by the subcategory instead of the first letter. Simply add the groupBy clause to the shortcode: `[post_index groupBy='subcategory']`

= 0.5 =

* Using shortcodes instead of content parsing.

= 0.3 =

* Parses the post-index hook and extracts the category name from it.

= 0.2 =

* Fixed a bug that caused the meta box to not correctly load the custom field values.

= 0.1 =

* First version - This version is available only German at the moment!
