<?php 
defined('ABSPATH') or die();

class AIautotool_autocomment extends rendersetting{

    // Constructor

    public  $active = false;
    public  $active_option_name = 'AIautotool_autocomment_active';
    public $aiautotool_config_settings;
    public  $usage_option_name = 'Commentauto_AI_usage';
   
    public  $icon = '<i class="fa-regular fa-comments"></i>';
    private $client = null;
    public $notices = [];
    public $limit = AIAUTOTOOL_FREE;
    private $plan_limit_aiautotool ;
    public $name_plan ;
    public $config = array();
    public $notice ;
    public function __construct() {
        $this->name_plan =  __('Auto Comments By AI','ai-auto-tool');
        $this->plan_limit_aiautotool =  'plan_limit_aiautotool_'.$this->active_option_name;
       
        
        $this->notice = new aiautotool_Warning_Notice();
        $this->active = get_option($this->active_option_name, false);
        if ($this->active=='true') {
            $this->init();
        }
        add_action('wp_ajax_update_active_option_canonical_'.$this->active_option_name, array($this, 'update_active_option_callback'));
        add_action('wp_ajax_nopriv_update_active_option_canonical_'.$this->active_option_name, array($this, 'update_active_option_callback'));



        
    }
    private function aiautotool_has_plugin_data() {
        return get_option($this->plan_limit_aiautotool) !== false;
    }
    private function aiautotool_initialize_plugin_data() {
        $current_date = date('Y-m-d');
        
        $expiration = strtotime('+1 month', strtotime($current_date));
        $expiration = date('Y-m-d', $expiration);
        update_option($this->plan_limit_aiautotool, array( 'start_date' => $current_date,'expiration'=>$expiration));
    }
    public function aiautotool_check_post_limit() {
        if (!$this->aiautotool_checklimit($this->config)) {
            /* translators: %s là đường dẫn tới trang nâng cấp lên bản Pro của AI Auto Tool.*/
            $title = sprintf('AI Auto Tool Limit Quota. Please <a class="aiautotool_btn_upgradepro aiautotool_red" href="%s" target="_blank"><i class="fa-solid fa-unlock-keyhole"></i> Upgrade Pro</a>',
                                    aiautotool_premium()->get_upgrade_url()
                                );
            $this->notice->add_notice(
                                $title,
                                'notice-error',
                                null,
                                true,
                                'aiautotool'
                            );
            return false;

        }
        $stored_data = get_option($this->plan_limit_aiautotool, array());
        $current_date = date('Y-m-d');
        
        if ($this->is_new_month($current_date, $stored_data['start_date'])) {
            // Nếu đã qua một tháng, đặt lại số lượng bài đăng và ngày bắt đầu
            $expiration = strtotime('+1 month', strtotime($current_date));
            $expiration = date('Y-m-d', $expiration);
            update_option($this->plan_limit_aiautotool, array( 'start_date' => $current_date,'expiration'=>$expiration));
            update_option($this->usage_option_name, 0,null, 'no');
        }

        
    }

    private function is_new_month($current_date, $start_date) {
        $current_month = date('m', strtotime($current_date));
        $start_month = date('m', strtotime($start_date));

        return ($current_month != $start_month);
    }
    public function aiautotool_checklimit($config) {
        // return ($config['number_post'] !== -1 && $config['number_post'] >= $config['usage']) || $config['number_post'] === -1;
        //     if($config['number_post'] !== -1){
            
        //     if ($config['number_post'] > $config['usage'] ) {
        //        return true;
        //     }else{
        //         return false;
        //     }
        // }else{
         
        // return true;
        // }
        return $this->check_quota();
    }

