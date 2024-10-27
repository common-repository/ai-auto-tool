<?php 
/*
Plugin Name: Ai Auto Tool Content Writing Assistant (Gemini Bard Writer, ChatGPT ) All in One
Plugin URI: https://aiautotool.com
Description: The AI Auto Tool Plugin is a powerful tool that automates various tasks for effortless content creation and management.
Author: KCT
Version: 2.1.2
Author URI: https://aiautotool.com
License: GPL2
*/

defined( 'ABSPATH' ) || exit;
define( 'MENUSUBPARRENT','ai_auto_tool' );
define('AIAUTOTOOL_URI', plugin_dir_url( __FILE__ ));
define('AIAUTOTOOL_VS', '2.1.2');

define('AIAUTOTOOL_DIR', plugin_dir_path( __FILE__ ));
define('AIAUTOTOOL_BASENAME', plugin_basename( __FILE__ ));

define('AIAUTOTOOL_FREE', 30);
define('AIAUTOTOOL_API_GEM','https://bard.aitoolseo.com');
define('AIAUTOTOOL_API_GTP','https://bard.aiautotool.com');

    

 
if ( ! function_exists( 'aiautotool_premium' ) ) {
    function aiautotool_premium() {
        global $aiautotool_premium;
        
        if ( ! isset( $aiautotool_premium ) ) {
            
            require_once dirname(__FILE__) . '/vendor/freemius/start.php';

            $aiautotool_premium = fs_dynamic_init( array(
                'id'                  => '15096',
                'slug'                => 'ai-auto-tool',
                'premium_slug'        => 'ai-auto-tool-pay',
                'type'                => 'plugin',
                'public_key'          => 'pk_a1bb06b76f38f9ff1132089d99b21',
                'is_premium'          => false,
                'has_premium_version' => false,
                'has_addons'          => false,
                'has_paid_plans'      => true,
                 
                'menu'                => array(
                    'slug'           => 'ai_auto_tool',
                    'contact'        => true,
                    'support'        => false,
                    'network'        => true,
                ),
            ) );
        }

        return $aiautotool_premium;
    }

   
    aiautotool_premium();
    do_action( 'aiautotool_premium_loaded' );
}


aiautotool_premium()->add_filter( 'hide_freemius_powered_by', '__return_true' );


function aiautotool_activate()
{
    require_once esc_html( plugin_dir_path( __FILE__ ) ) . 'vendor/aiautotool/class-ai-auto-tool-activator.php';
    Ai_auto_tool_Activator::activate();
}


function aiautotool_deactivate()
{
    require_once esc_html( plugin_dir_path( __FILE__ ) ) . 'vendor/aiautotool/class-ai-auto-tool-deactivator.php';
   Ai_auto_tool_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'aiautotool_activate' );
register_deactivation_hook( __FILE__, 'aiautotool_deactivate' );

