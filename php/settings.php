<?php
	class PostIndexSettings {
		public $settings;
		
		private $basename;
		private $pluginName;
		private $pluginLabel;
		private $optionName;
		
		public function __construct($pluginName, $pluginLabel, $basename) {
			$this->pluginName = $pluginName;
			$this->pluginLabel = $pluginLabel;
			$this->basename = $basename;
			$this->optionName = $pluginName . '-option';
			
			$this->load();
			$this->addHooks();
		}
		
		public function addHooks() {
			if(!is_admin()) return;
			
			add_action('admin_menu', array($this, 'CreateMenu'), 10);
			global $wp_version;
			if ( version_compare($wp_version, '2.7', '>=' ) ) {
				add_filter( 'plugin_action_links', array($this, 'AddFilterPluginActionLinks'), 10, 2 );
			}
		}
		
		function AddFilterPluginActionLinks( $links, $file ) {
			if ( $file == $this->basename ) {
				$links[] = '<a href="'.$this->GetPluginOptionsURL().'">' . __('Settings') . '</a>';
			}
			return $links;
		}
		
		function GetPluginOptionsURL() {
			if (function_exists('admin_url')) {	// since WP 2.6.0
				$adminurl = trailingslashit(admin_url());			
			} else {
				$adminurl = trailingslashit(get_settings('siteurl')).'wp-admin/';
			}
			return $adminurl.'options-general.php'.'?page=' . $this->pluginName;
		}

		function CreateMenu() {
			add_options_page($this->pluginLabel, $this->pluginLabel, 10, $this->pluginName, array($this, 'OptionsPage'));
		}	
		
		private function buildInfoLinks($nameList, $fieldList) {
			$infoLinks = NULL;
					
			if(!empty($nameList) && !empty($fieldList)) {
				$infoLinks = array();
				
				foreach(array_combine($nameList, $fieldList) as $name => $field) {
					if(!empty($name) && !empty($field)) {
						$infoLinks[$name] = $field;
					}
				}
			}
			
			if(!is_null($infoLinks)) {
				ksort($infoLinks);
			}
						
			return $infoLinks;
		}
		
		function OptionsPage(){
			global $pb_PluginName;
			
			if(isset($_POST['updateSettings'])) {
				$settings['defaultCategory'] = $_POST['defaultCategory'];
				$settings['infoSeparator'] = $_POST['infoSeparator'];
				$settings['postLabel'] = $_POST['postLabel'];
				$settings['pageDescription'] = $_POST['pageDescription'];
				
				// parse infoLinks
				$settings['infoLinks'] = $this->buildInfoLinks($_POST['infoLinksName'], $_POST['infoLinksField']);
				
				$this->settings = $settings;
				$this->save();
			
				echo "<div id='message' class='updated' style='width: 505px;'><p><b>" . __('Einstellungen wurden gespeichert.', 'post-index') . "</b></p></div>";
			} 
		
			extract($this->settings);

			?><div class="wrap">
			<div id="icon-options-general" class="icon32"><br /></div>
				<h2><? printf(__('%s Settings', 'post-index'), $this->pluginLabel); ?></h2>

				<form method="post" action="options-general.php?page=<?=$this->pluginName;?>">
					<input type="hidden" name="updateSettings" value="1" />
					<h3><? _e('General settings', 'post-index'); ?></h3>
					<p><? _e('Please define general settings for the plugin.', 'post-index'); ?></p>

					<table class="form-table">
						<tr>
							<th><? _e('Default category', 'post-index'); ?></th>
							<td><input type="text" value="<?php echo($defaultCategory); ?>" class="regular-text" name="defaultCategory" style='width: 293px;' /></td>
						</tr>   
						<tr>
							<th><? _e('Page description', 'post-index'); ?></th>
							<td><textarea class="regular-text" name="pageDescription" style='width: 293px;'><?php echo($pageDescription); ?></textarea></td>
						</tr>
						<tr>
							<th><? _e('Post label', 'post-index'); ?><br /><sub><? _e('(for non, one and many posts)', 'post-index');?></sub></th>
							<td><input type="text" value="<?php echo($postLabel[0]); ?>" class="regular-text" name="postLabel[0]" style='width: 90px;' /><input type="text" value="<?php echo($postLabel[1]); ?>" class="regular-text" name="postLabel[1]" style='width: 90px;' /><input type="text" value="<?php echo($postLabel[2]); ?>" class="regular-text" name="postLabel[2]" style='width: 90px;' /></td>
						</tr>
					</table>
				
					<h3><? _e('Additional links', 'post-index');?></h3>
					<p><? printf(__('Additional links can be added via custom fields. The sentence that is displayed below each link in the index can be build with the following text parts. Define the custom fields for the additional links with the fields at the end of this section and use them directly in your post. You can display a %s at the end of this page.', 'post-index'), '<a href="#preview">' . __('preview', 'post-index') . '</a>'); ?></p>

					<table class="form-table">
						<tr>
							<th><? _e('Sentence', 'post-index');?><br /><sub><? _e('(First part, repeated separator, last separator, last part)', 'post-index'); ?></sub></th>
							<td><input type="text" value="<?=$infoSeparator[0];?>" class="regular-text" name="infoSeparator[0]" style='width: 90px;' /><input type="text" value="<?=$infoSeparator[1];?>" class="regular-text" name="infoSeparator[1]" style='width: 50px;' /><input type="text" value="<?=$infoSeparator[2];?>" class="regular-text" name="infoSeparator[2]" style='width: 50px;' /><input type="text" value="<?=$infoSeparator[3];?>" class="regular-text" name="infoSeparator[3]" style='width: 90px;' /></td>
						</tr>
					</table>
					<br />
					<table>
						<thead>
						<tr>
							<th><? _e('Name', 'post-index'); ?></th>
							<th><? _e('Custom Field ID', 'post-index'); ?></th>
						</tr></thead>
						<tbody id="infoLinks"><?php
							$i = 0;
							if(!empty($infoLinks)) {							
								foreach($infoLinks as $name => $field) {
						?><tr id="infoLink<?=$i;?>">
							<td><input type="text" value="<?=$name;?>" class="regular-text" name="infoLinksName[<?=$i;?>]" style='width: 293px;' /></td>
							<td><input type="text" value="<?=$field;?>" class="regular-text" name="infoLinksField[<?=$i;?>]" style='width: 293px;' /></td>
							<td><a onclick="removeLine('infoLink<?=$i;?>');" class="add-new-h2"><? _e('Remove', 'post-index'); ?></a></td>
						</tr><?php
									$i++;
								}
							}
					?></tbody><tfoot>
					<tr>
						<td>
						<p><a id="addInfoLink" onclick="addInfoLink('infoLinks', <?=$i;?>);" class="add-new-h2"><? _e('Add', 'post-index'); ?></a></p>
						</td>
					</tr>
					</tfoot></table>
					<!-- move to a separate script and load it using the wp methods! -->
					<script language="javascript">
						function removeLine(childElement){
							if (document.getElementById(childElement)) {
								var child = document.getElementById(childElement);
								var parent = child.parentNode;
								
								parent.removeChild(child);
							}
							return false;
						}
						
						function addInfoLink(parentElement, lineId) {
							var parent = document.getElementById(parentElement);
							var newLine = document.createElement('tr');
							newLine.setAttribute('id', 'infoLink' + lineId);
							
							var newNameTD = document.createElement('td');
							
							var newNameInput = document.createElement('input');
							newNameInput.setAttribute('type', 'text');
							newNameInput.setAttribute('class', 'regular-text');
							newNameInput.setAttribute('name', 'infoLinksName[' + lineId + ']');
							newNameInput.setAttribute('style', 'width: 293px;');
							
							newNameTD.appendChild(newNameInput);
							newLine.appendChild(newNameTD);
							
							var newFieldTD = document.createElement('td');
							
							var newFieldInput = document.createElement('input');
							newFieldInput.setAttribute('type', 'text');
							newFieldInput.setAttribute('class', 'regular-text');
							newFieldInput.setAttribute('name', 'infoLinksField[' + lineId + ']');
							newFieldInput.setAttribute('style', 'width: 293px;');
							
							newFieldTD.appendChild(newFieldInput);
							newLine.appendChild(newFieldTD);
							
							var newRemoveButtonTD = document.createElement('td');
							var newRemoveButtonLink = document.createElement('a');
							newRemoveButtonLink.setAttribute('class', 'add-new-h2');
							newRemoveButtonLink.setAttribute('onclick', 'removeLine(\'infoLink' + lineId + '\');');
							
							var buttonName = document.createTextNode('<? _e('Remove', 'post-index'); ?>');
							newRemoveButtonLink.appendChild(buttonName);
							
							newRemoveButtonTD.appendChild(newRemoveButtonLink);
							newLine.appendChild(newRemoveButtonTD);
							
							parent.appendChild(newLine);
							
							document.getElementById('addInfoLink').setAttribute('onClick', 'addInfoLink(\'infoLinks\', ' + (lineId+1) + ');');
						}
					</script>
					<p class="submit">
						<input type="submit" class="button-primary" value="<? _e('Save Changes', 'post-index'); ?>" name="submit" />
					</p>
				</form>
				
				<div id="icon-edit-pages" class="icon32 icon32-posts-post"><br /></div>
				<a name="preview"><h2><? _e('Preview', 'post-index'); ?></h2></a>
				<br />
				<?php
					if(!empty($infoLinks)) {
						$i = 0;
						foreach($infoLinks as $name => $link) {							
							echo $this->getSeparator($i++, count($infoLinks), $infoSeparator);
							echo '<a href="#">' . $name . '</a>';
						}
						
						echo $this->getSeparator($i, count($infoLinks), $infoSeparator);
					}
				?>
			</div><?php
		}
		
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
		
		public function load() {
			$this->settings = get_option($this->optionName);
			if(is_null($this->settings) || empty($this->settings)) {
				$this->loadDefaults();
			}
		}
		
		public function save() {
			update_option($this->optionName, $this->settings);
		}
		
		public function loadDefaults() {
			$settings['defaultCategory'] = __('General', 'post-index');
			$settings['infoSeparator'] = array(__('also at ', 'post-index'), ', ', ' ' . __('und', 'post-index') . ' ', '');
			$settings['postLabel'] = array('no post', 'one post', ' ' . __('posts', 'post-index'));
			$settings['pageDescription'] = __('You will find ${PostCount} in the category ${Category} on this blog.', 'post-index');
			$settings['infoLinks'] = array ( 'Amazon' => 'url_amazon' );
			
			$this->settings = $settings;
		}
	}	
?>