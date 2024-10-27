<?php 
defined('ABSPATH') or die();
class Aiautotool_Canonical extends rendersetting {
    public  $active = true;
    public  $active_option_name = 'Aiautotool_Canonical_active';
    public  $usage_option_name = 'Canonical_AI_usage';
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
        
        $this->name_plan =  __('Canonical & delete post','ai-auto-tool');
        $this->plan_limit_aiautotool =  'plan_limit_aiautotool_'.$this->active_option_name;
        
        $this->notice = new aiautotool_Warning_Notice();
        $this->active = get_option($this->active_option_name, true);
        if ($this->active=='true') {
            $this->init();
        }
        add_action('init', array($this,'redirect'), 1);
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
    add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_aiautotool_search_posts', array($this, 'search_posts'));
        add_action('wp_ajax_aiautotool_save_canonical', array($this, 'save_canonical'));

        add_action('wp_ajax_aiautotool_del_canonical', array($this, 'del_canonical'));
        // add_action('init', array($this, 'schedule_cron_job'));


        add_filter('cron_schedules', array($this, 'aiautotool_canonical_intervals'));

        if (!wp_next_scheduled('aiautotool_canonical_event')) {
            wp_schedule_event(time(), 'aiautotool_canonical_intervals', 'aiautotool_canonical_event');
            
        }