if( ! class_exists( 'AI_Auto_Tool' ) ) {
    class AI_Auto_Tool {
        protected $subclasses = [];
        private $aiautotool_config_settings;
        public function __construct() {

            add_action('plugins_loaded', array($this, 'load_textdomain'));
           
            add_action( 'admin_menu', array( $this, 'add_menu_page' ) , 10, 2);
            add_action( 'admin_init', array( $this, 'register_settings' ) );
            add_action( 'rest_api_init', array($this,'create_post_route'));
            add_action( 'wp_ajax_aiautotool_check', array( $this, 'aiautotool_check' ));
            add_action( 'wp_ajax_nopriv_aiautotool_check', array( $this, 'aiautotool_check' ) );
            add_action('wp_ajax_aiautotool_insert_post', array( $this, 'aiautotool_insert_post_ajax' ));
            add_action('wp_ajax_nopriv_aiautotool_insert_post', array( $this, 'aiautotool_insert_post_ajax' ));
            add_action( 'wp_ajax_aiautotool_get_cate',  array( $this, 'aiautotool_get_cate' ));
            add_action( 'wp_ajax_nopriv_aiautotool_get_cate',  array( $this, 'aiautotool_get_cate' ));
            add_action( 'wp_ajax_get_all_media',  array( $this, 'get_all_media_callback' ));
            add_action( 'wp_ajax_nopriv_get_all_media',  array( $this, 'get_all_media_callback' ));
            add_action( 'wp_ajax_aiautotool_save_image',  array( $this, 'aiautotool_save_image' ));

            self::load_lib();
          self::load_inc();
          self::aiautotool_new_object();

            add_action( 'admin_enqueue_scripts', array( $this, 'add_aiautotool_button_js' ) );

            add_filter('plugin_action_links', array( $this, 'add_ai_auto_tool_settings_link' ), 10, 2);

           
            add_action('wp_ajax_ai_auto_tool_process_posts', array($this, 'process_posts_callback'));
            add_action('wp_ajax_nopriv_ai_auto_tool_process_posts', array($this, 'process_posts_callback'));


            add_action('wp_ajax_ai_auto_tool_process_img_404_posts', array($this, 'process_img_404_posts_callback'));
            add_action('wp_ajax_nopriv_ai_auto_tool_process_img_404_posts', array($this, 'process_img_404_posts_callback'));

        register_activation_hook(__FILE__, array($this, 'activate'));
        
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        $aiautotool_Warning_Notice = new aiautotool_Warning_Notice(array(
            'icon_url'    => plugins_url('/images/logo.svg', __FILE__),
            'title'       => 'Aiautotool Plugin ',
            'content'     => 'The plugin is available for free so if it benefits you. Join the plugin support group here.',
            'button_text' => 'Join group support Ai auto Tool Plugin',
            'button_url'  => 'https://t.me/aiautotoolchat',
        ));
            
            
        }

         public function init_settings() {
        
    }
        private function aiautotool_getdata() {
            
            return rendersetting::aiautotool_getdata();
            
        }

        public function load_textdomain() {
            load_plugin_textdomain('ai-auto-tool', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        }
         public function activate() {
            if (class_exists('activeCallBack')) {
             $activeCallBack = new activeCallBack();
             $activeCallBack->activate();
            }
         }
         public function deactivate(){
            
            $siteurl = get_option('siteurl');
                $siteurl = str_replace('https://', 'http://', $siteurl);

                update_option('siteurl', $siteurl);
            // }
                 $home = get_option('home');
                $home = str_replace('https://', 'http://', $home);

                update_option('home', $home);
            
            if (class_exists('activeCallBack')) {
                $activeCallBack = new activeCallBack();
                $activeCallBack->deactivate();
            }

          global $wpdb;
            $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'aiautotool_%'" );
            $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'fs_%'" );
         }
        function add_ai_auto_tool_settings_link($links, $file) {
            if (false !== strpos($file, 'ai-auto-tool/Ai-Auto-Tool.php')) {
                $settings_link = '<a href="' . admin_url('admin.php?page=ai_auto_tool') . '">Settings</a>';
                array_unshift($links, $settings_link);
            }
            return $links;
        }
        public function addSubclass($subclass) {
            $this->subclasses[] = $subclass;
        }
        public function aiautotool_new_object(){
            

            

           if (class_exists('URL_Scanner_Plugin')) {
                        $url_scanner_plugin = new URL_Scanner_Plugin();
                         $this->addSubclass($url_scanner_plugin);
                    }
           
           if (class_exists('AIAutoToolsinglepost')) {
                $AIAutoToolsinglepost = new AIAutoToolsinglepost();
                $this->addSubclass($AIAutoToolsinglepost);
            }
             if (class_exists('AIautotool_autocomment')) {
                $AIautotool_autocomment = new AIautotool_autocomment();
                $this->addSubclass($AIautotool_autocomment);
            }

            if (class_exists('AIautotool_autocreatetags')) {
                $AIautotool_autocreatetags = new AIautotool_autocreatetags();
                $this->addSubclass($AIautotool_autocreatetags);
            }

            if (class_exists('AI_AutoTool_cloudflareAI')) {
                $AI_AutoTool_cloudflareAI = new AI_AutoTool_cloudflareAI ();
                $this->addSubclass($AI_AutoTool_cloudflareAI);
                 
            }

            if (class_exists('AI_AutoTool_suggetauto')) {
                $AI_AutoTool_suggetauto = new AI_AutoTool_suggetauto ();
                $this->addSubclass($AI_AutoTool_suggetauto);
                 
            }

            if (class_exists('AIautotool_Prompt_CPT')) {
                $AIautotool_Prompt_CPT = new AIautotool_Prompt_CPT ();
                $this->addSubclass($AIautotool_Prompt_CPT);
                 
            }

            
            if (class_exists('AIAutoTool_SubmitIndex')) {
                $AIAutoTool_SubmitIndex = new AIAutoTool_SubmitIndex();
                $this->addSubclass($AIAutoTool_SubmitIndex);
            }

           

            if (class_exists('Aiautotool_exLink_backlink')) {
                $Aiautotool_exLink_backlink = new Aiautotool_exLink_backlink();
                $this->addSubclass($Aiautotool_exLink_backlink);
            }
            if (class_exists('Aiautotool_Canonical')) {
                $Aiautotool_Canonical = new Aiautotool_Canonical();
                $this->addSubclass($Aiautotool_Canonical);
            }
            

            
            if (class_exists('Aiautotool_tool_extra_config')) {
                $Aiautotool_tool_extra_config = new Aiautotool_tool_extra_config();
                $this->addSubclass($Aiautotool_tool_extra_config);
            }

            if (class_exists('AIautotool_Web_Stories')) {
                $AIautotool_Web_Stories = new AIautotool_Web_Stories();
                $this->addSubclass($AIautotool_Web_Stories);
            }

            if (class_exists('AIAutoToolSecurityBot')) {
                $AIAutoToolSecurityBot = new AIAutoToolSecurityBot();
                $this->addSubclass($AIAutoToolSecurityBot);
            }
            
            // if (class_exists('AIAutotool_ThumbPlusSettings')) {
            //     $AIAutotool_ThumbPlusSettings = new AIAutotool_ThumbPlusSettings();
            //     $this->addSubclass($AIAutotool_ThumbPlusSettings);
            // }

            if (class_exists('aiautotool_AutoGenerateUsername')) {
                $aiautotool_AutoGenerateUsername = new aiautotool_AutoGenerateUsername();
                $this->addSubclass($aiautotool_AutoGenerateUsername);
            }
            
            if (class_exists('Post_Exporter_Plugin')) {
                $Post_Exporter_Plugin = new Post_Exporter_Plugin();
                $this->addSubclass($Post_Exporter_Plugin);
            }
            if (class_exists('Auto_Install_Plugins')) {
                $Auto_Install_Plugins = new Auto_Install_Plugins();
                $this->addSubclass($Auto_Install_Plugins);
            }

            
            if (class_exists('Aiautotool_Footer_Base')) {
                $Aiautotool_Footer_Base = new Aiautotool_Footer_Base();
                $this->addSubclass($Aiautotool_Footer_Base);
            }
            if (class_exists('aiautotool_SSL_active')) {
                $aiautotool_SSL_active = new aiautotool_SSL_active();
                 $this->addSubclass($aiautotool_SSL_active);
            }
            if (class_exists('activeCallBack')) {
             $activeCallBack = new activeCallBack();
             
            }
            if (class_exists('AI_AutoTool_Prompt_Customer')) {
                $AI_AutoTool_Prompt_Customer = new AI_AutoTool_Prompt_Customer();
                 
            }
           
           

        }
        public function show_render_setting() {
           
                foreach ($this->subclasses as $subclass) {
                $subclass->render_setting();
            }
        }
        public function show_render_tab_setting() {
           
                foreach ($this->subclasses as $subclass) {
                $subclass->render_tab_setting();
            }
        }

        public function render_list_feature(){
             foreach ($this->subclasses as $subclass) {
                $subclass->render_feature();
            }
        }
        public function render_plan(){
             foreach ($this->subclasses as $subclass) {
                $subclass->render_plan();
            }
        }
        public function load_inc() {
            // Đường dẫn đến thư mục "inc"
            $inc_directory = plugin_dir_path(__FILE__) . 'inc';
            $inc_files = glob($inc_directory . '/*.php');
            if ($inc_files) {
                foreach ($inc_files as $file) {
                    if (is_file($file)) {
                        include $file;
                    }
                }
            }
        }
        public function load_lib() {
            $inc_directory = plugin_dir_path(__FILE__) . 'lib';
            $inc_files = glob($inc_directory . '/*.php');
            if ($inc_files) {
                foreach ($inc_files as $file) {
                    if (is_file($file)) {
                        include $file;
                    }
                }
            }
        }
    
    
    



    function replace_links_in_content($links,$content){
    
  if (count($links) > 3) {
    shuffle($links);
    $links = array_slice($links, 0, 3);
  } 

    $nofollow = "  ";
    $newwindow = " target='_blank' ";
    $before = "([\s,\(\);:]*?[A-Za-z0-9\x80-\xFF+]*?[\s,\)\(;:]){0,0}";
    $after = "([\s,\(\);:]*?[A-Za-z0-9\x80-\xFF+]*?[\s,\)\(;:]){0,0}";
    $insensitive = 'i';
    $contentbefor = $content;
    foreach ($links as $link) {
            $content = ai_internal_link_building_blocks::findtags($content); 
            $anchor_text = $link->archortext;
            $url = $link->url;
            $name = $anchor_text;

            $replace1 = '<a href="'.$url.'"'.$nofollow.$newwindow.'>';
            $replace2 = '</a>';

                $escapes = array('.','$','^','[',']','?','+','(',')','*','|','\\');

            foreach($escapes as $s){
                $r = '\\\\'.$s;
                $name = str_replace($s, stripslashes($r), $name);
            }
            $needle ='@()('.$before.$name.'\b'.$after.')()@';
            
            $content = ai_internal_link_building_blocks::findtags($content,false);  blocks.
            $content = ai_internal_link_building::replace($content, $needle, $replace1, $replace2, 1,$insensitive);
            $content = ai_internal_link_building_blocks::findblocks($content); 
            $content = ai_internal_link_building_blocks::findblocks($content); 
            $content = ai_internal_link_building_blocks::findblocks($content); 
            $protectblocks = array();
        
            $content = trim($content, ' ');
            if (md5($content)!=md5($contentbefor)) {
                
            }else{
                $pos = strpos($content, "</p>");

              if ($pos !== false) {
                $p_tags = explode('</p>', $content);
                $p_tag_count = count($p_tags);

                if ($p_tag_count > 1) {
                  $random_pos = rand(1, $p_tag_count - 1); 
                  $pos = strpos($content, '</p>' . $p_tags[$random_pos]);
                }

                
                $replacement = "<a href=\"" . $url . "\">" .  $anchor_text . "</a>";
                $content = substr($content, 0, $pos + strlen("</p>")) . $replacement . substr($content, $pos + strlen("</p>"));

              } else {
                
                $content .= "<a href=\"" . $url . "\">" .  $anchor_text . "</a>";
              }
            }

  }

return $content;
}



       public function add_aiautotool_button_js() {
        $setting = new rendersetting();
        wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css' ,array(), '1.2'.rand());
        
        wp_enqueue_style('aiautotool', plugin_dir_url(__FILE__) . 'css/aiautotool.css', array(), '1.2'.rand());
        wp_enqueue_script('jquery');
        wp_enqueue_media();
        wp_enqueue_script('socket-io', 'https://cdn.socket.io/4.6.0/socket.io.min.js', array(), '4.6.0');
        wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11"', array(), '4.6.0');
        
    
         $langcodedefault = explode('-',get_bloginfo("language"));
         $langcodedefault = $langcodedefault[0];

    wp_register_script('kct_cr_scriptx', plugin_dir_url( __FILE__ ) .'js/aiautotool.js', array(), '1.2'.rand(), true);
       
     global $post;

     //$settingseditornumberline = wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
     $settingseditornumberline = '';
     
    // wp_localize_script('jquery', 'cm_settings', $settings);
    // wp_enqueue_style('wp-codemirror');
    
        if(isset($post->ID)){

            wp_localize_script( 'kct_cr_scriptx', 'aiautotool_js_setting',array( 'ajax_url' => admin_url( 'admin-ajax.php') , 'postID' => $post->ID, 'postTitle' => $post->post_title,'security' => wp_create_nonce('aiautotool_nonce'),'languageCodes'=>$setting->languageCodes,
                'langcodedefault'=>$langcodedefault ,
                'fsdata'=>$this->aiautotool_getdata(),
                'code_mirrror'=>$settingseditornumberline,
                'gemini_api'=>AIAUTOTOOL_API_GEM,
                'gpt_api'=>AIAUTOTOOL_API_GTP,
                'prompt'=>get_option('aiautotool_prompt_options')
                ));
        }else{
            wp_localize_script( 'kct_cr_scriptx', 'aiautotool_js_setting',array( 
                'ajax_url' => admin_url( 'admin-ajax.php') , 
                'security' => wp_create_nonce('aiautotool_nonce'),
                'languageCodes'=>$setting->languageCodes,
                'langcodedefault'=>$langcodedefault,
                'fsdata'=>$this->aiautotool_getdata(),
                'code_mirrror'=>$settingseditornumberline,
                'gemini_api'=>AIAUTOTOOL_API_GEM,
                'gpt_api'=>AIAUTOTOOL_API_GTP,
                'prompt'=>get_option('aiautotool_prompt_options')
            ));
        }


    
    wp_enqueue_script('kct_cr_scriptx');

   

}

    public function aiautotool_hook_script(){
        
        wp_enqueue_script( 'codemirror-js', plugin_dir_url(__FILE__) .'vendor/codemirror/codemirror.min.js', array(), false, true );
        wp_enqueue_style( 'codemirror-css', plugin_dir_url(__FILE__) .'vendor/codemirror/codemirror.min.css' );
        wp_enqueue_script( 'codemirror-mode-perl', plugin_dir_url(__FILE__) .'vendor/codemirror/perl.min.js', array('codemirror-js'), false, true );
        wp_enqueue_style( 'codemirror-theme', plugin_dir_url(__FILE__) .'vendor/codemirror/cobalt.css' );
        
    }


        public function add_menu_page() {
            add_menu_page(
                '<i class="fa-solid fa-gears"></i> Ai Auto Tool',
                '<i class="fa-solid fa-gears"></i> Ai Auto Tool',
                'manage_options',
                MENUSUBPARRENT,
                array( $this, 'menu_page' ),
                'data:image/svg+xml;base64,' . base64_encode(file_get_contents(plugin_dir_path(__FILE__) . '/images/logo.svg')),
                3
            );

             add_submenu_page(
                MENUSUBPARRENT,  
                '<i class="fa-solid fa-wand-magic-sparkles"></i> Integrations', 
                '<i class="fa-solid fa-wand-magic-sparkles"></i> Integrations', 
                'manage_options',              
                'aiautotool_integrations',  
                array($this, 'aiautotool_integrations')  
            );
            
           
           
        }
        public function aiautotool_integrations(){
            ?>
            <div class="wrap aiautotool_container">
                <div class="aiautotool_box_f_container">
                <?php self::render_list_feature(); ?>
        
        
                </div>
            </div>
            <?php
        }
        public function menu_page() {
             $this->config_page();
        }

         function is_free_plan() {
            if ( ! aiautotool_premium()->is_registered() ) {
                return true;
            }

            if ( ! aiautotool_premium()->has_paid_plan() ) {
                return true;
            }

            return (
                'free' === aiautotool_premium()->get_plan_name() ||
                ! aiautotool_premium()->has_features_enabled_license()
            );
        }

        public function config_page() {
            
            if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        
        $api_key_list = get_option('aiautotool_api_key_list', array());
        $config_img = get_option('aiautotool_config_img', false);
        $aiautotool_images_list = get_option('aiautotool_images_list', []);
         $users = get_users();
        
        if (isset($_POST['aiautotool_add_key'])) {
            $new_key = 'KCT-'.uniqid();
            $user_id = sanitize_text_field($_POST['aiautotool_user']); 
            $api_key_list = get_option('aiautotool_api_key_list', array());
            $api_key_list[] = array(
                'key' => $new_key,
                'user_id' => $user_id,
            );
            update_option('aiautotool_api_key_list', $api_key_list,null, 'no');

            echo esc_html('<div class="notice notice-success"><p>New API key added.</p></div>');
        }

        if (isset($_POST['aiautotool_save_config'])) {
            if(isset($_POST['aiautotool_config_img'])){
                $config_img = sanitize_text_field($_POST['aiautotool_config_img']);
                update_option('aiautotool_config_img', $config_img,null, 'no');
            }
            
        }

        if (isset($_POST['aiautotool_remove_api_key'])) {
            $remove_key = sanitize_text_field($_POST['aiautotool_remove_api_key']);
            unset($api_key_list[$remove_key]);
            update_option('aiautotool_api_key_list', $api_key_list,null, 'no');
        }
        if (isset($_POST['aiautotool_remove_log_img'])) {
            
            
            update_option('aiautotool_images_list', [],null, 'no');
            $aiautotool_images_list = get_option('aiautotool_images_list', []);
            echo esc_html('<div class="notice notice-success"><p>Reset log Images Success!!!!.</p></div>');
        }

        if (isset($_POST['aiautotool_remove_img_log_item'])) {
            $remove_key = sanitize_text_field($_POST['aiautotool_remove_img_log_item']);
            unset($aiautotool_images_list[$remove_key]);
            update_option('aiautotool_images_list', $aiautotool_images_list,null, 'no');
            echo esc_html('<div class="notice notice-success"><p>Remove log item image Success!!!!.</p></div>');
        }
        $this->aiautotool_config_settings = get_option('aiautotool_code_settings',array());
        if(isset($_POST['aiautotool_code_settings'])){
            foreach($_POST['aiautotool_code_settings'] as $key => $value){
                if($key!='btnall'){
                    $this->aiautotool_config_settings[$key] = $value;
                }
                
            }
            $result = update_option('aiautotool_code_settings',(array )$this->aiautotool_config_settings,null, 'no');
            
            $this->aiautotool_config_settings = get_option('aiautotool_code_settings',array());
           
        }
        
        ?>


        
            <div class=" aiautotool_container ">
                <div class="ft-wrap-top"></div>
                <div class="wrap ft-wrap">
                    <div class="ft-wrap-body">
                    <div class="ft-box ">
            <div class="ft-menu aiautotool-animate-faster wave-animate-fast">
                <div class="ft-logo"><img src="<?php echo plugins_url('/images/logo.svg', __FILE__); ?>">
                    <br>AI Autotool Settings</div>

              
                 <button href="#tab-setting" class="nav-tab sotabt "><i class="fa-solid fa-gears"></i> Plan & Quota</button>
                <button href="#set-prompt" class="nav-tab sotabt "><i class="fa-solid fa-gears"></i>Edit Prompt</button>
                <?php self::show_render_tab_setting(); ?>

                <button  href="#tab2"  class="nav-tab  sotab "><i class="fa-solid fa-code"></i> <?php _e('WP HEAD', 'ai-auto-tool'); ?></button>
                <button  href="#tab3"  class="nav-tab  sotab" ><i class="fa-solid fa-code"></i> <?php _e('WP BODY', 'ai-auto-tool'); ?></button>
                <button  href="#tab4"  class=" nav-tab sotab" ><i class="fa-solid fa-code"></i> <?php _e('WP FOOTER', 'ai-auto-tool'); ?></button>
                <button href="#tab-infodonate" class="nav-tab sotab"><i class="fa-solid fa-circle-info"></i> Info plugin</button>

            </div>
            <div class="ft-main ">
                <div id="tab-setting"  class="tab-content sotab-box ftbox ">
                    <h2></h2>
                    <div class="ft-card-note"> 
                        
                        <?php $this->render_subscription_info(); ?>
                        </div>

                                    </div>
                <div id="set-prompt"   class="tab-content sotab-box ftbox " >
                    <?php if (class_exists('AI_AutoTool_Prompt_Customer')) {
                $AI_AutoTool_Prompt_Customer = new AI_AutoTool_Prompt_Customer();
                 $AI_AutoTool_Prompt_Customer->render_setting();
            } ?>
                </div>

                 <div id="tab-setting1" class="tab-content sotab-box ftbox"  style="display:none">
                <!-- Content for the API tab goes here -->
                <h2><i class="fa-solid fa-gears"></i> <?php _e('API Key','ai-auto-tool') ?></h2>

                <form method="post">
                     <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                        <?php _e('New API Key - Select User for API KEY when post Single:','ai-auto-tool'); ?>
                    
                    </p>
                    <p>
                        
                    <select name="aiautotool_user" id="aiautotool_user">
                        <?php foreach ($users as $user) : ?>
                            <option value="<?php echo esc_html($user->ID); ?>"><?php echo esc_html($user->display_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                        <button type="submit" name="aiautotool_add_key" class="button-primary ft-submit"><?php _e('Add New API Key','ai-auto-tool') ?></button>
                    </p>
                </form>

                <h3>API Key List</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>API Key</th>
                            <th>User </th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($api_key_list as $k=>$api_key): ?>
                            <tr>
                                <td><?php echo esc_html($api_key['key']); ?></td>
                                <td><?php echo esc_html(get_user_by( 'id', $api_key['user_id'] )->user_login); ?></td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="aiautotool_remove_api_key" value="<?php echo esc_html($k); ?>">

                                        <button type="submit" class="button-secondary "><?php _e('Remove','ai-auto-tool'); ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

               
                <form method="post">
                    <?php 
                        $checked = '';
                        if ($config_img) {
                            $checked = 'checked';
                        }
                         ?>
                   

                    <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                    <?php _e('Allow image auto save when aiautotool.com post article','ai-auto-tool'); ?>
                    </p>
                        <label class="aiautotool_box_f_toggle-switch">
                            <input type="checkbox" id="aiautotool_config_img" name="aiautotool_config_img" value="1"  <?php echo esc_html($checked);?>>>
                            <span class="aiautotool_box_f_toggle-slider"></span>
                        </label>
                    <p>
                        <button type="submit" name="aiautotool_save_config" class="button-primary ft-submit"><?php _e('Save Configuration','ai-auto-tool'); ?></button>
        </p>
        </form>
            </div>
            <div class="sotab-box ftbox tab-content" id="tab2" style="display:none">
                <form method="post" >
            <h2><?php _e('WP HEAD', 'ai-auto-tool'); ?></h2>
            <div class="ft-card">
              <h3><i class="fa-solid fa-code"></i> <?php _e('Add to WP head', 'ai-auto-tool') ?></h3>
                <p>
                <textarea class="ft-code-textarea" name="aiautotool_code_settings[code2]" placeholder="<?php _e('Input code', 'ai-auto-tool'); ?>"><?php if(!empty($this->aiautotool_config_settings['code2'])){echo esc_textarea(stripslashes($this->aiautotool_config_settings['code2']));} ?></textarea>
                </p>
            </div>
             <button type="submit" name="btncode" class="button-primary ft-submit"><?php _e('Save head','ai-auto-tool'); ?></button>
             </form>
            </div>
            <!-- Javascript 2 -->
            <div class="sotab-box ftbox tab-content" id="tab3" style="display:none">
                <form method="post" >
            <h2><?php _e('WP BODY', 'ai-auto-tool'); ?></h2>
            <div class="ft-card">
              <h3><i class="fa-solid fa-code"></i> <?php _e('Add to WP body', 'ai-auto-tool') ?></h3>
                <p>
                <textarea class="ft-code-textarea" name="aiautotool_code_settings[code3]" placeholder="<?php _e('Input code', 'ai-auto-tool'); ?>"><?php if(!empty($this->aiautotool_config_settings['code3'])){echo esc_textarea(stripslashes($this->aiautotool_config_settings['code3']));} ?></textarea>
                </p>
            </div>
                 <button type="submit" name="btncode" class="button-primary ft-submit"><?php _e('Save body','ai-auto-tool'); ?></button></form>
            </div>
            <!-- Javascript 3 -->
            <div class="sotab-box ftbox tab-content" id="tab4" style="display:none">
                <form method="post" >
            <h2><?php _e('WP FOOTER', 'ai-auto-tool'); ?></h2>
            <div class="ft-card">
              <h3><i class="fa-solid fa-code"></i> <?php _e('Add to WP footer', 'ai-auto-tool') ?></h3>
                <p>
                <textarea class="ft-code-textarea" name="aiautotool_code_settings[code4]" placeholder="<?php _e('Input code', 'ai-auto-tool'); ?>"><?php if(!empty($this->aiautotool_config_settings['code4'])){echo esc_textarea(stripslashes($this->aiautotool_config_settings['code4']));} ?></textarea>
                </p>
            </div>  
                 <button type="submit" name="btncode" class="button-primary ft-submit"><?php _e('Save footer','ai-auto-tool'); ?></button>
             </form>
            </div>

            <!-- infodonate -->
            <div id="tab-infodonate"  class="tab-content sotab-box ftbox" style="display:none;">
                
                <div class="ft-card-note"> 
                    <p><i class="fa-solid fa-circle-info"></i>DEV By: <b><a target="_blank" href="https://aiautotool.com">AI auto Tool</a></b></p>
                    <p>Author: <b>KCT</b></p>
                    <p>Skin by: <a target="_blank" href="https://wordpress.org/plugins/foxtool/"><b>Fox Theme</b></a></p>
                    <p><?php _e('If you feel this plugin is useful, please Donate to me, I will try to add more useful functions in the next updates :)','aiautotool'); ?></p>
                    <p>
                    <a class="ft-donate-a" target="_blank" href="https://paypal.me/aiautotool">Donate me if it's useful to you</a>
                    
                    </p>
                    </div>
            </div>

            <!-- tab log -->
             <div id="tab-log" class="tab-content sotab-box ftbox" style="display:none;">

        <h2><?php _e('Log Img','ai-auto-tool'); ?></h2>
               <form method="post">
                                        <input type="hidden" name="aiautotool_remove_log_img" value="1">

                                        <button type="submit" class="button-secondary"><?php _e('Clear all log Images','ai-auto-tool'); ?></button>
                                    </form>
        <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('From url','ai-auto-tool'); ?></th>
                            <th><?php _e('To Url','ai-auto-tool'); ?></th>
                            <th><?php _e('Action','ai-auto-tool'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($aiautotool_images_list as $k=>$img_key): ?>
                            <tr>
                                <td><?php echo esc_html($img_key['url_remote']); ?></td>
                                <td><?php echo  esc_html($img_key['url_image_new']['url_out']); ?></td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="aiautotool_remove_img_log_item" value="<?php echo esc_html($k); ?>">

                                        <button type="submit" class="button-secondary"><?php _e('Remove','ai-auto-tool'); ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <!-- Content for the Log tab goes here -->
            </div>
            <!-- end tab log -->

            <?php  self::show_render_setting(); ?>
                <div class="sotab-box ftbox">
                    
                </div>
            </div>
        </div>
        <script type="text/javascript"></script>
        <div class="ft-sidebar-right">
            <div class="ft-widget ft-widget-color1">
                <h2><?php _e('If you find it helpful','ai-auto-tool'); ?></h2>
                <a target="_blank" href="https://wordpress.org/support/plugin/ai-auto-tool/reviews/?filter=5">Rate now
                <div class="starloader"></div></a>
            </div>
            <h3><?php _e('Active tool','ai-auto-tool'); ?></h3>
            <div class="aiautotool_box_f_container  ">
            <?php self::render_list_feature(); ?>
            </div>
        </div>
    </div>
    
        </div>
        <?php

       
        }


        public function render_subscription_info() {
                $domain = esc_html(home_url());
                echo "<p><i class=\"fa-solid fa-globe\"></i> Domain: <strong>$domain</strong></p>";
                echo "<p><i class=\"fa-solid fa-globe\"></i> Plugin Version: <strong>" . AIAUTOTOOL_VS . "</strong></p>";
               $dt =  $this->aiautotool_getdata();
                if ($this->is_free_plan()) {
                    $accountType = 'Free';
                    echo "<p><i class=\"fa-solid fa-bell\"></i> Current Plan: <b class=\"activate-license ai-auto-tool\">$accountType</b> </p>";
                    echo '<p><i class="fa-solid fa-chart-simple"></i> Total limit: <b>' . AIAUTOTOOL_FREE . '</b> Request <abbr data-title="Includes services that use AI APIs"><code>(Post, Tag, Comment)</code></abbr></p>';
                    echo '<p><i class="fa-solid fa-chart-simple"></i> Total Usage: <b>' . esc_html($dt['usage']) . '</b> </p>';
                    echo "<p style=\"display: flex;align-items: center;justify-content: center; \"><a style=\"color:#ff4444;font-weight:bold\" href=\"" . aiautotool_premium()->get_upgrade_url() . "\" class=\"aiautotool_btn_upgradepro activate-license\" ><i class=\"fa-solid fa-unlock-keyhole\"></i> Upgrade Pro</a></p>";

                } else {
                     $fs = rendersetting::is_premium();
                    $accountType = $fs->get_plan_title();
                    $plan_id = aiautotool_premium()->_get_subscription(aiautotool_premium()->_get_license()->id)->plan_id;
                    if($plan_id==25184){
                        echo '<p><i class="fa-solid fa-chart-simple"></i> Total limit: <b>5000</b> Request <abbr data-title="Includes services that use AI APIs"><code>(Post, Tag, Comment)</code></abbr></p>';
                    }else{
                        echo '<p><i class="fa-solid fa-chart-simple"></i> Total limit: <b>Unlimited</b> Request <abbr data-title="Includes services that use AI APIs"><code>(Post, Tag, Comment)</code></abbr></p>';
                    }
                    
                    echo '<p><i class="fa-solid fa-chart-simple"></i> Total Usage: <b>' . esc_html($dt['usage']) . '</b> </p>';
                   
                    echo "<p><i class=\"fa-solid fa-bell\"></i> Current Plan: <b>$accountType</b> </p>";

                }

                echo '<h3><i class="fa-solid fa-angles-right"></i> ' . __('Subscription features Auto ', 'ai-auto-tool') . '</h3>';
                self::render_plan();
            }

        public function create_post_route() { 
                register_rest_route( 'aiautotool', '/createpost', array(
                    'methods' => 'POST',
                    'callback' => array($this,'aiautotool_insert_post_ajax'),
                ) );
                register_rest_route( 'aiautotool', '/get_cate', array(
                    'methods' => 'GET',
                    'callback' => array($this,'aiautotool_get_cate'),
                ));
                register_rest_route( 'aiautotool', '/media', array(
                    'methods' => 'GET',
                    'callback' => array($this,'get_all_media_callback'),
                ));
                register_rest_route( 'aiautotool', '/check', array(
                    'methods' => 'GET',
                    'callback' => array($this,'aiautotool_check'),
                ));
            }

        public function register_settings() {
            register_setting( 'ai_auto_tool_api_keys', 'ai_auto_tool_api_keys' );
            register_setting( 'ai_auto_tool_log', 'ai_auto_tool_log' );
        }

        public function aiautotool_insert_post_ajax() {
            // Lấy dữ liệu POST
            $name = sanitize_text_field($_POST['name']);
            $detail = wp_kses_post($_POST['detail']);
            $category_name = sanitize_text_field($_POST['category']);
            $tags = sanitize_text_field($_POST['tags']);
            $date = sanitize_text_field($_POST['date']);
            $key = sanitize_text_field($_POST['key']);
            $postname = $this->kct_aiautotool_sanitize($name);
            $user_id = '';
            $user_ids = get_users( array(
                'fields' => 'ID',
            ) );
            $random_user_id = $user_ids[array_rand($user_ids)];
            $api_key_list = get_option('aiautotool_api_key_list', array());
            if (count($api_key_list)>0) {
                foreach ($api_key_list as $k=>$api_key){
                    if ($key==$api_key['key']) {
                        $user_id = $random_user_id;//rand(1, 11);//$api_key['user_id'];

                        $config_img = get_option('aiautotool_config_img', false);

                        if($config_img){
                            
                            $aiautotool_images_list = get_option('aiautotool_images_list', []);
                           

                            $html       = str_replace('\"', '"', $detail);
                            $arrayImageUpload = $this->kct_aiautotool_get_all_img($html);
                           
                            $attachThumnail   = 0;

                            
                            if (!empty($arrayImageUpload)){
                                foreach ($arrayImageUpload as $post_image_url){
                                    $image_item = null;
                                    foreach ($aiautotool_images_list as $item) {
                                        if ($item['url_remote'] === $post_image_url) {
                                            $image_item = $item;
                                            break;
                                        }
                                    }
                                    if ($image_item !== null) {
                                        $image_url_new = $image_item['url_image_new']['url_out'];
                                        $detail = str_replace($post_image_url, $image_url_new, $detail);
                                        if ($attachThumbnail == 0) {
                                            $attachThumbnail = $image_item['url_image_new']['attach_id'];
                                        }
                                    }else{
                                        try {
                                            $image_url_new = $this->kct_aiautotool_save_image($post_image_url,$postname);
                                            if (!empty($image_url_new)){
                                                $imgUploaded[] = $image_url_new;
                                                if ($attachThumbnail == 0) {
                                                    $attachThumbnail = $image_url_new['attach_id'];
                                                }
                                                 $aiautotool_images_list[] = [
                                                        'url_remote' => $post_image_url,
                                                        'url_image_new' => $image_url_new,
                                                    ];
                                                    update_option('aiautotool_images_list', $aiautotool_images_list,null, 'no');
                                            }
                                        }catch (Exception $e) {
                                        }
                                    }
                                    
                                }
                            }
                            foreach ($imgUploaded as $img){
                                $detail = str_replace($img['url'],$img['baseurl'],$detail);
                            }
                            
                           



                        }

                       
                        $category = get_category_by_slug($category_name);
                        $category_id = $category->term_id;

                        // Insert the post
                        $post_id = wp_insert_post(array(
                            'post_title' => $name,
                            'post_content' => $detail,
                            'post_category' => array($category_id),
                            'tags_input' => $tags,
                            'post_status' => 'publish',
                            'post_author'=>$user_id,
                            'post_date' => $date,
                            'meta_input' => array(
                                'key' => $key,
                            ),
                            'post_type' => 'post',
                        ));

                        // Return the URL of the new post
                        if ($post_id) {
                            if ($attachThumbnail != 0) {
                                set_post_thumbnail($post_id, $attachThumbnail);
                            }
                            $post_url = get_permalink($post_id);
                            echo json_encode(array('url' => $post_url));
                        } else {
                            echo json_encode(array('error' => 'Failed to insert post.'));
                        }
                    }
                }
            }
            
            
            wp_die();
        }
        public function kct_aiautotool_url_encode($string)
        {
            return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
        }
        function kct_aiautotool_save_image($imgURL,$post_title){
            $image_name = basename( $imgURL );
            $filetype  = wp_check_filetype($image_name);
            $upload_dir = wp_upload_dir();
            //$unique_file_name = aiktp_sanitize($post_title)."-".wp_unique_filename( $upload_dir['path'], $image_name );
            $extension = $filetype['ext']?$filetype['ext']:"jpg";
            if (empty($extension)) $extension = "jpg";
            $unique_file_name = $this->kct_aiautotool_sanitize($post_title)."-".uniqid().".".$extension;
            $filename = $upload_dir['path'] . '/' . $unique_file_name;
            $baseurl = $upload_dir['baseurl'] .$upload_dir['subdir']. '/' . $unique_file_name;
            $ch = curl_init($imgURL);
            $fp = fopen($filename, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_exec($ch);
            $rescode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
            curl_close($ch);
            fclose($fp);
            if ($rescode == 200  && filesize($filename) > 100){

                 $wp_filetype = wp_check_filetype(basename($filename), null);
                
                $attachment = array(
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title' => sanitize_file_name($post_title),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );
                
                $attach_id    = wp_insert_attachment($attachment, $filename);
                $imagenew     = get_post($attach_id);
                $fullsizepath = get_attached_file($imagenew->ID);
                
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attach_data = wp_generate_attachment_metadata($attach_id, $fullsizepath);
                wp_update_attachment_metadata($attach_id, $attach_data);
                


                $output['url'] = $imgURL;
                $output['file_name'] = $unique_file_name;
                $output['path'] = $filename;
                $output['baseurl'] = $baseurl;
                $output['attach_id'] = $attach_id;
                $output['url_out'] = $baseurl;
                return $output;
            }else{
                return null;
            }
        }

        public function kct_aiautotool_sanitize($title) {
                $replacement = '-';
                $map = array();
                $quotedReplacement = preg_quote($replacement, '/');
                $default = array(
                    '/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ|À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ|å/' => 'a',
                    '/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ|È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ|ë/' => 'e',
                    '/ì|í|ị|ỉ|ĩ|Ì|Í|Ị|Ỉ|Ĩ|î/' => 'i',
                    '/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ|Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ|ø/' => 'o',
                    '/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ|Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ|ů|û/' => 'u',
                    '/ỳ|ý|ỵ|ỷ|ỹ|Ỳ|Ý|Ỵ|Ỷ|Ỹ/' => 'y',
                    '/đ|Đ/' => 'd',
                    '/ç/' => 'c',
                    '/ñ/' => 'n',
                    '/ä|æ/' => 'ae',
                    '/ö/' => 'oe',
                    '/ü/' => 'ue',
                    '/Ä/' => 'Ae',
                    '/Ü/' => 'Ue',
                    '/Ö/' => 'Oe',
                    '/ß/' => 'ss',
                    '/[^\s\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]/mu' => ' ',
                    '/\\s+/' => $replacement,
                    sprintf('/^[%s]+|[%s]+$/', $quotedReplacement, $quotedReplacement) => '',
                );
                //Some URL was encode, decode first
                $title = urldecode($title);
                $map = array_merge($map, $default);
                return strtolower(preg_replace(array_keys($map), array_values($map), $title));
            }
        public function kct_aiautotool_get_all_img($html)
        {
            
            $html = stripslashes($html);
            preg_match_all('/<img[^>]+src\s*=\s*["\']([^"\']+)["\']/i', $html, $matches);
            return $matches[1]; 
        }
        public function kct_aiautotool_startsWith($haystack, $needle)
        {
            $length = strlen($needle);
            return (substr($haystack, 0, $length) === $needle);
        }

        public function kct_aiautotool_endsWith($haystack, $needle)
        {
            $length = strlen($needle);
            
            return $length === 0 || (substr($haystack, -$length) === $needle);
        }



        public function aiautotool_get_cate() {
            header('Content-Type: application/json; charset=utf-8');
            if ( isset( $_GET['key'] ) ) {
                $key = sanitize_text_field($_GET['key']);

                $api_key_list = get_option('aiautotool_api_key_list', array());
                if (count($api_key_list)>0) {
                    foreach ($api_key_list as $k=>$api_key){
                        if ($key==$api_key['key']) {
                            $categories = get_categories();

                            $categories_array = array();
                            foreach ( $categories as $category ) {
                                $categories_array[] = array(
                                    'id' => $category->cat_ID,
                                    'name' => $category->name,
                                    'slug' => $category->slug
                                );
                            }
                            echo json_encode( $categories_array );
                            die();
                        }
                    }
                }
                
            } else {
                wp_send_json_error( ' key not in AJAX.' );
            }
           die();
        }




        public function get_all_media_callback() {


            $key = sanitize_text_field($_GET['key']);
            $api_key_list = get_option('aiautotool_api_key_list', array());
            if (count($api_key_list)>0) {
                foreach ($api_key_list as $k=>$api_key){
                    if ($key==$api_key['key']) {
                        $args = array(
                            'post_type'      => 'attachment',
                            'post_mime_type' => 'image',
                            'post_status'    => 'inherit',
                            'posts_per_page' => - 1,
                        );
                        $media_query = new WP_Query( $args );
                        $media = array();
                        if ( $media_query->have_posts() ) {
                            while ( $media_query->have_posts() ) {
                                $media_query->the_post();
                                $media[] = array(
                                    'id'   => get_the_ID(),
                                    'title'=> get_the_title(),
                                    'url'  => wp_get_attachment_url(),
                                    'type' => get_post_mime_type(),
                                );
                            }
                            wp_reset_postdata();
                            wp_send_json( $media );
                             die();
                        }else{
                            $arr = array('ok'=>'notfindimg');
                            wp_send_json( $arr );
                             die();
                        }

                    }
                }
            }
            
            
            die();
        }


        function aiautotool_check() {
        
            header('Content-Type: application/json; charset=utf-8');
            if ( isset( $_GET['key'] ) ) {
                $key = sanitize_text_field($_GET['key']);

                $api_key_list = get_option('aiautotool_api_key_list', array());
                if (count($api_key_list)>0) {
                    foreach ($api_key_list as $k=>$api_key){
                        if ($key==$api_key['key']) {
                            $arr = array(
                                'results'=>'ok',
                                'API_KEY'=>$api_key['key'],
                                'domain'=>sanitize_text_field($_SERVER['HTTP_HOST'])
                            );
                            echo json_encode( $arr );
                            die(0);
                        }
                    }
                    $arr = array(
                                'results'=>'notok',
                                'API_KEY'=>$api_key['key'],
                                'domain'=>sanitize_text_field($_SERVER['HTTP_HOST'])
                            );
                            echo json_encode( $arr );
                           die(0);

                }


                
            } else {
                $arr = array(
                                'results'=>'notok',
                                'API_KEY'=>$api_key['key'],
                                'domain'=>sanitize_text_field($_SERVER['HTTP_HOST'])
                            );
                echo json_encode( $arr );


                die(0);
            }

        }


        public function aiautotool_save_image(){
              $reponse = array();

              if(!empty($_POST['link_img'])){
                  $link_img = sanitize_text_field($_POST['link_img']);
                  $postId = sanitize_text_field($_POST['postId']);
                  $postTitle = sanitize_text_field($_POST['postTitle']);
                  $i =  sanitize_text_field($_POST['i']);

                  if(strpos(  $link_img,$_SERVER['HTTP_HOST']) !== false){
                    //echo $link_img;
                      $av = array(
                      'stt'=>'ok',
                      'linkedit'=>$link_img
                      );
                  }else{

                    // $imagenew = upload_image($postTitle,$link_img,$postId,$i);
                    $imagenews = $this->kct_aiautotool_save_image($link_img,$postTitle);
                    $imagenew = $imagenews['url_out'];
                      $av = array(
                      'stt'=>'ok',
                      'linkedit'=>$imagenew
                      );
                  }
                  
                  
                }else{

                  $av = array(
                  'stt'=>'notok',
                  'linkedit'=>''
                  );
                }


              header( "Content-Type: application/json" );
                echo json_encode($av);

                exit();
            }


        
        
    }
    new AI_Auto_Tool();


function aiautotool_load_func() {
            $inc_directory = plugin_dir_path(__FILE__) . 'func';
            $inc_files = glob($inc_directory . '/*.php');
            // print_r($inc_files);
            if ($inc_files) {
                foreach ($inc_files as $file) {
                    if (is_file($file)) {
                        include $file;
                    }
                }
            }
        }
aiautotool_load_func();
}

add_filter('auto_update_plugin_ai-auto-tool', '__return_true');


