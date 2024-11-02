<?php
defined('ABSPATH') or die();


class Aiautotool_exLink_backlink extends rendersetting{

    public  $active = false;
    public  $active_option_name = 'Aiautotool_exLink_backlink_active';


    private $aiautotool_ex_link_list = array();
    private $ds_site = array();
    private $current_site_domain = '';
    private $aiautotool_ex_link_sendrs = array();
    private $aiautotool_ex_link_listidhassend = array();


    private $aiautotool_idpost_haslink = array();

    private $apikey = '';

    public $plugin_slug;
    public $version;
    public $cache_key;
    public $cache_allowed;
    public function __construct() {
        

         $this->active = get_option($this->active_option_name, false);
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
    public function init(){


         add_action('wp_ajax_get_microsite_data', array($this, 'get_microsite_data'));
            add_action('wp_ajax_nopriv_get_microsite_data', array($this, 'get_microsite_data'));
            
            // Thêm mã JavaScript và form HTML
            

            add_filter('cron_schedules', array($this, 'aiautotool_autoexlink_intervals'));

            if (!wp_next_scheduled('aiautotool_autoexlink_event')) {
                wp_schedule_event(time(), 'aiautotool_autoexlink_intervals', 'aiautotool_autoexlink_event');
                
            }

            add_action('aiautotool_autoexlink_event', array($this, 'aiautotool_autoexlink_callback'));

             add_action('admin_menu', array($this, 'aiautotool_linkexauto_menu'));
              add_action('admin_init', array($this, 'handle_log_deletion'));

              add_action('admin_post_aiautotool_submit', array($this, 'handle_submit_form'));

              add_action('rest_api_init', array($this, 'register_autolinkex_route'));
              $this->current_site_domain = parse_url(get_site_url(), PHP_URL_HOST);
               $this->init_ds_site();
              $this->aiautotool_ex_link_list = get_option('aiautotool_ex_link_list', array());
               $this->aiautotool_ex_link_sendrs = get_option('aiautotool_ex_link_sendrs', array());

               $this->aiautotool_ex_link_listidhassend = get_option('aiautotool_ex_link_listidhassend', array());
               $this->aiautotool_idpost_haslink =  get_option('aiautotool_idpost_haslink', array());
               $this->get_api_key();




               $this->setschedulejon($this->active_option_name);

    }

    public function setschedulejon($jobkey){

        add_filter('cron_schedules', array($this, 'cron_interval'));

        if (!wp_next_scheduled('aiautotool_'.$jobkey.'_event')) {
            wp_schedule_event(time(), 'cron_interval', 'aiautotool_'.$jobkey.'_event');
            
        }

        add_action('aiautotool_'.$jobkey.'_event', array($this, 'run_cron_pushlink'));

      

    }



    public function run_cron_pushlink() {
        // update_option('aiautotool_linkinfo', array());
        $link_info = get_option('aiautotool_linkinfo', array(),null, 'no');

        self::write_log_to_file('run backlink new ');
        foreach ($link_info as $key => &$link) {
            if (!$link['completed'] && current_time('timestamp') >= $link['next_run']) {
                // $links = $link['link_list'];
                self::write_log_to_file('run backlink new 2');
                $all_completed = true;

                foreach ($link['link_list'] as $key2 => $target_link) {
                    if (!$target_link['status']) {
                        // Extract domain
                        self::write_log_to_file('run backlink new 3');
                        $domain = parse_url($target_link['url'], PHP_URL_HOST);
                        // if (!preg_match('/^http/', $target_link['url'])) {
                            // Nếu không có, thêm "http://" vào trước domain
                            $domain = 'https://' . $domain;
                        // }
                        // Construct API URL
                        $api_url = $domain . '/wp-json/autolinkex/v1/pushlink';
                        self::write_log_to_file('run backlink new 4'.$api_url);

                        $linkanchortext = self::fix_content_spin($link['anchor_text']);
                        // Prepare data for API request
                        $api_data = array(
                            'domain' => get_home_url(),
                            'anchor_text' => $linkanchortext,
                            'link' => $target_link['url'],
                            'apikey' => get_option('aiautotool_ex_apikey'),
                            'callback'=>array('callbackurl'=>get_home_url(). '/wp-json/autolinkex/v1/callbackpushlink','camp_id'=>$key,'link_id'=>$key2)
                        );

                        // Send API request
                        $response = wp_remote_post($api_url, array(
                            'body' => json_encode($api_data),
                            'headers' => array('Content-Type' => 'application/json'),
                        ));

                        // Handle API response
                        if (is_wp_error($response)) {
                            // Handle error
                            // self::write_log_to_file('nhận dữ liệu: apikey not true '.json_encode($json_data));

                            self::write_log_to_file('API '. $api_url .' request failed: ' . $response->get_error_message());
                            error_log('API request failed: ' . $response->get_error_message());
                        } else {
                            $body = wp_remote_retrieve_body($response);
                            $api_result = json_decode($body, true);

                            // Handle API result
                            // if ($api_result && isset($api_result['status'])&&$api_result['status']==true) {
                                // API call was successful
                                // Update link information
                                $link['next_run'] = current_time('timestamp') + $link['schedule'] * 3600;//strtotime("+{$link['schedule']} hours");

                                // Update status and timerun for the current link in link_list
                                $target_link['status'] = true;
                                $target_link['timerun'] = current_time('timestamp');

                                self::write_log_to_file('Send backlink success '.json_encode( $api_result));
                            
                        }
                        $link['link_list'][$key2] = $target_link;
                        break;
                    }

                    
                }
                //$link['link_list'] = $links ;
                foreach ($link['link_list'] as $target_link) {
                    if (!$target_link['status']) {
                        $all_completed = false;
                    }
                }
                if ($all_completed) {

                    $link['completed'] = true;
                }

                
            }

            
        }

        update_option('aiautotool_linkinfo', $link_info,null, 'no');
    }




    public function cron_interval($schedules) {
        $schedules['cron_interval'] = array(
            'interval' => 2 * 60,
            'display' => __('aiauto backlink Interval','ai-auto-tool'),
        );
        return $schedules;
    }

    public function handle_submit_form() {
        // Validate nonce
        check_admin_referer('aiautotool_submit_nonce', 'aiautotool_nonce');

        // Sanitize and save form data
        $link_info = get_option('aiautotool_linkinfo', array());

        if(isset($_POST['link_id'])){
            $link_id = sanitize_text_field($_POST['link_id']);
            foreach ($link_info as $key =>$link) {
                    if ($link['id'] === $link_id) {
                        $link_info[$key] = array(
                            'id' => $link_id,
                            'anchor_text' => wp_kses_post($_POST['anchor_text']),
                            'link_list' => $this->parse_link_list($_POST['link_list']),
                            'schedule' => intval($_POST['schedule']),
                            'next_run' => current_time('timestamp'),
                            'completed' => false,
                        );
                        break;
                    }
                }
            update_option('aiautotool_linkinfo', $link_info,null, 'no');
        }else{
            $link_info[] = array(
                'id' => uniqid(),
                'anchor_text' => wp_kses_post($_POST['anchor_text']),
                'link' => esc_url($_POST['link']),
                'link_list' => $this->parse_link_list($_POST['link_list']),
                'schedule' => intval($_POST['schedule']),
                'next_run' => current_time('timestamp'),
                'completed' => false,
            );
            update_option('aiautotool_linkinfo', $link_info,null, 'no');
        }
        

        // Redirect after submission
        wp_redirect(admin_url('admin.php?page=aiautotool-linkexauto'));
        exit;
    }

    private function parse_link_list($raw_link_list) {
        $links = array();
        $lines = explode("\n", $raw_link_list);
        
        foreach ($lines as $line) {
            // Assuming the format is url,status,timerun
            $data = explode(',', $line);
            $link = array(
                'url' => esc_url($data[0]),
                'status' => '',
                'timerun' => '',
                'anchortext'=>'',
                'link'=>'',
                'msg'=>'',
            );

            $links[] = $link;
        }

        return $links;
    }

    private function reverse_link_list($links) {
        $raw_link_list = '';

        foreach ($links as $link) {
            $raw_link_list .= $link['url'] . "\n";
        }

        return rtrim($raw_link_list, "\n");
    }

    private function reverse_link_listfull($links) {
        $raw_link_list = '';
// print_r($links);
        $count = count($links);
        $i=0;
        foreach ($links as $link) {
            if($link['status']===true){
                $i++;
            }
            $raw_link_list .= '<div style="border: 1px #000 solid;border-radius: 5px;margin: 5px;padding: 2px;">'.$link['url'].' - status: '.($link['status']===true? 'True':'') .' - time run: '.(date('Y-m-d H:i:s', $link['timerun']) ). "</div>";
        }

        $string = $i.' <i style="color:var(--color)" class="fa-sharp fa-solid fa-badge-check"></i> / '.$count.' Links';
        return $string;

        return rtrim($raw_link_list, "\n");
    }
    private function fix_content_spin($content){
        $spintax = new Spintaxkct();
        $content = $spintax->process($content);
        return trim($content);
    }
    public function render_setting() {
        // Cài đặt cho lớp auto_ex_link ở đây
       if($this->active=="true"){
        ?>
        <div id="tab-exlink-setting" class="tab-content" style="display:none;">
            <h2><i class="fa-solid fa-link"></i> Microsite Extenlink Setting</h2>
            <?php self::aiautotool_linkexauto_page_initsite(); ?>
        </div>
            <?php
        }
    }

    public function render_tab_setting() {
        // Cài đặt cho lớp auto_ex_link ở đây
        if ($this->active=='true') {
            echo '<button href="#tab-exlink-setting" class="nav-tab sotab"><i class="fa-solid fa-link"></i> Microsite Backlink</button>';
        }
        
    }
    
    public function render_feature(){
        $autoToolBox = new AutoToolBox("<i class=\"fa-solid fa-link\"></i> Microsite auto BackLink", "It also offers a Microsite management system that aids in SEO optimization and automatically links to websites within the same system. This helps you build a strong network of links and optimize your online presence", "https://aiautotool.com", $this->active_option_name, $this->active,plugins_url('../images/logo.svg', __FILE__));

       
         echo ($autoToolBox->generateHTML());
        ?>
        

        <?php
    }
    public function get_api_key(){
        $this->apikey = get_option('aiautotool_ex_apikey'); 
        if(empty($this->apikey)){
            $this->apikey = 'KCT-'.uniqid();
            update_option('aiautotool_ex_apikey',$this->apikey,null, 'no');
        }
        return $this->apikey;
    }
    private function init_ds_site() {

        $this->ds_site = get_option('aiautotool_ex_dssite',array()); 
        if (empty($this->ds_site)) {
            $this->ds_site[] = home_url(); 
            update_option('aiautotool_ex_dssite', $this->ds_site,null, 'no');
        }
         $this->ds_site = get_option('aiautotool_ex_dssite',array()); 
       
    }


    public function register_autolinkex_route() {
        register_rest_route('autolinkex/v1', '/autolinkex', array(
            'methods' => 'POST', // Phương thức POST để gửi dữ liệu JSON
            'callback' => array($this, 'handle_autolinkex_request'),
        ));
        register_rest_route('autolinkex/v1', '/loadsite', array(
            'methods' => 'GET', // Phương thức POST để gửi dữ liệu JSON
            'callback' => array($this, 'handle_loadsite_request'),
        ));

        register_rest_route('autolinkex/v1', '/pushlink', array(
            'methods' => 'POST', // Phương thức POST để gửi dữ liệu JSON
            'callback' => array($this, 'handle_pushlink_request'),
        ));

        register_rest_route('autolinkex/v1', '/callbackpushlink', array(
            'methods' => 'POST', // Phương thức POST để gửi dữ liệu JSON
            'callback' => array($this, 'handle_callbackpushlink_request'),
        ));
    }


    public function  handle_loadsite_request($request) {

        $this->ds_site = get_option('aiautotool_ex_dssite',array()); 
        $this->apikey = get_option('aiautotool_ex_apikey'); 
        $arr = array(
            'status'=>'success',
            'items'=> $this->ds_site,
            'apikey'=>$this->apikey
        );

        return wp_send_json($arr);
    }
    public function handle_autolinkex_request($request) {
        // Lấy dữ liệu JSON từ yêu cầu
        $apikey = get_option('aiautotool_ex_apikey'); 
        $json_data = $request->get_json_params();

        // Kiểm tra nếu dữ liệu JSON không hợp lệ hoặc không tồn tại
        if (empty($json_data) || !is_array($json_data)) {
            return new WP_REST_Response(array('error' => 'Invalid JSON data'), 400);
        }
        if($apikey===$json_data['apikey']){
            $this->aiautotool_ex_link_list[] = $json_data;
            update_option('aiautotool_ex_link_list', $this->aiautotool_ex_link_list,null, 'no');
            
            $arr = array(
                'status'=>'success',
                'mgs'=> $this->$current_site_domain.' nhận link '.json_encode($json_data)
            );
             self::write_log_to_file('nhận dữ liệu: '.json_encode($json_data));
             self::update_node($json_data['url']);
            // Trả về phản hồi JSON (tuỳ theo nhu cầu của bạn)
             echo json_encode($arr);
            return rest_ensure_response($arr);
        }else{
            $arrx = array(
                'status'=>false,
                'msg'=>'apikey not true'
            );
             self::write_log_to_file('nhận dữ liệu: apikey not true '.json_encode($json_data));
            return rest_ensure_response($arrx);
        }
        
    }

    

    public function handle_callbackpushlink_request($request) {
        if ( PHP_VERSION_ID >= 70016 && function_exists( 'fastcgi_finish_request' ) ) {
            fastcgi_finish_request();
        } elseif ( function_exists( 'litespeed_finish_request' ) ) {
            litespeed_finish_request();
        }
        $apikey = get_option('aiautotool_ex_apikey');
        $json_data = $request->get_json_params();
        self::write_log_to_file('Callbackpush link: '.json_encode( $json_data));
        if (empty($json_data) || !is_array($json_data)) {
            return new WP_REST_Response(array('error' => 'Invalid JSON data'), 400);
        }

        if ($apikey === $json_data['apikey']) {
            $link_info = get_option('aiautotool_linkinfo', array());
            // foreach ($link_info as $key => &$link) {
            if(isset($link_info[$json_data['callbackdata']['camp_id']])){



                $link = $link_info[$json_data['callbackdata']['camp_id']];
                
                    
                    $all_completed = true;
                    foreach ($link['link_list'] as $key2 => $target_link) {
                        if($key2==$json_data['callbackdata']['link_id']){
                            $link['next_run'] = current_time('timestamp') + $link['schedule'] * 3600;//
                            $target_link['status'] = true;
                            $target_link['timerun'] = current_time('timestamp');
                            $target_link['anchortext'] = $json_data['anchortext'];
                            $target_link['link'] = $json_data['link'];
                            $target_link['msg'] = $json_data['msg'];

                            $link['link_list'][$key2] = $target_link;
                            break;
                        }

                    }
                foreach ($link['link_list'] as $target_link) {
                    if (!$target_link['status']) {
                        $all_completed = false;
                    }
                }
                if ($all_completed) {

                    $link['completed'] = true;
                } 
                $link_info[$json_data['callbackdata']['camp_id']] = $link;
                update_option('aiautotool_linkinfo', $link_info,null, 'no');
            }

        }

    }
    public function handle_pushlink_request($request) {

        if ( PHP_VERSION_ID >= 70016 && function_exists( 'fastcgi_finish_request' ) ) {
            fastcgi_finish_request();
        } elseif ( function_exists( 'litespeed_finish_request' ) ) {
            litespeed_finish_request();
        }

        $datacallback = array();
        $apikey = get_option('aiautotool_ex_apikey');
        $datacallback['callback'] = '';
        $json_data = $request->get_json_params();
        self::write_log_to_file('put link to post. '.json_encode( $json_data));
        if (empty($json_data) || !is_array($json_data)) {
            return new WP_REST_Response(array('error' => 'Invalid JSON data'), 400);
        }
        self::write_log_to_file('put link to post. '.json_encode( $json_data));
        if ($apikey === $json_data['apikey']) {

            $callbackdata =  $json_data['callback'];
            $datacallback['callback'] = $callbackdata;
            $datacallback['linkanchortext'] = $json_data['anchor_text'];

            
            self::update_node($json_data['domain']);
            
            // $post = get_page_by_path(parse_url($json_data['link'], PHP_URL_PATH), OBJECT, 'post');
            $postid = url_to_postid(parse_url($json_data['link'], PHP_URL_PATH)) ;
            $post = get_post($postid);
           self::write_log_to_file('Found post. '.json_encode( $post));
            if ($post && $post->post_type === 'post') {
                $textadd = self::fix_content_spin($json_data['anchor_text']);
                $anchor_text_count = get_post_meta($post->ID, 'pushlinkadd', true);

                if ($anchor_text_count === '') {
                    $anchor_text_count = 0;
                }


                if($anchor_text_count>=3){
                        self::write_log_to_file(' post add link push 3 time. '.json_encode( $post));
                        $arrx = array(
                            'status' => false,
                            'msg' => 'Post add link push > 3 time.',
                        );
                        $datacallback['msg'] ='Post add link push > 3 time.';
                        self::callbacksend($datacallback);
                        return rest_ensure_response($arrx);
                        
                }else{
                        $post_content = $post->post_content;

                        // Tiến hành chèn $textappend vào sau thẻ p thứ 3 trong bài
                        $post_content = self::insert_text_after_third_paragraph($post_content, $textadd);

                        // Cập nhật nội dung cho post
                        wp_update_post(array('ID' => $post->ID, 'post_content' => $post_content));

                        $arrx = array(
                            'status' => true,
                            'msg' => 'Post content updated successfully.'.$json_data['link'],
                        );
                        $datacallback['msg'] ='Found post, insert link success.';
                        self::callbacksend($datacallback);
                        self::write_log_to_file('Found post. '.json_encode( $arrx));
                        $anchor_text_count++;
                        update_post_meta($post->ID, 'pushlinkadd', $anchor_text_count);

                        return rest_ensure_response($arrx);
                    }

                // Lấy nội dung của post
                
            } else {
                $arrx = array(
                    'status' => false,
                    'msg' => 'Post not found',
                );
                $datacallback['msg'] ='Post not found';
                self::callbacksend($datacallback);
                self::write_log_to_file('Post not found ' . json_encode($json_data));
                return rest_ensure_response($arrx);
            }
        } else {
            $arrx = array(
                'status' => false,
                'msg' => 'Apikey not true',
            );
            $datacallback['msg'] ='Apikey not true';
            self::callbacksend($datacallback);
            self::write_log_to_file('Apikey not true ' . json_encode($json_data));
            return rest_ensure_response($arrx);
        }

        
    }

    public function callbacksend($data){

        $api_url = $data['callback']['callbackurl'];
        $api_data = array(
            'domain' => get_home_url(),
            'anchortext' => $data['linkanchortext'],
            'link' => '',
            'apikey' => get_option('aiautotool_ex_apikey'),
            'callback'=>$data['callback'],
            'msg'=>$data['msg']
        );
        $response = wp_remote_post($api_url, array(
                                    'body' => json_encode($api_data),
                                    'headers' => array('Content-Type' => 'application/json'),
                                ));
        if (is_wp_error($response)) {
                                    // Handle error
                                    // self::write_log_to_file('nhận dữ liệu: apikey not true '.json_encode($json_data));

                                    self::write_log_to_file('API callback pushlink'. $api_url .' request failed: ' . $response->get_error_message());
                                    error_log('API request failed: ' . $response->get_error_message());
                                } else {
                                    $body = wp_remote_retrieve_body($response);
                                    

                                        self::write_log_to_file('Send callback backlink success '.json_encode( $api_data));
                                    
                                }
    }

    // Hàm chèn $text vào sau thẻ p thứ 3 trong bài viết
    public static function insert_text_after_third_paragraph($content, $text) {
        // Tìm vị trí của thẻ p thứ 3
        preg_match_all('/<p(.*?)<\/p>/', $content, $matches, PREG_OFFSET_CAPTURE);

        if (isset($matches[0][2])) {
            $third_paragraph_pos = $matches[0][2][1] + strlen($matches[0][2][0]);
            $content = substr($content, 0, $third_paragraph_pos) . $text . substr($content, $third_paragraph_pos);
        }

        return $content;
    }


    public function send_post_to_autolinkex($data) {
        // URL của REST endpoint
        $autolinkoffon = get_option('aiautotool_autolinkoffon',0); 
        if($autolinkoffon==0){
            $filtered_ds_site = array_filter($this->ds_site, function ($site) {
                return parse_url($site, PHP_URL_HOST) !== $this->current_site_domain;
            });

             $data['apikey'] = get_option('aiautotool_ex_apikey'); 
            self::write_log_to_file('send dữ liệu: '.json_encode($data));

            

            $json_data = json_encode($data);
            $dsrs = array();
            shuffle($filtered_ds_site);
            foreach ($filtered_ds_site as $site_url) {

               $api_url = $site_url.'/wp-json/autolinkex/v1/autolinkex'; 
               self::write_log_to_file('send => '.$api_url );
               $curl_options = array(
                    CURLOPT_URL => $api_url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $json_data,
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                    ),
                );

                // Khởi tạo một phiên cURL
                $curl = curl_init();

                // Thiết lập các tùy chọn cURL
                curl_setopt_array($curl, $curl_options);

                // Thực hiện yêu cầu cURL và lấy phản hồi
                $response = curl_exec($curl);

                if (curl_errno($curl)) {
                    // Xử lý lỗi nếu có
                    curl_close($curl);
                    return false;
                }

                // Đóng phiên cURL
                curl_close($curl);

                $response_data = json_decode($response, true);
                $dsrs = $response_data;
                $this->aiautotool_ex_link_sendrs[] = $dsrs;
                break;
            }
            // $this->aiautotool_ex_link_sendrs = $dsrs;
            update_option('aiautotool_ex_link_sendrs', $this->aiautotool_ex_link_sendrs,null, 'no');
            self::write_log_to_file('send data: '.json_encode($dsrs));
            return $dsrs;
        }
         
    }

