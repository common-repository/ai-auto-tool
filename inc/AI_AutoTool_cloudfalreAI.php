<?php 
defined('ABSPATH') or die();

class AI_AutoTool_cloudflareAI extends rendersetting{

   

    public  $active = true;
    public  $active_option_name = 'Aiautotool_tool_cloudflareAI_active';
    public $aiautotool_config_settings;
    public  $usage_option_name = 'cloudflareAI_usage';
   
    public  $icon = '<i class="fa-brands fa-cloudflare"></i>';
    private $client = null;
    public $notices = [];
    public $limit = AIAUTOTOOL_FREE;
    private $plan_limit_aiautotool ;
    public $name_plan ;
    public $config = array();
    public $notice ;
    public $aiautotool_setting_cloudflare_image;
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
        $this->name_plan =  __('Image AI Generator','ai-auto-tool');
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
          

        $quota = $this->config['number_post'] == -1 ? 'Unlimited' : esc_html($this->config['number_post']);
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
         
        $this->aiautotool_setting_cloudflare_image = get_option('aiautotool_setting_cloudflare_image');
        
        
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

            add_filter('cron_schedules', array($this, 'aiautotool_schedule_image_auto_cloudflare_intervals'));
            

            if (!wp_next_scheduled('schedule_create_image_auto_cloudflare_event_new')) {
                wp_schedule_event(time(), 'aiautotool_schedule_image_auto_cloudflare_intervals', 'schedule_create_image_auto_cloudflare_event_new');
                
            }

            

            add_action('schedule_create_image_auto_cloudflare_event_new', array($this, 'schedule_create_image_auto_cloudflare'));
            $setting = get_option('aiautotool_setting_cloudflare_image');
            if(!empty($setting['id_account_cl'])&&!empty($setting['apikey'])){

                 wp_register_script('kct_cloudflareai', plugin_dir_url( __FILE__ ) .'../js/cloudflareai.js', '', '1.2'.rand(), true);
                 
                 wp_enqueue_script('kct_cloudflareai');


                 add_action('wp_ajax_aiautotool_cloudfalre_generate_image', array($this, 'generate_image'));


                add_action('wp_ajax_aiautotool_suggest_prompt', array($this, 'suggest_prompt'));

            }

