<?php
class PostSummary {
	private $foreignLinks;
	private $postLabel;
	private $pageDescription;
	private $defaultCategory;
	private $infoSeparator;
	
	private $characterTable;
	
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
	
	function parse($category_name) {	
		query_posts( array ( 'category_name' => empty($category_name) ? $this->defaultCategory : $category_name,
		                     'orderby' => 'title',
		                     'order' => 'ASC',
		                     'posts_per_page' => -1
		                   )
		           );
		           
		$this->itemCount = 0;
		$this->items = NULL;
		           
		while (have_posts()) : the_post();
			++$this->itemCount;
			
			$title = $this->getCustomFieldValue('book_title', get_the_title());
			$author = $this->getCustomFieldValue('book_author', NULL);
			
			$title = str_replace('&#8220;', '', $title);
			$title = str_replace('&#8221;', '', $title);
			
			$decoded_title = html_entity_decode($title);
			$decoded_title = str_replace($this->characterTable['raw'], $this->characterTable['index'], $decoded_title);
			$decoded_title = str_replace($this->characterTable['utf8'], $this->characterTable['index'], $decoded_title);
			$decoded_title = str_replace($this->characterTable['post'], $this->characterTable['index'], $decoded_title);
			$decoded_title = str_replace($this->characterTable['in'], $this->characterTable['index'], $decoded_title);
			
			$firstLetter = substr($decoded_title, 0, 1);
						        
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
			                
			$this->items[$firstLetter][] = $curItem;
			
		endwhile;

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
		echo str_replace('${PostCount}', $this->getPostLabel($this->itemCount), $this->pageDescription);
		
		if(is_null($this->items) || empty($this->items))
			return;
	
		echo '<hr />';
		echo '<p>' . __('Springe direkt zu:') . ' ';
		foreach(array_keys($this->items) as $index)
		{
			echo '<a href="#letter_' . $index . '">' . $index . '</a> ';
		}
		
		echo '</p>' . "\n";
		
		foreach($this->items as $index => $books) 
		{
			echo '<a name="letter_' . $index .'"></a><em><strong>' . $index . '</strong></em>'."\n";
			echo '<ul>'."\n";
			foreach($books as $book) 
			{
				echo '<li><strong><a href="' . $book['permalink'] . '">' . $book['title'] . '</a></strong>';
				if(!is_null($book[author])) {
					/* translators: a book 'from' an author */
					echo ' ' . __('von') . ' . $book['author'];
				}
				
				$linkList = $book['linkList'];
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
}
?>