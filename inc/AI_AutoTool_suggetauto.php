<?php 
defined('ABSPATH') or die();

class AI_AutoTool_suggetauto extends rendersetting{

    // Constructor

    public  $active = true;
    public  $active_option_name = 'AI_AutoTool_suggetauto_active';
    public $aiautotool_config_settings;
    public  $usage_option_name = 'AI_AutoTool_suggetauto_usage';
   
    public  $icon = '<i class="fa-solid fa-lightbulb"></i>';
    private $client = null;
    public $notices = [];
    public $limit = AIAUTOTOOL_FREE;
    private $plan_limit_aiautotool ;
    public $name_plan ;
    public $config = array();
    public $notice ;
    public $aiautotool_setting_suggest_post_draft;
    public $arr_source_ai_model = array(
        'Cloudfalre'=>array(
            '@cf/lykon/dreamshaper-8-lcm',
            '@cf/bytedance/stable-diffusion-xl-lightning'
        ),
        'Huggingface'=>array(
            'stabilityai/stable-diffusion-3-medium-diffusers',
            'stablediffusionapi/realistic-stock-photo'
        )
    );
    public function __construct() {
        $this->name_plan =  __('AI Sugges new Post','ai-auto-tool');
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
            
            $this->notice->add_notice(
                                AIAUTOTOOL_TITLE_UPGRADE,
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
        // echo '<p>'.$this->icon.' '.$this->name_plan.':<strong>  Usage : '.$this->config['usage'].'</strong></p>';
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
         
        $this->aiautotool_setting_suggest_post_draft = get_option('aiautotool_setting_suggest_post_draft');
        
        
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

            add_filter('cron_schedules', array($this, 'aiautotool_schedule_suggest_post_draft_intervals'));
            

            if (!wp_next_scheduled('schedule_create_draft_suggest_post_event_new')) {
                wp_schedule_event(time(), 'aiautotool_schedule_suggest_post_draft_intervals', 'schedule_create_draft_suggest_post_event_new');
                
            }

            

            add_action('schedule_create_draft_suggest_post_event_new', array($this, 'schedule_create_draft_suggest_post'));
            $setting = get_option('aiautotool_setting_suggest_post_draft');
           
        }else{
            

             $this->notice->add_notice(
                                AIAUTOTOOL_TITLE_UPGRADE,
                                'notice-error',
                                null,
                                true,
                                'aiautotool'
                            );
        }
        
    }
    
    public function ai_autotool_add_meta_box() {
        // add_meta_box(
        //     'ai_autotool_meta_box', // ID of the metabox
        //     'AI AutoTool Image Creator', // Title of the metabox
        //     array($this,'ai_autotool_meta_box_content'), // Callback function
        //     'post', // Post type where the metabox will appear (you can change it to 'page' or custom post types if needed)
        //     'side', // Context (normal, side, advanced)
        //     'high' // Priority (default, low, high)
        // );
    }
    public function ai_autotool_meta_box_content($post) {
        
    }

    
    public function ai_autotool_save_meta_box_data($post_id) {
        
        
    }

    public function suggest_prompt() {
            check_ajax_referer('aiautotool_nonce', 'security');

            $title = sanitize_text_field($_POST['title']);

            $prompt = $this->find_prompt_suggestion($title);
            header('Content-Type: application/json; charset=utf-8');

            echo json_encode(array('prompt' => $prompt));
            wp_die();
        }

        private function find_prompt_suggestion($title) {
            
            return $this->find_prompt($title,'');
        }

    public function aiautotool_schedule_suggest_post_draft_intervals($schedules) {
         
        $setting = get_option('aiautotool_setting_suggest_post_draft');
        $current_interval = 5;
        if(!empty($setting)){
            if(isset($setting['time_suggest_post_draft'])){
                $current_interval = $setting['time_suggest_post_draft'];
            }
        }
        
        
       

        $schedules['aiautotool_schedule_suggest_post_draft_intervals'] = array(
            'interval' =>  $current_interval* 60, 
            'display' => 'Schedule auto suggest post title'
        );
        return $schedules;
    }