     public  function aiautotool_autoexlink_callback() {
        // self::write_log_to_file('run taskx');
        self::task_linkex();
    }


    public function aiautotool_parseHtmlAnchor($htmlcode) {
        // Sử dụng DOMDocument để phân tích HTML
        $dom = new DOMDocument();
        $dom->loadHTML($htmlcode);

        // Lấy thẻ <a> trong HTML
        $anchorElement = $dom->getElementsByTagName('a')->item(0);

        // Khởi tạo mảng để chứa kết quả
        $result = array('text' => '', 'link' => '');

        // Lấy text và link nếu thẻ <a> tồn tại
        if ($anchorElement) {
            
            $result['link'] = $anchorElement->getAttribute('href');
        }
        $result['text'] = strip_tags($htmlcode);
        return $result;
    }

    

    // Callback function to display the admin page content
public function aiautotool_linkexauto_page() {
    // Check if the user has the required capability to access this page
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this page.');
    }
    $link_info = get_option('aiautotool_linkinfo', array());
    $link_to_edit = null;
    $link_id = null;
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
        // Nếu action là edit và có id, chuyển đến trang sửa
        $link_id = $_GET['id'];
        foreach ($link_info as $link) {
                if ($link['id'] === $link_id) {
                    $link_to_edit = $link;
                    break;
                }
            }
            
    } 

    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        // Nếu action là edit và có id, chuyển đến trang sửa
        $link_id = $_GET['id'];
        foreach ($link_info as $key =>$link) {
                if ($link['id'] === $link_id) {
                    unset($link_info[$key]);
                    break;
                }
            }
        update_option('aiautotool_linkinfo', $link_info,null, 'no');
        $link_info = get_option('aiautotool_linkinfo', array());  
    } 
    
   
    ?>
    <div class="wrap aiautotool_container">
     <div class="aiautotool_left">
            <div class="ft-box">
                <div class="ft-menu">
                     <div class="ft-logo"><img src="<?php echo esc_url(plugins_url('../images/logo.svg', __FILE__)); ?>">
                    <br>Backlink control auto</div>

                    <button class="nav-tab sotabt" href="#tab-view"><i class="fa-regular fa-gauge-max"></i> <?php esc_html_e('Manual send backlink', 'ai-auto-tool' ); ?></button>
                    <button class="nav-tab sotabt" href="#tab-sendlink"><i class="fa-solid fa-shield-halved"></i> <?php esc_html_e('View List Sendlink', 'ai-auto-tool' ); ?></button>
                <button class="nav-tab sotabt" href="#tab-log"><i class="fa-solid fa-shield-halved"></i> <?php esc_html_e('Log', 'ai-auto-tool' ); ?></button>

                    
                </div>
                <div class="ft-main">

                    <div id="tab-view" class="tab-content sotab-box ftbox">
                        <?php 

                        if (!$link_to_edit) {
                            ?>
                            <form id="aiautotool-form" method="post" action="<?php echo esc_url(admin_url('admin-ajax.php'));?>">
                            <?php wp_nonce_field('aiautotool_submit_nonce', 'aiautotool_nonce'); ?>
                            <!-- Add your form fields here -->
                            <input type="hidden" name="action" value="aiautotool_submit">
                           
                            <h3><i class="fa-regular fa-star"></i> Link & Text:</h3>
                            <?php
                                $anchor_text_value = $link_id ? $link_to_edit['anchor_text'] : '';
                                $editor_settings = array(
                                    'textarea_name' => 'anchor_text',
                                    'textarea_rows' => 5, // Số hàng của trình soạn thảo
                                    'teeny' => false, // Sử dụng giao diện Teeny (true) hoặc không (false)
                                    'quicktags' => true, // Sử dụng Quicktags (true) hoặc không (false)
                                    'tinymce' => true, // Sử dụng TinyMCE (true) hoặc không (false)
                                );

                                wp_editor($anchor_text_value, 'anchor_text_editor', $editor_settings);
                                ?>
                            
                            <h3><i class="fa-regular fa-star"></i> Link List:</h3>
                            <textarea name="link_list" class="ft-code-textarea" style="height:100px" placeholder="List of Links"></textarea>

                            <h3><i class="fa-regular fa-star"></i> Time run:</h3>
                            <select name="schedule">
                                <option value="0">Run immediately</option>
                                <option value="1">1 hour 1 link</option>
                                <option value="2">2 hours 1 link</option>
                                <option value="3">3 hours 1 link</option>
                                <option value="5">5 hours 1 link</option>
                                <option value="12">12 hours 1 link</option>
                                <option value="24">1 day 1 link</option>
                                <option value="48">2 days 1 link</option>
                                <option value="72">3 days 1 link</option>
                                <option value="168">7 days 1 link</option>
                                <option value="240">10 days 1 link</option>
                                <option value="480">20 days 1 link</option>
                            </select>
                            <input type="submit" class="ft-submit" value="Push link">
                        </form>
                            <?php
                        }else{
                            ?>
                            <form id="aiautotool-form" method="post" action="<?php echo esc_url(admin_url('admin-ajax.php'));?>">
                            <?php wp_nonce_field('aiautotool_submit_nonce', 'aiautotool_nonce'); ?>
                            <input type="hidden" name="action" value="aiautotool_submit">
                            <input type="hidden" name="link_id" value="<?php echo esc_attr($link_id); ?>">
                            <h3><i class="fa-regular fa-star"></i> Link & Text:</h3>
                            <?php
                                $anchor_text_value = $link_id ? $link_to_edit['anchor_text'] : '';
                                $editor_settings = array(
                                    'textarea_name' => 'anchor_text',
                                    'textarea_rows' => 5, // Số hàng của trình soạn thảo
                                    'teeny' => false, // Sử dụng giao diện Teeny (true) hoặc không (false)
                                    'quicktags' => true, // Sử dụng Quicktags (true) hoặc không (false)
                                    'tinymce' => true, // Sử dụng TinyMCE (true) hoặc không (false)
                                );

                                wp_editor($anchor_text_value, 'anchor_text_editor', $editor_settings);
                                ?>
                           
                            
                            <h3><i class="fa-regular fa-star"></i> Link List:</h3>
                            <textarea name="link_list" class="ft-code-textarea" style="height:100px" placeholder="List of Links"><?php echo esc_textarea($link_id ? $this->reverse_link_list($link_to_edit['link_list']) : ''); ?></textarea>

                            <h3><i class="fa-regular fa-star"></i> Time run Schedule:</h3>
                            
                            <select name="schedule">
                                <option value="0" <?php selected($link_id ? $link_to_edit['schedule'] : 0, 0); ?>>Run immediately</option>
                                <option value="1" <?php selected($link_id ? $link_to_edit['schedule'] : 0, 1); ?>>1 hour 1 link</option>

                                <option value="2" <?php selected($link_id ? $link_to_edit['schedule'] : 0, 2); ?>>2 hours 1 link</option>
                                <option value="3" <?php selected($link_id ? $link_to_edit['schedule'] : 0, 3); ?>>3 hours 1 link</option>
                                <option value="5" <?php selected($link_id ? $link_to_edit['schedule'] : 0, 5); ?>>5 hours 1 link</option>
                                <option value="12" <?php selected($link_id ? $link_to_edit['schedule'] : 0, 12); ?>>12 hours 1 link</option>
                                <option value="24" <?php selected($link_id ? $link_to_edit['schedule'] : 0, 24); ?>>1 day 1 link</option>
                                <option value="48" <?php selected($link_id ? $link_to_edit['schedule'] : 0, 48); ?>>2 days 1 link</option>
                                <option value="72" <?php selected($link_id ? $link_to_edit['schedule'] : 0, 72); ?>>3 days 1 link</option>
                                <option value="168" <?php selected($link_id ? $link_to_edit['schedule'] : 0, 168); ?>>7 days 1 link</option>
                                <option value="240" <?php selected($link_id ? $link_to_edit['schedule'] : 0, 240); ?>>10 days 1 link</option>
                                <option value="480" <?php selected($link_id ? $link_to_edit['schedule'] : 0, 480); ?>>20 days 1 link</option>
                                <!-- Add other schedule options -->
                            </select>
                            <input type="submit" class="ft-submit" value="<?php echo ($link_id ? 'Update' : 'Add'); ?> Link">
                        </form>
                            <?php
                        }

                         ?>
                             
                    </div>
                    <div id="tab-sendlink" class="tab-content sotab-box ftbox">
                           
 <?php
                $link_info = get_option('aiautotool_linkinfo', array());

                if (!empty($link_info)) {
                    echo '<div class="logss">';
                    echo '<table class="ft-card-note aiautotool_form_canonical_table">';
                    echo '<tr><th class="aiautotool_form_canonical_th">No</th><th class="aiautotool_form_canonical_th">Link</th><th class="aiautotool_form_canonical_th">Anchor Text</th><th class="aiautotool_form_canonical_th">Link List</th><th class="aiautotool_form_canonical_th">Schedule</th><th class="aiautotool_form_canonical_th">Next Run</th><th class="aiautotool_form_canonical_th">Status</th><th class="aiautotool_form_canonical_th">Tool</th></tr>';
                    $i=1;
                    uasort($link_info, function ($a, $b) {
                        return $b['id'] <=> $a['id'];
                    });
                    foreach ($link_info as $key=> $link) {
                        $textlink = self::aiautotool_parseHtmlAnchor($link['anchor_text']);
                        echo '<tr data-key="'.esc_attr($key).'" style="" class="aiautotool_form_canonical_td aiautotool-popup-trigger" data-popup-content="'.esc_attr(json_encode($link)).'">';
                        echo '<td  class="aiautotool_form_canonical_td">' . esc_attr($i++) . ' </td>';
                        echo '<td  class="aiautotool_form_canonical_td">' . (esc_attr($textlink['link'])) . ' </td>';
                        echo '<td  class="aiautotool_form_canonical_td">' . (esc_attr($textlink['text'])) . '</td>';
                        echo '<td  class="aiautotool_form_canonical_td">' . esc_html(nl2br($this->reverse_link_listfull($link['link_list'])))  . '</td>';
                        echo '<td  class="aiautotool_form_canonical_td">' . esc_html($link['schedule']) . ' hours</td>';
                        echo '<td  class="aiautotool_form_canonical_td">' . esc_html(date('Y-m-d H:i:s', $link['next_run'])) . '</td>';
                        echo '<td  class="aiautotool_form_canonical_td">' . ($link['completed'] ? '<i style="color:var(--color)" class="fa-sharp fa-solid fa-badge-check"></i>' : '<i class="fa-sharp fa-solid fa-alarm-clock"></i>') . '</td>';
                        echo '<td  class="aiautotool_form_canonical_td"><a href="' . esc_url(admin_url('admin.php?page=aiautotool-linkexauto&action=edit&id=' . $link['id'])) . '"><i class="fa-sharp fa-solid fa-pen-to-square"></i></a> | ';
                        echo '<a href="' . esc_url(admin_url('admin.php?page=aiautotool-linkexauto&action=delete&id=' . $link['id'])) . '"><i class="fa-sharp fa-solid fa-trash"></i></a></td>';
                        echo '</tr>';

                    }

                    echo '</table>';
                    echo '</div>';
                } else {
                    echo '<p>No links found.</p>';
                }
                ?>
                    </div>
                     <div id="tab-log" class="tab-content sotab-box ftbox">
                        <?php
                        echo '<form method="post">';

