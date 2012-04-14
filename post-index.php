<?php
/*
	Plugin Name: Post Index
	Plugin URI: http://wordpress.org/extend/plugins/post-index/
	Description: This plugin summarises all found blog posts added to a specific category and lists them alphabetically. Additional custom fields could be used to display links to other pages or additional information to a post.
	Version: 0.5
	Author: Thomas A. Hirsch
	Author URI: http://www.thirsch.de/
	Last Updated: 2012-04-14
 	License: GPLv2 or later

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

define('POST_INDEX_PLUGIN_NAME', 'post-index');
define('POST_INDEX_PLUGIN_PREFIX', POST_INDEX_PLUGIN_NAME . '_');
define('POST_INDEX_PLUGIN_OPTIONS', POST_INDEX_PLUGIN_PREFIX . 'option');
define('POST_INDEX_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('POST_INDEX_PLUGIN_LABEL', 'Post Index');

include_once 'php/settings.php';
$postIndexPluginSettings = new PostIndexSettings( POST_INDEX_PLUGIN_NAME
												, POST_INDEX_PLUGIN_LABEL
												, POST_INDEX_PLUGIN_BASENAME
												);

/*
 * Include the index builder.
 */
if(!is_admin()) {
	include_once 'php/postsummary.php';
	
	function postSummaryFilter($data) {
		global $postIndexPluginSettings;
	
		$ps = new PostSummary($postIndexPluginSettings);
		
		$pattern = '/\<\!\-\-\s*post-index\s*(?:\(\'?([^\']*)\'?\))?\s*\-\-\>/';
		if(preg_match_all($pattern, $data, $matches))
		{
			for($i = 0; $i < count($matches[0]); $i++) {
				$category_name = $matches[1][$i];
				$replace_pattern = '/' . preg_quote($matches[0][$i]) . '/';
	
				ob_start();	
				$ps->parse($category_name);
				$ps->printOut();
	
				$content = ob_get_contents();
				ob_end_clean();
											
				$data = preg_replace($replace_pattern, $content, $data);	
			}
		}
		
		return $data;
	}
	
	add_filter('the_content', 'postSummaryFilter');
	
	/**
	 * ShortCode API hook to include the post index into your post.
	 */
	function post_index_func( $atts ) {
		global $postIndexPluginSettings;
		extract( shortcode_atts( array(
			'category' => $postIndexPluginSettings->defaultCategory), $atts ) );
		
		$ps = new PostSummary($postIndexPluginSettings);
	
		ob_start();	
		$ps->parse($category);
		$ps->printOut();
	
		$content = ob_get_contents();
		ob_end_clean();
		
		return $content;
	}
	
	add_shortcode('post-index', 'post_index_func');
}

/*
 * Include the meta box for the post admin interface, but only in the admin area.
 */
if(is_admin()) {
   include_once 'php/postadmin.php';
   new PostAdminWidget($postIndexPluginSettings);
}

?>