        add_action('aiautotool_canonical_event', array($this, 'aiautotool_canonical_cron'));
}
 public function aiautotool_canonical_intervals($schedules) {
        $translate_interval = get_option('aiautotool_canonical_public_time', 5);
        $schedules['aiautotool_canonical_intervals'] = array(
            'interval' => $translate_interval * 60, 
            'display' => 'aiautotool canonical Interval'
        );
        return $schedules;
    }
    public function add_menu() {
       
        add_submenu_page(
            MENUSUBPARRENT,
            '<i class="fa-solid fa-angles-right"></i> Canonical List',
            '<i class="fa-solid fa-angles-right"></i> Canonical List',
            'manage_options',
            'aiautotool_canonical',
            array($this, 'aiautotool_canonical')
        );
        add_submenu_page(
            MENUSUBPARRENT,
            '<i class="fa-solid fa-angles-right"></i> 301 Redirect',
            '<i class="fa-solid fa-angles-right"></i> 301 Redirect',
            'manage_options',
            'aiautotool_301_edirect',
            array($this, 'canonical_301_redirect')
        );
    }
    public function del_canonical(){
        check_ajax_referer('aiautotool_nonce', 'security');
        $ids = sanitize_text_field($_POST['ids']);

        if ($ids<0) {
            wp_send_json_error(array('message' => 'Error id'));
        }
        $canonical_data = get_option('aiautotool_canonical_data');
        if(isset($canonical_data[$ids])){
            unset($canonical_data[$ids]);
            update_option('aiautotool_canonical_data',$canonical_data,null, 'no');
            wp_send_json_success($canonical_data);
        }
        wp_send_json_success(array('msg'=>'No action'));

    }
    public function aiautotool_canonical(){

        $canonical_data = get_option('aiautotool_canonical_data',array());
       

        
        ?>
        <div id="" class="wrap aiautotool_container" style="">
        <div class="aiautotool_left">
            <div class="ft-box">
                <div class="ft-menu">
                     <div class="ft-logo"><img src="<?php echo plugins_url('../images/logo.svg', __FILE__); ?>">
                    <br>Aiautotool Canonical</div>
                </div>
                <div class="ft-main">
        <h1 class="wp-heading-inline">  <img src="<?php echo plugins_url('../images/logo.svg', __FILE__); ?>" width="16px" height="16px"  /> AI Autotool Single Post</h1>
        <div >
            <div  id="aiautotool_post_form" class="wrap aiautotool_container">
                <?php self::render_search_canonical_setting();?>
                <div class="aiautotool_canonical_container">
                    <div class="aiautotool_canonical_box_left">
                        <h3>List Link Canonical</h3>
                        <table class="aiautotool_form_canonical_table">
            <thead>
                <tr>
                    <th class="aiautotool_form_canonical_th">No.</th>
                    <th class="aiautotool_form_canonical_th">Url Target </th>
                    <th class="aiautotool_form_canonical_th">Total</th>
                    <th class="aiautotool_form_canonical_th">Action</th>
                </tr>
            </thead>
            <tbody id="postList">
                <!-- Dữ liệu post sẽ được thêm vào đây -->
                <?php
                if(count($canonical_data)>0){
                    foreach($canonical_data as $key=>$item){
                    $post_ids = $item['post_id'];
                    $canonical_url = $item['canonical_url'];
                    if(isset($item['kq'])){
                        $kq = $item['kq'];
                    }else{
                        $kq = array();
                    }
                        ?>
                        <tr>
                            <td><?php echo $key;?></td>
                            <td><a data-json="<?php echo $key;?>" href="javascript:void(0);" onclick="showRightBox(this)"><?php echo $canonical_url;?></a></td>
                            <td>(<?php echo count($post_ids) ;?>/<?php echo count($kq);?>) post</td>
                            <td><span data-ids=<?php echo $key; ?> type="button" class="aiautotool_btn btn_writer canonical_del "><i class="mce-ico mce-i-dashicon dashicons-del"></i> Del</span></td>

                        </tr>
                        <?php
                   }
                }
               
                 ?>
               
            </tbody>
        </table>
                       
                    </div>
                    <div class="aiautotool_canonical_box_right" id="aiautotool_canonical_box_right">
                        <h3>Info</h3>
                        <table  class="aiautotool_form_canonical_table">
                            <thead>
                                <tr>
                                    <th class="aiautotool_form_canonical_th">ID</th>
                                    <th class="aiautotool_form_canonical_th">Title</th>
                                    <th class="aiautotool_form_canonical_th">Link nanonical</th>
                                    <th class="aiautotool_form_canonical_th">Has remove post</th>
                                    <th class="aiautotool_form_canonical_th">Has 301 redirect</th>
                                    <th class="aiautotool_form_canonical_th">Time Canonical</th>
                                    <th class="aiautotool_form_canonical_th">Time Delete && 301</th>
                                </tr>
                            </thead>
                            <tbody id="tablelistkq">
                                <!-- Dữ liệu post sẽ được thêm vào đây -->
                            </tbody>
                            
                            
                            <!-- Thêm dữ liệu cho bảng bên phải ở đây -->
                        </table>
                       
                    </div>
                </div>

                <script>
                    var canonical_data = [<?php echo json_encode($canonical_data);?>]

                    function showRightBox(element) {
                        var jsonData = element.dataset.json;
                        var rightBox = document.getElementById("aiautotool_canonical_box_right");
                        rightBox.style.display = "block";
                        // var decodedData = JSON.parse(jsonData);
                        var kq = canonical_data[0][jsonData]['kq'];
                        const postList = document.getElementById('tablelistkq');
                        postList.innerHTML = '';
                        jQuery.each(kq, function(index, post) {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${post.post_id}</td>
                            <td><a href="${post.post_url}">${post.post_title}</a></td>
                            <td>${post.post_canonical}</td>
                            <td>${post.del_post}</td>
                            <td>${post.post_301}</td>
                            <td>${post.canonical_time_execute}</td>
                            <td>${post.time_del_post}</td>
                        `;
                        postList.appendChild(row);
                    }); 
                        console.log(kq);
                    }
               
                </script>
                <hr>
                
                <?php //self::render_search_canonical_setting();?>
</div>
</div>
<!-- xxx -->
</div>
</div>
</div>
</div>
<script>
    jQuery(document).ready(function ($) {

        $('.canonical_del').click(function(event){

            var ids = $(this).data('ids');
            
            if(ids>=0){
                var data = {
                    action: 'aiautotool_del_canonical',
                    ids: ids,
                    security:aiautotool_js_setting.security
                };
                $.post('<?php echo admin_url('admin-ajax.php');?>', data, function(response) {
                if (response.success) {
                        var results = response.data;
                      $(this).remove();
                       // const postList = document.getElementById('postList');
                       //  postList.innerHTML = '';
                       //  jQuery.each(results.data, function(index, item) {
                       //      const row = document.createElement('tr');
                       //      row.innerHTML = `
                       //          <td>${index}</td>
                       //          <td>${item.canonical_url}</td>
                       //          <td>(${item.post_id.length}) post</td>
                       //          <td><span data-ids="${index}" type="button" class="aiautotool_btn btn_writer canonical_del"><i class="mce-ico mce-i-dashicon dashicons-del"></i> Del</span></td>
                       //      `;
                       //      postList.appendChild(row);
                       //  }); 

                        

                       

                    } else {
                       
                    }
                });
            }
        })

    });
    