echo '<input type="hidden" name="aiautotool_linkauto_delete_log" value="1" />';
echo '<input type="submit" class="button button-primary" value="Delete Log" />';
echo '</form>';
    ?>
    <div id="logss" style="width: 800px !important; overflow-x: auto;white-space: nowrap;">
    <?php
    self::display_log();

                          ?>
                          </div>
                    </div>
                </div>
            </div>

    </div>
     </div>

     <script type="text/javascript">
         
         <?php 
            if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
               ?>
               localStorage.setItem("selectedTab", "#tab-view");
               jQuery('.tab-content').hide();
               jQuery('#tab-view').show();
                
               <?php
                    
            } 
            
          ?>


          jQuery(document).ready(function($) {
            $('.aiautotool-popup-trigger').on('click', function() {
                var key = $(this).data('key');
                
                var content = $(this).data('popup-content');

                if (!$(event.target).is('i.fa-pen-to-square') && !$(event.target).is('i.fa-trash')) {
                    showPopup(content);
                    return false;
                }

                
            });
function showPopup(content) {

    var listlink = content.link_list;
    var popupHTML = '<div class="aiautotool-popup" >';
    popupHTML += '<button id="exportCsvBtn">Export CSV</button>';
    
    popupHTML += '<table  class="ft-card-note aiautotool_form_canonical_table">';
    popupHTML += '<thead><tr><th class="aiautotool_form_canonical_th">Link</th><th class="aiautotool_form_canonical_th">Status</th><th class="aiautotool_form_canonical_th">Time Run</th><th class="aiautotool_form_canonical_th">Anchor text</th><th class="aiautotool_form_canonical_th">Msg</th></tr></thead>';
    popupHTML += '<tbody>';

    listlink.forEach(function(item) {
        popupHTML += '<tr>';
        popupHTML += '<td class="aiautotool_form_canonical_td"><a href="' + item.url + '" target="_blank">' + item.url + '</a></td>';

        popupHTML += '<td class="aiautotool_form_canonical_td">' + (item.status ? '<i style="color:var(--color)" class="fa-sharp fa-solid fa-badge-check"></i>' : '<i class="fa-sharp fa-solid fa-alarm-clock"></i>') + '</td>';
        popupHTML += '<td class="aiautotool_form_canonical_td">' + (item.timerun ? new Date(item.timerun * 1000).toLocaleString() : '') + '</td>';
        popupHTML += '<td class="aiautotool_form_canonical_td">' + (item.anchortext ? item.anchortext : '') + '</td>';
        popupHTML += '<td class="aiautotool_form_canonical_td">' + (item.msg ? item.msg : '') + '</td>';
        popupHTML += '</tr>';
    });

    popupHTML += '</tbody>';
    popupHTML += '</table>';
    
    popupHTML += '<div class="aiautotool-popup-close">&times;</div>';
    popupHTML += '</div>';

    // Thêm popup vào body
    $('body').append(popupHTML);

    $('.aiautotool-popup').fadeIn();

    $('.aiautotool-popup-close').on('click', function() {
        $('.aiautotool-popup').fadeOut(function() {
            
            $(this).remove();
        });
    });

    $('#exportCsvBtn').on('click', function() {
        // Gọi hàm exportCSV(content) để xử lý xuất CSV
        exportCSV(content);
    });
}


function exportCSV(content) {
    // Lấy table từ popup
    var table = $('.aiautotool-popup table');

    // Tạo chuỗi CSV
    var csvContent = "data:text/csv;charset=utf-8,";

    // Duyệt qua từng dòng của table (trừ dòng header)
    table.find('tr').each(function() {
        // Duyệt qua từng ô trong dòng
        $(this).find('td').each(function(index) {
            // Thêm giá trị của ô vào chuỗi CSV
            csvContent += $(this).text() + ',';
        });
        // Xuống dòng sau mỗi dòng
        csvContent += '\n';
    });

    // Tạo đối tượng chứa dữ liệu CSV
    var encodedUri = encodeURI(csvContent);

    // Tạo thẻ a để tạo và tải xuống tệp CSV
    var link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "link_list.csv");
    document.body.appendChild(link); // Thêm thẻ a vào body
    link.click(); // Kích hoạt sự kiện click để tải xuống
    document.body.removeChild(link); // Xóa thẻ a sau khi tải xuống
}


        });

     </script>

    <?php
    // Read the log file contents
   
  
}


