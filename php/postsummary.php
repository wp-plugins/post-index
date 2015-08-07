<?php
class PostSummary {
	private $foreignLinks;
	private $postLabel;
	private $pageDescription;
	private $defaultCategory;
	private $infoSeparator;
	private $category;
	
	private $groupBy;
	
	private $items;
	private $itemCount;
	
	public function __construct($pluginSettings) {
		$this->foreignLinks = $pluginSettings->settings['infoLinks'];
		$this->postLabel = $pluginSettings->settings['postLabel'];
		$this->pageDescription = $pluginSettings->settings['pageDescription'];
		$this->defaultCategory = $pluginSettings->settings['defaultCategory'];
		$this->infoSeparator = $pluginSettings->settings['infoSeparator'];		
		$this->showGroupCount = $pluginSettings->settings['showGroupCount'];	
	}
	
	function getCustomFieldValue($fieldName, $alternative = null) {
		$customFieldValues = get_post_custom_values($fieldName);
		if(is_null($customFieldValues)) {
			return $alternative;
		}
		
		return current($customFieldValues);
	}
	
	function getPostLabel($count) {
		if($count > 1)
			return $count . ' ' . $this->postLabel[2];
		
		if($count == 1)
			return $this->postLabel[1];
		
		return $this->postLabel[0];
	}
	
	function parse($category_name, $groupBy, $groupByCf = null, $categoryslug = '', $post_type = null) {
      $category = NULL;
      
      if(!empty($post_type)) {
      	$categoryId = null;
      }
            
            
      if(!empty($category_name) || !empty($categoryslug)) {
        if(empty($categoryslug)) { 

          $this->category = empty($category_name) ? $this->defaultCategory : $category_name;	
          $category = get_category(get_cat_ID($this->category));

          if(is_wp_error($category)) {
        	echo '<div class="error">Warning! Category ' . $category_name . ' not found!</div>';
          }
        } else {
          $category = get_category_by_slug($categoryslug);
          if(is_wp_error($category)) {
        	echo '<div class="error">Warning! Category ' . $categoryslug . ' not found!</div>';
          }
        }
        $categoryId = $category->term_id;
      }
      
      $query = array ( 'orderby' => 'title'
		             , 'order' => 'ASC'
		             , 'posts_per_page' => -1
		             , 'post_type' => empty($post_type) ? 'post' : $post_type
		             );
		             
      if(!empty($categoryId)) {
        $query['cat'] = $categoryId;
      }
      		
	  query_posts( $query );
		           
   	  $this->items = array();
		$this->groupBy = $groupBy;
				
		if($groupBy == 'subcategory') {
			$this->itemCount = array();

			foreach(get_categories(array( 'parent' => $categoryId, 'hide_empty' => 0 )) as $subCat) {
				$this->items[$subCat->cat_name] = array();
				$this->itemCount[$subCat->cat_name] = 0;
			} 
		} else {
			$this->itemCount = 0;
		}
           
		while (have_posts()) : the_post();
			$title = $this->getCustomFieldValue('book_title', get_the_title());
			$author = $this->getCustomFieldValue('book_author');
			
			     
			$linkList = array();
			
			if(!empty($this->foreignLinks)) {
				foreach($this->foreignLinks as $name => $urlField) {
					$url = $this->getCustomFieldValue($urlField);
					if(!is_null($url))
						$linkList[] = array ( 'name' => $name, 'url' => $url );
				}
			}
			
			$curItem = array( 'title' => $title
			                , 'author' => $author
			                , 'permalink' => get_permalink()
			                , 'linkList' => $linkList );

            if ($groupByCf) {
                $cfValue = $this->getCustomFieldValue($groupByCf);
                $curItem['sortValue'] = $cfValue;
                if (!$cfValue) {
                    continue;
                }

                $firstLetter = strtoupper(substr(sanitize_title($cfValue), 0, 1));
            } else {
                $curItem['sortValue'] = $title;
                $firstLetter = strtoupper(substr(sanitize_title($title), 0, 1));
            }

			if($groupBy == 'subcategory') {
				$post_categories = get_the_category();

				foreach ($post_categories as $c) {
					if ($c->parent == $categoryId) {
						$this->items[$c->cat_name][$firstLetter][] = $curItem;
						++$this->itemCount[$c->cat_name];
					}
				}
			} else {
				$this->items[$firstLetter][] = $curItem;
				++$this->itemCount;
			}
		endwhile;
		
		ksort($this->items, SORT_STRING);

        foreach ($this->items as &$elements) {
            uasort($elements, function ($a, $b) {
                if ($a['sortValue'] == $b['sortValue']) {
                    return 0;
                }

                return ($a['sortValue'] < $b['sortValue']) ? -1 : 1;
            });
        }
		
		// Reset Query
		wp_reset_query();
	}
	