    public function aiautotool__update_usage() {
        $this->aiautotool_check_post_limit();
        
        // Get the current usage value
        $current_value = get_option($this->usage_option_name, 0);

        // Increment the value by 1
        $new_value = $current_value + 1;
        if($this->config['number_post']!=-1){
            if($this->config['number_post'] >= $new_value){
                update_option($this->usage_option_name, $new_value, 'no');
            }
        }else{
            update_option($this->usage_option_name, $new_value, 'no');
        }
        
        
        return $new_value;
    }
    public function render_plan(){
         if ($this->active=='true') {
           $quota = $this->config['number_post']==-1? 'Unlimited':$this->config['number_post'];
        echo '<p>' . esc_html($this->icon) . ' ' . esc_html($this->name_plan) . ': <strong> Usage: ' . esc_html($this->config['usage']) . '</strong></p>';

       

        }
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
        
        
        $configs = get_option($this->plan_limit_aiautotool, array());
         

        
        
        if (!$this->aiautotool_has_plugin_data()) {
            $this->aiautotool_initialize_plugin_data();
            $configs = get_option($this->plan_limit_aiautotool, array());
        }
        if(isset($configs['expiration'])){
            $this->config  = array(
                'number_post'=>$this->limit,
                'usage'=>get_option($this->usage_option_name, 0, 'no'),
                'time_exprice'=>$configs['expiration']
            );
        }else{
            $this->config  = array(
                'number_post'=>$this->limit,
                'usage'=>get_option($this->usage_option_name, 0, 'no'),
                'time_exprice'=>''
            );
        }
        
        if($this->is_premium()->get_plan_name()=='aiautotoolpro'||$this->is_premium()->get_plan_name()=='premium'){
           
            $this->config  = array(
                'number_post'=>-1,
                'usage'=>get_option($this->usage_option_name, 0, 'no'),
                'time_exprice'=>$this->is_premium()->_get_license()->expiration
            );
        }

        add_action('admin_init', array($this, 'init_settings'));
        add_action('admin_init', array($this, 'aiautotool_check_post_limit'));
        add_action( 'admin_notices', [ $this, 'display_notices' ], 10, 1 );

       if ($this->aiautotool_checklimit($this->config)) {

            add_filter('cron_schedules', array($this, 'aiautotool_schedule_autocomment_intervals'));
            

            if (!wp_next_scheduled('schedule_create_autocomment_event_new')) {
                wp_schedule_event(time(), 'aiautotool_schedule_autocomment_intervals', 'schedule_create_autocomment_event_new');
                
            }

            

            add_action('schedule_create_autocomment_event_new', array($this, 'schedule_create_autocomments'));

        }else{
            
            /* translators: %s là đường dẫn tới trang nâng cấp lên bản Pro của AI Auto Tool.*/
            $title = sprintf('AI Auto Tool Limit Quota. Please <a class="aiautotool_btn_upgradepro aiautotool_red" href="%s" target="_blank"><i class="fa-solid fa-unlock-keyhole"></i> Upgrade Pro</a>',
                                    aiautotool_premium()->get_upgrade_url()
                                );
            $this->notice->add_notice(
                                $title,
                                'notice-error',
                                null,
                                true,
                                'aiautotool'
                            );
        }
        
    }
    public function aiautotool_schedule_autocomment_intervals($schedules) {
         
        $setting = get_option('aiautotool_setting_autocomment');
        $current_interval = 5;
        if(!empty($setting)){
            if(isset($setting['time_comment'])){
                $current_interval = $setting['time_comment'];
            }
        }
        
        
       

        $schedules['aiautotool_schedule_autocomment_intervals'] = array(
            'interval' =>  $current_interval* 60, 
            'display' => 'Schedule create auto Comment'
        );
        return $schedules;
    }

