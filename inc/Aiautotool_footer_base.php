<?php 
defined('ABSPATH') or die();
class Aiautotool_Footer_Base  extends rendersetting{
    private $aiautotool_footer_settings;
    public $styles = array(
						    'Footer1' => 'footer1.png',
						    'Footer2' => 'footer1.png',
						    'Footer3' => 'footer1.png',
						    'Footer4' => 'footer1.png',
						    'Footer5' => 'footer1.png',
						    'Footer6' => 'footer1.png',
						    'Footer7' => 'footer1.png',
						);
    public  $active = true;
    public  $active_option_name = 'aiautotool_footer_settings_active';
    public function __construct() {

      $this->active = get_option($this->active_option_name, true);
        if ($this->active=='true') {
            $this->init();
        }
        add_action('wp_ajax_update_active_option_canonical_'.$this->active_option_name, array($this, 'update_active_option_callback'));
        add_action('wp_ajax_nopriv_update_active_option_canonical_'.$this->active_option_name, array($this, 'update_active_option_callback'));


    	
    }

    public function init(){
      $this->aiautotool_footer_settings = get_option('aiautotool_footer_settings');
       
        
        add_action('admin_init', array($this, 'init_settings'));
        add_action('wp_footer', array($this, 'display_footer'));
        $this->display_footer();
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

    public function init_settings() {
        // Register settings
        register_setting('aiautotool_footer_settings_group', 'aiautotool_footer_settings');
        // register_setting('aiautotool_config_settings_group', 'aiautotool_config_settings');
    }
     public function render_setting() {
        ?>
        <div  id="aiautotool-auto-footer" class="tab-content" style="display:none;">
            <h1><i class="fa-solid fa-shield-halved"></i> <?php _e('Auto Footer WP', 'ai-auto-tool'); ?></h1>
             <form method="post" action="options.php">
             	<?php settings_fields('aiautotool_footer_settings_group'); ?>
             	<div class="ft-on">
				<label class="nut-fton">
				<input class="toggle-checkbox" type="checkbox" name="aiautotool_footer_settings[activefooter]" value="1" <?php if ( isset($this->aiautotool_footer_settings['activefooter']) && 1 == $this->aiautotool_footer_settings['activefooter'] ) echo 'checked="checked"'; ?> />
				<span class="ftder"></span></label>
				<label class="ft-on-right"><?php _e('ON/OFF', 'ai-auto-tool'); ?></label>
				</div>

             	<h3><i class="fa-regular fa-pen-to-square"></i> <?php _e('Input info Footer ', 'ai-auto-tool') ?></h3>
             	<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php _e('Select type Footer', 'ai-auto-tool'); ?></p>
             	<p>
					<?php 
						if (empty($this->aiautotool_footer_settings)) {
                $this->aiautotool_footer_settings = array(); // Initialize as empty array
            }

            if(!isset($this->aiautotool_footer_settings['footer_template'])){
              $this->aiautotool_footer_settings['footer_template'] = '';
            }
						?>

						<select id="footerselect" name="aiautotool_footer_settings[footer_template]" onchange="changeImage()">
						    <?php foreach($this->styles as $label => $image_path) { 
						        $selected = ($this->aiautotool_footer_settings['footer_template'] == $label) ? 'selected="selected"' : '';
						    ?>
						        <option value="<?php echo $label; ?>" <?php echo $selected; ?>><?php echo $label; ?></option> 
						    <?php } ?> 
						</select>

						<p id="selectedImage">
						    <!-- Selected image will be displayed here -->
						</p>

						<script>
						function changeImage() {
						    var select = document.getElementById("footerselect");
						    var selectedLabel = select.options[select.selectedIndex].text;
						    var selectedImage = "<?php echo $this->styles['Footer1']; ?>"; // Default image path

						    // Get the image path based on the selected label
						    if (selectedLabel in <?php echo json_encode($this->styles); ?>) {
						        selectedImage = <?php echo json_encode($this->styles); ?>[selectedLabel];
						    }

						    // Display the selected image
						    document.getElementById("selectedImage").innerHTML = '<img class="ft-footer-img-display" src="<?php echo AIAUTOTOOL_URI; ?>/images/footer/' + selectedImage + '" alt="' + selectedLabel + ' Image">';
						}
						</script>

					<p>

             	<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php _e('Title Footer, Name Company', 'ai-auto-tool'); ?></p>
             	<input class="ft-input-big" placeholder="Name Company" type="text" name="aiautotool_footer_settings[title]" value="<?php if(!empty($this->aiautotool_footer_settings['post-thum11'])){echo sanitize_text_field($this->aiautotool_footer_settings['title']);} ?>">

             	<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php _e('Hotline', 'ai-auto-tool'); ?></p>
             	<input class="ft-input-big" placeholder="+1999999999" type="text" name="aiautotool_footer_settings[hotline]" value="<?php if(!empty($this->aiautotool_footer_settings['post-thum11'])){echo sanitize_text_field($this->aiautotool_footer_settings['hotline']);} ?>">

             	<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php _e('Email', 'ai-auto-tool'); ?></p>
             	<input class="ft-input-big" placeholder="info@xxx" type="text" name="aiautotool_footer_settings[email]" value="<?php if(!empty($this->aiautotool_footer_settings['post-thum11'])){echo sanitize_text_field($this->aiautotool_footer_settings['email']);} ?>">

             	<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php _e('Address', 'ai-auto-tool'); ?></p>
             	<input class="ft-input-big" placeholder="123 abc, xyz" type="text" name="aiautotool_footer_settings[address]" value="<?php if(!empty($this->aiautotool_footer_settings['address'])){echo sanitize_text_field($this->aiautotool_footer_settings['post-thum11']);} ?>">

             	<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php _e('Link profile Facebook', 'ai-auto-tool'); ?></p>
             	<input class="ft-input-big" placeholder="#" type="text" name="aiautotool_footer_settings[facebook]" value="<?php if(!empty($this->aiautotool_footer_settings['post-thum11'])){echo sanitize_text_field($this->aiautotool_footer_settings['facebook']);} ?>">

             	<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php _e('Link profile Youtube', 'ai-auto-tool'); ?></p>
             	<input class="ft-input-big" placeholder="#" type="text" name="aiautotool_footer_settings[youtube]" value="<?php if(!empty($this->aiautotool_footer_settings['youtube'])){echo sanitize_text_field($this->aiautotool_footer_settings['post-thum11']);} ?>">

             	<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php _e('Link profile Twitter', 'ai-auto-tool'); ?></p>
             	<input class="ft-input-big" placeholder="#" type="text" name="aiautotool_footer_settings[twitter]" value="<?php if(!empty($this->aiautotool_footer_settings['post-thum11'])){echo sanitize_text_field($this->aiautotool_footer_settings['twitter']);} ?>">

             	 <?php submit_button(__( 'Save all', 'ai-auto-tool' ),'ft-submit'); ?>
             </form>
        </div>
            <?php
      }
     public function render_tab_setting() {
      if($this->active=="true"){
         echo '<button href="#aiautotool-auto-footer" class="nav-tab sotab"><i class="fa-solid fa-shield-halved"></i> '.__('Auto Footer WP','ai-auto-tool').'</button>';
       }
    }

    public function render_feature() {
      $autoToolBox = new AutoToolBox(__('Auto Footer Aiautotool','ai-auto-tool'), "Add footer auto for site.", "#", $this->active_option_name, $this->active,plugins_url('../images/logo.svg', __FILE__));

        echo $autoToolBox->generateHTML();
      
    }

    public function render_other_setting() {
        $other_setting = get_option('other_setting', '');
        echo '<input type="text" name="other_setting" value="' . esc_attr($other_setting) . '">';
    }

    

    public function display_footer() {
    	if(isset($this->aiautotool_footer_settings['activefooter'])){
    		$selected_template = isset($this->aiautotool_footer_settings['footer_template']) ? $this->aiautotool_footer_settings['footer_template'] : 'Footer1';

	        switch ($selected_template) {
	            case 'Footer1':
	            add_action('wp_footer', array($this,'display_template1'));
	                
	                break;
	            case 'Footer2':
	            add_action('wp_footer', array($this,'display_template2'));
	            
	                break;
	            case 'Footer3':
	                add_action('wp_footer', array($this,'display_template3'));
	            
	                break;
	            case 'Footer4':
	                add_action('wp_footer', array($this,'display_template4'));
	            
	                break;
	            case 'Footer5':
	                add_action('wp_footer', array($this,'display_template5'));
	            
	                break;
	            case 'Footer6':
	                add_action('wp_footer', array($this,'display_template6'));
	            
	                break;
	            case 'Footer7':
	                add_action('wp_footer', array($this,'display_template7'));
	            
	                break;
    	}
        
            // Add more cases for additional templates
        }
    }

    // Define functions to display each template
    public function display_template1() {
      ob_start();
       ?>
       <style type="text/css">

       	:root{
   			scrollbar-color: var(--scroll) #ffffff00;
			scrollbar-width: thin !important;
			--body:#e4e7ec;
			 --card:#ffffff;
			 --card-shadow:0 0px 0px rgba(0, 0, 0, 0.2);
			 --shadow:0px 1px 7px #0000004a;
			 --htext:#333333;
			 --text:#333333;
			 --texta:#0768ea;
			 --textnote:#555;
			 --border:1px solid #ccc;
			 --border-one:1px solid #ccc;
			 --border-hat:1px dashed #ccc;
			 --comment:#fff;
			 --note:#f5f5f5;
			 --note-light:#f5f5f5;
			 --menu:#eee;
			 --menu-mobile:#ddd;
			 --menu-mobile-chil:#fff;
			 --menu-border:2px dashed #ccc;
			 --down-border:2px solid #0768ea;
			 --scroll:#ccc;
			 --bar:#fff;
			 --menu-duoi:#ffffff;
			 --input:#eee;
			 --card-tran:#ffffff82;
}
* {
 scrollbar-color:var(--scroll) #ffffff00;
 scrollbar-width:thin !important;
}
       
.aiautotool_footer_1_site{
	background-color: #007f57;
}
.aiautotool_footer_1_site-footer {
  background-color: #007f57;
  color: white;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  padding: 20px;
}

.aiautotool_footer_1_footer-column {
  text-align: left;
}

.aiautotool_footer_1_footer-column h3 {
  border-bottom: 2px solid white;
  padding-bottom: 5px;
}

.aiautotool_footer_1_footer-column p {
  margin: 5px 0;
}
.aiautotool_footer_1_footer-column p a{
  color:#;
}

.aiautotool_footer_1_footer-column svg{
	width:20px;
	height:20px;
}
@media only screen and (max-width: 768px) {
  .aiautotool_footer_1_site-footer {
    grid-template-columns: 1fr; 
  }

  
}
.progress-bar {
  background: linear-gradient(90deg, rgba(128,0,255,0) 0%, var(--texta) 100%);
  height: 4px;
  border-radius: 10px;
  width: 100%;
  transform-origin: left;
 -webkit-animation : grow 10s linear infinite;
  animation: grow 10s linear infinite;
}
.progress-bar {
 background: linear-gradient(90deg, rgba(128,0,255,0) 0%, #ffffff6b 100%);
}
@keyframes grow {
  0% {
    transform: scaleX(0);
  }
}

@-webkit-keyframes grow {
  0% {
    -webkit-transform: scaleX(0);
  }
}
  </style>

  <div class="aiautotool_footer_1_site">
  	<div class="progress-bar"></div>


  <footer class="aiautotool_footer_1_site-footer">
  	
    <div class="aiautotool_footer_1_footer-column">
      <h3>Contact Information</h3>
      <p><?php if(isset($this->aiautotool_footer_settings['title'])){ echo $this->aiautotool_footer_settings['title'] ==''? get_bloginfo():$this->aiautotool_footer_settings['title']; }else{ echo get_bloginfo(); }  ?></p>
      <p><?php if(isset($this->aiautotool_footer_settings['email'])){  echo $this->aiautotool_footer_settings['email'] ==''? esc_html('info@').parse_url(home_url(), PHP_URL_HOST):$this->aiautotool_footer_settings['email'];}else{ echo esc_html('info@').parse_url(home_url(), PHP_URL_HOST);} ?></p>
      <p><?php _e('Hotline','ai-auto-tool');?>: <?php if(isset($this->aiautotool_footer_settings['hotline'])){ echo $this->aiautotool_footer_settings['hotline'] ==''? esc_html('+1999838321'):$this->aiautotool_footer_settings['hotline'];}else{ echo esc_html('+1999838321'); } ?></p>
      <p><?php _e('Address','ai-auto-tool');?>: <?php if(isset($this->aiautotool_footer_settings['address'])){ echo $this->aiautotool_footer_settings['address'] ==''? esc_html('Your Address'):$this->aiautotool_footer_settings['address'];}else{ echo esc_html('Your Address'); } ?></p>
      <p>© <?php echo date('Y').' '.parse_url(home_url(), PHP_URL_HOST)  ?> </p>
    </div>

    <div class="aiautotool_footer_1_footer-column">
      <h3>Information</h3>
      <p>Privacy Policy</p>
      <p>Contact Us</p>
      <p>Mission Statement</p>
    </div>

    <div class="aiautotool_footer_1_footer-column">
      <h3>Profile</h3>

      <p><a href="<?php if(isset($this->aiautotool_footer_settings['facebook'])){  echo $this->aiautotool_footer_settings['facebook'] ==''? esc_html('#'):$this->aiautotool_footer_settings['facebook'];}else{ echo esc_html('#'); } ?>"><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="10" height="10" viewBox="0 0 48 48">
<path fill="#039be5" d="M24 5A19 19 0 1 0 24 43A19 19 0 1 0 24 5Z"></path><path fill="#fff" d="M26.572,29.036h4.917l0.772-4.995h-5.69v-2.73c0-2.075,0.678-3.915,2.619-3.915h3.119v-4.359c-0.548-0.074-1.707-0.236-3.897-0.236c-4.573,0-7.254,2.415-7.254,7.917v3.323h-4.701v4.995h4.701v13.729C22.089,42.905,23.032,43,24,43c0.875,0,1.729-0.08,2.572-0.194V29.036z"></path>
</svg>  Facebook</a></p>
      <p><a href="<?php if(isset($this->aiautotool_footer_settings['youTube'])){  echo $this->aiautotool_footer_settings['youTube'] ==''? esc_html('#'):$this->aiautotool_footer_settings['youTube'];}else{ echo  esc_html('#');} ?>"><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100" viewBox="0 0 48 48">
<path fill="#FF3D00" d="M43.2,33.9c-0.4,2.1-2.1,3.7-4.2,4c-3.3,0.5-8.8,1.1-15,1.1c-6.1,0-11.6-0.6-15-1.1c-2.1-0.3-3.8-1.9-4.2-4C4.4,31.6,4,28.2,4,24c0-4.2,0.4-7.6,0.8-9.9c0.4-2.1,2.1-3.7,4.2-4C12.3,9.6,17.8,9,24,9c6.2,0,11.6,0.6,15,1.1c2.1,0.3,3.8,1.9,4.2,4c0.4,2.3,0.9,5.7,0.9,9.9C44,28.2,43.6,31.6,43.2,33.9z"></path><path fill="#FFF" d="M20 31L20 17 32 24z"></path>
</svg> YouTube</a></p>
      <p><a href="<?php if(isset($this->aiautotool_footer_settings['facebook'])){  echo $this->aiautotool_footer_settings['twitter'] ==''? esc_html('#'):$this->aiautotool_footer_settings['facebook'];}else{ echo esc_html('#');} ?>"><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100" viewBox="0 0 48 48">
<path fill="#03A9F4" d="M42,12.429c-1.323,0.586-2.746,0.977-4.247,1.162c1.526-0.906,2.7-2.351,3.251-4.058c-1.428,0.837-3.01,1.452-4.693,1.776C34.967,9.884,33.05,9,30.926,9c-4.08,0-7.387,3.278-7.387,7.32c0,0.572,0.067,1.129,0.193,1.67c-6.138-0.308-11.582-3.226-15.224-7.654c-0.64,1.082-1,2.349-1,3.686c0,2.541,1.301,4.778,3.285,6.096c-1.211-0.037-2.351-0.374-3.349-0.914c0,0.022,0,0.055,0,0.086c0,3.551,2.547,6.508,5.923,7.181c-0.617,0.169-1.269,0.263-1.941,0.263c-0.477,0-0.942-0.054-1.392-0.135c0.94,2.902,3.667,5.023,6.898,5.086c-2.528,1.96-5.712,3.134-9.174,3.134c-0.598,0-1.183-0.034-1.761-0.104C9.268,36.786,13.152,38,17.321,38c13.585,0,21.017-11.156,21.017-20.834c0-0.317-0.01-0.633-0.025-0.945C39.763,15.197,41.013,13.905,42,12.429"></path>
</svg> Twitter</a></p>
    </div>
  </footer>
    </div>
       <?php
       echo ob_get_clean();   
    }

    public function display_template2() {
        ?>
      
       <?php
    }

    public function display_template3() {
        ?>
       
       <?php
    }
    public function display_template4() {
        ?>
       
       <?php
    }
    public function display_template5() {
        ?>
       
       <?php
    }
    public function display_template6() {
        ?>
       
       <?php
    }
    public function display_template7() {
        ?>
       
       <?php
    }
}