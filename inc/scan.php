<?php


defined('ABSPATH') or die();

function markdown($markdownText) {
    // Sử dụng thư viện hoặc API Markdown phù hợp ở đây
    // Ví dụ, sử dụng thư viện Parsedown:
   
    $parsedown = new Parsedown();
    $html = $parsedown->text($markdownText);

    return $html;
}
class URL_Scanner_Plugin extends rendersetting{

    public  $active = true;
    public  $active_option_name = 'URL_Scanner_Plugin_active';
    public  $usage_option_name = 'Schedule_AI_usage';
    public  $icon = '<i class="fa-regular fa-clock"></i>';
   
    protected $postfields = array();
    protected $shortcodes = array();
    protected $htmltags = array();
    public $limit = AIAUTOTOOL_FREE;
    private $plan_limit_aiautotool ;
    public $name_plan ;
    public $config = array();
    public $notice ;
    public function __construct() {
        
        $this->name_plan =  esc_html__('Autoblogging AI post','ai-auto-tool');
        $this->plan_limit_aiautotool =  'plan_limit_aiautotool_'.$this->active_option_name;
       
        $this->notice = new aiautotool_Warning_Notice();
        $this->active = get_option($this->active_option_name, true);
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
    // public function aiautotool_checklimit($config) {

    //     if($config['number_post'] !== -1){
    //         //print_r($config);
    //         if ($config['number_post'] >= $config['usage'] ) {
    //            return true;
    //         }else{
    //             return false;
    //         }
    //     }else{
         
    //     return true;
    //     }
    // }
    public function aiautotool_checklimit($config) {
        return $this->check_quota();
}

    public function aiautotool_update_usage() {
        $this->aiautotool_check_post_limit();
        
        $current_value = get_option($this->usage_option_name, 0);

        $new_value = $current_value + 1;
        if($this->config['number_post']!=-1){
            if($this->config['number_post'] > $new_value){
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

    public function init(){

        $configs = get_option($this->plan_limit_aiautotool, array());
         

        
        if (!$this->aiautotool_has_plugin_data()) {
            $this->aiautotool_initialize_plugin_data();
            $configs = (array) $this->aiautotool_has_plugin_data();
        }
        if(!isset($configs['expiration']))
        {
            $current_date = date('Y-m-d');
        
            $expiration = strtotime('+1 month', strtotime($current_date));
            $configs['expiration'] = date('Y-m-d', $expiration);
        }
        $this->config  = array(
                'number_post'=>$this->limit,
                'usage'=>get_option($this->usage_option_name, 0, 'no'),
                'time_exprice'=>$configs['expiration']
            );

        if($this->is_premium()->get_plan_name()=='aiautotoolpro'||$this->is_premium()->get_plan_name()=='premium'){
           
            $this->config  = array(
                'number_post'=>-1,
                'usage'=>get_option($this->usage_option_name, 0, 'no'),
                'time_exprice'=>$this->is_premium()->_get_license()->expiration
            );
        }
        // Hook to add a menu item to the admin menu
        add_action('admin_menu', array($this, 'add_menu'));

        add_action('admin_init', array($this, 'aiautotool_check_post_limit'));
        // Hook to enqueue JavaScript and CSS
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));

       
        

        if ($this->aiautotool_checklimit($this->config)) {
            // add_action('wp', array($this, 'schedule_cron'));
            add_filter('cron_schedules', array($this, 'aiautotool_schedule_cron_intervals'));
            add_filter('cron_schedules', array($this, 'schedule_create_content'));

            if (!wp_next_scheduled('cron_task_event_new')) {
                wp_schedule_event(time(), 'aiautotool_schedule_cron_intervals', 'cron_task_event_new');
                
            }

            if (!wp_next_scheduled('schedule_create_content_callback')) {
                wp_schedule_event(time(), 'schedule_create_content', 'schedule_create_content_callback');
                
            }

            add_action('cron_task_event_new', array($this, 'cron_task'));

            add_action('schedule_create_content_callback', array($this, 'schedule_create_content_callback'));
            
        }else{
           

             $this->notice->add_notice(
                                AIAUTOTOOL_TITLE_UPGRADE,
                                'notice-error',
                                null,
                                true,
                                'aiautotool'
                            );
        }
        add_action('wp_ajax_add_post', array($this, 'add_post_callback'));
        add_action('wp_ajax_nopriv_add_post', array($this, 'add_post_callback'));



        add_action('rest_api_init', array($this, 'register_aiautotool_scan_route'));

        

        // add_filter('post_row_actions', array($this, 'add_button_auto_Content'), 10, 2);


        add_action('wp_ajax_clear_log_action', array($this, 'clear_log_action_callback'));

        add_action('url_scanner_clear_log_cron', array($this, 'clear_log_action_callback'));

        if (!wp_next_scheduled('url_scanner_clear_log_cron')) {
            // Nếu chưa, đặt lịch cron
            wp_schedule_event(time(), 'twicedaily', 'url_scanner_clear_log_cron');
        }
         add_action('wp_ajax_aiautotool_idea_title_post', array($this, 'aiautotool_idea_title_post'));
    }

   
    public function aiautotool_idea_title_post(){

         check_ajax_referer('aiautotool_nonce', 'security');

        $categories = get_categories();
        $result = [];
        foreach ($categories as $category) {
            
            $args = [
                'category' => $category->term_id,
                'posts_per_page' => 10,
                'orderby' => 'rand',
                'fields' => 'titles',
            ];
            $posts = get_posts($args);

            $category_titles = "--" . $category->name . ":\n";
            foreach ($posts as $post) {
                $category_titles .= "----" . $post->post_title . "\n";
            }

            $result[] = $category_titles;
        }

        $output = implode("\n", $result);
        

        $prompt = "The most important: the response must be in the SAME LANGUAGE as the original text (text between \"======\"). Given the following sitemap structure of a website with various uncategorized topics, generate 30 relevant post suggestions that align with the content areas listed. The topics include:
            ======  %%SITEMAP%% ====== 
            Ensure the suggestions are engaging, informative, and suitable for the target audience of the website. The suggestions should also consider current trends and user interests in the respective fields. Output struct json all in ideas array.  {ideas :{title:'',categoires:'',tags:''}}";

        $question = str_replace('%%SITEMAP%%', $output, $prompt);

        $bardGenContent = new BardGenContent();
         $newcontent = $bardGenContent->bardcontentmore($question,'');
         if(!empty($newcontent)){
             $result = $this->aiautotool_return_json($newcontent);
             $result =  [
                            'output' => $result,
                            'token' => '',
                            'msg' => $result,
                            'model' => 'Aikct'
                        ];
             
             wp_send_json_success($result);
         }else{
             $result =  [
                        'output' => 'Error query',
                        'token' => '',
                        'msg' => 'Please set API KEY',
                        'model' => 'Aikct'
                    ];
                 wp_send_json_error($result);
         }  
        
       
         wp_die();
    }

    public function aiautotool_return_json($result){
        $pattern = '/\{(?:[^{}]|(?R))*\}/'; 
        preg_match_all($pattern, $result, $matches);
        $arritem = array();
        foreach ($matches[0] as $jsonString) {
            
            $decodedJson = json_decode($jsonString, true);
            if ($decodedJson !== null) {
                if(isset($decodedJson['tags']))
                {
                    foreach($decodedJson['tags'] as $item){
                        $arritem[] = $item;
                    }
                }elseif(isset($decodedJson['comments']))
                {
                    foreach($decodedJson['comments'] as $item){
                        $arritem[] = $item;
                    }
                }elseif(isset($decodedJson['ideas']))
                {
                    foreach($decodedJson['ideas'] as $item){
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
    public function add_button_auto_Content($actions, $post) {
        $post_id = $post->ID;
        $custom_button_html = '<a class="aiautotool_button_gen" href="javascript:void(0);" data-id="'.$post_id.'" onclick="bard_gen_content(this);"><img src="'.esc_url(plugins_url('../images/logo.svg', __FILE__)).'" width="16px" height="16px"  />Bard</a><div id="loading-icon" class="loader"></div>';
        $actions['custom'] = $custom_button_html;
        return $actions;
    }
 
     public function render_setting() {
        
       
        ?>
        <div id="tab-scan-setting" class="tab-content" style="display:none;">
            <h2><?php echo wp_kses_post($this->icon); ?> <?php esc_html_e('Schedule Post','ai-auto-tool'); ?></h2>
            <?php
            if (!current_user_can('manage_options')) {
            return;
        }

        // Save settings
        if (isset($_POST['aiautotool_draft_public_save_settings'])) {
           
            

            $translate_interval = intval($_POST['aiautotool_draft_public_time']); 
            update_option('aiautotool_draft_public_time', $translate_interval,null, 'no');

             $number_post = intval($_POST['aiautotool_draft_public_number_post']); 
            update_option('aiautotool_draft_public_number_post', $number_post,null, 'no');

            $aiautotool_draft_public_turnoff_auto_images = isset($_POST['turnoff_auto_images']) ? true : false; 

            update_option('aiautotool_draft_public_turnoff_auto_images', $aiautotool_draft_public_turnoff_auto_images,null, 'no');

            echo '<div class="updated"><p>Settings saved.</p></div>';
        }


        
        

        $current_interval = get_option('aiautotool_draft_public_time', 5);


        $number_post = get_option('aiautotool_draft_public_number_post', 1);

        $aiautotool_draft_public_turnoff_auto_images = get_option('aiautotool_draft_public_turnoff_auto_images', false);
        ?>
        <div class="wrap">
            
            <form method="post" action="">
                 <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                    <?php esc_html_e('Time public Draft','ai-auto-tool'); ?>
                    </p>
                     <select id="aiautotool_draft_public_time" name="aiautotool_draft_public_time">
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
                   <?php esc_html_e('Number Post for one public Draft','ai-auto-tool'); ?>:
                    </p>
                <select id="aiautotool_draft_public_number_post" name="aiautotool_draft_public_number_post">
                            <option value="1" <?php selected($number_post, 1); ?>>1</option>
                            <option value="2" <?php selected($number_post, 2); ?>>2</option>
                            <option value="3" <?php selected($number_post, 3); ?>>3</option>
                        </select>
                <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                   <?php esc_html_e('Turn off auto image when public post','ai-auto-tool'); ?>:
                    </p>

                     <label class="nut-switch">
                                    <input type="checkbox" name="turnoff_auto_images" id="turnoff_auto_images"  value="1" <?php if ( isset( $aiautotool_draft_public_turnoff_auto_images) && true ==  $aiautotool_draft_public_turnoff_auto_images ) echo 'checked="checked"'; ?>>
                                    <span class="slider"></span></label>
                                    <label class="ft-label-right"><?php esc_html_e('Disable auto Image in post.','ai-auto-tool'); ?></label>
                                    </p>

                <p class="submit">
                    <input type="submit" name="aiautotool_draft_public_save_settings" class=" ft-submit" value="<?php esc_html_e('Save Configuration','ai-auto-tool'); ?>" />
                </p>
            </form>
            <hr>
            
        </div>
        
        </div>
            <?php
    }

    public function render_tab_setting() {
        // Cài đặt cho lớp auto_ex_link ở đây
        if ($this->active=='true') {
        echo '<button href="#tab-scan-setting" class="nav-tab sotab"> '. wp_kses_post($this->icon).' '.esc_html__('Autoblogging AI post','ai-auto-tool').'</button>';
        }
    }

    public function render_feature(){
        $autoToolBox = new AutoToolBox(wp_kses_post($this->icon).' '.esc_html__('Autoblogging AI post','ai-auto-tool'), "Autoblogging uses AI Gemini. Just put in the list of article titles and set the time to post. The system works 100% automatically to take care of your website every day", "https://doc.aiautotool.com/integrations/schedule-ai-post-user-guide", $this->active_option_name, $this->active,esc_url(plugins_url('../images/logo.svg', __FILE__)));

         echo ($autoToolBox->generateHTML());
        
        
    }
    
    function add_post_callback() {
        check_ajax_referer('aiautotool_nonce', 'security');

        $languageCodes =  $this->languageCodes;
        $post_titles = sanitize_textarea_field($_POST['post_titles']);
        $post_category = intval($_POST['post_category']);
        $post_tags = sanitize_text_field($_POST['post_tags']);
        $post_language = sanitize_text_field($_POST['post_language']);
        $anchor_text = wp_kses_post($_POST['anchor_text']);
        $auto_change_title = 0;//isset($_POST['auto_change_title']) ? true : false; 
        $aiautotool_prompt = isset($_POST['aiautotool_prompt']) ? sanitize_text_field($_POST['aiautotool_prompt']):''; 

        $titles = explode(PHP_EOL, $post_titles);
        $user_id = '';
        $user_ids = get_users( array(
            'fields' => 'ID',
        ) );
        $kq = array();
        foreach ($titles as $title) {
             $words = explode(",", $post_tags);
            if (count($words) > 3) {
                        $randomWords = array_rand($words, 3); 
                        $result = [];
                        foreach ($randomWords as $index) {
                            $result[] = $words[$index]; 
                        }
                    } else {
                        $result = $words; 
                    }

                    $post_tags = implode(",", $result);
            $title = ucwords(mb_strtolower(html_entity_decode($title)));

            $random_user_id = $user_ids[array_rand($user_ids)];
            $user_id = $random_user_id;
            $new_post = array(
                'post_title' => $title,
                'post_content' => '',
                'post_name' => sanitize_title($title),
                'post_status' => 'draft',
                'post_author'=>$user_id,
                'post_category' => array($post_category),
                'tags_input' => $post_tags,
                'post_type' => 'post',
                'lang' => $post_language
            );

            $post_id = wp_insert_post($new_post);
             $selectedLanguageName = '';
             if(array_key_exists($post_language, $languageCodes)) {
                $selectedLanguageName = $languageCodes[$post_language];
                if(in_array('polylang/polylang.php', apply_filters('active_plugins', get_option('active_plugins')))){ 
                   pll_set_post_language($post_id, $post_language);
                }

               
            }
            update_post_meta($post_id, 'lang', $selectedLanguageName);
            update_post_meta($post_id, 'aiautotool_prompt', $aiautotool_prompt);
            
            update_post_meta($post_id, 'auto_change_title', $auto_change_title);
            update_post_meta($post_id, 'aiautotool_linkin', $anchor_text);


            $kq[] = array('title'=>$title,'url_edit'=>get_edit_post_link($post_id));
        }

        wp_send_json_success($kq);
    }
    
    private function fix_content_spin($content){
        print_r($content);
        $spintax = new Spintaxkct();
        $content = $spintax->process($content);
        return trim($content);
    }

    public function aiautotool_schedule_cron_intervals($schedules) {
          $current_interval = get_option('aiautotool_draft_public_time', 5);


        $schedules['aiautotool_schedule_cron_intervals'] = array(
            'interval' => $current_interval* 60, 
            'display' => 'aiautotool bard content public'
        );
        return $schedules;
    }
    public function schedule_create_content($schedules) {
         

        $schedules['schedule_create_content'] = array(
            'interval' =>  60, 
            'display' => 'Schedule create content'
        );
        return $schedules;
    }

    public function schedule_cron() {
        if (!wp_next_scheduled('url_scanner_cron')) {
            wp_schedule_event(time(), 'minute', 'url_scanner_cron');
        }
    }

    public function schedule_create_content_callback(){
        // set_time_limit(300);
         global $wpdb;
         $log = new AIautoTool_log();
         // print_r('a');
         if (false === ($draft_posts = get_transient('aiautotool_draft_posts'))) {
                
                $query = $wpdb->prepare(
                    "SELECT ID FROM {$wpdb->prefix}posts 
                    WHERE post_content = '' 
                    AND post_status = 'draft' 
                    AND post_type = 'post'
                    LIMIT 50"
                );
                $draft_posts = $wpdb->get_col($query);

                set_transient('aiautotool_draft_posts', $draft_posts, 24 * HOUR_IN_SECONDS);
            }

        $post_id = array_shift($draft_posts);
        
        if (!$post_id) {
            
                return;
        }
        print_r($post_id);
        $post = get_post($post_id);
        if (!$post) {
            return;
        }
        $attachThumbnail = '';
        if($this->aiautotool_checklimit($this->config)){
                
                $log->set_log('schedule_gen_content',array('post_id'=>$post_id,'post_title'=>get_the_title($post_id),'msg'=>'star run create content'));
                if ($post) {


                        $post_id = $post->ID;
                        $auto_generate_title = 0;//get_post_meta($post_id, 'auto_generate_title', false);
                        $aiautotool_prompt = get_post_meta($post_id, 'aiautotool_prompt', true);
                        $lang = get_post_meta($post_id, 'lang', 'Vietnamese');
                        $aiautotool_linkin = get_post_meta($post_id, 'aiautotool_linkin', true);
                        $bardGenContent = new BardGenContent();
                        $Imagescontent = new Imagescontent();
                        $title = self::fix_years(get_the_title($post_id));
                        $allprompt = get_option('aiautotool_prompt_options',array());
                        // print_r($aiautotool_prompt);
                        $auto = true;
                        if(empty($allprompt)){
                            $auto = true;
                        }else{
                            if($aiautotool_prompt==''){
                                if(isset($allprompt['aiautotool_prompt_artcile']))
                                {
                                    $artcle_prompt = $allprompt['aiautotool_prompt_artcile'];
                                }
                            }else{
                                $artcle_prompt = $aiautotool_prompt;
                            }
                                 
                            $artcle_prompt = str_replace('%%title%%',$title,$artcle_prompt);
                                 $content='';
                                 $cate = '';
                                 $blogtitle = '';
                                $prompt = $this->aiautotool_fix_question($artcle_prompt,$title,$lang,$blogtitle,$cate,$content);
                                $auto = false;
                            
                        }
                        
                        if($auto){
                             $newcontent = $bardGenContent->bardcontentfull($title,$lang); 
                        }else{
                             $newcontent = $bardGenContent->bardcontentmore($prompt,$lang);
                        }
                        // $log->set_log('schedule_gen_content',array('post_id'=>$post_id,'post_title'=>$title,'msg'=>'Prompt with create contet post'.$prompt));
                        
                       // print_r('==> content'.$newcontent.'');
                         $log->set_log('schedule_gen_content',array('post_id'=>$post_id,'post_title'=>$title,'msg'=>'run bard api create contet post'.$newcontent));
                        // print_r($newcontent);
                         if (!empty($newcontent)) {
                            $log->set_log('schedule_gen_content',array('post_id'=>$post_id,'post_title'=>$title,'msg'=>'create content success, next step find img'));


                            $aiautotool_draft_public_turnoff_auto_images = get_option('aiautotool_draft_public_turnoff_auto_images', false);
                            
                            if($aiautotool_draft_public_turnoff_auto_images===false){
                                $listimg = $bardGenContent->searchimg($title);
                            }else{
                                $listimg = false;
                            }
                            
                           
                            $current_content = $post->post_content;
                            $current_content = apply_filters('the_content', $current_content);
                            $current_content = str_replace(']]>', ']]&gt;', $current_content);
                            $current_content = preg_replace('#<img.*?>#i', '', $current_content);

                            $updated_content = $newcontent . $current_content;
                            $updated_content =  self::fix_years($updated_content);
                            
                            $random_count = mt_rand(1, 3);
                            if (!empty($listimg) && is_int($random_count) && $random_count > 0) {
                                $random_count = min($random_count, count($listimg));
                                $random_keys = array_rand($listimg, $random_count);
                            } 

                            
                            if ($listimg) {

                                $listimg10 = array_slice($listimg, 0, 10);

                                shuffle($listimg10);
                                $updated_content = $Imagescontent->insertImages($updated_content,$listimg10);
                                $log->set_log('schedule_gen_content',array('post_id'=>$post_id,'post_title'=>$title,'msg'=>'Found image success, next step update content to post.'));
                                  
                            }else{
                                $log->set_log('schedule_gen_content',array('post_id'=>$post_id,'post_title'=>$title,'msg'=>'No found image.'));
                            }
                            $postname = $this->kct_aiautotool_sanitize($title);
                            $html = stripslashes($updated_content);
                            preg_match_all('/<img[^>]+src\s*=\s*["\']([^"\']+)["\']/i', $html, $matches);
                            $listimg1 =  $matches[1]; 
                            $imgUploaded = array();
                            $attachThumnail   = 0;
                             if (!empty($listimg1)){
                                foreach ($listimg1 as $post_image_url){
                                    try {
                                                    $image_url_new = $this->kct_aiautotool_save_image($post_image_url,$postname);
                                                    if (!empty($image_url_new)){
                                                        $imgUploaded[] = $image_url_new;
                                                        if ($attachThumbnail == 0) {
                                                            $attachThumbnail = $image_url_new['attach_id'];
                                                        }
                                                         
                                                    }
                                                }catch (Exception $e) {
                                                }
                                }
                             }
                             foreach ($imgUploaded as $img){
                                $updated_content = str_replace($img['url'],$img['baseurl'],$updated_content);
                            }
                                    
                            if($auto_generate_title != 0){
                                $title = $bardGenContent->gentitle($title,$lang);
                            }
                            if ($attachThumbnail != 0) {
                                set_post_thumbnail($post_id, $attachThumbnail);
                            }
                         
                            if($aiautotool_linkin!=''){
                                
                                $textadd = self::fix_content_spin($aiautotool_linkin);
                                $updated_content = self::insert_text_after_third_paragraph($updated_content, $textadd);
                                
                            }
                            
                             
                            $post_data = array(
                                'ID' => $post_id,
                                'post_title'=>$title,
                                'post_content' => $updated_content,
                                'post_status'=>'draft'
                            );

                            $post_id = wp_update_post($post_data);
                            $log->set_log('schedule_gen_content',array('post_id'=>$post_id,'post_title'=>$title,'msg'=>'create content success and Update content to post draft : '.$title));
                            $this->aiautotool_update_usage();
                            $this->notice->add_notice( 'create content success and Update content to post draft : '.$title , 'notice-info', null, true,$this->name_plan );
                        }else{
                             $log->set_log('schedule_gen_content',array('post_id'=>$post_id,'post_title'=>$title,'msg'=>'create content Error '.$title));
                        }
                wp_reset_postdata();
            }

            if (!empty($draft_posts)) {
                set_transient('aiautotool_draft_posts', $draft_posts, 24 * HOUR_IN_SECONDS);
            } else {
                delete_transient('aiautotool_draft_posts');
            }
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

public static function insert_text_after_third_paragraph($content, $text) {
    preg_match_all('/<p(.*?)<\/p>/', $content, $matches, PREG_OFFSET_CAPTURE);

    $total_paragraphs = count($matches[0]);

    $random_position = mt_rand(2, $total_paragraphs - 1);

    if (isset($matches[0][$random_position])) {
        $paragraph_pos = $matches[0][$random_position][1] + strlen($matches[0][$random_position][0]);
        $content = substr($content, 0, $paragraph_pos) . $text . substr($content, $paragraph_pos);
    }

    return $content;
}


    private function process_post_content($post_id, $title, $lang, $auto_generate_title, $log){

    }

    public function cron_task2() {
        $log = new AIautoTool_log();
        set_time_limit(300);
        $draft_posts = get_transient('aiautotool_draft_posts_public');
        if (false === $draft_posts) {
            $number_post = get_option('aiautotool_draft_public_number_post', 500);
            $args = array(
                'post_type' => 'post',
                'post_status' => 'draft',
                'posts_per_page' => $number_post,
            );
            $draft_posts_query = new WP_Query($args);

            if ($draft_posts_query->have_posts()) {
                $draft_posts = array();
                while ($draft_posts_query->have_posts()) {
                    $draft_posts_query->the_post();
                    $draft_posts[] = get_the_ID();
                }
                set_transient('aiautotool_draft_posts_public', $draft_posts, 24 * HOUR_IN_SECONDS);
            } else {
                
                return;
            }
        }

        $post_id = array_shift($draft_posts);

        if (!$post_id) {
            delete_transient('aiautotool_draft_posts_public');
            return;
        }

        $content_post = get_post($post_id);
        $current_content = $content_post->post_content;
        if (strlen($current_content) < 255) {
             if($this->aiautotool_checklimit($this->config)){
                $log->set_log('schedule_publish',array('post_id'=>$post_id,'post_title'=>get_the_title(),'msg'=>'star run bard api'));
                
                        $auto_generate_title = 0;//get_post_meta($post_id, 'auto_generate_title', false);
                        $aiautotool_prompt = get_post_meta($post_id, 'aiautotool_prompt', true);
                        $lang = get_post_meta($post_id, 'lang', 'Vietnamese');
                        $aiautotool_linkin = get_post_meta($post_id, 'aiautotool_linkin', true);
                        $bardGenContent = new BardGenContent();
                        $Imagescontent = new Imagescontent();
                        $title = self::fix_years(get_the_title($post_id));
                        $allprompt = get_option('aiautotool_prompt_options',array());
                        $auto = true;
                        if(empty($allprompt)){
                            $auto = true;
                        }else{
                            if($aiautotool_prompt==''){
                                if(isset($allprompt['aiautotool_prompt_artcile']))
                                {
                                    $artcle_prompt = $allprompt['aiautotool_prompt_artcile'];
                                }
                            }else{
                                $artcle_prompt = $aiautotool_prompt;
                            }
                                 
                                 $content='';
                                 $cate = '';
                                 $blogtitle = '';
                                $prompt = $this->aiautotool_fix_question($artcle_prompt,$title,$lang,$blogtitle,$cate,$content);
                                $auto = false;
                            
                        }
                        if($auto){
                             $newcontent = $bardGenContent->bardcontentfull($title,$lang); 
                        }else{
                             $newcontent = $bardGenContent->bardcontentmore($prompt,$lang);
                        }

                $log->set_log('schedule_publish',array('post_id'=>$post_id,'post_title'=>get_the_title(),'msg'=>'create content by bard api'));
                 if (!empty($newcontent)) {
                    $log->set_log('schedule_publish',array('post_id'=>$post_id,'post_title'=>get_the_title(),'msg'=>'create content by bard api success next step search img'));
                    $listimg = $bardGenContent->searchimg($title);
                   
                    $current_content = apply_filters('the_content', $current_content);
                    $current_content = str_replace(']]>', ']]&gt;', $current_content);
                    $current_content = preg_replace('#<img.*?>#i', '', $current_content);

                    $updated_content = $newcontent . $current_content;
                    $updated_content =  self::fix_years($updated_content);
                    // $updated_content = self::preserveHtmlTagsShortcodes($updated_content);
                    $random_count = mt_rand(1, 3);
                    $random_keys = array_rand($listimg, $random_count);


                    if ($listimg) {

                        $listimg10 = array_slice($listimg, 0, 10);

                        shuffle($listimg10);
                        $updated_content = $Imagescontent->insertImages($updated_content,$listimg10);
                        $log->set_log('schedule_publish',array('post_id'=>$post_id,'post_title'=>get_the_title(),'msg'=>'Had found images , add to content'));
                    }else{
                        $log->set_log('schedule_publish',array('post_id'=>$post_id,'post_title'=>get_the_title(),'msg'=>'No found images.'));
                    }
                    $postname = $this->kct_aiautotool_sanitize($title);
                    $html = stripslashes($updated_content);
                    preg_match_all('/<img[^>]+src\s*=\s*["\']([^"\']+)["\']/i', $html, $matches);
                    $listimg1 =  $matches[1]; 
                    $imgUploaded = array();
                    $attachThumnail   = 0;
                     if (!empty($listimg1)){
                        foreach ($listimg1 as $post_image_url){
                            try {
                                            $image_url_new = $this->kct_aiautotool_save_image($post_image_url,$postname);
                                            if (!empty($image_url_new)){
                                                $imgUploaded[] = $image_url_new;
                                                if ($attachThumbnail == 0) {
                                                    $attachThumbnail = $image_url_new['attach_id'];
                                                }
                                                 
                                            }
                                        }catch (Exception $e) {
                                        }
                        }
                     }
                     foreach ($imgUploaded as $img){
                        $updated_content = str_replace($img['url'],$img['baseurl'],$updated_content);
                    }
                            
                    if($auto_generate_title!=0){
                        $title = $bardGenContent->gentitle($title,$lang);
                    }
                    if ($attachThumbnail ) {
                        set_post_thumbnail($post_id, $attachThumbnail);
                    }
                    $post_data = array(
                        'ID' => $post_id,
                        'post_title'=>$title,
                        'post_content' => $updated_content,
                        'post_status'=>'draft'
                    );

                    $post_id = wp_update_post($post_data);
                     $this->update_and_publish_post( $post_id );
                    $log->set_log('schedule_publish',array('post_id'=>$post_id,'post_title'=>get_the_title(),'msg'=>'Publish post '.get_the_title()));
                    $this->aiautotool_update_usage();
                }else{
                     $log->set_log('schedule_publish',array('post_id'=>$post_id,'post_title'=>get_the_title(),'msg'=>'create content Error 1 '.$title));
                }
            }else{
                $this->notice->add_notice(
                    AIAUTOTOOL_TITLE_UPGRADE,
                    'notice-error',
                    null,
                    true,
                    $this->name_plan
                );

            }
            set_transient('aiautotool_draft_posts_public', $draft_posts, 24 * HOUR_IN_SECONDS);
        } else {
            $log->set_log('schedule_publish',array('post_id'=>$post_id,'post_title'=>get_the_title(),'msg'=>'star run bard api and next step: publish post has content post.'));
            $result = $this->update_and_publish_post( $post_id );

            $log->set_log('schedule_publish',array('post_id'=>$post_id,'post_title'=>get_the_title(),'msg'=>'Publish post '.get_the_title()));
        }

        wp_reset_postdata();
    }

    public function cron_task() {
        
        set_time_limit(300);
        $log = new AIautoTool_log();
        $number_post = get_option('aiautotool_draft_public_number_post', 1);
        $args = array(
            'post_type' => 'post',
            'post_status' => 'draft',
            'posts_per_page' => $number_post, 
        );

        $draft_posts = new WP_Query($args);

        if ($draft_posts->have_posts()) {
            while ($draft_posts->have_posts()) {
                $draft_posts->the_post();
                $post_id = get_the_ID();
                $content_post = get_post($post_id);
                $current_content = $content_post->post_content;
                if(strlen($current_content) < 255){
                        if($this->aiautotool_checklimit($this->config)){
                            $log->set_log('schedule_publish',array('post_id'=>$post_id,'post_title'=>get_the_title(),'msg'=>'star run bard api'));
                            $auto_generate_title = 0;//get_post_meta($post_id, 'auto_generate_title', false);
                            $aiautotool_prompt = get_post_meta($post_id, 'aiautotool_prompt', true);
                            $lang = get_post_meta($post_id, 'lang', 'Vietnamese');
                            $aiautotool_linkin = get_post_meta($post_id, 'aiautotool_linkin', true);
                            $bardGenContent = new BardGenContent();
                            $Imagescontent = new Imagescontent();
                            $title = self::fix_years(get_the_title($post_id));
                            $allprompt = get_option('aiautotool_prompt_options',array());
                            $auto = true;
                            if(empty($allprompt)){
                                $auto = true;
                            }else{
                                if($aiautotool_prompt==''){
                                    if(isset($allprompt['aiautotool_prompt_artcile']))
                                    {
                                        $artcle_prompt = $allprompt['aiautotool_prompt_artcile'];
                                    }
                                }else{
                                    $artcle_prompt = $aiautotool_prompt;
                                }
                                     
                                     $content='';
                                     $cate = '';
                                     $blogtitle = '';
                                    $prompt = $this->aiautotool_fix_question($artcle_prompt,$title,$lang,$blogtitle,$cate,$content);
                                    $auto = false;
                                
                            }
                            if($auto){
                                 $newcontent = $bardGenContent->bardcontentfull($title,$lang); 
                            }else{
                                 $newcontent = $bardGenContent->bardcontentmore($prompt,$lang);
                            }

                              // $log->set_log('schedule_publish',array('post_id'=>$post_id,'post_title'=>$title,'msg'=>'Prompt with create contet post'.$prompt));


                            $log->set_log('schedule_publish',array('post_id'=>$post_id,'post_title'=>get_the_title(),'msg'=>'create content by bard api'));
                             if (!empty($newcontent)) {
                                $log->set_log('schedule_publish',array('post_id'=>$post_id,'post_title'=>get_the_title(),'msg'=>'create content by bard api success next step search img'));
                                $listimg = $bardGenContent->searchimg($title);
                                // $content_post = get_post($post_id);
                                // $current_content = $content_post->post_content;
                                $current_content = apply_filters('the_content', $current_content);
                                $current_content = str_replace(']]>', ']]&gt;', $current_content);
                                $current_content = preg_replace('#<img.*?>#i', '', $current_content);

                                $updated_content = $newcontent . $current_content;
                                $updated_content =  self::fix_years($updated_content);
                                // $updated_content = self::preserveHtmlTagsShortcodes($updated_content);
                                $random_count = mt_rand(1, 3);
                                $random_keys = array_rand($listimg, $random_count);


                                if ($listimg) {

                                    $listimg10 = array_slice($listimg, 0, 10);

                                    shuffle($listimg10);
                                    $updated_content = $Imagescontent->insertImages($updated_content,$listimg10);
                                    $log->set_log('schedule_publish',array('post_id'=>$post_id,'post_title'=>get_the_title(),'msg'=>'Had found images , add to content'));
                                }else{
                                    $log->set_log('schedule_publish',array('post_id'=>$post_id,'post_title'=>get_the_title(),'msg'=>'No found images.'));
                                }
                                $postname = $this->kct_aiautotool_sanitize($title);
                                $html = stripslashes($updated_content);
                                preg_match_all('/<img[^>]+src\s*=\s*["\']([^"\']+)["\']/i', $html, $matches);
                                $listimg1 =  $matches[1]; 
                                $imgUploaded = array();
                                $attachThumnail   = 0;
                                 if (!empty($listimg1)){
                                    foreach ($listimg1 as $post_image_url){
                                        try {
                                                        $image_url_new = $this->kct_aiautotool_save_image($post_image_url,$postname);
                                                        if (!empty($image_url_new)){
                                                            $imgUploaded[] = $image_url_new;
                                                            if ($attachThumbnail == 0) {
                                                                $attachThumbnail = $image_url_new['attach_id'];
                                                            }
                                                             
                                                        }
                                                    }catch (Exception $e) {
                                                    }
                                    }
                                 }
                                 foreach ($imgUploaded as $img){
                                    $updated_content = str_replace($img['url'],$img['baseurl'],$updated_content);
                                }
                                        
                                if($auto_generate_title!=0){
                                    $title = $bardGenContent->gentitle($title,$lang);
                                }
                                if ($attachThumbnail != 0) {
                                    set_post_thumbnail($post_id, $attachThumbnail);
                                }
                                $post_data = array(
                                    'ID' => $post_id,
                                    'post_title'=>$title,
                                    'post_content' => $updated_content,
                                    'post_status'=>'draft'
                                );

                                $post_id = wp_update_post($post_data);
                                 $this->update_and_publish_post( $post_id );
                                $log->set_log('schedule_publish',array('post_id'=>$post_id,'post_title'=>get_the_title(),'msg'=>'Publish post '.get_the_title()));
                                $this->aiautotool_update_usage();
                            }else{
                                 $log->set_log('schedule_publish',array('post_id'=>$post_id,'post_title'=>get_the_title(),'msg'=>'create content Error 1 '.$title));
                            }
                        }else{
                            $this->notice->add_notice(
                                AIAUTOTOOL_TITLE_UPGRADE,
                                'notice-error',
                                null,
                                true,
                                $this->name_plan
                            );

                        }
                }else{
                    $log->set_log('schedule_publish',array('post_id'=>$post_id,'post_title'=>get_the_title(),'msg'=>'star run bard api and next step: publish post has content post.'));
                    $result = $this->update_and_publish_post( $post_id );

                    $log->set_log('schedule_publish',array('post_id'=>$post_id,'post_title'=>get_the_title(),'msg'=>'Publish post '.get_the_title()));
                }
               
                
            }
        }

        wp_reset_postdata();
    }
    public function update_and_publish_post($post_id) {
        $current_time = current_time('mysql');

        $post_data = array(
            'ID'            => $post_id,
            'post_date'     => $current_time,
            'post_date_gmt' => get_gmt_from_date($current_time),
            'post_status' =>'publish'
        );

        wp_update_post($post_data);

        $pingback = new aiautotool_Telegram_Notifications('6845486754:AAH5KyPTuluu_OCRnPklp6UEbzVfe0CtHkU','730694172');
        $message = "Publish article:".get_permalink($post_id)."\n";
        $message .= "timeactive: " . current_time('Y-m-d H:i:s') . "\n";
        $pingback->send_bot_message($message);

        
    }
    public function bard_content($post_id){
    // Hàm xử lý công bố bài viết dạng draft

            $auto_generate_title = get_post_meta($post_id, 'auto_generate_title', true); // Lấy giá trị meta
            $lang = get_post_meta($post_id, 'lang', true);

            $bardGenContent = new BardGenContent();
            $Imagescontent = new Imagescontent();
            $title = self::fix_years(get_the_title($post_id)); // Lấy tiêu đề của bài viết dựa trên ID
            $newcontent = $bardGenContent->bardcontent($title, $lang);

            if (!empty($newcontent)) {
                $listimg = $bardGenContent->searchimg($title);
                $content_post = get_post($post_id);
                $current_content = $content_post->post_content;
                $current_content = apply_filters('the_content', $current_content);
                $current_content = str_replace(']]>', ']]&gt;', $current_content);
                $current_content = preg_replace('#<img.*?>#i', '', $current_content);

                $updated_content = $newcontent . $current_content;
                $updated_content =  self::fix_years($updated_content);

                $random_count = mt_rand(1, 3);
                $random_keys = array_rand($listimg, $random_count);

                if ($listimg) {
                    $listimg10 = array_slice($listimg, 0, 10);
                    shuffle($listimg10);
                    $updated_content = $Imagescontent->insertImages($updated_content, $listimg10);
                }

                $postname = $this->kct_aiautotool_sanitize($title);
                $html = stripslashes($updated_content);
                preg_match_all('/<img[^>]+src\s*=\s*["\']([^"\']+)["\']/i', $html, $matches);
                $listimg1 =  $matches[1]; 
                $imgUploaded = array();
                $attachThumbnail = 0;

                if (!empty($listimg1)){
                    foreach ($listimg1 as $post_image_url){
                        try {
                            $image_url_new = $this->kct_aiautotool_save_image($post_image_url, $postname);
                            if (!empty($image_url_new)){
                                $imgUploaded[] = $image_url_new;
                                if ($attachThumbnail == 0) {
                                    $attachThumbnail = $image_url_new['attach_id'];
                                }
                            }
                        } catch (Exception $e) {
                        }
                    }
                }

                foreach ($imgUploaded as $img){
                    $updated_content = str_replace($img['url'], $img['baseurl'], $updated_content);
                }

                if ($auto_generate_title){
                    $title = $bardGenContent->gentitle($title, $lang);
                }

                if ($attachThumbnail != 0) {
                    set_post_thumbnail($post_id, $attachThumbnail);
                }

                $post_data = array(
                    'ID' => $post_id,
                    'post_title' => $title,
                    'post_content' => $updated_content,
                    'post_status' => 'publish'
                );

                $post_id = wp_update_post($post_data);
                return get_permalink($post_id);
            }else{
                return false;
            }
        } 


    public function kct_aiautotool_save_image($imgURL,$post_title){
            
            $imageUploader = new KCT_AIAutoTool_ImageUploader();
            $kq = $imageUploader->saveImage($imgURL, $post_title);
            return $kq;
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
    public function preserveHtmlTagsShortcodes($string) {
        //preserve shortcodes
        if (preg_match_all("/\[.*\]/", $string , $shortcodes)) {
            $this->shortcodes = $shortcodes[0];
            foreach($this->shortcodes as $num => $tag) {
                $string = str_replace($tag, "[3x" . (10000+ $num) . "]", $string);
            }
        }
        
        // and html tags
        if(preg_match_all('@<[\/\!]*?[^<>]*?>@si', $string, $htmltags)) {
            $this->htmltags = $htmltags[0];
            foreach($this->htmltags as $num => $tag) {
                $string = str_replace($tag, "[3x" . (1000+ $num) . "]", $string);
            }
        }
        return $string;
    }
    
    public function replaceHTMLTagsShortcodes($string) {
         // replace the preserved html tags
        foreach($this->htmltags as $num => $tag) {
            $string = str_replace("[3x" . (1000+ $num) . "]", $tag, $string);
        }
        // and shortcodes
        foreach($this->shortcodes as $num => $tag) {
            $string = str_replace("[3x" . (10000+ $num) . "]", $tag, $string);
        }
        return $string;
    }

    
    public function fix_years($title) {
        

        return $title;
    }
    public function register_aiautotool_scan_route(){
        register_rest_route('aiautotool/v1', '/bardcontent', array(
            'methods' => 'POST', // Phương thức POST để gửi dữ liệu JSON
            'callback' => array($this, 'bard_gencontent'),
        ));
        register_rest_route('aiautotool/v1', '/createcontentbard', array(
            'methods' => 'POST', // Phương thức POST để gửi dữ liệu JSON
            'callback' => array($this, 'bard_content_request'),
        ));
    }
    public function bard_content_request($request){
        $idpost = $request['post_id'];
        $kq = $this->bard_content($idpost);
        return wp_send_json_success($kq );
    }
    public function bard_gencontent($request){
        $title = $request['title'];
        $idpost = $request['id'];
        $bardGenContent = new BardGenContent();
        $newcontent = $bardGenContent->sendRequest($title);

         if (!empty($newcontent)) {

            $content_post = get_post($idpost);
            $current_content = $content_post->post_content;
            $current_content = apply_filters('the_content', $current_content);
            $current_content = str_replace(']]>', ']]&gt;', $current_content);
            $updated_content = $newcontent . $current_content;

            // Cập nhật nội dung của bài viết
            $post_data = array(
                'ID' => $idpost,
                'post_content' => $updated_content,
            );

            $post_id = wp_update_post($post_data);
        }

        return wp_send_json_success($newcontent);
    }

    public function aiautotool_get_all_draft() {
        $drafts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'draft',
            'numberposts' => -1,
        ));

        $draftsWithContent = 0;
        $draftsWithoutContent = 0;

        foreach ($drafts as $draft) {
            $content = get_post_field('post_content', $draft->ID);

            if (empty($content)) {
                // Nếu nội dung rỗng, tăng counter cho draft chưa có nội dung
                $draftsWithoutContent++;
            } else {
                // Ngược lại, tăng counter cho draft đã có nội dung
                $draftsWithContent++;
            }
        }
        if(count($drafts)==0){
            return array('total'=>0,'hascontent'=>0,'nocontent'=>0);
        }else{
            return array('total'=>count($drafts),'hascontent'=>$draftsWithContent,'nocontent'=>$draftsWithoutContent);
        }

        
    }
    public function add_menu() {
  

        add_submenu_page(
        MENUSUBPARRENT,       
        wp_kses_post($this->icon).' '.esc_html__('Autoblogging AI post','ai-auto-tool'),          
        wp_kses_post($this->icon).' '.esc_html__('Autoblogging AI post','ai-auto-tool'),    
        'manage_options',             
        'add-post-draft',  
        array($this, 'add_key_to_post')  
    );

        
    }


        public function clear_log_action_callback() {
           
            update_option('aiautotool_logall', array(),null, 'no');

            wp_send_json_success('Log cleared successfully.');
            wp_die();
        }


    public function add_key_to_post() {
        

        $language_code = explode('_',get_locale());
        $language_code = $language_code[0];
        $languages = $this->languageCodes;

      

        echo '<div class=" aiautotool_container">';
        ?>
        <div class="ft-wrap-top"></div>
        <div class="wrap ft-wrap">
            <div class="ft-wrap-body">
            <div class="ft-box">
                <div class="ft-menu">
                    <div class="ft-logo"><img src="<?php echo esc_url(plugins_url('../images/logo.svg', __FILE__)); ?>">
                    <br>Autoblogging</div>
                    <button href="#tab-schedule" class="nav-tab sotabt "> <?php echo wp_kses_post($this->icon).' '. esc_html__('Autoblogging AI post','ai-auto-tool'); ?></button>
                   
                    <button href="#tab-log-schedule" class="nav-tab sotabt "><i class="fa-regular fa-folder-closed"></i> <?php esc_html_e('View log schedule','ai-auto-tool'); ?></button>
                
                </div>
                <div class="ft-main">

                    <div id="tab-schedule" class="tab-content sotab-box ftbox">
                         <h2><i class="fa-regular fa-clock"></i> <?php esc_html_e('Autoblogging AI post','ai-auto-tool'); ?></h2>
                               
                            <!-- start form -->
                            <form id="add-post-form"  method="post" >
                                <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                                    <?php esc_html_e('Multiple Post Titles Input','ai-auto-tool'); ?> 
                                    <br>
                                   <?php esc_html_e('Allow users to input and save multiple post titles.','ai-auto-tool'); ?>
                                   <br>
                                   <?php esc_html_e('Store these titles as draft posts in the WordPress database. Support gen Title auto => <a href="https://aitoolseo.com/" target="_blank" class=" delete-post-csdl">Gen Title</a>','ai-auto-tool'); ?>

                                   <button class="aiautotool_idea_generated" title="AI Suggest Post title....">
                                        <i class="fa-solid fa-bolt"></i> Idea Title
                                    </button>
                                    <script type="text/javascript">jQuery(document).ready(function($) {
                             $('.aiautotool_idea_generated').on('click', function(e) {
                            e.preventDefault();
                            console.log('Idea click');
                            

                            function suggestIdea(retries) {
                                
                            showLoading();

                                $.ajax({
                                    url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', 
                                    type: 'POST',
                                    data: {
                                        action: 'aiautotool_idea_title_post',
                                        security: aiautotool_js_setting.security
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            console.log(response);
                                            
                                            hideLoading();
                                            var output = response.data.output;
                                            console.log(output);
                                            if (Array.isArray(output)) {
                                                var itemDivtong = '';
                                                output.forEach(function(item) {
                                                    console.log(item.title);
                                                    itemDivtong += item.title+"\n";
                                                    
                                                });

                                                $('#post-titles').html(itemDivtong);
                                            }else{
                                                retryRequestidea(retries);
                                            }
                                            
                                        } else {
                                            retryRequestidea(retries);
                                        }
                                    },
                                    error: function() {
                                        retryRequestidea(retries);
                                    }
                                });
                            }
                            
                            function retryRequestidea(retries) {
                                if (retries > 0) {
                                    console.log('Retrying request... Attempts left: ' + retries);
                                    suggestIdea(retries - 1);
                                } else {
                                    hideLoading();
                                    aiautotool_alert_error('Failed to Idea!', '');
                                }
                            }

                            suggestIdea(2);
                        });

     });
                    </script>
                                </p>
                                <textarea id="post-titles" class="ft-code-textarea" style="height:200px" name="post_titles" rows="5" cols="50"  placeholdertext="<?php esc_html_e('Enter post titles','ai-auto-tool'); ?>"></textarea>

                                <?php 
                                if (class_exists('AIautotool_Prompt_CPT')) {
                                    $AIautotool_Prompt_CPT = new AIautotool_Prompt_CPT ();
                                    $AIautotool_Prompt_CPT->render_select_prompt();
                                     
                                }
                                 ?>
                                <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                                    <?php esc_html_e('Select categories for post when post publish','ai-auto-tool'); ?>
                                </p>
                                <?php 
                                wp_dropdown_categories(array(
                                    'name' => 'post_category',
                                    'show_option_all' => 'Select a category',
                                    'hide_empty' => 0,
                                    'hierarchical' => 1
                                ));

                                 ?>
                                 <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                                    <?php esc_html_e('Input tag for post, have random 3 tag in input.','ai-auto-tool'); ?>
                                </p>
                                 <input type="text" class="ft-input-big" name="post_tags" id="post_tags" placeholdertext="<?php esc_html_e('Enter tags (comma-separated)','ai-auto-tool'); ?>" placeholder="Enter tags (comma-separated)">
                                 <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                                    <?php esc_html_e('Language for title, Ai has write content for language your select.','ai-auto-tool'); ?>
                                </p>
                                 <?php 
                                 echo '<select name="post_language" id="post_language">';
                                    foreach ($languages as $code => $name) {
                                        $is_selected = selected($language_code, $code, false);
                                         echo '<option value="' . esc_html($code) . '" ' . esc_html($is_selected) . '>' . esc_html($name) . '</option>';
                                    }
                                    echo '</select>';

                                  ?>

                                  <h3><i class="fa-regular fa-star"></i> Link & Text:</h3>
                            <?php
                                $anchor_text_value = '';
                                $editor_settings = array(
                                    'textarea_name' => 'anchor_text',
                                    'textarea_rows' => 5, 
                                    'teeny' => false, 
                                    'quicktags' => true, 
                                    'tinymce' => true, 
                                );

                                wp_editor($anchor_text_value, 'anchor_text_editor', $editor_settings);
                                ?>

                                   <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                                    <?php esc_html_e('If your check , AI has rewrite your title post.','ai-auto-tool'); ?> 
                                </p>

                                <p>
                                    <label class="nut-switch">
                                    <input type="checkbox" name="auto_change_title" id="auto_change_title"  value="1">
                                    <span class="slider"></span></label>
                                    <label class="ft-label-right"><?php esc_html_e('Allows AI auto change title for Post','ai-auto-tool'); ?></label>
                                    </p>
                                 
                                  <input type="submit" value="<?php esc_html_e('Add Post','ai-auto-tool'); ?>" id="btnSubmitaddpost" class="ft-submit"><div id="loading-icon" class="loader"></div>
                                  </form>
                                  
                                  <div id="progress-bar-container">
                                        <div id="progress-bar" style="width: 0;"></div>
                                    </div>

                                  <div id="post-list"></div>
                                  <style type="text/css">
                                      #progress-bar-container {
    width: 100%;
    height: 20px;
    background-color: #f0f0f0;
    margin-bottom: 10px; /* Thêm khoảng cách giữa thanh tiến trình và danh sách */
    border-radius: 5px;
    overflow: hidden;
}

#progress-bar {
    height: 100%;
    background-color: #4caf50;
    transition: width 0.3s ease-in-out;
}

                                  </style>
                                  <script type="text/javascript">
                                     jQuery('#tab-setting').show();

                                  </script>
                            <!-- end form -->
                    </div>
                    
                     <div id="tab-log-schedule" class="tab-content sotab-box ftbox" style="display:none;">

                        <h2><?php esc_html_e('Logs','ai-auto-tool'); ?></h2>
                         <button id="clear-log-button" class="ft-submit" ><?php esc_html_e('Clear Log', 'ai-auto-tool'); ?></button>

                        <p>
                            <?php $draft = self::aiautotool_get_all_draft(); 

                                echo esc_html__('Has content:','ai-auto-tool').esc_html($draft['hascontent']).' / total '.esc_html($draft['total']);
                            ?>
                        </p>
                            <?php  $log = new AIautoTool_log();
                                $log->show_log('schedule_publish');
                                $log->show_log('schedule_gen_content');
                             ?>

                    </div>
                    <script>
    jQuery(document).ready(function($) {
        $('#clear-log-button').on('click', function(event) {
            event.preventDefault();
            // Gửi yêu cầu xóa log thông qua AJAX
            $.ajax({
                url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                type: 'POST',
                data: {
                    action: 'clear_log_action',
                },
                success: function(response) {
                    // Nếu thành công, làm mới nội dung log
                    location.reload();
                },
            });
        });
    });
</script>

                   
                    </div>
                </div>
        <div class="ft-sidebar-right">
            <div class="ft-widget ft-widget-color1">
                <h2><?php esc_html_e('If you find it helpful','ai-auto-tool'); ?></h2>
                <a target="_blank" href="https://wordpress.org/support/plugin/ai-auto-tool/reviews/?filter=5">Rate now
                <div class="starloader"></div></a>
            </div>
           
            <!-- Nội dung bên phải ở đây -->
             <!-- Nội dung bên phải ở đây -->
            
            <div class="ft-box-">
                <div class="ft-main">
                    <h3><?php echo wp_kses_post($this->icon); ?> <?php esc_html_e('Config Schedule time','ai-auto-tool'); ?> </h3>
                    <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                        <?php esc_html_e('Note: This form only saves the list of titles you enter into a draft. Configure the time for the AI BOT to run in the background to generate content for draft articles. For example, if you choose 5 hours, the AI BOT will automatically select a random draft article every 5 hours and proceed to create content and images for the article. Afterward, it will automatically publish the article according to the schedule you set.','ai-auto-tool'); ?>
                       
                    </p>
                    <img class="ft-img" src="<?php echo esc_url(plugins_url('../images/schedule-post.png', __FILE__)); ?>" />   

                    <h3><?php echo wp_kses_post($this->icon); ?><?php esc_html_e('Manually set up Cron in WordPress','ai-auto-tool'); ?> </h3>
                    <p>
            <?php esc_html_e('Edit the wp-config.php file:','ai-auto-tool'); ?></p>
            <p><?php esc_html_e('Open the wp-config.php file, usually located in the root directory of your WordPress website, and add the following line:','ai-auto-tool'); ?></p>

            <code>define('DISABLE_WP_CRON', true);</code>

           <p> <?php esc_html_e('Schedule Cron using crontab:','ai-auto-tool'); ?></p>
           <p> <?php  esc_html_e('Open crontab using the command:','ai-auto-tool');?></p>
            crontab -e
            <code>
                */5 * * * * wget -q -O - http://yourdomain.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1

            </code>
            <p>
            <?php esc_html_e('Here:','ai-auto-tool'); ?>

            */5 * * * *: <?php esc_html_e('Represents the Cron interval, here set to every 5 minutes. You can adjust it as needed.','ai-auto-tool'); ?>

                    </p>
                </div>
                                  
            </div>
       

        <style>
            .loader {
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    animation: spin 2s linear infinite;
    display: none; 
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}


        </style>
        <script>
        jQuery(document).ready(function($) {
    const loadingIcon = document.getElementById("loading-icon");
    const btnSubmitKeyword = document.getElementById("btnSubmitaddpost");
    const progressBar = $('#progress-bar');

    $('#add-post-form').on('submit', function(e) {
        e.preventDefault();

        var postTitles = $('#post-titles').val().split('\n');
        var autoGenerateTitle = $('#auto_change_title').prop('checked');
        var aiautotool_prompt = $('#aiautotool_prompt').val();
        var securityToken = aiautotool_js_setting.security;
        var batchSize = 10;
       var  anchor_text = tinymce.activeEditor.getContent();

        function sendBatchAjaxRequest(startIndex) {
            var endIndex = Math.min(startIndex + batchSize, postTitles.length);
            var batchData = postTitles.slice(startIndex, endIndex).join('\n');

            if(aiautotool_prompt){
                var data = {
                    action: 'add_post',
                    post_titles: batchData,
                    post_category: $('#post_category').val(),
                    post_tags: $('#post_tags').val(),
                    auto_change_title: autoGenerateTitle,
                    post_language: $('#post_language').val(),
                    anchor_text:tinymce.activeEditor.getContent(),
                    aiautotool_prompt:$('#aiautotool_prompt').val(),
                    security: securityToken
                };
            }else{
                var data = {
                    action: 'add_post',
                    post_titles: batchData,
                    post_category: $('#post_category').val(),
                    post_tags: $('#post_tags').val(),
                    auto_change_title: autoGenerateTitle,
                    post_language: $('#post_language').val(),
                    anchor_text:tinymce.activeEditor.getContent(),
                    security: securityToken
                };
            }
            

            btnSubmitKeyword.style.display = "none";
            loadingIcon.style.display = "inline-block";

            $.post(aiautotool_js_setting.ajax_url, data, function(response) {
                if (response.success) {
                    var postList = $('#post-list');
                    var posts = response.data;

                    for (var i = 0; i < posts.length; i++) {
                        var post = posts[i];
                        var listItem = '<li>' + (startIndex + 1) + ''  + '. <a href="' + post.url_edit + '">' + post.title + '</a></li>';
                        postList.append(listItem);
                    }

                    var progress = ((startIndex + batchSize) / postTitles.length) * 100;
                    progressBar.css('width', progress + '%');

                    if (endIndex < postTitles.length) {
                        sendBatchAjaxRequest(endIndex);
                    } else {
                        btnSubmitKeyword.style.display = "inline-block";
                        loadingIcon.style.display = "none";

                        progressBar.css('width', '0%');
                    }
                } else {
                    btnSubmitKeyword.style.display = "inline-block";
                    loadingIcon.style.display = "none";
                }
            });
        }
        sendBatchAjaxRequest(0);
    });
});


</script>
        <?php
    }

    
    public function enqueue_scripts() {
        
    }

   



}