    public function schedule_create_autocomments(){
        if (!$this->aiautotool_checklimit($this->config)) {
           /* translators: %s là đường dẫn tới trang nâng cấp lên bản Pro của AI Auto Tool.*/
            $title = sprintf('AI Auto Tool Limit Quota. Please <a class="aiautotool_btn_upgradepro aiautotool_red" href="%s" target="_blank"><i class="fa-solid fa-unlock-keyhole"></i> Upgrade Pro</a>',
                                    aiautotool_premium()->get_upgrade_url()
                                );
            $this->notice->add_notice(
                                $title,
                                'notice-error',
                                null,
                                true,
                                'aiautotool'
                            );
            return false;

        }
         global $wpdb;
         $generated_posts = get_option('autocomment_generated_posts', array());
         $setting = get_option('aiautotool_setting_autocomment');
         $posttypearr = array('post');
         if(isset($setting['post_type']))
         {
            $posttypearr = $setting['post_type'];
            
         }
         if(!empty($generated_posts)){
            $implode_post_types = "'" . implode("','", $posttypearr) . "'";
            $sql = "SELECT * FROM {$wpdb->prefix}posts 
                WHERE post_status = 'publish' 
                AND post_type IN ($implode_post_types)
                AND ID NOT IN (" . implode(',', $generated_posts) . ")
                LIMIT 1";
            $query = $wpdb->prepare(
                $sql
            );
         }else{
             $query = $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}posts 
                WHERE post_status = 'publish' 
                AND post_type = 'post'
               
                LIMIT 1"
                 );
         }
        

        
         $result = $wpdb->get_results($query);
         // print_r( $result);
         if ($result) {
            foreach ($result as $post) {
                $post_id = $post->ID;
                print_r($post_id);
                $post_title = $post->post_title;
                $post_content = $post->post_content;
                // $post_content = $this->aiautotool_fixcontent_PostContent($post->post_content);
                $lang = get_post_meta($post_id, 'lang', '');
                $bardGenContent = new BardGenContent();
                $question = "The most important: the response must be in the SAME LANGUAGE as the original text (text between \"======\").
Create %%NUMMBERCOMMENT%% comments from readers of the article.
Each comment have a different tone about the post\'s content. Tone must be either positive, negative, informative, argumentative, ironic, sarcastic or comical.
Commenters must have a ====== %%TITLTE%%  ====== intellectual level.
Most of comment must have several syntaxics errors.
The answer must also include neutral nicknames (unrelated to the topic) of one to two words separated by spaces.
The post\'s content is between \"=========\". 
The answers must only be in JSON format, with this exact format, you have to fill empty values,Each item in comments has the form { \"nickname\": \"\", \"comment\": \"\", \"tone\": \"\" }";
                $question = str_replace('%%TITLTE%%',$post_title,$question);
                $question = str_replace('%%NUMMBERCOMMENT%%',rand(8, 15),$question);
                $question = str_replace('%%CONTENT%%',$post_content,$question);

                
                $json = $bardGenContent->bardcontentmore($question,'the SAME LANGUAGE as the original text (text between \"======\")');
                
                $newcontent = $this->aiautotool_fixjsonreturn($json);
                
                if($newcontent){
                     $checkinsert = false;
                    if(count($newcontent)>0){
                        foreach ($newcontent as $key_comment => $comment_data) {
                            $comment_data = (Object)$comment_data;
                            print_r($comment_data);
                            $comment_author = $comment_data->nickname;
                            $comment_content = $comment_data->comment;
                            $comment_post_ID = $post_id;
                            $post_publish_timestamp = strtotime($post->post_date);
                            $current_timestamp = time();
                            $comment_timestamp = mt_rand($post_publish_timestamp, $current_timestamp);
                            $timezone = get_option('timezone_string');
                            if (!empty($timezone)) {
                                date_default_timezone_set($timezone);
                            }
                            
                            $comment_parent = 0;
                            if ($post->post_type == 'product') {
                                $commentdata = array(
                                    'comment_author'   => $comment_author,
                                    'comment_content'  => $comment_content,
                                    'comment_date'     => date( 'Y-m-d H:i:s', $comment_timestamp ),
                                    'comment_date_gmt' => gmdate( 'Y-m-d H:i:s', $comment_timestamp ),
                                    'comment_post_ID'  => $comment_post_ID,
                                    'comment_parent'   => $comment_parent,
                                    'comment_type'     => 'review',
                                    'comment_approved' => true,
                                );
                            }else{
                                $commentdata = array(
                                    'comment_author'   => $comment_author,
                                    'comment_content'  => $comment_content,
                                    'comment_date'     => date( 'Y-m-d H:i:s', $comment_timestamp ),
                                    'comment_date_gmt' => gmdate( 'Y-m-d H:i:s', $comment_timestamp ),
                                    'comment_post_ID'  => $comment_post_ID,
                                    'comment_parent'   => $comment_parent,
                                    'comment_approved' => true,
                                );
                            }
                            
                            // Adding the comment to the database
                            $comment_id = wp_insert_comment( $commentdata );
                            // Storing the comment ID
                            $comments[] = $comment_id;
                            $generated_posts[] = $post_id;
                            update_option('autocomment_generated_posts', $generated_posts);

                            /* translators: %1$s là đường dẫn tới bài viết, %2$s là tiêu đề của bài viết. */
                            $message = sprintf(
                                'A New Comment created for post <a href="%1$s">%2$s</a>',
                                get_permalink($post_id),
                                $post->post_title
                            );

                            $this->notice->add_notice(
                                $message,
                                'notice-info',
                                null,
                                true,
                                $this->name_plan
                            );



                            $checkinsert = true;
                        }
                        
                    }
                    if($checkinsert){
                        $this->aiautotool__update_usage();
                    }
                }else{
                    print_r('không gen dc json');
                }
            }
         }else{
            $this->notice->add_notice( __( 'No Post find ', 'ai-auto-tool' ), 'notice-info', null, true,$this->name_plan );
         }
    }

    private function aiautotool_product_review(){

    }
    private function aiautotool_fixcontent_PostContent($content) {
        
        $cleaned_content = strip_tags($content);

        // $cleaned_content = preg_replace('/[^\w\s]/', '', $cleaned_content);

        $cleaned_content = substr($cleaned_content, 0, 1000);

        return $cleaned_content;
    }
    public function aiautotool_fixjsonreturn($result){
        $pattern = '/\{(?:[^{}]|(?R))*\}/'; 
        preg_match_all($pattern, $result, $matches);
        $arritem = array();
        
        foreach ($matches[0] as $jsonString) {
            
            $decodedJson = json_decode($jsonString, true);
            
            
            if ($decodedJson !== null) {
                if(isset($decodedJson['comments']))
                {
                    foreach($decodedJson['comments'] as $item){
                        $arritem[] = $item;
                    }
                }else{
                    $arritem[] = $decodedJson;
                }
            } else {
                return null;
            }
        }
        return $arritem;
    }
    
    public function display_notices() {
        $screen        = get_current_screen();
        $stored        = get_option( 'aiautotool_submitindex_notices', [] );
        $this->notices = array_merge( $stored, $this->notices );
        delete_option( 'aiautotool_submitindex_notices' );
        foreach ( $this->notices as $notice ) {
            if ( ! empty( $notice['show_on'] ) && is_array( $notice['show_on'] ) && ! in_array( $screen->id, $notice['show_on'], true ) ) {
                return;
            }
            $class = 'notice instant-indexing-notice ' . $notice['class'];
            printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), wp_kses_post( $notice['message'] ) );
        }
    }
    

    
    // Khởi tạo cài đặt
    public function init_settings() {
        register_setting('aiautotool-settings-group', 'aiautotool_setting_autocomment');
        add_settings_section('aiautotool-section', __('AI Auto Tool Settings', 'ai-auto-tool'), array($this, 'section_callback'), 'aiautotool-settings');
        add_settings_field('submitindex_field', __('Information:', 'ai-auto-tool'), array($this, 'submitindex_field_callback'), 'aiautotool-settings', 'aiautotool-section');
        add_settings_field('post_types_field', __('Post Types List:', 'ai-auto-tool'), array($this, 'post_types_field_callback'), 'aiautotool-settings', 'aiautotool-section');
    }

    // Gọi hàm này khi vào trang cài đặt
    
    public function render_setting() {
        if($this->active!="true"){
            return '';
        }
        $setting = get_option('aiautotool_setting_autocomment');
        $current_interval = 1;
        $number_post = 4;
        if(!empty($setting)){
            if(isset($setting['time_comment'])){
                $current_interval = $setting['time_comment'];
            }
            if(isset($setting['number_comment'])){
                $number_post = $setting['number_comment'];
            }
        }else{
            $setting = array('time_comment'=>1,
                                'post_type'=>array('post')
            );
            update_option('aiautotool_setting_autocomment',$setting ,null, 'no');
            $setting = get_option('aiautotool_setting_autocomment');
        }


        
        
    ?>

    <div id="tool-autocomment" class="tab-content" style="display:none;">
        <h1><i class="fa-regular fa-comments"></i> <?php esc_html_e('Config using AI auto create comment', 'ai-auto-tool'); ?></h1>
        <div class="wrap">
            <h3><?php esc_html_e('Config post type for auto create comment', 'ai-auto-tool'); ?></h3>
            <form method="post" action="options.php">
                <?php
                settings_fields('aiautotool-settings-group');
                
                ?>

                <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                    <?php esc_html_e('Time create comment','ai-auto-tool'); ?>
                    </p>
                     <select id="aiautotool_setting_autocomment[time_comment]" name="aiautotool_setting_autocomment[time_comment]">
                            <option value="1" <?php selected($current_interval, 1); ?>>1 minute</option>
                            <option value="5" <?php selected($current_interval, 5); ?>>5 minutes</option>
                            <option value="10" <?php selected($current_interval, 10); ?>>10 minutes</option>
                            <option value="15" <?php selected($current_interval, 15); ?>>15 minutes</option>
                            <option value="30" <?php selected($current_interval, 30); ?>>30 minutes</option>
                            <option value="60" <?php selected($current_interval, 60); ?>>1 hour</option>
                            <option value="180" <?php selected($current_interval, 180); ?>>3 hour</option>
                            <option value="300" <?php selected($current_interval, 300); ?>>5 hour</option>
                            <option value="600" <?php selected($current_interval, 600); ?>>10 hour</option>
                            <option value="900" <?php selected($current_interval, 900); ?>>15 hour</option>
                            <option value="1440" <?php selected($current_interval, 1440); ?>>24 hour</option>
                        </select>
                <!-- <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                   <?php esc_html_e('Number Comment for one Post','ai-auto-tool'); ?>:
                    </p>
                <select id="aiautotool_setting_autocomment[number_comment]" name="aiautotool_setting_autocomment[number_comment]">
                            <?php 
                            for($i=1;$i<=15;$i++){
                                ?>
                                <option value="<?php echo esc_html($i);?>" <?php selected($number_post, $i); ?>><?php echo esc_html($i);?></option>
                                <?php
                            }
                             ?>
                            
                            
                        </select> -->
                
                <p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('Select post type', 'ai-auto-tool'); ?></p>

                <?php
                    $post_types = get_post_types(array( 'public' => true ), 'names' );
                    $i = 0;
                    foreach ($post_types as $post_type) {
                        ?>
                        <label class="nut-switch">
                           <input type="checkbox" name="aiautotool_setting_autocomment[post_type][]" value="<?php echo esc_attr($post_type); ?>" <?php echo in_array($post_type, $setting['post_type']) ? 'checked="checked"' : ''; ?> />

                            <span class="slider"></span>
                        </label>
                        <label class="ft-label-right"><?php esc_html_e('Active :  ', 'ai-auto-tool'); ?>
                           <?php echo esc_html($post_type); ?></label>
                        </p>
                        <?php
                        $i++;
                    }
                    ?>


                <?php submit_button(__('Save Config', 'ai-auto-tool'), 'ft-submit'); ?>
            </form>

            
        </div>
    </div>
    <?php
}


    public function render_tab_setting() {
        if($this->active=="true"){

         echo '<button href="#tool-autocomment" class="nav-tab sotab"><i class="fa-regular fa-comments"></i> '.esc_html__('Auto comment','ai-auto-tool').'</button>';
        }
    }

    public function render_feature() {

       $autoToolBox = new AutoToolBox($this->icon.' '.$this->name_plan, esc_html__('Auto General Comment using AI','ai-auto-tool'), "#", $this->active_option_name, $this->active,plugins_url('../images/logo.svg', __FILE__));

        echo ($autoToolBox->generateHTML());

    }

    // Callback cho section
    public function section_callback() {
        echo '<p>' . esc_html__('Enter information and select Post Types.', 'ai-auto-tool') . '</p>';
    }

    // Callback cho textarea thông tin
    public function submitindex_field_callback() {
        $value = get_option('aiautotool_setting_autocomment');
        echo '<textarea name="aiautotool_setting_autocomment" rows="5" cols="50">' . esc_textarea($value) . '</textarea>';
    }

    // Callback cho danh sách Post Types
    public function post_types_field_callback() {
        $post_types = get_post_types(array('public' => true), 'objects');
        $selected_post_types = get_option('aiautotool_setting_post_types', array());

        foreach ($post_types as $post_type) {
            $checked = in_array($post_type->name, $selected_post_types) ? 'checked' : ''; // 'checked' không cần "checked=\"checked\""
echo '<input type="checkbox" name="aiautotool_setting_post_types[]" value="' . esc_attr($post_type->name) . '" ' . ($checked ? esc_attr($checked) : '') . ' /> ' . esc_html($post_type->label) . '<br>';

        }
    }
    private function get_settings() {
        $settings = get_option( 'aiautotool_setting_autocomment', [] );
        

        return $settings;
    }
    public function get_setting( $setting, $default = null ) {
        $settings = $this->get_settings();

        if ( $setting === 'json_key' ) {
            if(isset($settings[ 'json_key' ])){
                $jsonkey = $settings[ 'json_key' ];
                if(count($jsonkey)>0){
                    $keyrandom = $jsonkey[array_rand($jsonkey)];
                    return $keyrandom;
                }else{
                    return null;
                }
                
                
            }else{
                return null;
            }
        }

        return ( isset( $settings[ $setting ] ) ? $settings[ $setting ] : $default );
    }

   
    public function add_notice( $message, $class = '', $show_on = null, $persist = false, $id = '' ) {
        $notice = [
            'message' => $message,
            'class'   => $class . ' is-dismissible',
            'show_on' => $show_on,
        ];

        if ( ! $id ) {
            $id = md5( serialize( $notice ) );
        }

        if ( $persist ) {
            $notices        = get_option( 'aiautotool_submitindex_notices', [] );
            $notices[ $id ] = $notice;
            update_option( 'aiautotool_submitindex_notices', $notices,null, 'no' );
            return;
        }
        $this->notices[ $id ] = $notice;
    }
   

    
}
 ?>