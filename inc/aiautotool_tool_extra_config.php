<?php  
defined('ABSPATH') or die();
class Aiautotool_tool_extra_config extends rendersetting{
	 public  $active = true;
    public  $active_option_name = 'Aiautotool_tool_extra_config_active';
    public $aiautotool_config_settings;
    public function __construct() {

    	 $this->active = get_option($this->active_option_name, true);
        if ($this->active=='true') {
            $this->init();
        }
        add_action('wp_ajax_update_active_option_canonical_'.$this->active_option_name, array($this, 'update_active_option_callback'));
        add_action('wp_ajax_nopriv_update_active_option_canonical_'.$this->active_option_name, array($this, 'update_active_option_callback'));



    }


    public function update_active_option_callback() {
        check_ajax_referer('aiautotool_nonce', 'security');
        if (isset($_POST['active'])) {
            $active = sanitize_text_field($_POST['active']);
            update_option($this->active_option_name, $active,null, 'no');
            print_r($active);
        }

        wp_die();
    }
    function init(){
    	$this->aiautotool_config_settings = get_option('aiautotool_config_settings',array());

    	if (empty($this->aiautotool_config_settings)) {
	        // Set default values for the aiautotool_config_settings array
	        $default_settings = array(
	            'tool-blockcomment' => 1,
	            'tool-edit1' => 1,
	            'tool-edit11' => 1,
	            'tool-widget1' => 1,
	            'tool-mana2' => 1,
	            'tool-mana3' => 1,
	            'speed-off1' => 1,
	            'speed-off2' => 1,
	            'speed-off3' => 1,
	            'speed-off4' => 1,
	            'speed-link1' => 1,
	            'speed-link2' => 1,
	            'speed-zip1' => 1,
	            'scuri-off2' => 1,
	            'scuri-off3' => 1,
	            'scuri-off4' => 1,
	            'scuri-off5' => 1
	        );

	        // Use array_merge to combine default settings with existing settings
	        $this->aiautotool_config_settings = array_merge((array)$default_settings, (array)$this->aiautotool_config_settings);

	        // Save the default settings to the database
	        update_option('aiautotool_config_settings', $this->aiautotool_config_settings,null, 'no');
	    }
        
        add_action('admin_init', array($this, 'init_settings'));
    }

   

    public function init_settings() {
        // Register settings
        register_setting('aiautotool_extra_config_group', 'aiautotool_config_settings');
        // register_setting('aiautotool_config_settings_group', 'aiautotool_config_settings');
    }

    public function render_setting() {
    	// print_r($this->aiautotool_config_settings);
        ?>
        <div  id="tool-extra-config" class="tab-content" style="display:none;">
            <h1><i class="fa-solid fa-shield-halved"></i> <?php esc_html_e('Extra Config Settings', 'ai-auto-tool'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('aiautotool_extra_config_group'); ?>
                
                <h2></h2>
				
				
				
				<div id="play1" class="toggle-div ft-card">

				<!-- tool editor -->
				<h3><i class="fa-regular fa-pen-to-square"></i> <?php esc_html_e('Editor ', 'ai-auto-tool') ?></h3>

					<!-- tool class 1 -->
					<!-- <label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-playarticle]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-playarticle']) && 1 ==  $this->aiautotool_config_settings['tool-playarticle'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Add button Speak article', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('If active , a button speak on befor content in Article.', 'ai-auto-tool'); ?></p> -->


					<!-- tool class 1 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-blockcomment]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-blockcomment']) && 1 ==  $this->aiautotool_config_settings['tool-blockcomment'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Block All Comment', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('If active ,Block all Comment to post.', 'ai-auto-tool'); ?></p>


