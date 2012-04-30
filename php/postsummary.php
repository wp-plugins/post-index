<?php
class PostSummary {
	private $foreignLinks;
	private $postLabel;
	private $pageDescription;
	private $defaultCategory;
	private $infoSeparator;
	private $category;
	private $characterTable;
	
	private $groupBy;
	
	private $items;
	private $itemCount;
	
	function u8e($c) {
		return utf8_encode($c); 
	}
	function u8d($c) {
		return utf8_decode($c);
	}
	
	public function __construct($pluginSettings) {
		$this->foreignLinks = $pluginSettings->settings['infoLinks'];
		$this->postLabel = $pluginSettings->settings['postLabel'];
		$this->pageDescription = $pluginSettings->settings['pageDescription'];
		$this->defaultCategory = $pluginSettings->settings['defaultCategory'];
		$this->infoSeparator = $pluginSettings->settings['infoSeparator'];		
		$this->showGroupCount = $pluginSettings->settings['showGroupCount'];
		
		$this->characterTable = array(
			'index'	=> array('ae'    ,'Ae'    ,'oe'    ,'Oe'    ,'ue'    ,'Ue'    ,'ss'    ),
			'raw'	=> array('ä'     ,'Ä'     ,'ö'     ,'Ö'     ,'ü'     ,'Ü'     ,'ß',       ),
			'in'	=> array(chr(228),chr(196),chr(246),chr(214),chr(252),chr(220),chr(223) ),
			'post'	=> array('&auml;','&Auml;','&ouml;','&Ouml;','&uuml;','&Uuml;','&szlig;'),
			'feed'	=> array('&#228;','&#196;','&#246;','&#214;','&#252;','&#220;','&#223;' ),
			'utf8'	=> array($this->u8e('ä'),$this->u8e('Ä'),$this->u8e('ö'),$this->u8e('Ö'),$this->u8e('ü'),$this->u8e('Ü'),$this->u8e('ß') )
		);
	}
	
