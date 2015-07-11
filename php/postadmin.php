<?php
   class PostAdminWidget {
      private $pluginSettings;
      
      public function __construct($pluginSettings) {
         add_action('add_meta_boxes', array($this, 'addCustomBox'));
         add_action('save_post', array($this, 'savePostData'));
         
         $this->pluginSettings = $pluginSettings;
      }
      
      public function addCustomBox() {
         add_meta_box( POST_INDEX_PLUGIN_PREFIX.'sectionid'
                     , __('Post Index - Additional links', 'post-index')
                     , array($this, 'innerCustomBox')
                     , 'post' 
                     );
      }
      
      /**
       * Clone of post-index.php
       */
      function getCustomFieldValue($postId, $fieldName, $alternative) {
         $customFieldValues = get_post_custom_values($fieldName, $postId);
         if(is_null($customFieldValues)) {
            return $alternative;
         }
		
         return current($customFieldValues);
      }
      
      function setCustomFieldValue($postId, $fieldName, $value) {
         add_post_meta($postId, $fieldName, $value, true) or update_post_meta($postId, $fieldName, $value);
      }
      
      function deleteCustomField($postId, $fieldName) {
         delete_post_meta($postId, $fieldName);
      }
      
      public function innerCustomBox($post) {
         //TODO settings-link u.U. nur, wenn entsprechende Rechte vorhanden sind!
         if(empty($this->pluginSettings->settings) || empty($this->pluginSettings->settings['infoLinks'])) {
            _e('No additional links defined yet!', 'post-index');
            echo ' <a href="'.$this->pluginSettings->GetPluginOptionsURL().'">' . __('Settings') . '</a>';
            return;
         } 
         $settings = $this->pluginSettings->settings;
         
         wp_nonce_field( POST_INDEX_PLUGIN_BASENAME, POST_INDEX_PLUGIN_PREFIX.'noncename' );
         
         ?><table class="form-table"><?php
         foreach($settings['infoLinks'] as $name => $field) {
            $value = $this->getCustomFieldValue($post->ID, $field, '');
            ?><tr>
               <td><label for="<?=POST_INDEX_PLUGIN_PREFIX . $field;?>" style="white-space:nowrap;"><?=$name;?></label></td><td style="width: 100%;"><input type="text" class="regular-text" value="<?=$value;?>" name="<?=POST_INDEX_PLUGIN_PREFIX . $field;?>" style="width: 100%;" /></td>
            </tr><?php
         }
         ?></table>
         <?php
      }
      
      public function savePostData( $postId ) {
         // verify if this is an auto save routine. 
         // If it is our form has not been submitted, so we dont want to do anything
         if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
            return;

         // verify this came from the our screen and with proper authorization,
         // because save_post can be triggered at other times
         if ( !wp_verify_nonce( isset($_POST[POST_INDEX_PLUGIN_PREFIX.'noncename']) ? $_POST[POST_INDEX_PLUGIN_PREFIX.'noncename'] : '', POST_INDEX_PLUGIN_BASENAME ) )
            return;
  
         // Check permissions
         if ( 'page' == $_POST['post_type'] ) {
            if ( !current_user_can( 'edit_page', $postId ) )
               return;
         } else {
            if ( !current_user_can( 'edit_post', $postId ) )
               return;
         }
         
         $settings = $this->pluginSettings->settings;
         
         foreach($settings['infoLinks'] as $name => $field) {
            $url = $_POST[POST_INDEX_PLUGIN_PREFIX . $field];
            if(!empty($url)) {
               $this->setCustomFieldValue($postId, $field, $url);
            } else {
               $this->deleteCustomField($postId, $field);
            }
         }
      }
   }
?>