            add_action('add_meta_boxes', array($this,'ai_autotool_add_meta_box'));
            add_action('save_post', array($this,'ai_autotool_save_meta_box_data'));
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
    public function generate_image() {
        check_ajax_referer('aiautotool_nonce', 'security');

        $prompt = sanitize_text_field($_POST['prompt']);
        $title = sanitize_text_field($_POST['title']);
        $model = sanitize_text_field($_POST['model']);
        $source_ai_model = sanitize_text_field($_POST['source_ai_model']);

        if ($prompt) {
            $setting = get_option('aiautotool_setting_cloudflare_image');

            if($source_ai_model =='Cloudfalre'){
                if (!empty($setting['id_account_cl']) && !empty($setting['apikey'])) {
                $imgCloudflare = new ImgCloudflare($setting['id_account_cl'], $setting['apikey']);
                $image = $imgCloudflare->get_img($prompt, $model);

                    if ($image) {
                       
                        
                        
                            $upload_dir = wp_upload_dir();
                            $filename = sanitize_file_name(strtolower($title)).'_'.time(). '.png';
                            $file_path = $upload_dir['path'] . '/' . $filename;

                            file_put_contents($file_path, $image);
                            $image_size = @getimagesize($file_path);
                            if ($image_size !== false) {
                                $wp_file_type = wp_check_filetype($filename, null);
                                $attachment = array(
                                    'post_mime_type' => $wp_file_type['type'],
                                    'post_title'     => sanitize_file_name($title),
                                    'post_content'   => '',
                                    'post_status'    => 'inherit'
                                );

                                // Thêm hình ảnh vào thư viện Media
                                $attachment_id = wp_insert_attachment($attachment, $file_path);

                                // Tạo metadata cho hình ảnh
                                require_once(ABSPATH . 'wp-admin/includes/image.php');
                                $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
                                wp_update_attachment_metadata($attachment_id, $attachment_data);

                                // Lấy URL hình ảnh
                                $image_url = wp_get_attachment_url($attachment_id);

                                echo esc_url($image_url);

                                wp_die();
                            }else{
                                echo false;
                                wp_die();
                            }
                            
                        
                        
                    }
                }
                echo false;
                wp_die();
            }else if($source_ai_model =='Huggingface'){
                if (!empty($setting['hunggingfacetoken'])) {
                    $imgCloudflare = new ImgCloudflare('','');
                    $image = $imgCloudflare->hungingfaceimg($setting['hunggingfacetoken'], $prompt, $model);
                    if ($image) {
                        
                            $upload_dir = wp_upload_dir();
                            $filename = sanitize_file_name(strtolower($title)).'_'.time(). '.png';
                            $file_path = $upload_dir['path'] . '/' . $filename;

                            file_put_contents($file_path, $image);
                            $image_size = @getimagesize($file_path);
                            if ($image_size !== false) {
                                
                                $wp_file_type = wp_check_filetype($filename, null);
                                $attachment = array(
                                    'post_mime_type' => $wp_file_type['type'],
                                    'post_title'     => sanitize_file_name($title),
                                    'post_content'   => '',
                                    'post_status'    => 'inherit'
                                );

                                $attachment_id = wp_insert_attachment($attachment, $file_path);

                                require_once(ABSPATH . 'wp-admin/includes/image.php');
                                $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
                                wp_update_attachment_metadata($attachment_id, $attachment_data);

                                $image_url = wp_get_attachment_url($attachment_id);

                                
                                echo esc_url($image_url);

                                wp_die();
                            }else{
                                echo false;
                                 wp_die();
                            }

                        
                    }
                    echo false;
                    wp_die();
                }else{
                    echo false;
                    wp_die();
                }
            }
            

            echo false;
            wp_die();
        }

        echo false;
        wp_die();
    }