					<!-- tool class 1 -->
					<!-- <label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-404img]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-404img']) && 1 ==  $this->aiautotool_config_settings['tool-404img'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Fix image 404', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('If active , when post content, auto check image 404 and remove it.', 'ai-auto-tool'); ?></p> -->

					<!-- tool class 1 -->
					<!-- <label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-schedulecheckimg404]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-schedulecheckimg404']) && 1 ==  $this->aiautotool_config_settings['tool-schedulecheckimg404'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Schedule run check image 404 and fix thumb', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('If active , Schedule run check image 404 and fix thumb.', 'ai-auto-tool'); ?></p> -->


					<!-- tool class 1 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-edit1]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-edit1']) && 1 ==  $this->aiautotool_config_settings['tool-edit1'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Turn On Editor Classic', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('Active Editor Classic for new post and edit', 'ai-auto-tool'); ?></p>
					<!-- tool class 11 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-edit11]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-edit11']) && 1 ==  $this->aiautotool_config_settings['tool-edit11'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Add button for Editor Classic', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('add button fontselect, fontsizeselect, separator, table for Editor', 'ai-auto-tool'); ?></p>
					
					<!-- tool class 1 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-widget1]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-widget1']) && 1 ==  $this->aiautotool_config_settings['tool-widget1'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Turn On Widget Classic', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('Active Old Widget Classic', 'ai-auto-tool'); ?></p>
					
				  <h3><i class="fa-solid fa-store-slash"></i> <?php esc_html_e('Disable Update auto', 'ai-auto-tool') ?></h3>
					<!-- tool off upload 1 -->
					<p>
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-upload1]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-upload1']) && 1 ==  $this->aiautotool_config_settings['tool-upload1'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Disable Update Core', 'ai-auto-tool'); ?></label>
					</p>
					<p>
					<!-- tool off upload 2 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-upload2]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-upload2']) && 1 ==  $this->aiautotool_config_settings['tool-upload2'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Disable Update Language Packet', 'ai-auto-tool'); ?></label>
					</p>
					<p>
					<!-- tool off upload 3 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-upload3]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-upload3']) && 1 ==  $this->aiautotool_config_settings['tool-upload3'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Disable Update Themes', 'ai-auto-tool'); ?></label>
					</p>
					<p>
					<!-- tool off upload 4 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-upload4]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-upload4']) && 1 ==  $this->aiautotool_config_settings['tool-upload4'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Disable Update Plugin', 'ai-auto-tool'); ?></label>
					</p>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('Turn on/off auto Update of WordPress', 'ai-auto-tool'); ?></p>	
					
				  <h3><i class="fa-solid fa-angles-right"></i> <?php esc_html_e('Redirect and Block copy content', 'ai-auto-tool') ?></h3>
					<!-- tool manager 1 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-mana1]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-mana1']) && 1 ==  $this->aiautotool_config_settings['tool-mana1'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('301 page 404 to Home', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('This feature allows you to redirect non-existent links (404 error) to the homepage', 'ai-auto-tool'); ?></p>
					
					<!-- tool manager 2 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-mana2]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-mana2']) && 1 ==  $this->aiautotool_config_settings['tool-mana2'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Block copy content and using Devtool', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('This feature prevents users from copying text, right-clicking, and accessing Devtool', 'ai-auto-tool'); ?></p>
					
					<!-- tool manager 2 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-mana3]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-mana3']) && 1 ==  $this->aiautotool_config_settings['tool-mana3'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Turn On Editor classic for Description in categories page', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('This feature allows you to add Editor classic to the article and product category description box when you edit', 'ai-auto-tool'); ?></p>
				  
				  <h3><i class="fa-regular fa-eye-slash"></i> <?php esc_html_e('Hide the tools on page admin', 'ai-auto-tool') ?></h3>
					<!-- tool hiden 1 -->
					<p>
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-hiden1]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-hiden1']) && 1 ==  $this->aiautotool_config_settings['tool-hiden1'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Hide News Feed', 'ai-auto-tool'); ?></label>
					</p>
					<!-- tool hiden 2 -->
					<p>
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-hiden2]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-hiden2']) && 1 ==  $this->aiautotool_config_settings['tool-hiden2'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Hide Posts', 'ai-auto-tool'); ?></label>
					</p>
					<!-- tool hiden 3 -->
					<p>
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-hiden3]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-hiden3']) && 1 ==  $this->aiautotool_config_settings['tool-hiden3'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Hide Page', 'ai-auto-tool'); ?></label>
					</p>
					<!-- tool hiden 4 -->
					<p>
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-hiden4]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-hiden4']) && 1 ==  $this->aiautotool_config_settings['tool-hiden4'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Hide Feedback', 'ai-auto-tool'); ?></label>
					</p>
					<!-- tool hiden 5 -->
					<p>
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-hiden5]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-hiden5']) && 1 ==  $this->aiautotool_config_settings['tool-hiden5'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Hide Media', 'ai-auto-tool'); ?></label>
					</p>
					<!-- tool hiden 6 -->
					<p>
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-hiden6]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-hiden6']) && 1 ==  $this->aiautotool_config_settings['tool-hiden6'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Hide Interface', 'ai-auto-tool'); ?></label>
					</p>
					<!-- tool hiden 7 -->
					<p>
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-hiden7]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-hiden7']) && 1 ==  $this->aiautotool_config_settings['tool-hiden7'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Hide Plugins', 'ai-auto-tool'); ?></label>
					</p>
					<!-- tool hiden 8 -->
					<p>
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-hiden8]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-hiden8']) && 1 ==  $this->aiautotool_config_settings['tool-hiden8'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Hide User', 'ai-auto-tool'); ?></label>
					</p>
					<!-- tool hiden 9 -->
					<p>
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-hiden9]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-hiden9']) && 1 ==  $this->aiautotool_config_settings['tool-hiden9'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Hide tools', 'ai-auto-tool'); ?></label>
					</p>
					<!-- tool hiden 10 -->
					<p>
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[tool-hiden10]" value="1" <?php if ( isset( $this->aiautotool_config_settings['tool-hiden10']) && 1 ==  $this->aiautotool_config_settings['tool-hiden10'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Hide setup', 'ai-auto-tool'); ?></label>
					</p>
					
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('If you feel that the above tools are unnecessary, you can hide them to give the WP admin a clearer view. This function only hides and does not block access to the link.', 'ai-auto-tool'); ?></p>
				  


				<!-- end tool editor -->
				  <h3><i class="fa-regular fa-square-minus"></i> <?php esc_html_e('Turn off unnecessary items', 'ai-auto-tool') ?></h3>
					<!-- tôi ưu 1 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[speed-off1]" value="1" <?php if ( isset($this->aiautotool_config_settings['speed-off1']) && 1 == $this->aiautotool_config_settings['speed-off1'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Turn off jQuery Migrate', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('jQuery Migrate is a library used to maintain the functionality of some themes and plugins using outdated code. If your website no longer uses this library, you can turn it off', 'ai-auto-tool'); ?></p>
					<!-- tôi ưu 2 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[speed-off2]" value="1" <?php if ( isset($this->aiautotool_config_settings['speed-off2']) && 1 == $this->aiautotool_config_settings['speed-off2'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Turn off Gutenberg CSS', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('If you don\'t use it, you can turn off Gutenberg CSS on the homepage', 'ai-auto-tool'); ?></p>
					<!-- tôi ưu 3 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[speed-off3]" value="1" <?php if ( isset($this->aiautotool_config_settings['speed-off3']) && 1 == $this->aiautotool_config_settings['speed-off3'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Turn off Classic CSS', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('If you don\'t use it, you can turn off Classic CSS on the homepage', 'ai-auto-tool'); ?></p>
					<!-- tôi ưu 4 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[speed-off4]" value="1" <?php if ( isset($this->aiautotool_config_settings['speed-off4']) && 1 == $this->aiautotool_config_settings['speed-off4'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Turn off Emoji', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('If you don\'t use it, you can turn off Emoji', 'ai-auto-tool'); ?></p>
					
				  <h3><i class="fa-brands fa-square-js"></i> <?php esc_html_e('Optimization libraries core WP', 'ai-auto-tool') ?></h3>
					<!-- thư vien js 1 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[speed-link1]" value="1" <?php if ( isset($this->aiautotool_config_settings['speed-link1']) && 1 == $this->aiautotool_config_settings['speed-link1'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Enable Instant-page', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('Instant-page is a library that allows you to preload the content of a page into the browser memory simply by hovering over any link on the page. When you click the link, it provides a fast and seamless experience', 'ai-auto-tool'); ?></p>
					<!-- thư vien js 2 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[speed-link2]" value="1" <?php if ( isset($this->aiautotool_config_settings['speed-link2']) && 1 == $this->aiautotool_config_settings['speed-link2'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Enable Smooth-scroll', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('Smooth-scroll is a library that allows you to create smooth scrolling effects, making the website appear faster to users', 'ai-auto-tool'); ?></p>
					
				  <h3><i class="fa-regular fa-file-zipper"></i> <?php esc_html_e('Compress HTML output', 'ai-auto-tool') ?></h3>
					<!-- nén 1 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[speed-zip1]" value="1" <?php if ( isset($this->aiautotool_config_settings['speed-zip1']) && 1 == $this->aiautotool_config_settings['speed-zip1'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Enable HTML compression', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('With this function, HTML will be compressed into a single line, removing unnecessary characters and spaces to speed up page loading', 'ai-auto-tool'); ?></p>
					
				<!-- scurity -->
				<h3><i class="fa-regular fa-badge-check"></i> <?php esc_html_e('Optimize Security For WP', 'ai-auto-tool') ?></h3>
					<!-- scuri off 1 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[scuri-off1]" value="1" <?php if ( isset($this->aiautotool_config_settings['scuri-off1']) && 1 == $this->aiautotool_config_settings['scuri-off1'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Disable REST API', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('Nếu bạn không sử dụng REST API thì nên Disable đi để bảo mật website', 'ai-auto-tool'); ?></p>
					<!-- scuri off 2 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[scuri-off2]" value="1" <?php if ( isset($this->aiautotool_config_settings['scuri-off2']) && 1 == $this->aiautotool_config_settings['scuri-off2'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Disable XML RPC', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('Disable XML RPC ', 'ai-auto-tool'); ?></p>
					<!-- scuri off 3 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[scuri-off3]" value="1" <?php if ( isset($this->aiautotool_config_settings['scuri-off3']) && 1 == $this->aiautotool_config_settings['scuri-off3'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Disable Wp-Embed', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('Disable Wp-Embed', 'ai-auto-tool'); ?></p>
					<!-- scuri off 4 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[scuri-off4]" value="1" <?php if ( isset($this->aiautotool_config_settings['scuri-off4']) && 1 == $this->aiautotool_config_settings['scuri-off4'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Disable X-Pingback', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('Disable X-Pingback', 'ai-auto-tool'); ?></p>
					<!-- scuri off 5 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[scuri-off5]" value="1" <?php if ( isset($this->aiautotool_config_settings['scuri-off5']) && 1 == $this->aiautotool_config_settings['scuri-off5'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Disable info feed_links_extra, rsd_link, wlwmanifest_link, wp_generator, start_post_rel_link, index_rel_link, parent_post_rel_link, adjacent_posts_rel_link_wp_head in tag <head> ', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e(' Disable feed_links_extra, rsd_link, wlwmanifest_link, wp_generator, start_post_rel_link, index_rel_link, parent_post_rel_link, adjacent_posts_rel_link_wp_head in tag <head>', 'ai-auto-tool'); ?></p>
					<!-- scuri off 6 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[scuri-off6]" value="1" <?php if ( isset($this->aiautotool_config_settings['scuri-off6']) && 1 == $this->aiautotool_config_settings['scuri-off6'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Disable Feed - Rss', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('Disable feed, Rss,Atom, Rss2 comments, Atom Comments', 'ai-auto-tool'); ?></p>


				

				  <h3><i class="fa-regular fa-badge-check"></i> <?php esc_html_e('Remove version', 'ai-auto-tool') ?></h3>
					<!-- scuri ver off 1 -->
					<p>
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[scuri-verof1]" value="1" <?php if ( isset($this->aiautotool_config_settings['scuri-verof1']) && 1 == $this->aiautotool_config_settings['scuri-verof1'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Remove version JS, CSS', 'ai-auto-tool'); ?></label>
					</p>
					<!-- scuri ver off 2 -->
					<p>
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[scuri-verof2]" value="1" <?php if ( isset($this->aiautotool_config_settings['scuri-verof2']) && 1 == $this->aiautotool_config_settings['scuri-verof2'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Remove version WordPress', 'ai-auto-tool'); ?></label>
					</p>
					
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('To prevent hackers from probing and exploiting vulnerabilities in outdated JS, CSS, WordPress versions, it is advisable to conceal the version information to make it more challenging for them to identify the specific versions you are using.', 'ai-auto-tool'); ?></p>
					
				  <h3><i class="fa-regular fa-badge-check"></i> <?php esc_html_e('Block SQL injection, cross-site scripting (XSS) from request', 'ai-auto-tool') ?></h3>
					<!-- SQL injection -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[scuri-sql1]" value="1" <?php if ( isset($this->aiautotool_config_settings['scuri-sql1']) && 1 == $this->aiautotool_config_settings['scuri-sql1'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Block SQL injection, cross-site scripting (XSS)', 'ai-auto-tool'); ?></label>
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('This feature helps protect the website against attacks such as SQL injection and cross-site scripting (XSS).', 'ai-auto-tool'); ?></p>
				<!-- end scurity -->
				  <h3><i class="fa-regular fa-database"></i> <?php esc_html_e('Optimize post content in the database', 'ai-auto-tool') ?></h3>
					<!-- csdl 1 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[speed-data1]" value="1" <?php if ( isset($this->aiautotool_config_settings['speed-data1']) && 1 == $this->aiautotool_config_settings['speed-data1'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Enable limit for saved versions', 'ai-auto-tool'); ?></label>
					
					<p>
					<input class="ft-input-small" placeholder="3" name="aiautotool_config_settings[speed-data11]" type="number" value="<?php if(!empty($this->aiautotool_config_settings['speed-data11'])){echo esc_attr($this->aiautotool_config_settings['speed-data11']);} ?>"/>
    		        <label class="ft-label-right"><?php esc_html_e('Enter the number of saved versions', 'ai-auto-tool'); ?></label>
					</p>
					
					<!-- csdl 2 -->
					<label class="nut-switch">
					<input type="checkbox" name="aiautotool_config_settings[speed-data2]" value="1" <?php if ( isset($this->aiautotool_config_settings['speed-data2']) && 1 == $this->aiautotool_config_settings['speed-data2'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>
					<label class="ft-label-right"><?php esc_html_e('Change Time save post auto', 'ai-auto-tool'); ?></label>
					
					<p>
					<input class="ft-input-small" placeholder="1" name="aiautotool_config_settings[speed-data21]" type="number" value="<?php if(!empty($this->aiautotool_config_settings['speed-data21'])){echo esc_attr($this->aiautotool_config_settings['speed-data21']);} ?>"/>
    		        <label class="ft-label-right"><?php esc_html_e('Save time (minutes)', 'ai-auto-tool'); ?></label>
					</p>
					
					<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('If you enable this function and set automatic limits and save time for posts or pages, it will reduce the data stored in the database', 'ai-auto-tool'); ?></p>
					
					
				  <h3><i class="fa-regular fa-trash-can"></i> <?php esc_html_e('Delete trash for database optimization', 'ai-auto-tool'); ?></h3>
				    <div class="ft-del">
					<a class="delete-post-csdl" href="#" id="delete-revisions"><i class="fa-regular fa-trash-can"></i> <?php esc_html_e('Delete revisions', 'ai-auto-tool'); ?></a>
					<a class="delete-post-csdl" href="#" id="delete-auto-drafts"><i class="fa-regular fa-trash-can"></i> <?php esc_html_e('Delete autosave', 'ai-auto-tool'); ?></a>
					<a class="delete-post-csdl" href="#" id="delete-all-trashed-posts"><i class="fa-regular fa-trash-can"></i> <?php esc_html_e('Empty trash', 'ai-auto-tool'); ?></a>
					<div id="del-result"></div>
					<script>
						jQuery(document).ready(function($) {
							// xoa revisions
							$('#delete-revisions').click(function(event) {
								event.preventDefault();
								$.ajax({
									url: '<?php echo esc_url(admin_url('admin-ajax.php'));?>',
									type: 'POST',
									data: {
										action: 'delete_revisions',
										security:aiautotool_js_setting.security
									},
									success: function(response) {
										$('#del-result').html('<span><?php esc_html_e('Delete revisions success', 'ai-auto-tool'); ?></span>');
									},
									error: function(response) {
										$('#del-result').html('<span><?php esc_html_e('Error. Delete not success!', 'ai-auto-tool'); ?></span>');
									}
								});
							});
							// xoa auto-drafts
							$('#delete-auto-drafts').click(function(event) {
								event.preventDefault();
								$.ajax({
									url: '<?php echo esc_url(admin_url('admin-ajax.php'));?>',
									type: 'POST',
									data: {
										action: 'delete_auto_drafts',
										security:aiautotool_js_setting.security
									},
									success: function(response) {
										$('#del-result').html('<span><?php esc_html_e('Delete success all drafts', 'ai-auto-tool'); ?></span>');
									},
									error: function(response) {
										$('#del-result').html('<span><?php esc_html_e('Error. Delete not success!', 'ai-auto-tool'); ?></span>');
									}
								});
							});
							// xoa tat ca trong thung rac
							$('#delete-all-trashed-posts').click(function(event) {
								event.preventDefault();
								$.ajax({
									url: '<?php echo esc_url(admin_url('admin-ajax.php'));?>',
									type: 'POST',
									data: { 
										action: 'delete_all_trashed_posts',
										security:aiautotool_js_setting.security
									},
									success: function(response) {
										$('#del-result').html('<span><?php esc_html_e('Delete success all Trash', 'ai-auto-tool'); ?></span>');
									},
									error: function(response) {
										$('#del-result').html('<span><?php esc_html_e('Error. Delete not success!', 'ai-auto-tool'); ?></span>');
									}
								});
							});
						});
					</script>
					</div>
					
				</div>	
                <?php submit_button(esc_html__( 'Save all', 'ai-auto-tool' ),'ft-submit'); ?>
            </form>
        </div>
        <?php
    }

    public function render_tab_setting() {
    	if($this->active=="true"){
         echo '<button href="#tool-extra-config" class="nav-tab sotab"><i class="fa-solid fa-shield-halved"></i> '.esc_html__('Optimize WP','ai-auto-tool').'</button>';
     	}
    }

    public function render_feature() {

       $autoToolBox = new AutoToolBox(__('Tool Extra Config','ai-auto-tool'), __('Total tool mini for wp','ai-auto-tool'), "#", $this->active_option_name, $this->active,plugins_url('../images/logo.svg', __FILE__));

         echo ($autoToolBox->generateHTML());
    }

    public function render_other_setting() {
        $other_setting = get_option('other_setting', '');
        echo '<input type="text" name="other_setting" value="' . esc_attr($other_setting) . '">';
    }
}