public function display_log() {
    // Lấy giá trị của biến log từ options
    $log_entries = get_option('aiautotool_ex_link_log_all', '');

    // Kiểm tra xem có log nào hay không
    if (!empty($log_entries)) {
        // Phân tách các mục log thành mảng để hiển thị
        $log_array = explode(PHP_EOL, $log_entries);

        // Hiển thị mỗi mục log trong một thẻ <pre> để bảo toàn định dạng
        echo '<pre>';
        foreach ($log_array as $log_entry) {
            echo esc_html($log_entry) . '<br>';
        }
        echo '</pre>';
    } else {
        echo 'No log.';
    }
}

public function aiautotool_linkexauto_page_initsite() {
    $ds_site = get_option('aiautotool_ex_dssite', array());
    $apikey = get_option('aiautotool_ex_apikey'); 
    $accessnewpost = get_option('aiautotool_accessnewpost'); 
    $autolinkoffon = get_option('aiautotool_autolinkoffon'); 
    if (!current_user_can('manage_options')) {
            return;
        }

        // Save settings
        if (isset($_POST['atp_save_settings'])&& wp_verify_nonce($_POST['form_nonce'], 'form_nonce_action')) {
            update_option('aiautolink_ex_key', sanitize_text_field($_POST['aiautolink_ex_key']),null, 'no');


            


            

            $translate_interval = intval($_POST['aiautolinkex_time_interval']); // Lấy giá trị thời gian từ select box
            update_option('aiautolinkex_time_interval', $translate_interval,null, 'no');
            $accessnewpost = intval($_POST['accessnewpost']);
             update_option('aiautotool_accessnewpost', $accessnewpost,null, 'no');


             $autolinkoffon = intval($_POST['autolinkoffon']);
             update_option('aiautotool_autolinkoffon', $autolinkoffon,null, 'no');

            echo '<div class="updated"><p>Settings saved.</p></div>';
        }

        $aiautolinkex_time_interval = get_option('aiautolinkex_time_interval');

    ?>
    <div class="wrap">
         
        <form id="initSiteForm">
            <?php wp_nonce_field('form_nonce_action', 'form_nonce'); ?>
       
            
            <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                  Input one site in microsite (https://example.com):
                    </p>
            <input type="text" id="micrositeUrl" class="ft-input-big" name="micrositeUrl" placeholder="Enter URL" required>
            <button type="button" id="loadMicrositeList" class="ft-submit">Load List Microsite</button>
            <div id="loading-icon" class="loader"></div>
        </form>
        <div id="micrositeResult"></div>
        <h3>List of Sites:</h3>
        <ul id="listsite">
            <?php
            if (!empty($ds_site)) {
                 $count = 0;
                foreach ($ds_site as $site) {
                    $count++;
                     echo '<li>' . esc_html($count) . '. ' . esc_html($site) . '</li>';
                }
            } else {
                echo '<li>No sites added yet.</li>';
            }
            ?>
        </ul>
    </div>
    <hr>
    <h3>Settings time run schedule</h3>

    


            <form method="post" action="">
                <?php wp_nonce_field('form_nonce_action', 'form_nonce'); ?>
       
            
                <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>Key Set</p>
    <input disabled type="text" id="aiautolink_ex_key" name="aiautolink_ex_key" value="<?php echo esc_attr($apikey); ?>" class="regular-text" />
    <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>Time Interval</p>
    <select id="aiautolinkex_time_interval" name="aiautolinkex_time_interval">
                            <option value="1" <?php selected($aiautolinkex_time_interval, 1); ?>>1 minute</option>
                            <option value="5" <?php selected($aiautolinkex_time_interval, 5); ?>>5 minutes</option>
                            <option value="10" <?php selected($aiautolinkex_time_interval, 10); ?>>10 minutes</option>
                            <option value="15" <?php selected($aiautolinkex_time_interval, 15); ?>>15 minutes</option>
                            <option value="30" <?php selected($aiautolinkex_time_interval, 30); ?>>30 minutes</option>
                            <option value="60" <?php selected($aiautolinkex_time_interval, 60); ?>>1 hour</option>
                        </select>
    <p></p>
    <label class="ft-label-right"><?php esc_html_e('Create new post with AI when not find related post.', 'ai-auto-tool'); ?></label>
                    <p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('When find related post for insert link not found, AI auto create singple post from archor text of link then insert backlink in new post.', 'ai-auto-tool'); ?></p>
                    <!-- tôi ưu 2 -->
                    <label class="nut-switch">
                    <input type="checkbox" name="accessnewpost" value="1" <?php if ( isset($accessnewpost) && 1 == $accessnewpost ) echo 'checked="checked"'; ?> />
                    <span class="slider"></span></label>
    <p><hr></p>
    <label class="ft-label-right"><?php esc_html_e('Turn off auto link send', 'ai-auto-tool'); ?></label>
                    <p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php esc_html_e('Off auto send backlink.', 'ai-auto-tool'); ?></p>
                    <!-- tôi ưu 2 -->
                    <label class="nut-switch">
                    <input type="checkbox" name="autolinkoffon" value="1" <?php if ( isset($autolinkoffon) && 1 == $autolinkoffon ) echo 'checked="checked"'; ?> />
                    <span class="slider"></span></label>
    <p class="submit">
                    <input type="submit" name="atp_save_settings" class="ft-submit" value="Save Settings" />
    </p>

            </form>
            <hr>
<style>
    
    button {
  padding: 15px 30px;
  font-size: 16px;
  background-color: #007bff;
  color: #fff;
  border: none;
  cursor: pointer;
}

.loader {
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    animation: spin 2s linear infinite;
    display: none; /* Ẩn ban đầu */
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
    <script>
        jQuery(document).ready(function($) {
            const loadingIcon = document.getElementById("loading-icon");
            const btnSubmitKeyword = document.getElementById("loadMicrositeList");

            $('#loadMicrositeList').click(function() {
                
                var inputUrl = $('#micrositeUrl').val();
                if (isValidURL(inputUrl)) {
                    btnSubmitKeyword.style.display = "none";
                loadingIcon.style.display = "inline-block";
                    $.ajax({
                        url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', // Thay bằng URL của trang hiện tại
                        type: 'POST',
                        data: {
                            action: 'get_microsite_data',
                            micrositeUrl: inputUrl,
                            security:aiautotool_js_setting.security
                        },
                        success: function(data) {
                            console.log(data);
                            var listsite = $('#listsite');
                            listsite.empty();
                             $.each(data.items, function(index, item) {
                                var stt = index + 1;
                                listsite.append('<li>' + stt + '. ' + item + '</li>');
                            });

                             btnSubmitKeyword.style.display = "inline-block";
                            loadingIcon.style.display = "none";
                            
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                            $('#micrositeResult').html('Error: Unable to fetch data from API.');
                        }
                    });
                } else {
                    alert('Invalid URL. Please enter a valid URL starting with "https://".');
                }
            });

            function isValidURL(url) {
                var pattern = /^https:\/\//i;
                return pattern.test(url);
            }
        });

    </script>
    <?php
}


    public function handle_log_deletion() {
    if (isset($_POST['aiautotool_linkauto_delete_log']) && $_POST['aiautotool_linkauto_delete_log'] == 1) {
        // Kiểm tra xem người dùng có quyền xoá log không
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to delete the log.');
        }

        update_option('aiautotool_ex_link_log_all','',null, 'no');
        echo '<div class="updated"><p>Log deleted successfully.</p></div>';
    }
}

    public function aiautotool_linkexauto_menu() {
    

   
    add_submenu_page(
        MENUSUBPARRENT,       // Parent menu slug (the menu slug of the parent menu)
        '<i class="fa-solid fa-link"></i>Auto send Backlink to microsite',           // Page title
        '<i class="fa-solid fa-link"></i>Auto send Backlink to microsite',           // Menu title
        'manage_options',              // Capability required to access the menu
        'aiautotool-linkexauto',  // Menu slug (unique identifier for the submenu)
        array($this, 'aiautotool_linkexauto_page')  // Callback function to display the page content
    );

   
}

public function aiautotool_linkexauto_another_submenu_page() {
    // Code to display content for the "Another Submenu" goes here
}


    public function aiautotool_autoexlink_intervals($schedules) {
        $translate_interval = get_option('aiautolinkex_time_interval', 5);
        $schedules['aiautotool_autoexlink_intervals'] = array(
            'interval' => $translate_interval * 60, 
            'display' => 'aiauto ex link Interval'
        );
        return $schedules;
    }


    public function schedule_link_auto() {
        if (!wp_next_scheduled('aiautotool_linkexauto_task')) {
            wp_schedule_event(time(), 'minute', 'aiautotool_linkexauto_task');
        }
    }


    public function task_linkex() {
        self::write_log_to_file('run task');
        // Kiểm tra nếu aiautotool_ex_link_list rỗng
        if (empty($this->aiautotool_ex_link_list)) {
            // Gọi hàm get_random_post_with_tag để lấy dữ liệu
            $post_data = self::get_random_post_with_tag();

            // Kiểm tra nếu có dữ liệu từ get_random_post_with_tag
            if ($post_data) {
                // Gọi hàm send_post_to_autolinkex với dữ liệu từ get_random_post_with_tag
                $response = self::send_post_to_autolinkex($post_data);

                // Xử lý kết quả nếu cần
                // $response chứa kết quả từ send_post_to_autolinkex
            }
        } else {
            // Lấy item đầu tiên từ aiautotool_ex_link_list
            $link_data = array_shift($this->aiautotool_ex_link_list);

            // Gọi hàm run_link với dữ liệu từ item đầu tiên
            $this->run_link($link_data);
            update_option('aiautotool_ex_link_list', $this->aiautotool_ex_link_list,null, 'no');
        }
    }

    public function run_link($linkobject){

        $related_post_data = self::find_related_post($linkobject['url'], $linkobject['text'],array());
        if ($linkobject['depth'] >= 10) {
           return null;
        }
         if(isset($linkobject['lang'])){
                $lang = $linkobject['lang'];
            }else{
                $lang = get_locale();
                $lang = explode('-',$lang);
                $lang = $lang[0];
            }
        if ($related_post_data) {
            // Lấy thông tin về bài viết liên quan
            $related_url = $related_post_data['url'];
            $related_anchor_text = $related_post_data['text'];
            $idlq = $related_post_data['id'];
            $listtinxl[] = $idlq;

            // Thực hiện việc đi link
            self::perform_linking($linkobject['url'], $linkobject['text'], $idlq, $linkobject['depth']+1);

            $senditerm = array('url' => $related_url, 'text' => $related_anchor_text,'id'=>$idlq,'depth'=>$linkobject['depth']+1);
            
            $response = self::send_post_to_autolinkex($senditerm);

            self::write_log_to_file("update link <a href=\"{$linkobject['url']}\">{$linkobject['text']}</a> to <a href=\"{$related_url}\">{$related_url}</a>");
            self::write_log_to_file(json_encode($response));

            //self::save_to_log($related_url, $related_anchor_text, $depth+1,$listtinxl);
        }else{
            self::write_log_to_file("Không tìm thấy tin liên quan link <a href='{$linkobject['url']}'>{$linkobject['text']}</a>");

            $accessnewpost = get_option('aiautotool_accessnewpost',0); 
            if(isset($accessnewpost)&&$accessnewpost==1){
                    if(class_exists(SinglePost)){
                 $languageCodes = $this->languageCodes;
                $selectedLanguageName = $languageCodes[$lang];
                 $postcreate = new SinglePost($linkobject['text'],$selectedLanguageName);
                 $newpost = $postcreate->getPost();
                 if(isset($newpost['title'])&&isset($newpost['content'])&&$newpost['title']!=''&&$newpost['content']!=''){
                     $post_data = array(
                        'post_title' => $newpost['title'],
                        'post_content' => $newpost['content'], 
                        'post_status' => 'publish',
                        'tags_input' => $anchor_text,
                    );
                        $languageCodes = $this->languageCodes;
                        $post_id = wp_insert_post($post_data);
                        if(array_key_exists($lang, $languageCodes)) {
                            $selectedLanguageName = $languageCodes[$lang];
                            if(in_array('polylang/polylang.php', apply_filters('active_plugins', get_option('active_plugins')))){ 
                               pll_set_post_language($post_id, $lang);
                            }
                            update_post_meta($post_id, 'lang', $selectedLanguageName);
                           
                        }
                        
                        $related_url  = get_permalink($post_id);

                        
                         self::perform_linking($linkobject['url'], $linkobject['text'], $post_id, $linkobject['depth']+1);


                        $senditerm = array('url' => $related_url, 'text' =>  $newpost['title'],'id'=>$post_id,'depth'=>$linkobject['depth']+1);
            
                            $response = self::send_post_to_autolinkex($senditerm);

                            self::write_log_to_file("create post and update link <a href=\"{$linkobject['url']}\">{$linkobject['text']}</a> to <a href=\"{$related_url}\">{$related_url}</a>");
                            self::write_log_to_file(json_encode($response));

                        // $listtinxl[] = $post_id;

                        // self::save_to_log($related_url, $newpost['title'], $depth+1,$listtinxl);

                 }
            }
            }
            

             $post_data = self::get_random_post_with_tag();

            // Kiểm tra nếu có dữ liệu từ get_random_post_with_tag
            if ($post_data) {
                // Gọi hàm send_post_to_autolinkex với dữ liệu từ get_random_post_with_tag
                $response = self::send_post_to_autolinkex($post_data);

                // Xử lý kết quả nếu cần
                // $response chứa kết quả từ send_post_to_autolinkex
            }
        }

        
        
    }
    public function link_auto_task() {
        // Kiểm tra log xem có link và archortext
        $log_data = self::get_log_data();
        $listtinxl = array();
        if (!$log_data) {
             
            // Nếu không có, quét và lấy một bài viết có thẻ tag
            $post_data = self::get_random_post_with_tag();
            $url = $post_data['url'];
            $anchor_text = $post_data['text'];
            $depth=1;
            $listtinxl[] = $post_data['id'];
            $lang = $log_data['lang'];
            // $listtinxl = array();
            self::write_log_to_file('Không có log: lấy random post: '.$url.'với text:'.$anchor_text.' Độ sâu: '.$depth);

        } else {
            // Lấy thông tin từ log
            $url = $log_data['url'];
            $anchor_text = $log_data['anchor_text'];
            $depth = $log_data['depth'];
            if(isset($log_data['lang'])){
                $lang = $log_data['lang'];
            }else{
                $lang = get_locale();
            }
            
            $listtinxl = (array) $log_data['listtinxl'];
            self::write_log_to_file('Chạy link tiếp theo : '.$url.'với text:'.$anchor_text.' Độ sâu: '.$depth);
        }

        // Tìm bài viết liên quan với thẻ tag
        $related_post_data = self::find_related_post($url, $anchor_text,$listtinxl);

        if ($related_post_data) {
            // Lấy thông tin về bài viết liên quan
            $related_url = $related_post_data['url'];
            $related_anchor_text = $related_post_data['tag'];
            $idlq = $related_post_data['id'];
            $listtinxl[] = $idlq;

            // Thực hiện việc đi link
            self::perform_linking($url, $anchor_text, $idlq, $depth + 1);

            self::save_to_log($related_url, $related_anchor_text, $depth+1,$listtinxl);

        }else{
            // not related post - create post by key $related_anchor_text and put link to article and publish
            if(class_exists(SinglePost)){
                 $postcreate = new SinglePost($anchor_text,$lang);
                 $newpost = $postcreate->getPost();
                 if(isset($newpost['title'])&&isset($newpost['content'])&&$newpost['title']!=''&&$newpost['content']!=''){
                     $post_data = array(
                        'post_title' => $newpost['title'],
                        'post_content' => $newpost['content'], 
                        'post_status' => 'publish',
                        'tags_input' => $anchor_text,
                    );

                        $post_id = wp_insert_post($post_data);
                        self::perform_linking($url, $anchor_text, $post_id, $depth + 1);
                        $listtinxl[] = $post_id;

                        self::save_to_log($related_url, $newpost['title'], $depth+1,$listtinxl);

                 }
            }
           
        }

        // Lưu thông tin vào log
        
        
        // Kiểm tra độ sâu, nếu đạt 10 cấp thì dừng lại
        if ($depth >= 10) {
            delete_option('aiautotool_linkauto_log');
            //wp_clear_scheduled_hook('aiautotool_linkauto_task');
        }
    }

    private static function has_link_and_text() {
        // Kiểm tra xem có link và anchor text trong log không
        $log_data = self::get_log_data();
        return !empty($log_data);
    }

    private  function get_random_post_with_tag() {

    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 1,
        'orderby' => 'rand',
        'post__not_in' => $this->aiautotool_ex_link_listidhassend,
        'tax_query' => array(
            array(
                'taxonomy' => 'post_tag',
                'operator' => 'EXISTS', // Check if the post has any tags
            ),
        ),
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        $post = $query->post;
        $url = get_permalink($post->ID);
        $tags = get_the_tags($post->ID);
        $lang = get_locale();
        if (function_exists('pll_get_post_language')) {
            // Polylang is active, get the current language
            $lang = pll_get_post_language($post->ID);
        } 
        
        // Check if the post has any tags assigned
        if (!empty($tags)) {
            $random_tag = $tags[array_rand($tags)];
            $tag = $random_tag->name; // Get the first tag
        } else {
            $tag = $post->post_title; // Handle the case where there are no tags
        }
        $this->aiautotool_ex_link_listidhassend[] = $post->ID;
        update_option('aiautotool_ex_link_listidhassend', $this->aiautotool_ex_link_listidhassend,null, 'no');

        return array('url' => $url, 'text' => $tag,'id'=>$post->ID,'lang'=>$lang,'depth'=>1);
    }

    return null;
}


    private static function get_log_data() {
        // Lấy thông tin từ log
        return get_option('aiautotool_linkauto_log');
    }

    private  function find_related_post($source_url, $source_anchor_text,$listtinxl) {
    // Retrieve the current post's ID based on the source URL
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 1,
        'orderby' => 'rand',
        'post__not_in' => $this->aiautotool_idpost_haslink, // Loại bỏ các bài viết đã được liên kết trước đó
        's' => $source_anchor_text, // Sử dụng chuỗi văn bản để tìm kiếm
    );

    $related_query = new WP_Query($args);

    if ($related_query->have_posts()) {
        $related_post = $related_query->post;
        $related_url = get_permalink($related_post->ID);
        $tags = get_the_tags($related_post->ID);

        if (!empty($tags)) {
            $random_tag = $tags[array_rand($tags)];
            $related_anchor_text = $random_tag->name;
        } else {
            $related_anchor_text = $source_anchor_text; // Xử lý khi không có tags
        }

        return array('id' => $related_post->ID, 'url' => $related_url, 'text' => $related_anchor_text);
    }

    return null; // Không tìm thấy bài viết liên quan
}


    private function perform_linking($source_url, $source_text, $idlq, $depth) {
        // Thực hiện công việc đi link từ source_url đến target_url
        // sử dụng source_text và target_text
        // Thay thế phần này bằng logic của bạn

        $post = get_post($idlq);

        if ($post) {
            // Lấy nội dung của bài viết
            $post_content = $post->post_content;

            // Tạo một liên kết tùy chỉnh
            $custom_link = "<p><a href='$source_url'>$source_text</a></p>";

            // Tìm vị trí để chèn liên kết sau thẻ </p>
            $pos = strpos($post_content, '</p>');

            if ($pos !== false) {
                // Chèn liên kết vào nội dung tại vị trí tìm được
                $post_content = substr_replace($post_content, $custom_link, $pos + 4, 0); // +4 để chèn sau </p>
                
                // Cập nhật nội dung mới cho bài viết
                $post->post_content = $post_content;

                // Cập nhật bài viết
                wp_update_post($post);

                // Thông báo thành công hoặc trả về thông tin về liên kết đã chèn
                self::write_log_to_file("Đã chèn liên kết $source_url với text $source_text vào bài viết ID $idlq (Độ sâu: $depth).");
                return "Đã chèn liên kết tùy chỉnh vào bài viết ID $idlq (Độ sâu: $depth).";
            }
            $this->aiautotool_idpost_haslink[] = $idlq;
            update_option('aiautotool_idpost_haslink',$this->aiautotool_idpost_haslink,null, 'no');
        }
        self::write_log_to_file("Không tìm thấy bài viết với ID $idlq hoặc không có thẻ </p> trong nội dung.");
        return "Không tìm thấy bài viết với ID $idlq hoặc không có thẻ </p> trong nội dung.";

    }

    private  function save_to_log($url, $anchor_text, $depth,$listtinxl) {
    $log_entry = array(
        'url' => $url,
        'anchor_text' => $anchor_text,
        'depth' => $depth,
        'listtinxl'=>$listtinxl
    );

    // Write the log entry to the file
    self::write_log_to_file('Chuẩn bị đi link '.$url.'với text:'.$anchor_text.' Độ sâu: '.$depth);

    // You can also store the log entry in an option if needed
    update_option('aiautotool_linkauto_log', $log_entry,null, 'no');
}


    private function write_log_to_file($log_entry) {
    // Lấy giá trị hiện tại của biến log từ options
    $existing_log = get_option('aiautotool_ex_link_log_all', '');

    // Format the log entry
    $formatted_log_entry = date('Y-m-d H:i:s') . ' - ' . ($log_entry) . PHP_EOL;

    // Nếu đã có giá trị log trước đó, thêm log mới vào cuối
    $new_log = $existing_log . $formatted_log_entry;

    // Cập nhật giá trị của biến log vào options
    update_option('aiautotool_ex_link_log_all', $new_log,null, 'no');
}


 public function enqueue_scripts() {
        wp_enqueue_script('jquery');

      
    }

    // Hàm xử lý yêu cầu AJAX
    public function get_microsite_data() {
        check_ajax_referer('aiautotool_nonce', 'security');
        if (isset($_POST['micrositeUrl'])) {
            $inputUrl = sanitize_text_field($_POST['micrositeUrl']);
            if ($this->is_valid_url($inputUrl)) {
                $api_url = $inputUrl . '/wp-json/autolinkex/v1/loadsite';
                $api_data = file_get_contents($api_url);
                if ($api_data !== false) {
                    $api_data = (Object)json_decode($api_data, true);
                    
                    if ($api_data->status=='success') {
                        // code...
                        $dssitenew = $api_data->items;
                        $apikey = $api_data->apikey;
                        $this->ds_site = get_option('aiautotool_ex_dssite',array()); 
                        $mergedArray = array_merge($dssitenew, $this->ds_site);
                        $uniqueArray = array_unique($mergedArray);
                        update_option('aiautotool_ex_dssite', $uniqueArray,null, 'no');
                        
                        update_option('aiautotool_ex_apikey', $apikey,null, 'no');
                        $this->ds_site = $uniqueArray;
                        $arr = array(
                            'status'=>'success',
                            'items'=> $this->ds_site
                        );
                        echo wp_send_json($arr);

                    }else{
                        echo 'Error: Unable to parse JSON data from the API.';
                    }
                   
                } else {
                    echo 'Error: Unable to fetch data from the API.';
                }
            } else {
                echo 'Error: Invalid URL. Please enter a valid URL starting with "https://".';
            }
        }
        wp_die();
    }

    // Hàm kiểm tra tính hợp lệ của URL
    private function is_valid_url($url) {
        return preg_match('/^https:\/\//i', $url);
    }

    public function update_node($url){
        $domainnew = self::extractDomain($url);
        if ($domainnew) {
            $dssitenew = array($domainnew);
            $this->ds_site = get_option('aiautotool_ex_dssite',array()); 
            $mergedArray = array_merge($dssitenew, $this->ds_site);
            $uniqueArray = array_unique($mergedArray);
            update_option('aiautotool_ex_dssite', $uniqueArray,null, 'no');
        }
        
    }
    public function extractDomain($url) {
        $url = trim($url, " \t\n\r\0\x0B/");
        $parsedUrl = parse_url($url);
        if (isset($parsedUrl['host'])) {
            $domain = $parsedUrl['host'];
            $formattedDomain = "https://" . $domain;
            return $formattedDomain;
        } else {
            return false; 
        }
    }




}