	/**
	 * TODO Move this method to a more generic place, because it is also used in the settings manager
	 */
	private function getSeparator($current, $max, $infoSeparator) 
		{
			if($current > 0 && $current < ($max - 1)) 
			{
				return $infoSeparator[1];
			}
		
			if($current == 0)
				return $infoSeparator[0];
			
			if($current == $max)
				return $infoSeparator[3];
			
			return $infoSeparator[2];
		}
	
	function printOut($maxColumns = 1, $showLetter = true, $showList = true)
	{		
		if(is_null($this->items))
		return;
		
		echo "<!-- index generated by post-index: http://www.wordpress.org/plugins/post-index --> \n";
		if($this->groupBy == 'subcategory') { 
			foreach($this->items as $subCategory => $item) {
				ksort($item, SORT_STRING);
				
				echo '<h2>'.$subCategory.'</h2>'."\n";
				// Parse Subcategory
				$this->printItem($item, $this->itemCount[$subCategory], $subCategory, $maxColumns, $showLetter, $showList, 'h3');
			}
		}
		else {
			$this->printItem($this->items, $this->itemCount, $this->category, $maxColumns, $showLetter, $showList);
		}
		
		echo "\n<!-- end of post-index content. --> \n";
	}
		
	/**
	 * writes the index to stdout.
	 *
	 * @param int $maxColumns determines the amount of columns
	 */
	function printItem($item, $itemCount, $categoryName, $maxColumns, $showLetter, $showList, $headerTag = 'h2') {
		echo '<p>';
		$categoryId = uniqid();
		
		if(!empty($this->pageDescription)) {
			echo str_replace( '${Category}'
							, $categoryName
							, str_replace( '${PostCount}'
										 , $this->getPostLabel($itemCount)
										 , $this->pageDescription
										 )
							);
			
			if($itemCount > 0)
				echo '<hr />';
		}

		if ($itemCount > 0) {
			if ($showList) {
				echo '<p>' . __('Jump to', 'post-index') . ' ';

				$groups = array_keys($item);
				$countOfGroups = count($groups);

				for ($i = 0; $i < $countOfGroups; $i++) {
					echo '<a href="#letter_' . $categoryId . '_' . $groups[$i] . '">' . $groups[$i] . '</a>';
					if ($i < ($countOfGroups - 1)) {
						echo ',';
					}
					echo ' ';
				}

				echo '</p>' . "\n";
			}
			
			$itemCountPerGroup = 2;							// used for the calculation of items per column.
			$maxPostsPerColumn = floor(($countOfGroups * $itemCountPerGroup + $itemCount) / $maxColumns);
			$columnPercentage = (100 / $maxColumns) - 2;  	// 2 is a offset needed for the padding.
			
			if($maxColumns > 1) {
				// start first column
				echo '<div style="float: left; width: ' . $columnPercentage . '%; border-right: 1px solid lightgray;padding: 5px;">';
			}
						
			$currentItemsPerColumn = 0;		// contains the amount of posts of the current column.
			$currentColumn = 1;				// indicates the current column.
			
			foreach($item as $index => $posts) 
			{
				$currentItemsPerColumn += $itemCountPerGroup;

				if($showLetter) {
                    echo '<a name="letter_' . $categoryId . '_' . $index .'"></a><'. $headerTag .'>' . $index;


                    if($this->showGroupCount == 1) {
                        echo ' <small>(' . count($posts) . ')</small>';
                    }

                    echo  '</'.$headerTag.'>' . "\n";
				}

				echo '<ul>'."\n";
				foreach($posts as $post) 
				{
					echo '<li><strong><a href="' . $post['permalink'] . '">' . $post['title'] . '</a></strong>';
					if(!is_null($post['author'])) {
						/* translators: a book 'by {author}' */
						echo ' ' . sprintf(__('by %s', 'post-index'), $post['author']);
					}
					
					$linkList = $post['linkList'];
					if(count($linkList) > 0) 
					{
						echo '<br /><div style="font-size: smaller;">';
					
						for($i = 0; $i < count($linkList); $i++)
						{
							$link = $linkList[$i];
							echo $this->getSeparator($i, count($linkList), $this->infoSeparator);
							echo '<a href="' . $link['url'] . '" target="_blank">' . $link['name'] . '</a>';
						}
						echo '</div>';
					}
					echo '</li>'."\n";
					
					$currentItemsPerColumn++;
				}
				echo '</ul>';
				
				// check a column break only on complete groups...
				if($maxColumns > 1 && $currentItemsPerColumn >= $maxPostsPerColumn) {
					$currentItemsPerColumn = 0;
					$currentColumn++;
					if($currentColumn < $maxColumns) {
						echo '</div><div style="float: left; width: ' . $columnPercentage . '%; border-right: 1px solid lightgray;padding: 5px;">';
					} else {
						echo '</div><div style="float: left; width: ' . $columnPercentage . '%; padding: 5px;">';
					}
				}
			}
			
			if($maxColumns > 1) {
				echo '</div><div style="clear: both;"></div>';
			}
		}
		echo '</p>';
	}
}
?>