    public function schedule_create_draft_suggest_post() {
    if (!$this->aiautotool_checklimit($this->config)) {
        $this->notice->add_notice(
            AIAUTOTOOL_TITLE_UPGRADE,
            'notice-error',
            null,
            true,
            'aiautotool'
        );
        return false;
    }

    global $wpdb;
    $setting = get_option('aiautotool_setting_suggest_post_draft');
    $posttypearr = array('post');
    if (isset($setting['post_type'])) {
        $posttypearr = $setting['post_type'];
    }

    // Query lấy ra 20 bài viết ngẫu nhiên
    $query = "
        SELECT ID, post_title 
        FROM {$wpdb->prefix}posts 
        WHERE post_status = 'publish' 
        AND post_type = 'post' 
        ORDER BY RAND() 
        LIMIT 20";
    
    $result = $wpdb->get_results($query);
    
    if ($result) {
        $titles = array_map(function($post) {
            return $post->post_title;
        }, $result);
        
        // Gọi function find_sugget_title để lấy dữ liệu JSON
        $suggestions = $this->find_sugget_title($titles);

        // Duyệt qua các kết quả và cập nhật title của bài viết thành dạng draft
        if (is_array($suggestions)) {
            foreach ($suggestions as $suggestion) {
                if (isset($suggestion['title'])) {
                    global $wpdb;

                    // Kiểm tra nếu tiêu đề đã tồn tại
                    $title_exists = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_title = %s AND post_status = 'publish' AND post_type = 'post'",
                        $suggestion['title']
                    ));

                    if ($title_exists == 0) {
                        $user_ids = get_users( array(
                            'fields' => 'ID',
                        ) );
                        $random_user_id = $user_ids[array_rand($user_ids)];
                        $title = ucwords(mb_strtolower(html_entity_decode($suggestion['title'])));
                        $random_user_id = $user_ids[array_rand($user_ids)];
                        $user_id = $random_user_id;

                        $new_post = array(
                            'post_title'   => $title,
                            'post_content' => '', // Có thể thêm nội dung nếu cần
                            'post_author'=>$user_id,
                            'post_status'  => 'draft',
                            'post_type'    => 'post', // Hoặc loại bài viết khác nếu cần
                        );

                        $post_id = wp_insert_post($new_post);

                        if ($post_id) {
                          
                            if (!empty($suggestion['categorie'])) {
                                $categories = array_map('trim', explode(',', $suggestion['categorie']));
                                $category_ids = array();

                                foreach ($categories as $category) {
                                    // Kiểm tra xem danh mục đã tồn tại chưa
                                    $category_id = term_exists($category, 'category');

                                    if (!$category_id) {
                                        // Nếu danh mục chưa tồn tại, tạo mới
                                        $category_id = wp_create_category($category);
                                    }

                                    // Thêm danh mục vào mảng danh mục ID
                                    if (is_array($category_id)) {
                                        $category_id = $category_id['term_id'];
                                    }
                                    $category_ids[] = $category_id;
                                }

                                // Gán danh mục cho bài viết
                                wp_set_post_categories($post_id, $category_ids);
                            }
                            if (!empty($suggestion['tags'])) {
                                $tags = explode(',', $suggestion['tags']);
                                wp_set_post_tags($post_id, $tags);
                            }

                            if (isset($suggestion['langue'])) {
                                update_post_meta($post_id, 'lang', $suggestion['langue']);
                            }
                           // translators: %s là tiêu đề của bài viết nháp.
                            $smp = __( 'Created new draft post with title: %s', 'ai-auto-tool' );

                            $this->notice->add_notice(
                                sprintf($smp, $suggestion['title']),
                                'notice-info',
                                null,
                                true,
                                $this->name_plan
                            );

                        } else {
                            $this->notice->add_notice( __( 'Failed to create draft post with title ' , 'ai-auto-tool' ), 'notice-error', null, true ,$this->name_plan);
                        }
                    } else {
                        $this->notice->add_notice( __( 'Draft post with title already exists ', 'ai-auto-tool' ), 'notice-info', null, true ,$this->name_plan);
                    }
                }
            }

             $this->aiautotool__update_usage();
        } else {
            $this->notice->add_notice( __( 'No suggestions found', 'ai-auto-tool' ), 'notice-info', null, true ,$this->name_plan );
        }


    } else {
        $this->notice->add_notice( __( 'No Posts found', 'ai-auto-tool' ), 'notice-info', null, true,$this->name_plan );
    }
}

    public function find_sugget_title($titles) {
        // Check if titles is an array
        if (!is_array($titles)) {
            return json_encode(array('error' => 'Invalid input'));
        }
        $setting = get_option('aiautotool_setting_suggest_post_draft');
        $number_post_title_draft = $setting['number_post_title_draft'];
        // Create the prompt string using the titles
        $prompt = "the response must be in the SAME LANGUAGE as the original list of articles. Please suggest ".$number_post_title_draft." new titles for the following list of articles:\n\n";
        foreach ($titles as $title) {
            $prompt .= "- $title\n";
        }
        $prompt .= 'the response must be in the SAME LANGUAGE as the original list of articles. The answers must only be in JSON format, with this exact format, you have to fill empty values,Each item in comments has the form { \"title\": \"\",\"langue\": \"\",\"categorie\": \"\",\"tags\": \"\" }. the response must be in the SAME LANGUAGE as the original list of articles. If the title has a date (day, month, or year), the result must contain the latest year.The language of categorie and tags must match the language of the title.';
        $bardGenContent = new BardGenContent();
            $json = $bardGenContent->bardcontentmore($prompt,'');
            
             $json = $this->aiautotool_fixjsonreturn($json);
            // print_r($json);
            if($json){
                return $json;
            }else{
                return false;
            }
     
    }


        private function generate_thumbnail_image($title, $content) {

            if($title){
                $setting = get_option('aiautotool_setting_suggest_post_draft');
                if($setting['source_img_ai']=='Cloudfalre'){
                    if(!empty($setting['id_account_cl'])&&!empty($setting['apikey'])){
                        $imgCloudflare = new ImgCloudflare($setting['id_account_cl'], $setting['apikey']);
                        $image = $imgCloudflare->get_img($this->find_prompt($title,$content),$setting['model_ai']);
                        
                        if ($image) {
                            
                                $upload_dir = wp_upload_dir();
                                $filename = sanitize_file_name(strtolower($title)).'_'.time(). '.png';
                                if(wp_mkdir_p($upload_dir['path'])) {
                                    $file = $upload_dir['path'] . '/' . $filename;
                                } else {
                                    $file = $upload_dir['basedir'] . '/' . $filename;
                                }
                                file_put_contents($file, $image);
                                $image_size = @getimagesize($file);
                                if ($image_size !== false) {
                                    return $file;
                                }else{
                                    return false;
                                }
                                
                            
                            
                        }
                    }
                }else if($setting['source_img_ai']=='Huggingface'){
                    if (!empty($setting['hunggingfacetoken'])&&!empty($setting['hunggingface_model_ai'])) {
                        $imgCloudflare = new ImgCloudflare('','');
                        $image = $imgCloudflare->hungingfaceimg($setting['hunggingfacetoken'], $this->find_prompt($title,$content), $setting['hunggingface_model_ai']);
                        if ($image) { 
                            $upload_dir = wp_upload_dir();
                                $filename = sanitize_file_name(strtolower($title)).'_'.time(). '.png';
                                if(wp_mkdir_p($upload_dir['path'])) {
                                    $file = $upload_dir['path'] . '/' . $filename;
                                } else {
                                    $file = $upload_dir['basedir'] . '/' . $filename;
                                }
                                file_put_contents($file, $image);
                                $image_size = @getimagesize($file);
                                if ($image_size !== false) {
                                    return $file;
                                }else{
                                    return false;
                                }
                            
                        }
                    }

                    

                
                
                return false;
            }
            
            return false;
        }
    }
        public function find_prompt($title,$content){
            $string = "You are a prompt engineer. Your task is to carefully analyze the provided blog title and content. And create a prompt for the stable-diffusion-xl-lightning model, enabling the generation of a visual that matches the text. This prompt should concisely capture the essence, main themes, and nuances of the provided blog post, aiming to facilitate the creation of the most accurate and engaging image that reflects the main message and context without being overly complex or detailed. The prompt must be that is simple enough to generate an image and not too detailed. You must create a different prompt each time a request is sent.  You must generate only a prompt. The prompt must be in English. Title: '{$title}, content : '".strip_tags($content);
            $bardGenContent = new BardGenContent();
            $json = $bardGenContent->bardcontentmore($string,'English');
            if($json){
                return $json;
            }else{
                return "Thumbnail for Article: ".$title;
            }
        }
        private function set_post_thumbnail($post_id, $image_url) {
            $upload_dir = wp_upload_dir();
            $image_data = file_get_contents($image_url);
            $filename = basename($image_url);
            if(wp_mkdir_p($upload_dir['path'])) {
                $file = $upload_dir['path'] . '/' . $filename;
            } else {
                $file = $upload_dir['basedir'] . '/' . $filename;
            }
            file_put_contents($file, $image_data);

            $wp_filetype = wp_check_filetype($filename, null );
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => sanitize_file_name($filename),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            $attach_id = wp_insert_attachment($attachment, $file, $post_id);
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attach_id, $file);
            wp_update_attachment_metadata($attach_id, $attach_data);

            // Set as post thumbnail
            set_post_thumbnail($post_id, $attach_id);
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
        register_setting('aiautotool-settings-suggest-group', 'aiautotool_setting_suggest_post_draft');
        add_settings_section('aiautotool-section', __('AI Auto Tool Settings', 'ai-auto-tool'), array($this, 'section_callback'), 'aiautotool-settings');
        add_settings_field('submitindex_field', __('Information:', 'ai-auto-tool'), array($this, 'submitindex_field_callback'), 'aiautotool-settings', 'aiautotool-section');
        add_settings_field('post_types_field', __('Post Types List:', 'ai-auto-tool'), array($this, 'post_types_field_callback'), 'aiautotool-settings', 'aiautotool-section');
    }

    // Gọi hàm này khi vào trang cài đặt
    
    public function render_setting() {
        if($this->active!="true"){
            return '';
        }
        $setting = get_option('aiautotool_setting_suggest_post_draft');
        $current_interval = 1;
        $current_model = '';
        $number_post = 4;
        if(!empty($setting)){
            if(isset($setting['time_suggest_post_draft'])){
                $current_interval = $setting['time_suggest_post_draft'];
            }
            if(isset($setting['number_comment'])){
                $number_post = $setting['number_comment'];
            }

            if(isset($setting['model_ai'])){
                $current_model = $setting['model_ai'];
            }

            
        }else{
            $setting = array('time_suggest_post_draft'=>1,
                                'post_type'=>array('post')
            );
            update_option('aiautotool_setting_suggest_post_draft',$setting ,null, 'no');
            $setting = get_option('aiautotool_setting_suggest_post_draft');
        }


    ?>

    <div id="tool-suggest" class="tab-content" style="display:none;">
        <h1>
    <?php echo esc_html($this->icon); ?> <?php esc_html_e(' Config AI Auto suggest title post ', 'ai-auto-tool'); ?>
</h1>

        <div class="wrap">
            <h3><?php esc_html_e('Configure AI-powered title suggestion runtime ', 'ai-auto-tool'); ?></h3>
            <form method="post" action="options.php">
                <?php
                settings_fields('aiautotool-settings-suggest-group');
                
                ?>
                
                
             <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                    <?php esc_html_e('Time create draft post with title suggest','ai-auto-tool'); ?>
                    </p>
                     <select id="aiautotool_setting_suggest_post_draft[time_suggest_post_draft]" name="aiautotool_setting_suggest_post_draft[time_suggest_post_draft]">
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
                 <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                   <?php esc_html_e('Number title need create when run','ai-auto-tool'); ?>:
                    </p>
                <select id="aiautotool_setting_suggest_post_draft[number_post_title_draft]" name="aiautotool_setting_suggest_post_draft[number_post_title_draft]">
                            <?php 
                            for($i=1;$i<=15;$i++){
                                ?>
                                <option value="<?php echo esc_html($i);?>" <?php selected($number_post, $i); ?>><?php echo esc_html($i);?></option>
                                <?php
                            }
                             ?>
                            
                            
                        </select> 
                

                <?php submit_button(esc_html__('Save Config', 'ai-auto-tool'), 'ft-submit'); ?>
            </form>

            
        </div>
    </div>
    <?php
}


    public function render_tab_setting() {
        if($this->active=="true"){
            echo '<button href="#tool-suggest" class="nav-tab sotab"> ' . esc_html($this->icon) . esc_html__(' AI Suggest New Post', 'ai-auto-tool') . '</button>';

         
        }
    }

    public function render_feature() {

       $autoToolBox = new AutoToolBox($this->icon.' '.$this->name_plan, __('The AI Suggest New Post feature automatically scans 20 existing titles on the website at random. It then uses Gemini AI to generate a list of topic-related titles based on the scanned titles and saves them as drafts. The Auto Blogging feature will publish these drafts according to a preset schedule. This allows the website to operate fully automatically based on AI, which is particularly effective when the site has over 100 initial posts with various categories and topics.','ai-auto-tool'), "#", $this->active_option_name, $this->active,plugins_url('../images/logo.svg', __FILE__));
       echo ($autoToolBox->generateHTML());

       
    }

    // Callback cho section
    public function section_callback() {
        echo '<p>' . esc_html__('Enter information and select Post Types.', 'ai-auto-tool') . '</p>';
    }

    // Callback cho textarea thông tin
    public function submitindex_field_callback() {
        $value = get_option('aiautotool_setting_suggest_post_draft');
        echo '<textarea name="aiautotool_setting_suggest_post_draft" rows="5" cols="50">' . esc_textarea($value) . '</textarea>';
    }

    // Callback cho danh sách Post Types
    public function post_types_field_callback() {
        $post_types = get_post_types(array('public' => true), 'objects');
        $selected_post_types = get_option('aiautotool_setting_post_types', array());

        foreach ($post_types as $post_type) {
           $checked = in_array($post_type->name, $selected_post_types) ? 'checked' : ''; // Không cần "checked=\"checked\""
echo '<input type="checkbox" name="aiautotool_setting_post_types[]" value="' . esc_attr($post_type->name) . '" ' . ($checked ? esc_attr($checked) : '') . ' /> ' . esc_html($post_type->label) . '<br>';

        }
    }
    private function get_settings() {
        $settings = get_option( 'aiautotool_setting_suggest_post_draft', [] );
        

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