    public function ai_autotool_add_meta_box() {
        add_meta_box(
            'ai_autotool_meta_box', // ID of the metabox
            'AI AutoTool Image Creator', // Title of the metabox
            array($this,'ai_autotool_meta_box_content'), // Callback function
            'post', // Post type where the metabox will appear (you can change it to 'page' or custom post types if needed)
            'side', // Context (normal, side, advanced)
            'high' // Priority (default, low, high)
        );
    }
    public function ai_autotool_meta_box_content($post) {
         $setting = get_option('aiautotool_setting_cloudflare_image');
            if (!empty($setting['id_account_cl']) && !empty($setting['apikey'])) {

        wp_nonce_field('ai_autotool_meta_box', 'ai_autotool_meta_box_nonce');
        echo '<style>

</style>';

        // Metabox content
        ?>
        <div class="formkct1">
            <div class="form">
        <button id="ai_autotool_suggest_prompt" class="btnkct1 btnkct1--gooey">Suggest Prompt
              <div class="btnkct1__blobs">
              <div></div>
              <div></div>
              <div></div>
              </div>
            </button>
            <?php
        
         echo '<div class="form-group"><label for="ai_autotool_prompt">Prompt:</label>';
        echo '<textarea id="ai_autotool_prompt" name="ai_autotool_prompt" rows="4" style="width:100%;"></textarea></div>';


 $setting = get_option('aiautotool_setting_cloudflare_image');
        $selected_source = isset($setting['source_img_ai']) ? $setting['source_img_ai'] : '';


        ?>

        <div>
            <div class="form-group">
            <!-- Select box cho nguồn AI -->
            <label for="source_ai_model">Source AI:</label>
            <select id="source_ai_model" name="source_ai_model">
                <?php
                foreach ($this->arr_source_ai_model as $source => $models) {
                    $selected = ($source === $selected_source) ? ' selected' : '';

                    echo '<option value="' . esc_attr($source) . '"' . esc_attr($selected) . '>' . esc_html($source) . '</option>';
                }

                ?>
            </select>
            </div>
        </div>
        
        <div>
            <div class="form-group">
            <!-- Select box cho model AI -->
            <label for="modal_ai">Model AI:</label>
            <select id="ai_autotool_model" name="ai_autotool_model">
                <option value="">--Model AI--</option>
                <!-- Options sẽ được cập nhật bằng JavaScript -->
            </select>
            </div>
        </div>

        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                var sourceSelect = document.getElementById('source_ai_model');
                var modalSelect = document.getElementById('ai_autotool_model');
                var arrSourceModel = <?php echo json_encode($this->arr_source_ai_model); ?>;

                function updateModels() {
                    var selectedSource = sourceSelect.value;
                    var models = arrSourceModel[selectedSource] || [];
                    modalSelect.innerHTML = '';

                    models.forEach(function(model) {
                        var option = document.createElement('option');
                        option.value = model;
                        option.text = model;
                        modalSelect.add(option);
                    });
                }

                sourceSelect.addEventListener('change', updateModels);
                updateModels();
                
                var selectedModel = '<?php echo isset($setting['modal_ai']) ? esc_js($setting['modal_ai']) : ''; ?>';
                if (selectedModel) {
                    var option = document.createElement('option');
                    option.value = selectedModel;
                    option.text = selectedModel;
                    modalSelect.add(option);
                    modalSelect.value = selectedModel;
                }
            });
        </script>
        <?php
    
        
        ?>
        <button id="ai_autotool_generate_image" class="btnkct1 btnkct1--gooey">Generate Image
              <div class="btnkct1__blobs">
              <div></div>
              <div></div>
              <div></div>
              </div>
            </button>
            </div>
            </div>
        <?php
        // echo '<button type="button" id="ai_autotool_generate_image" style="margin-top: 10px;">Generate Image</button>';
        echo '<div id="img_gen" style="margin-top: 20px;"></div>';
    }else{
        echo '<label>Please set API Token Key Worker AI Cloudfalre.</label>';
    }
    }

    
    public function ai_autotool_save_meta_box_data($post_id) {
        
        
    }

    public function suggest_prompt() {
            check_ajax_referer('aiautotool_nonce', 'security');

            $title = sanitize_text_field($_POST['title']);
            $content = sanitize_text_field($_POST['content']);

            $prompt = $this->find_prompt_suggestion($title,$content);
            header('Content-Type: application/json; charset=utf-8');

            echo json_encode(array('prompt' => $prompt));
            wp_die();
        }

        private function find_prompt_suggestion($title,$content='') {
            
            return $this->find_prompt($title,$content);
        }

    public function aiautotool_schedule_image_auto_cloudflare_intervals($schedules) {
         
        $setting = get_option('aiautotool_setting_cloudflare_image');
        $current_interval = 5;
        if(!empty($setting)){
            if(isset($setting['time_cloudflare_image'])){
                $current_interval = $setting['time_cloudflare_image'];
            }
        }
        
        
       

        $schedules['aiautotool_schedule_image_auto_cloudflare_intervals'] = array(
            'interval' =>  $current_interval* 60, 
            'display' => 'Schedule create auto Comment'
        );
        return $schedules;
    }

    public function schedule_create_image_auto_cloudflare() {
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
        $generated_posts = get_option('autoimagecloudflare_generated_posts', array());
        $setting = get_option('aiautotool_setting_cloudflare_image');
        $posttypearr = array('post');
        if(isset($setting['post_type'])) {
            $posttypearr = $setting['post_type'];
        }

        
//  AND p.ID NOT IN (" . implode(',', $generated_posts) . ")
        // if (!empty($generated_posts)) {
        //     $implode_post_types = "'" . implode("','", $posttypearr) . "'";
        //     $sql = "SELECT * FROM {$wpdb->prefix}posts p
        //             LEFT JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_thumbnail_id'
        //             WHERE p.post_status = 'publish'
        //             AND p.post_type IN ($implode_post_types)
                   
        //             AND pm.post_id IS NULL
        //             ORDER BY p.post_date DESC
        //             LIMIT 1";
        //     $query = $wpdb->prepare($sql);
        // } else {
            $query = $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}posts p
                LEFT JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_thumbnail_id'
                WHERE p.post_status = 'publish'
                AND p.post_type = 'post'
                AND pm.post_id IS NULL
                ORDER BY p.post_date DESC
                LIMIT 1"
            );
        // } 


        $result = $wpdb->get_results($query);
        if ($result) {
            foreach ($result as $post) {
                $post_id = $post->ID;
                $post_title = $post->post_title;
                $post_content = $post->post_content;
                $image_url = $this->generate_thumbnail_image($post_title, $post_content);

                if($image_url) {
                    $this->set_post_thumbnail($post_id, $image_url);

                    $generated_posts[] = $post_id;
                    update_option('autoimagecloudflare_generated_posts', $generated_posts);
                    // translators: %1$s là đường dẫn tới bài viết, %2$s là tiêu đề của bài viết.
                    $message = sprintf(
                        'A New Thumbnail created for post <a href="%1$s">%2$s</a>',
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


                   
                    
                    $this->aiautotool__update_usage();
                } else {
                    $this->notice->add_notice( __( 'Failed to generate image', 'ai-auto-tool' ), 'notice-error', null, true ,$this->name_plan);
                }
            }
        } else {
            $this->notice->add_notice( __( 'No Post found', 'ai-auto-tool' ), 'notice-info', null, true,$this->name_plan );
        }
    }

        private function generate_thumbnail_image($title, $content) {

            if($title){
                $setting = get_option('aiautotool_setting_cloudflare_image');
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
        register_setting('aiautotool-settings-group', 'aiautotool_setting_cloudflare_image');
        add_settings_section('aiautotool-section', __('AI Auto Tool Settings', 'ai-auto-tool'), array($this, 'section_callback'), 'aiautotool-settings');
        add_settings_field('submitindex_field', __('Information:', 'ai-auto-tool'), array($this, 'submitindex_field_callback'), 'aiautotool-settings', 'aiautotool-section');
        add_settings_field('post_types_field', __('Post Types List:', 'ai-auto-tool'), array($this, 'post_types_field_callback'), 'aiautotool-settings', 'aiautotool-section');
    }

    // Gọi hàm này khi vào trang cài đặt
    
    public function render_setting() {
        if($this->active!="true"){
            return '';
        }
        $setting = get_option('aiautotool_setting_cloudflare_image');
        $current_interval = 1;
        $current_model = '';
        $number_post = 4;
        if(!empty($setting)){
            if(isset($setting['time_cloudflare_image'])){
                $current_interval = $setting['time_cloudflare_image'];
            }
            if(isset($setting['number_comment'])){
                $number_post = $setting['number_comment'];
            }

            if(isset($setting['model_ai'])){
                $current_model = $setting['model_ai'];
            }

            
        }else{
            $setting = array('time_cloudflare_image'=>1,
                                'post_type'=>array('post')
            );
            update_option('aiautotool_setting_cloudflare_image',$setting ,null, 'no');
            $setting = get_option('aiautotool_setting_cloudflare_image');
        }


    
    if(empty($setting['source_img_ai']))
    {
        $source_img = 'Cloudfalre';
    }else{
        $source_img = $this->aiautotool_setting_cloudflare_image['source_img_ai'];
    }

    if(empty($setting['hunggingface_model_ai']))
    {
        $hunggingface_model_ai = 'stabilityai/stable-diffusion-3-medium-diffusers';
    }else{
        $hunggingface_model_ai = $this->aiautotool_setting_cloudflare_image['hunggingface_model_ai'];
    }

    
    ?>

    <div id="tool-cloudflareai" class="tab-content" style="display:none;">
        <h1> <?php echo esc_attr($this->icon); esc_html_e(' Config AI auto create Image ', 'ai-auto-tool'); ?></h1>
        <div class="wrap">
            <h3><?php esc_html_e('Choice default source Create Image auto ', 'ai-auto-tool'); ?></h3>
            <form method="post" action="options.php">
                <?php
                settings_fields('aiautotool-settings-group');
                
                ?>
                <?php foreach ($this->arr_source_ai_model as $key => $value) { ?>
                    <label class="nut-switch">
                        <input type="radio" name="aiautotool_setting_cloudflare_image[source_img_ai]" value="<?php echo esc_attr($key); ?>" <?php echo esc_attr($key == $setting['source_img_ai'] ? 'checked="checked"' : ''); ?> />
                        <span class="slider"></span>
                    </label>
                    <label class="ft-label-right"><?php echo esc_html($key); ?></label>
                <?php } ?>

                
                
                <h3><?php esc_html_e('Config api key Cloudfalre  ', 'ai-auto-tool'); ?></h3>
                <div id="cloudfalre">
                 <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                    <?php esc_html_e('Api Token Key for Workers AI Cloudflare','ai-auto-tool'); ?> : <a href="https://developers.cloudflare.com/fundamentals/api/get-started/create-token/" target="_blank"><?php esc_html_e('Document help','ai-auto-tool'); ?></a>

                    <input class="ft-input-big" placeholder="API Token Key..." type="text" name="aiautotool_setting_cloudflare_image[apikey]" value="<?php echo esc_attr(!empty($this->aiautotool_setting_cloudflare_image['apikey']) ? esc_attr($this->aiautotool_setting_cloudflare_image['apikey']) : ''); ?>">

                       
                    
                </p>
                <p class="ft-note"><i class="fa-solid fa-lightbulb"></i> 
                    <?php esc_html_e('ID Account cloudflare','ai-auto-tool'); ?>  <a href="https://developers.cloudflare.com/fundamentals/setup/find-account-and-zone-ids/" target="_blank"><?php esc_html_e('Document help','ai-auto-tool'); ?></a>
                    
                     <input class="ft-input-big" placeholder="Account ID..." type="text" name="aiautotool_setting_cloudflare_image[id_account_cl]" value="<?php if(!empty($this->aiautotool_setting_cloudflare_image['id_account_cl'])){echo esc_attr($this->aiautotool_setting_cloudflare_image['id_account_cl']);} ?>">
                </p>
                <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                    <?php esc_html_e('Model Image','ai-auto-tool'); ?>
                    <select id="aiautotool_setting_cloudflare_image[model_ai]" name="aiautotool_setting_cloudflare_image[model_ai]">
                            <option value="@cf/lykon/dreamshaper-8-lcm" <?php selected($current_model, '@cf/lykon/dreamshaper-8-lcm'); ?>>@cf/lykon/dreamshaper-8-lcm</option>
                            <option value="@cf/bytedance/stable-diffusion-xl-lightning" <?php selected($current_model, '@cf/bytedance/stable-diffusion-xl-lightning'); ?>>@cf/bytedance/stable-diffusion-xl-lightning</option>
                            
                        </select>
                </p>
                </div>
                <h3><?php esc_html_e('Config api key Huggingface  ', 'ai-auto-tool'); ?></h3>
                <div id="hunggingface">
                    <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                    <?php esc_html_e('Api Token Key Huggingface ','ai-auto-tool'); ?> : <a href="https://huggingface.co/docs/api-inference/quicktour#get-your-api-token" target="_blank"><?php esc_html_e('Document help Get your API Token','ai-auto-tool'); ?></a>
                        <input class="ft-input-big" placeholder="API Token Key..." type="text" name="aiautotool_setting_cloudflare_image[hunggingfacetoken]" value="<?php if(!empty($this->aiautotool_setting_cloudflare_image['hunggingfacetoken'])){echo esc_attr($this->aiautotool_setting_cloudflare_image['hunggingfacetoken']);} ?>">
                    
                    </p>

                    <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                    <?php esc_html_e('Model Image','ai-auto-tool'); ?>
                    <select id="aiautotool_setting_cloudflare_image[hunggingface_model_ai]" name="aiautotool_setting_cloudflare_image[hunggingface_model_ai]">
                            <option value="stabilityai/stable-diffusion-3-medium-diffusers" <?php selected($hunggingface_model_ai, 'stabilityai/stable-diffusion-3-medium-diffusers'); ?>>stabilityai/stable-diffusion-3-medium-diffusers</option>
                            <option value="stablediffusionapi/realistic-stock-photo" <?php selected($hunggingface_model_ai, 'stablediffusionapi/realistic-stock-photo'); ?>>stablediffusionapi/realistic-stock-photo</option>
                            
                        </select>
                </p>

                </div>
                <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                    <?php esc_html_e('Time create Image','ai-auto-tool'); ?>
                    </p>
                     <select id="aiautotool_setting_cloudflare_image[time_cloudflare_image]" name="aiautotool_setting_cloudflare_image[time_cloudflare_image]">
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
                   <?php esc_html_e('Number Image for one Post','ai-auto-tool'); ?>:
                    </p>
                <select id="aiautotool_setting_cloudflare_image[number_comment]" name="aiautotool_setting_cloudflare_image[number_comment]">
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
                            <input type="checkbox" name="aiautotool_setting_cloudflare_image[post_type][]" value="<?php echo esc_attr($post_type); ?>" <?php echo in_array($post_type, $setting['post_type']) ? 'checked="checked"' : ''; ?> />
                            <span class="slider"></span>
                        </label>
                        <label class="ft-label-right"><?php esc_html_e('Active :  ', 'ai-auto-tool');
                            echo esc_attr($post_type); ?></label>
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

         echo '<button href="#tool-cloudflareai" class="nav-tab sotab"> '.esc_attr($this->icon).esc_html__(' Image Generator','ai-auto-tool').'</button>';
        }
    }

    public function render_feature() {

       $autoToolBox = new AutoToolBox($this->icon.' '.$this->name_plan, esc_html__('This feature enables the system to automatically search for articles without thumbnails and use AI (Cloudfalre, huggingface...) to create suitable images for those articles.','ai-auto-tool'), "#", $this->active_option_name, $this->active,plugins_url('../images/logo.svg', __FILE__));
       echo ($autoToolBox->generateHTML());
       
    }

    // Callback cho section
    public function section_callback() {
        echo '<p>' . esc_html__('Enter information and select Post Types.', 'ai-auto-tool') . '</p>';
    }

    // Callback cho textarea thông tin
    public function submitindex_field_callback() {
        $value = get_option('aiautotool_setting_cloudflare_image');
        echo '<textarea name="aiautotool_setting_cloudflare_image" rows="5" cols="50">' . esc_textarea($value) . '</textarea>';
    }

    // Callback cho danh sách Post Types
    public function post_types_field_callback() {
        $post_types = get_post_types(array('public' => true), 'objects');
        $selected_post_types = get_option('aiautotool_setting_post_types', array());

        foreach ($post_types as $post_type) {
            $checked = in_array($post_type->name, $selected_post_types) ? 'checked="checked"' : '';
            echo '<input type="checkbox" name="aiautotool_setting_post_types[]" value="' . esc_attr($post_type->name) . '" ' . esc_html($checked) . ' /> ' . esc_html($post_type->label) . '<br>';
        }
    }
    private function get_settings() {
        $settings = get_option( 'aiautotool_setting_cloudflare_image', [] );
        

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