	function getCustomFieldValue($fieldName, $alternative) {
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
	
	function parse($category_name, $groupBy) {
		$this->category = empty($category_name) ? $this->defaultCategory : $category_name;	
		$categoryId = get_cat_ID($this->category);
		
		query_posts( array ( 'category_name' => $this->category,
		                     'orderby' => 'title',
		                     'order' => 'ASC',
		                     'posts_per_page' => -1
		                   )
		           );
		           
   		$this->items = NULL;
		$this->groupBy = $groupBy;
				
		if($groupBy == 'subcategory') {
			$this->itemCount = array();

			foreach(get_categories(array( 'parent' => $categoryId, 'hide_empty' => 0 )) as $subCat) {
				$this->items[$subCat->cat_name] = array();
				$this->itemCount[$subCat->cat_name] = 0;
			} 
		} else {
			$this->itemCount = NULL;
		}
           
		while (have_posts()) : the_post();
			$title = $this->getCustomFieldValue('book_title', get_the_title());
			$author = $this->getCustomFieldValue('book_author', NULL);
			
			$title = str_replace('&#8220;', '', $title);
			$title = str_replace('&#8221;', '', $title);
			     
			$linkList = array();
			
			if(!empty($this->foreignLinks)) {
				foreach($this->foreignLinks as $name => $urlField) {
					$url = $this->getCustomFieldValue($urlField, NULL);
					if(!is_null($url))
						$linkList[] = array ( 'name' => $name, 'url' => $url );
				}
			}
			
			$curItem = array( 'title' => $title
			                , 'author' => $author
			                , 'permalink' => get_permalink()
			                , 'linkList' => $linkList );
			                
			                
            $decoded_title = html_entity_decode($title);
			$decoded_title = str_replace($this->characterTable['raw'], $this->characterTable['index'], $decoded_title);
			$decoded_title = str_replace($this->characterTable['utf8'], $this->characterTable['index'], $decoded_title);
			$decoded_title = str_replace($this->characterTable['post'], $this->characterTable['index'], $decoded_title);
			$decoded_title = str_replace($this->characterTable['in'], $this->characterTable['index'], $decoded_title);
			
			$firstLetter = strtoupper(substr($decoded_title, 0, 1));
			    
			if($groupBy == 'subcategory') {          
				$post_categories = get_the_category();
				$cats = array();
	
				foreach($post_categories as $c){					
					if($c->parent == $categoryId) {
						$this->items[$c->cat_name][$firstLetter][] = $curItem;				
						++$this->itemCount[$c->cat_name];
					}
				}
			} else {	
				$this->items[$firstLetter][] = $curItem;
				++$this->itemCount;
			}
		endwhile;
		
		ksort($this->items);
		
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
	
	function printOut() 
	{		
		if(is_null($this->items) || empty($this->items))
		return;
		
		if($this->groupBy == 'subcategory') { 

			foreach($this->items as $subCategory => $item) {
				echo '<h2>'.$subCategory.'</h2>'."\n";
				// Parse Subcategory
				$this->printItem($item, $this->itemCount[$subCategory], 'h3');
			}
		}
		else {
			$this->printItem($this->items, $this->itemCount);
		}
	}
	
	/**
	 * counts elements of an multidimensional array
	 * 
	 * @param array $array Input Array
	 * @param int $limit dimensions that shall be considered (-1 means no limit )
	 * @return int counted elements
	 */
	function multicount ($array, $limit = -1)
	{
	   $cnt = 0;
	   $limit = $limit > 0 ? (int) $limit : -1;
	   $arrs[] = $array;
	   for ($i=0; isset($arrs[$i]) && is_array($arrs[$i]); ++$i)
	   {
		  foreach ($arrs[$i] as $value)
		  {
			 if (!is_array($value) ) ++$cnt;
			 elseif( $limit==-1 || $limit>1 ) 
			 {
				if( $limit>1 ) --$limit;
				$arrs[] = $value;
			 }
		  }
	   }      
	   return $cnt;
	}
	
	function printItem($item, $itemCount, $headerTag = 'h2') {
		echo '<p>';
		$categoryId = uniqid();
		
		if(!empty($this->pageDescription)) {
			echo str_replace( '${Category}'
							, $this->category
							, str_replace( '${PostCount}'
										 , $this->getPostLabel($itemCount)
										 , $this->pageDescription
										 )
							);
			
			if($itemCount > 0)
				echo '<hr />';
		}
		
		if($itemCount > 0)
		{
			
			echo '<p>' . __('Jump to', 'post-index') . ' ';
			foreach(array_keys($item) as $index)
			{
				echo '<a href="#letter_' . $categoryId . '_' . $index . '">' . $index . '</a> ';
			}
		
			echo '</p>' . "\n";
				
			foreach($item as $index => $posts) 
			{
				echo '<a name="letter_' . $categoryId . '_' . $index .'"></a><'. $headerTag .'>' . $index . '</'.$headerTag.'>';
				
				if($this->showGroupCount == 1) {
					echo ' (' . count($posts) . ')';
				}
				
				echo "\n";
				echo '<ul>'."\n";
				foreach($posts as $post) 
				{
					echo '<li><strong><a href="' . $post['permalink'] . '">' . $post['title'] . '</a></strong>';
					if(!is_null($post[author])) {
						/* translators: a book 'by {author}' */
						echo ' ' . sprintf(__('by %s', 'post-index'), $book['author']);
					}
					
					$linkList = $post['linkList'];
					if(count($linkList) > 0) 
					{
						echo '<br />';
					
						for($i = 0; $i < count($linkList); $i++)
						{
							$link = $linkList[$i];
							echo $this->getSeparator($i, count($linkList), $this->infoSeparator);
							echo '<a href="' . $link['url'] . '" target="_blank">' . $link['name'] . '</a>';
						}
					}
					echo '</li>'."\n";
				}
				echo '</ul>';
			}
		}
		echo '</p>';
	}
}
?>