</script>
        <?php
    }
    public function enqueue_scripts() {
        global $post;
       $setting = new rendersetting();
        $aiautotool_canonical_data = get_option('aiautotool_canonical_data');
// Trong mã PHP của bạn, sau khi có $aiautotool_canonical_data
        wp_register_script('kct_cr_scriptx1', plugin_dir_url( __FILE__ ) .'../plugin_button.js', array('jquery'), '1.2'.rand(), true);

        if($post){
            wp_localize_script( 'kct_cr_scriptx1', 'aiautotool_data',
            array( 'ajax_url' => admin_url( 'admin-ajax.php') , 'postID' => $post->ID, 'postTitle' => $post->post_title,'security' => wp_create_nonce('aiautotool_nonce'),'languageCodes'=>$setting->languageCodes,
            'aiautotool_canonical_data'=> $aiautotool_canonical_data ));
        }else{
            wp_localize_script( 'kct_cr_scriptx1', 'aiautotool_data',
            array( 'ajax_url' => admin_url( 'admin-ajax.php') , 'security' => wp_create_nonce('aiautotool_nonce'),'languageCodes'=>$setting->languageCodes,
            'aiautotool_canonical_data'=> $aiautotool_canonical_data ));
        }
    


    
    wp_enqueue_script('kct_cr_scriptx1');


    }

    public function canonical_301_redirect(){
        $canonical_redirects_data = get_option('canonical_redirects_data',array());

        
        if(isset($_POST['addnew'])){
            // print_r($_POST);
            $source = sanitize_text_field($_POST['urlRequest']);
            $target = esc_url($_POST['urlDestination']);
            $canonical_redirects_data[$source] = $target;
            
            update_option('canonical_redirects_data', $canonical_redirects_data,null, 'no');

        }

        if(isset($_POST['update'])){
            // print_r($_POST);
            $source = sanitize_text_field($_POST['urlRequest']);
            $target = esc_url($_POST['urlDestination']);
            $canonical_redirects_data[$source] = $target;
            
            update_option('canonical_redirects_data', $canonical_redirects_data,null, 'no');

        }
        if(isset($_POST['del'])){
            // print_r($_POST);
            $source = sanitize_text_field($_POST['urlRequest']);
            $target = esc_url($_POST['urlDestination']);
            // $canonical_redirects_data[$source] = $target;
            unset($canonical_redirects_data[$source]);
            update_option('canonical_redirects_data', $canonical_redirects_data,null, 'no');

        }
        
        $canonical_redirects_data = get_option('canonical_redirects_data', array());

        ?>

<h1 class="wp-heading-inline">  <img src="<?php echo plugins_url('../images/logo.svg', __FILE__); ?>" width="16px" height="16px"  /> 301 Control Link</h1>
        <div >
            <div   class="wrap aiautotool_container">
                
                <div class="aiautotool_canonical_container">
                    <div class="aiautotool_canonical_box_left">
                        <div class="aiautotool_301_redirect_form">
                            <h2>301 Control Link</h2>
                            <form id="redirectForm" method="POST" action="" class="addnew">
                                <input type="hidden"  name="addnew" value="addnew">
                                <div class="form-row">
                                    <label for="urlRequest">URL Request:</label>
                                    
                                    <label for="urlDestination">URL Destination:</label>
                                    <label for="urlDestination"></label>
                                </div>
                                <div class="form-row">
                                    <input type="text" id="urlRequest" name="urlRequest" required>
                                    <input type="text" id="urlDestination" name="urlDestination" required>
                                    <button type="submit" id="addNewButtonredirect" name="addnewbtn">Add New</button>
                                </div>
                            </form>
                            <?php 
                            foreach($canonical_redirects_data as $source=>$target){
                               
                                ?>
                                <form  method="POST" action="" class="addnew">
                                    <input type="hidden"  name="addnew" value="update">
                                <div class="form-row">
                                    <input type="text" id="urlRequest" name="urlRequest" value="<?php echo esc_html($source);?>" required>
                                    <input type="text" id="urlDestination" name="urlDestination" value="<?php echo esc_html($target);?>" required>
                                    <button type="submit" name="update">Update</button>
                                    <button type="submit" name="del" >Delete</button>
                                </div>
                                </form>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div class="aiautotool_right"></div>
                </div>
            </div>
        </div>
    

<?php
    }
    public function search_posts() {
        // check_ajax_referer('aiautotool_canonical_nonce', 'security');
        check_ajax_referer('aiautotool_nonce', 'security');
        $search_query = sanitize_text_field($_POST['search_query']);

        if (empty($search_query)) {
            wp_send_json_error(array('message' => 'Vui lòng nhập từ khóa tìm kiếm.'));
        }

        $args = array(
            'post_type' => 'post',
            'posts_per_page' => 100,
            's' => $search_query,
        );

        $posts = new WP_Query($args);
        $results = array();

        if ($posts->have_posts()) {
            while ($posts->have_posts()) {
                $posts->the_post();
                $post_id = get_the_ID();
                $post_title = get_the_title();
                $post_url = get_permalink();

                $results[] = array(
                    'id' => $post_id,
                    'title' => $post_title,
                    'url' => $post_url,
                );
            }
            wp_reset_postdata();

            wp_send_json_success($results);
        } else {
            wp_send_json_error(array('message' => 'Không tìm thấy bài viết nào.'));
        }
    }

    public function save_canonical() {
        
        check_ajax_referer('aiautotool_nonce', 'security');

        $selected_posts = isset($_POST['selected_posts']) ? $_POST['selected_posts'] : array();
        $canonical_url = esc_url($_POST['canonical_url']);
         $autoDelete = sanitize_text_field($_POST['autoDelete']);
         $deleteSchedule = sanitize_text_field($_POST['deleteSchedule']);
         
        if (empty($selected_posts) || empty($canonical_url)) {
            wp_send_json_error(array('message' => 'Vui lòng chọn bài viết và điền URL Canonical.'));
        }
        $canonical_data = get_option('aiautotool_canonical_data', array());
        $kq = array();
        foreach($selected_posts as $postid){
            $kqnew = array(
                    'post_id'=>$postid,
                    'post_title'=>get_the_title($postid),
                    'post_url'=>get_permalink($postid),
                    'post_canonical'=>$canonical_url,
                    'canonical_time_execute'=>'',
                    'del_post'=>false,
                    'post_301'=>false,
                    'time_del_post'=>''
                );
                $kq[$postid]=$kqnew;
        }
        $canonical_data[] = array('post_id' => $selected_posts, 'canonical_url' => $canonical_url,'autoDelete'=>$autoDelete,'deleteSchedule'=>$deleteSchedule,'kq'=>$kq);
        update_option('aiautotool_canonical_data', $canonical_data,null, 'no');

        wp_send_json_success(array('message' => 'Save Success.'));
    }

    public function schedule_cron_job() {
        if (!wp_next_scheduled('aiautotool_canonical_cron')) {
            wp_schedule_event(time(), 'hourly', 'aiautotool_canonical_cron');
        }
    }


    public function aiautotool_canonical_cron() {
        // Lấy danh sách bài viết cần được cập nhật canonical URL
        $canonical_data = get_option('aiautotool_canonical_data');

        if (empty($canonical_data)) {
            return;
        }
        $number_post = get_option('aiautotool_canonical_number_post', 3);
        // Lấy số lượng bài viết cần cập nhật (ví dụ: 3 bài viết)

        foreach($canonical_data as $key=>&$item){
            $post_ids = $item['post_id'];
            $canonical_url = $item['canonical_url'];
            $autoDelete = $item['autoDelete'];
            
            $deleteSchedule = $item['deleteSchedule'];

            if(isset($item['kq'])){
                 $kq = $item['kq'];
            }else{
                $kq = array();
            }
           
            $post_ids = array_slice($post_ids, 0, $number_post);
           
            foreach($post_ids as $postid){
                update_post_meta($postid, '_yoast_wpseo_canonical', $canonical_url);
                update_post_meta($postid, '_yoast_wpseo_canonical_time_execute', date('Y-m-d H:i:s'));

                $canonical_time = strtotime(date('Y-m-d H:i:s'));
                $end_time = strtotime("+".$deleteSchedule." days", $canonical_time);
                $end_time_formatted = date('Y-m-d H:i:s', $end_time);
                $kqnew = array(
                    'post_id'=>$postid,
                    'post_title'=>get_the_title($postid),
                    'post_url'=>get_permalink($postid),
                    'post_canonical'=>$canonical_url,
                    'canonical_time_execute'=>date('Y-m-d H:i:s'),
                    'del_post'=>false,
                    'post_301'=>false,
                    'time_del_post'=>$end_time_formatted
                );
                $kq[$postid]=$kqnew;
            }
            $item['kq'] = $kq;
            $post_ids = array_slice($item['post_id'], $number_post);
            $item['post_id'] = $post_ids;
        }
        update_option('aiautotool_canonical_data', $canonical_data,null, 'no');

        self::checkAndDeletePosts();

        
    }

    public function checkAndDeletePosts() {
        $canonical_data = get_option('aiautotool_canonical_data');
        
        if (empty($canonical_data)) {
            return;
        }
        
        foreach ($canonical_data as $key => &$canonical) {
            if (isset($canonical['kq'])) {
                $kq = $canonical['kq'];
                $autoDelete = $canonical['autoDelete'];
                $canonical_url = $canonical['canonical_url'];
                
                if ($autoDelete) {
                    $deleteSchedule = $canonical['deleteSchedule'];
                    $current_time = strtotime(date('Y-m-d H:i:s'));
                    
                    foreach ($kq as &$item) {
                        if($item['canonical_time_execute']!==''){
                            $canonical_time = strtotime($item['canonical_time_execute']);
                            $end_time = strtotime("+".$deleteSchedule." days", $canonical_time);
                            
                            if ($end_time < $current_time && $item['del_post'] === false) {
                                $post_id = $item['post_id'];
                                $post_url = $item['post_url'];
                                wp_delete_post($post_id, true);
                                $item['del_post'] = true;
                                // update 301_canonical_redirects option
                                $canonical_redirects_data = get_option('canonical_redirects_data');
                                $canonical_redirects_data[$post_url] = $canonical_url;
                                update_option('canonical_redirects_data', $canonical_redirects_data,null, 'no');
                                $item['post_301'] = true;
                            }
                        }
                        
                    }
                    $canonical['kq'] = $kq ;
                }else{

                }
            }
        }
        
        update_option('aiautotool_canonical_data', $canonical_data,null, 'no');
    }


    public function redirect() {
        // this is what the user asked for (strip out home portion, case insensitive)

        $redirects = get_option('canonical_redirects_data');
        if (!empty($redirects)) {
            $userrequest = self::str_ireplace(str_replace('https','http',get_option('home')),'',str_replace('https','http',self::get_address()));

            $userrequest = ltrim($userrequest);
            $param = explode('?', $userrequest, 2);
            $userrequest = current($param);

            $wildcard = false;
            $do_redirect = '';


            // compare user request to each 301 stored in the db
            foreach ($redirects as $storedrequest => $destination) {
                // check if we should use regex search
                $a = $storedrequest;

                $storedrequest1 = $storedrequest;
                $storedrequest =  self::str_ireplace(str_replace('https','http',get_option('home')),'',str_replace('https','http',$storedrequest));

                //self::extractPathFromURL($storedrequest);//str_replace(str_replace('https','http',get_option('home')),'',$storedrequest );
                

                

                if(urldecode(trim($userrequest, '/')) == trim($storedrequest,'/')){
                    // simple comparison redirect
                    if($userrequest!='/'||$userrequest!=''){
                        $do_redirect = $destination;
                   }
                    

                }

               
 
                if($userrequest==$storedrequest){
                   if($userrequest!='/'||$userrequest!=''){
                        $do_redirect = $destination;
                   }
                    
                }
                // redirect. the second condition here prevents redirect loops as a result of wildcards.
                if ($do_redirect !== '' && trim($do_redirect,'/') !== trim($userrequest,'/')) {
                    // check if destination needs the domain prepended
                    if (strpos($do_redirect,'/') === 0){
                        $do_redirect = home_url().$do_redirect;
                    }

                    

                    header ('HTTP/1.1 301 Moved Permanently');
                    header ('Location: ' . $do_redirect);
                    exit();
                }
                else { unset($redirects); }
            }
        }
    } // end funcion redirect

    public function extractPathFromURL($url) {
    $parsedUrl = parse_url($url);

    if (isset($parsedUrl['path'])) {
        return $parsedUrl['path'];
    } else {
        // Trường hợp không có path trong URL
        return "/";
    }
}
    public static function str_ireplace($search, $replace, $subject)
    {
        $token = chr(1);
        $haystack = strtolower($subject);
        $needle = strtolower($search);
        while (($pos=strpos($haystack, $needle))!==false) {
            $subject = substr_replace($subject, $token, $pos, strlen($search));
            $haystack = substr_replace($haystack, $token, $pos, strlen($search));
        }
        $subject = str_replace($token, $replace, $subject);
        return $subject;
    }
    public function get_address() {
            if( !( isset( $_SERVER['HTTP_HOST'] ) && isset( $_SERVER['REQUEST_URI'] ) ) ) return;

            // return the full address
            return self::get_protocol().'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }

    public function get_protocol() {
        // Set the base protocol to http
        $protocol = 'http';
        // check for https
        if ( isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) == "on" ) {
            $protocol .= "s";
        }

        return $protocol;
    } // end function get_protocol


    public function render_setting() {
        // Cài đặt cho lớp auto_ex_link ở đây
       
        ?>
        <div id="tab-canonical-setting" class="tab-content" style="display:none;">
            <h2><i class="fa-solid fa-angles-right"></i>Canonical Setting</h2>
            <?php
            if (!current_user_can('manage_options')) {
            return;
        }

        // Save settings
        if (isset($_POST['aiautotool_canonical_save_settings'])) {
           
            

            $translate_interval = intval($_POST['aiautotool_canonical_public_time']); 
            update_option('aiautotool_canonical_public_time', $translate_interval,null, 'no');

             $number_post = intval($_POST['aiautotool_canonical_number_post']); 
            update_option('aiautotool_canonical_number_post', $number_post,null, 'no');

            echo '<div class="updated"><p>Settings saved.</p></div>';
        }


        
        

        $current_interval = get_option('aiautotool_canonical_public_time', 5);


        $number_post = get_option('aiautotool_canonical_number_post', 1);
        ?>
        <div class="wrap">
            
            <form method="post" action="">
                <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>Time Schedule Canonical</p>
                <select id="aiautotool_canonical_public_time" name="aiautotool_canonical_public_time">
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
                 <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>Number Post when Schedule Canonical run </p>
                <select id="aiautotool_canonical_number_post" name="aiautotool_canonical_number_post">
                            <option value="1" <?php selected($number_post, 1); ?>>1</option>
                            <option value="2" <?php selected($number_post, 2); ?>>2</option>
                            <option value="3" <?php selected($number_post, 3); ?>>3</option>
                        </select>

               
                <p class="submit">
                    <input type="submit" name="aiautotool_canonical_save_settings" class="ft-submit" value="Save Settings" />
                </p>
            </form>
            <hr>
            
        </div>
        <?php
             ?>
        </div>
            <?php
    }

    public function render_search_canonical_setting() {
        ?>
        <!-- <div id="" class="wrap aiautotool_container" style="">
        <div class="aiautotool_left">
            <div class="ft-box">
                <div class="ft-menu">
                     <div class="ft-logo"><img src="<?php echo plugins_url('../images/logo.svg', __FILE__); ?>">
                    <br>Aiautotool Canonical</div>
                </div>
                <div class="ft-main"> -->
        <div class="wrap ">
            <h3><i class="fa-solid fa-angles-right"></i>Aiautotool Canonical</h3>
                <!-- start box -->
                <div class="aiautotool_form_canonical_body">
    <div class="aiautotool_form_canonical_tab" id="step1">
        <div class="aiautotool_form_canonical_header">Step 1: Enter Search Keyword</div>
        <h3 class="aiautotool_form_canonical_h2">Enter your search keyword:</h3>
        <input onkeydown="handleKeyPress(event)" class="aiautotool_form_canonical_input" type="text" id="searchKeyword" placeholder="Search keyword">
        <div class="aiautotool_form_canonical_button_center">
            <button class="ft-submit" onclick="validateStep1()">Next</button>
        </div>
        <div class="aiautotool_form_canonical_loading" id="loading1">&#x231B;</div>
    </div>

    <div class="aiautotool_form_canonical_tab" id="step2">
        <div class="aiautotool_form_canonical_header aiautotool_form_canonical_header_step2">Step 2: List of Searched Posts</div>
        <h3 class="aiautotool_form_canonical_h2">List of search results:</h3>
        <div class="aiautotool_form_canonical_checkbox-label">
            
        </div>
        <table class="aiautotool_form_canonical_table">
            <thead>
                <tr>
                    <th class="aiautotool_form_canonical_th"><input type="checkbox" id="check-all">
            <label class="aiautotool_form_canonical_label" for="check-all">Check All</label></th>
                    <th class="aiautotool_form_canonical_th">ID Post</th>
                    <th class="aiautotool_form_canonical_th">Title</th>
                    <th class="aiautotool_form_canonical_th">Action</th>
                </tr>
            </thead>
            <tbody id="postList">
                <div id="loading-icon" class="loader" style="display:block"></div>
                <!-- Dữ liệu post sẽ được thêm vào đây -->
            </tbody>
        </table>
        <div class="aiautotool_form_canonical_button_center">
            <button class="ft-submit" onclick="showStep(1)">Previous</button>
            <button class="ft-submit" onclick="validateStep2()">Next</button>
        </div>
        <div class="aiautotool_form_canonical_loading" id="loading2">&#x231B;</div>
    </div>

    <div class="aiautotool_form_canonical_tab" id="step3">
        <div class="aiautotool_form_canonical_header aiautotool_form_canonical_header_step3">Step 3: Enter Canonical URL and Schedule Post Deletion</div>
        <h3 class="aiautotool_form_canonical_h2">Enter Canonical URL and Schedule Post Deletion:</h3>
        <label class="aiautotool_form_canonical_label" for="canonicalUrl">Canonical URL:</label>
        <input class="aiautotool_form_canonical_input" type="text" id="canonicalUrl" placeholder="Canonical URL">
        <div class="aiautotool_form_canonical_checkbox-label">
            <input class="aiautotool_form_canonical_checkbox" type="checkbox" id="autoDelete">
            <label class="aiautotool_form_canonical_label" for="autoDelete">Auto delete posts</label>
        </div>
        <select class="aiautotool_form_canonical_select" id="deleteSchedule">
            <option value="1">After 1 day</option>
            <option value="7">After 1 week</option>
            <option value="10">After 10 days</option>
            <option value="30">After 1 month</option>
        </select>
        <div class="aiautotool_form_canonical_button_center">
            <button class="ft-submit" onclick="showStepWithLoading(2, 'loading3')">Previous</button>
            <button class="ft-submit" onclick="validateStep3()">Schedule Deletion</button>
        </div>
        <div class="aiautotool_form_canonical_loading" id="loading3">&#x231B;</div>
    </div>

    <script>
       
        function handleKeyPress(event) {
            // Kiểm tra xem phím Enter đã được nhấn và mã phím là 13
            if (event.key === 'Enter' || event.keyCode === 13) {
                // Gọi hàm validateStep1()
                validateStep1();
            }
        }
        var searchData = []; 

        // Mô phỏng dữ liệu post
        for (let i = 1; i <= 10; i++) {
            searchData.push({ id: i, title: `Post ${i}` });
        }

        function showStep(step) {
            // Ẩn tất cả các tab
            document.querySelectorAll('.aiautotool_form_canonical_tab').forEach(tab => tab.style.display = 'none');
            // Hiển thị tab theo step được chọn
            document.getElementById(`step${step}`).style.display = 'block';

            // Ẩn biểu tượng tải
            document.querySelectorAll('.aiautotool_form_canonical_loading').forEach(loading => loading.style.display = 'none');
        }

        function showStepWithLoading(step, loadingId) {
            // Hiển thị biểu tượng tải
            document.getElementById(loadingId).style.display = 'inline-block';
            // Tạm dừng để mô phỏng việc thực hiện code
            setTimeout(function () {
                showStep(step);
                // Ẩn biểu tượng tải
                document.getElementById(loadingId).style.display = 'none';
            }, 1000); // Giả sử 1000ms là thời gian delay
        }

        // Hiển thị danh sách post ở Step 2
        function showPostList(s,loadingId) {
            document.getElementById(loadingId).style.display = 'inline-block';
           
            var data = {
                action: 'aiautotool_search_posts',
                search_query: s,
                security:aiautotool_js_setting.security
            };
            const postList = document.getElementById('postList');
            postList.innerHTML = '';
            document.getElementById(loadingId).style.display = 'inline-block';
            jQuery('#'+loadingId).show();
           console.log(document.getElementById(loadingId).style.display);
            jQuery.post('<?php echo admin_url('admin-ajax.php');?>', data, function(response) {
                if (response.success) {
                    var results = response.data;
                  
                    // document.getElementById(loadingId).style.display = 'none';
                    jQuery('#'+loadingId).hide();
                    jQuery('#loading-icon').hide();
                    jQuery.each(results, function(index, post) {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td><input type="checkbox" class="postCheckbox"  name="postCheckbox[]" value="${post.id}"></td>
                            <td>${post.id}</td>
                            <td><td><a href="${post.url}">${post.title}</a></td>
                        `;
                        postList.appendChild(row);
                    }); 

                    

                   

                } else {
                   const row = document.createElement('tr');
                        row.innerHTML = `
                            <td><input type="checkbox" name="postCheckbox[]" value=""></td>
                            <td>0</td>
                            <td><td>No post for search</td>
                        `;
                        postList.appendChild(row);
                }
            });
            
            
        }

        // Xử lý sự kiện khi nhấn nút "Check All"
        document.getElementById('check-all').addEventListener('change', function () {
            var isChecked = this.checked;
            var postCheckboxes = document.querySelectorAll('.postCheckbox');
            postCheckboxes.forEach(function (checkbox) {
                checkbox.checked = isChecked;
            });
        });
        function validateStep1() {
            const searchKeyword = document.getElementById('searchKeyword').value;
            if (!searchKeyword) {
                alert('Please enter a search keyword.');
                return;
            }else{
                showPostList(searchKeyword,'loading2');
            }
            // Nếu trường hợp kiểm tra thành công, tiếp tục đến Step 2
            showStepWithLoading(2, 'loading1');
        }
        function validateStep2() {
            const checkedPostIds = Array.from(document.querySelectorAll('.postCheckbox:checked')).map(checkbox => checkbox.value);
             if (checkedPostIds.length === 0) {
                    alert('Please select at least one post to Canonical.');
                    // Ẩn biểu tượng tải
                    document.getElementById('loading3').style.display = 'none';
                    return;
                }
            // Nếu trường hợp kiểm tra thành công, tiếp tục đến Step 2
            showStepWithLoading(3, 'loading1');
        }
        function validateStep3() {
            const canonicalUrl = document.getElementById('canonicalUrl').value;
             if (!canonicalUrl) {
                    alert('Please fill input Canonical Url.');
                    // Ẩn biểu tượng tải
                    document.getElementById('loading3').style.display = 'none';
                    return;
                }
            // Nếu trường hợp kiểm tra thành công, tiếp tục đến Step 2
            scheduleDelete() ;
        }
        // Lập lịch xóa post ở Step 3
        function scheduleDelete() {
            // Hiển thị biểu tượng tải
            document.getElementById('loading3').style.display = 'inline-block';

            const canonicalUrl = document.getElementById('canonicalUrl').value;
            const autoDelete = document.getElementById('autoDelete').checked;
            const deleteSchedule = document.getElementById('deleteSchedule').value;

            const checkedPostIds = Array.from(document.querySelectorAll('.postCheckbox:checked')).map(checkbox => checkbox.value);

            console.log(`Canonical URL: ${canonicalUrl}`);
            console.log(`Auto delete posts: ${autoDelete}`);
            console.log(`Delete schedule: ${deleteSchedule} days`);
            console.log(`Checked Post IDs: ${checkedPostIds}`);

            
            var data = {
                action: 'aiautotool_save_canonical',
                selected_posts: checkedPostIds,
                canonical_url: canonicalUrl,
                autoDelete:autoDelete,
                deleteSchedule:deleteSchedule,
                security:aiautotool_js_setting.security

            };

            jQuery.post('<?php echo admin_url('admin-ajax.php');?>', data, function(response) {
                if (response.success) {
                    
                    location.reload();
                } else {
                    
                }
            });
        }

        showStep(1);
        
    </script>
</div>
                <!-- end box -->
            </div>
           <!--  </div>
            </div>
        </div>
        </div> -->
        <?php
    }
     public function render_tab_setting() {
        if ($this->active=='true') {
        // Cài đặt cho lớp auto_ex_link ở đây
        echo '<button href="#tab-canonical-setting" class="nav-tab  sotab"><i class="fa-solid fa-angles-right"></i> Canonical</button>';
        }
    }
     public function render_feature(){
        $autoToolBox = new AutoToolBox($this->icon.' '.$this->name_plan, "Canonical multiple posts intelligently, schedule deletion, and 301 old posts after canonicalizing for a certain period, supporting the retention of old link values ", "https://doc.aiautotool.com/", $this->active_option_name, $this->active,plugins_url('../images/logo.svg', __FILE__));

        echo $autoToolBox->generateHTML();
        
    }
}
