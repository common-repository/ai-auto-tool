<?php 
defined('ABSPATH') or die();

class AIAutoTool_SubmitIndex extends rendersetting{

   
    public  $active = false;
    public  $active_option_name = 'AIAutoTool_SubmitIndex_active';
    public $aiautotool_config_settings;
    public  $usage_option_name = 'searchindex_AI_usage';
   
    public  $icon = '<i class="fa-regular fa-comments"></i>';
    private $client = null;
    public $notices = [];
    public $limit = AIAUTOTOOL_FREE;
    private $plan_limit_aiautotool ;
    public $name_plan ;
    public $config = array();
    public $notice ;
    public function __construct() {
        $this->name_plan =  __('Index GOOGLE API','ai-auto-tool');
        $this->plan_limit_aiautotool =  'plan_limit_aiautotool_'.$this->active_option_name;
       
        
        $this->notice = new aiautotool_Warning_Notice();
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

    function init(){
        
        
        
        add_action('admin_init', array($this, 'init_settings'));
        add_action( 'wp_ajax_push_link', [ $this, 'ajax_pushurltoapi' ] );
        add_action( 'admin_notices', [ $this, 'display_notices' ], 10, 1 );

        // print_r($this->get_setting( 'json_key' ));
        
        if ( $this->get_setting( 'json_key' ) ) {
            add_action('admin_menu', array($this, 'add_menu'));
                $post_types = $this->get_setting( 'post_type', [] );
                foreach ( $post_types as $key => $post_type ) {
                    if ( empty( $post_type ) ) {
                        continue;
                    }
                    add_action( 'save_post_' . $post_type, [ $this, 'publish_post' ], 10, 2 );
                    // add_filter( 'bulk_actions-edit-' . $post_type, [ $this, 'register_bulk_actions' ] );
                    // add_filter( 'handle_bulk_actions-edit-' . $post_type, [ $this, 'bulk_action_handler' ], 10, 3 );
                }
                add_action( 'wp_trash_post', [ $this, 'delete_post' ], 10, 1 );
            }
           

        
        add_action('wp_ajax_clear_log_submitindex', array($this, 'clear_log_submitindex_callback'));
    }

    public function clear_log_submitindex_callback() {
            // Xóa log bằng cách cập nhật giá trị option thành chuỗi rỗng
            update_option('aiautotool_auto_submitindex_submissions', array(),null, 'no');

            // Trả về một phản hồi để cho biết đã xóa thành công
            wp_send_json_success('Log cleared successfully.');
            wp_die();
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
    public function add_menu() {
        add_submenu_page(
            MENUSUBPARRENT,
            '<i class="fa-solid fa-share-nodes"></i> Submit index ',
            '<i class="fa-solid fa-share-nodes"></i> Submit index',
            'manage_options',
            'submit-index',
            array($this, 'ai_autotool_console')
        );
        
    }

    public function ajax_pushurltoapi() {
        // check_ajax_referer( 'aiautotool-console' );

        // if ( ! current_user_can( apply_filters( 'rank_math/indexing_api/capability', 'manage_options' ) ) ) {
        //  die( '0' );
        // }

        $url_input = $this->get_input_urls();
        $action    = sanitize_title( wp_unslash( $_POST['ajax_action'] ) );
        header( 'Content-type: application/json' );

        $result = $this->send_to_api( $url_input, $action, true );
        wp_send_json( array('success'=>true,'data'=>$result ));
        exit();
    }
    public function ai_autotool_console(){
        ?>

        <div class="wrap aiautotool_container">
            <div class="aiautotool_left">
            <div class="ft-box">
                <div class="ft-menu">
                    <div class="ft-logo"><img src="<?php echo plugins_url('../images/logo.svg', __FILE__); ?>">
                    <br>Instant Indexing</div>
                    <button href="#tab-Instant_Indexing" class="nav-tab sotabt "><i class="fa-solid fa-share-nodes"></i> <?php _e('Manual Submit','ai-auto-tool'); ?></button>
                    
                    <button href="#tab-log-schedule" class="nav-tab sotabt "><i class="fa-regular fa-folder-closed"></i> <?php _e('View log','ai-auto-tool'); ?></button>
                
                </div>
                <div class="ft-main">

                    <div id="tab-Instant_Indexing" class="tab-content sotab-box ftbox">
                         <h2><i class="fa-solid fa-share-nodes"></i> <?php _e('Console Instant Indexing','ai-auto-tool'); ?></h2>
                               
                            <!-- start form -->
                            <form id="push_link_form"  method="post" >
                                <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                                    <?php _e('Multiple Post Url Input','ai-auto-tool'); ?> 
                                    <br>
                                   <?php _e('URLs (one per line, up to 100 for Google and 10,000 for IndexNow):','ai-auto-tool'); ?>
                                   <br>
                                   
                                   
                                </p>
                                <textarea id="url" class="ft-code-textarea" style="height:200px" name="url" rows="5" cols="50"  placeholdertext="<?php _e('Enter post url',''); ?>"></textarea>
                                
                                 <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                                    <?php _e('Action:','ai-auto-tool'); ?> 
                                   
                                </p>
                                <p>
                                    <label class="nut-switch">
                                    <input type="radio" name="action" id="auto_change_title" checked  value="update">
                                    <span class="slider"></span></label>
                                    <label class="ft-label-right"><?php _e('Google: : Publish/update URL','ai-auto-tool'); ?></label>
                                </p>

                                <p>
                                    <label class="nut-switch">
                                    <input type="radio" name="action" id="auto_change_title"  value="remove">
                                    <span class="slider"></span></label>
                                    <label class="ft-label-right"><?php _e('Google: : remove URL','ai-auto-tool'); ?></label>
                                </p>
                                
                                 
                                   

                                
                                  <input type="submit" value="<?php _e('Send to ','ai-auto-tool'); ?>" id="btnsendtoapi" class="ft-submit"><div id="loading-icon" class="loader"></div>
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
                                     jQuery('#tab-Instant_Indexing').show();

                                  </script>
                            <!-- end form -->
                    </div>
                    
                     <div id="tab-log-schedule" class="tab-content sotab-box ftbox" style="display:none;">

                        <h2><?php _e('Logs','ai-auto-tool'); ?></h2>
                         <button id="clear-log-button" class="ft-submit" ><?php _e('Clear Log', 'ai-auto-tool'); ?></button>

                        <p>
                          <?php 
                          $auto_submission_log = get_option( 'aiautotool_auto_submitindex_submissions', [] );

                          $this->displaylog($auto_submission_log);
                           ?>
                        </p>
                           

                    </div>

                    <script>
        jQuery(document).ready(function($) {
    const loadingIcon = document.getElementById("loading-icon");
    const btnSubmitKeyword = document.getElementById("btnsendtoapi");
    const progressBar = $('#progress-bar');

    $('#push_link_form').on('submit', function(e) {
        e.preventDefault();

        var postTitles = $('#url').val().split('\n');
        var ajax_action =   $('input[name="action"]:checked').val();
        var securityToken = aiautotool_js_setting.security;
        var batchSize = 10;

        function sendBatchAjaxRequest(startIndex) {
            var endIndex = Math.min(startIndex + batchSize, postTitles.length);
            var batchData = postTitles.slice(startIndex, endIndex).join('\n');


            var data = {
                action: 'push_link',
                url: batchData,
                ajax_action:ajax_action,
                security: securityToken
            };

            btnSubmitKeyword.style.display = "none";
            loadingIcon.style.display = "inline-block";

            $.post(aiautotool_js_setting.ajax_url, data, function(response) {
                console.log(response);
                if (response.success) {
                    var postList = $('#post-list');
                    var posts = response.data;
                    console.log(response.data);
                    $.each( posts, function(index, val) {
                        var base = val;
                        if ( typeof val.urlNotificationMetadata !== 'undefined' ) {
                            base = val.urlNotificationMetadata;
                        }
                        var d = new Date(base.latestUpdate.notifyTime);
                        var listItem = '<li>' + ''  + '. <a href="' + val.urlNotificationMetadata.url + '">' + val.urlNotificationMetadata.url + '</a></li>';
                        postList.append(listItem);
                    });

                    
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
                    <script>
    jQuery(document).ready(function($) {
        $('#clear-log-button').on('click', function(event) {
            event.preventDefault();
            // Gửi yêu cầu xóa log thông qua AJAX
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'clear_log_submitindex',
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
            </div>
            <div class="aiautotool_right">
                
                <div class="aiautotool_box ft-main ">
            <div class="aiautotool_box_head">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="rgb(0, 174, 239)" class="bi bi-check" viewBox="0 0 16 16">
                <path d="M6.293 12.293a1 1 0 0 1-1.414 0l-5-5a1 1 0 1 1 1.414-1.414L6 9.586l9.293-9.293a1 1 0 0 1 1.414 1.414l-10 10z"></path>
            </svg>
        Ai auto Tool </div>
            <div class="ft-box-">
                <div class="ft-main">
                    <h3><i class="fa-regular fa-database"></i> Limit counter </h3>
                    <?php $limits = $this->get_limits();
$json = $this->get_settings(  );



                     ?>

                     <?php if ( $json['json_key'] ) { ?>
                <p class="" style="line-height: 1.8">

                     <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                        <a href="https://developers.google.com/search/apis/indexing-api/v3/quota-pricing" target="_blank"><strong><?php esc_html_e( 'Google API Remaining Quota:', 'aiautotool' ); ?></strong></a>
                    </p>
                <code><?php esc_html_e( 'Publish Requests Per Day', 'aiautotool' ); ?> = <strong id="aiautotool-limit-publishperday"><?php echo absint( $limits['publishperday'] ); ?></strong> / <?php echo absint( $limits['publishperday_max'] ); ?></code><br>
                <code><?php esc_html_e( 'Requests Per Minute Per Project', 'aiautotool' ); ?> = <strong id="aiautotool-limit-permin"><?php echo absint( $limits['permin'] ); ?></strong> / <?php echo absint( $limits['permin_max'] ); ?></code><br>
                <code><?php esc_html_e( 'Metadata Requests Per Minute Per Project', 'aiautotool' ); ?> = <strong id="aiautotool-limit-metapermin"><?php echo absint( $limits['metapermin'] ); ?></strong> / <?php echo absint( $limits['metapermin_max'] ); ?></code></p>
            <?php } ?>
                   
                       

                    <p>
            
                </div>
                                  
            </div>
        </div>
            </div>
            
        </div><?php
    }
    // Thêm trang cài đặt vào menu quản lý
    public function get_json_key() {
        $setting = get_option('aiautotool_setting_submitindex');
        $keys = explode( "\n",$setting['json_key']);
        return $keys;
    }
    public function get_input_urls() {
        return array_values( array_filter( array_map( 'trim', array_map( 'esc_url_raw', explode( "\n", wp_unslash( $_POST['url'] ) ) ) ) ) );
    }
    // Khởi tạo cài đặt
    public function init_settings() {
        register_setting('aiautotool-settings-group', 'aiautotool_setting_submitindex');
        add_settings_section('aiautotool-section', __('AI Auto Tool Settings', 'aiautotool'), array($this, 'section_callback'), 'aiautotool-settings');
        add_settings_field('submitindex_field', __('Information:', 'aiautotool'), array($this, 'submitindex_field_callback'), 'aiautotool-settings', 'aiautotool-section');
        add_settings_field('post_types_field', __('Post Types List:', 'aiautotool'), array($this, 'post_types_field_callback'), 'aiautotool-settings', 'aiautotool-section');
    }

    // Gọi hàm này khi vào trang cài đặt
    
    public function render_setting() {
        if($this->active!="true"){
            return '';
        }
    $setting = get_option('aiautotool_setting_submitindex');
    ?>

    <div id="tool-submitindex" class="tab-content" style="display:none;">
        <h1><i class="fa-solid fa-shield-halved"></i> <?php _e('Config Google API key Json', 'ai-auto-tool'); ?></h1>
        <div class="wrap">
            <h3><?php _e('Config Google API key Json', 'aiautotool'); ?></h3>
            <form method="post" action="options.php">
                <?php
                settings_fields('aiautotool-settings-group');
                ?>
                <p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php _e('Input json key content.', 'ai-auto-tool'); ?>
                Google JSON Key: paste Service Account JSON key contents in the field. <a target="_blank" href="https://rankmath.com/blog/google-indexing-api/">Example setup</a>
            </p>

                <?php
                if (isset($setting['json_key'])) {
                    $setting['json_key'] = (array)$setting['json_key'];
                    foreach ($setting['json_key'] as $index => $json_key_item) {
                        ?>
                        <div class="json-key-item aiautotool_submitindex_jsonkey" data-index="<?php echo $index;?>">
                            <textarea class="ft-code-textarea" style="height: 100px;" name="aiautotool_setting_submitindex[json_key][]" placeholder="Input json Key"><?php echo esc_textarea($json_key_item); ?></textarea>
                            <button type="button" class="remove-json-key-item delete-post-csdl button-secondary " data-index="<?php echo $index; ?>"><?php _e('Remove', 'ai-auto-tool'); ?></button>
                        </div>
                        <?php
                    }
                }
                ?>

                <button type="button" id="add-json-key-item" class="button ft-submit"><?php _e('Add More', 'ai-auto-tool'); ?></button>

                <p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php _e('Select post type: Submit posts from these post types automatically to the Google Instant Indexing API when a post is published, edited, or deleted.', 'ai-auto-tool'); ?></p>

                <?php
                $post_types = get_post_types();
                $i = 0;
                foreach ($post_types as $post_type) {
                    ?>
                    <label class="nut-switch">
                        <input type="checkbox" name="aiautotool_setting_submitindex[post_type][]" value="<?php echo $post_type; ?>" <?php if (isset($setting['post_type'][$i]) && $post_type == $setting['post_type'][$i]) echo 'checked="checked"'; ?> />
                        <span class="slider"></span></label>
                    <label class="ft-label-right"><?php _e('Active :  ', 'aiautotool');
                        echo $post_type; ?></label>
                    </p>
                    <?php
                    $i++;
                }
                ?>

                <?php submit_button(__('Save Config', 'ai-auto-tool'), 'ft-submit'); ?>
            </form>

            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    <?php
                    $currentIndex = 0;
                     if(isset($setting['json_key'])){
                        $currentIndex = count($setting['json_key']);
                    } ?>
                    var currentIndex = <?php echo sanitize_text_field($currentIndex); ?>;

                    $('#add-json-key-item').on('click', function() {
                        currentIndex++;
                        var newItem = '<div class="json-key-item aiautotool_submitindex_jsonkey" data-index="' + currentIndex + '"> ' +
                            '<textarea class="ft-code-textarea" style="height: 100px;" name="aiautotool_setting_submitindex[json_key][]" placeholder="Input json Key"></textarea>' +
                            '<button type="button" class="remove-json-key-item delete-post-csdl button-secondary " data-index="' + currentIndex + '"><?php _e('Remove', 'ai-auto-tool'); ?></button>' +
                            '</div>';
                        $(this).before(newItem);
                    });

                    $(document).on('click', '.remove-json-key-item', function() {

                        var indexToRemove = $(this).data('index');
                       
                        $('.json-key-item[data-index="' + indexToRemove + '"]').remove();
                    });
                });
            </script>
        </div>
    </div>
    <?php
}


    public function render_tab_setting() {
        if($this->active=="true"){

         echo '<button href="#tool-submitindex" class="nav-tab sotab"><i class="fa-solid fa-shield-halved"></i> '.__('Indexing API','ai-auto-tool').'</button>';
        }
    }

    public function render_feature() {

       $autoToolBox = new AutoToolBox($this->icon.' '.__('Index GOOGLE API','ai-auto-tool'), __('Index GOOGLE API','ai-auto-tool'), "#", $this->active_option_name, $this->active,plugins_url('../images/logo.svg', __FILE__));

        echo $autoToolBox->generateHTML();
    }

    // Callback cho section
    public function section_callback() {
        echo '<p>' . __('Enter information and select Post Types.', 'aiautotool') . '</p>';
    }

    // Callback cho textarea thông tin
    public function submitindex_field_callback() {
        $value = get_option('aiautotool_setting_submitindex');
        echo '<textarea name="aiautotool_setting_submitindex" rows="5" cols="50">' . esc_textarea($value) . '</textarea>';
    }

    // Callback cho danh sách Post Types
    public function post_types_field_callback() {
        $post_types = get_post_types(array('public' => true), 'objects');
        $selected_post_types = get_option('aiautotool_setting_post_types', array());

        foreach ($post_types as $post_type) {
            $checked = in_array($post_type->name, $selected_post_types) ? 'checked="checked"' : '';
            echo '<input type="checkbox" name="aiautotool_setting_post_types[]" value="' . esc_attr($post_type->name) . '" ' . $checked . ' /> ' . esc_html($post_type->label) . '<br>';
        }
    }
    private function get_settings() {
        $settings = get_option( 'aiautotool_setting_submitindex', [] );
        

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

    public function publish_post( $post_id ) {
        $post = get_post( $post_id );

        if ( $post->post_status !== 'publish' ) {
            return;
        }

        if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }

        $send_url = apply_filters( 'aiautotool/submitindex/publish_url', get_permalink( $post ), $post, 'google' );
        // Early exit if filter is set to false.
        if ( ! $send_url ) {
            return;
        }


        $this->send_to_api( $send_url, 'update', false );
        $this->add_notice( __( 'A recently published post has been automatically submitted to the Search Index API.', 'aiautotool' ), 'notice-info', null, true );
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
    public function delete_post( $post_id ) {
        $post_types = $this->get_setting( 'post_type', [] );

        $post = get_post( $post_id );
        if ( ! in_array( $post->post_type, $post_types, true ) ) {
            return;
        }

        // Only submit delete action if post was published.
        if ( $post->post_status !== 'publish' ) {
            return;
        }

        $send_url = apply_filters( 'aiautotool/submitindex/delete_url', get_permalink( $post ), $post );
        // Early exit if filter is set to false.
        if ( ! $send_url ) {
            return;
        }

        $this->send_to_api( $send_url, 'delete', false );
        $this->add_notice( __( 'A deleted post has been automatically submitted to the Search Index API for deletion.', 'aiautotool' ), 'notice-info', null, true );
    }


    public function get_limits() {
        $current_limits = [
            'publishperday' => 0,
            'permin'        => 0,
            'metapermin'    => 0,

            'bing_submitperday' => 0,
        ];

        $settings = $this->get_settings();
        
        
        $count = 200;

        if(isset($settings[ 'json_key' ])){
            $jsonkey = $settings[ 'json_key' ];
            if($jsonkey){
                // $jsonkey = json_decode($jsonkey);
                $count = 200 * count($jsonkey);
            }
        }
        
        
        
        
    

        $limit_publishperday = apply_filters( 'aiautotool/submitindex/limit_publishperday', $count );
        $limit_permin        = apply_filters( 'aiautotool/submitindex/limit_perminute', 600 );
        $limit_metapermin    = apply_filters( 'aiautotool/submitindex/limit_metaperminute', 180 );

        $limit_bingsubmitperday = apply_filters( 'aiautotool/submitindex/limit_bing_submitperday', 10 );

        $requests_log = get_option(
            'aiautotool_submitindex_requests',
            [
                'update'      => [],
                'delete'      => [],
                'getstatus'   => [],
                'bing_submit' => [],
            ]
        );

        $timestamp_1day_ago = strtotime( '-1 day' );
        $timestamp_1min_ago = strtotime( '-1 minute' );

        $publish_1day = 0;
        $all_1min     = 0;
        $meta_1min    = 0;

        foreach ( $requests_log['update'] as $time ) {
            if ( $time > $timestamp_1day_ago ) {
                $publish_1day++;
            }
            if ( $time > $timestamp_1min_ago ) {
                $all_1min++;
            }
        }
        foreach ( $requests_log['delete'] as $time ) {
            if ( $time > $timestamp_1min_ago ) {
                $all_1min++;
            }
        }
        foreach ( $requests_log['getstatus'] as $time ) {
            if ( $time > $timestamp_1min_ago ) {
                $all_1min++;
                $meta_1min++;
            }
        }

        $bing_submit_1day = 0;
        if ( ! isset( $requests_log['bing_submit'] ) ) {
            $requests_log['bing_submit'] = [];
        }

        foreach ( $requests_log['bing_submit'] as $time ) {
            if ( $time > $timestamp_1day_ago ) {
                $bing_submit_1day++;
            }
        }

        $current_limits['publishperday'] = $limit_publishperday - $publish_1day;
        $current_limits['permin']        = $limit_permin - $all_1min;
        $current_limits['metapermin']    = $limit_metapermin - $meta_1min;

        $current_limits['bing_submitperday'] = $limit_bingsubmitperday - $bing_submit_1day;

        $current_limits['publishperday_max'] = $limit_publishperday;
        $current_limits['permin_max']        = $limit_permin;
        $current_limits['metapermin_max']    = $limit_metapermin;

        $current_limits['bing_submitperday_max'] = $limit_bingsubmitperday;

        return $current_limits;
    }

    public function send_to_api( $url_input, $action, $is_manual = true ) {
        $url_input  = (array) $url_input;
        $urls_count = count( $url_input );

        if ( strpos( $action, 'bing' ) === false ) {
            
            $url_input = array_unique( $url_input );

            // if ( count( $url_input ) > 100 ) {
            //  $url_input = array_slice( $url_input, 0, 100 );
            // }

            $auto_submission_log = get_option( 'aiautotool_auto_submitindex_submissions', [] );
            if ( ! $is_manual ) {
                // We keep the auto-submitted URLs in a log to prevent duplicates.
                $logs = array_values( array_reverse( $auto_submission_log ) );
                if ( ! empty( $logs[0] ) && $logs[0]['url'] === $url_input[0] && time() - $logs[0]['time'] < self::THROTTLE_LIMIT ) {
                    return false;
                }
            }
            $keyjson = $this->get_setting( 'json_key' );
            
            // This is NOT a Bing API request, so it's Google.
            include_once AIAUTOTOOL_DIR . 'vendor/autoload.php';
            $this->client = new Google_Client();
            $this->client->setAuthConfig( (array)json_decode($keyjson,true));
            $this->client->setConfig( 'base_path', 'https://indexing.googleapis.com' );
            $this->client->addScope( 'https://www.googleapis.com/auth/indexing' );

            // Batch request.
            $this->client->setUseBatch( true );
            // init google batch and set root URL.
            $service = new Google_Service_Indexing( $this->client );
            $batch   = new Google_Http_Batch( $this->client, false, 'https://indexing.googleapis.com' );

            foreach ( $url_input as $i => $url ) {
                $post_body = new Google_Service_Indexing_UrlNotification();
                if ( $action === 'getstatus' ) {
                    $request_part = $service->urlNotifications->getMetadata( [ 'url' => $url ] ); // phpcs:ignore
                } else {
                    $post_body->setType( $action === 'update' ? 'URL_UPDATED' : 'URL_DELETED' );
                    $post_body->setUrl( $url );
                    $request_part = $service->urlNotifications->publish( $post_body ); // phpcs:ignore
                }
                $batch->add( $request_part, 'url-' . $i );

                // Log auto-submitted URLs.
                
            }

            // if ( ! $is_manual ) {
                
            // }

            $results   = $batch->execute();
            $data      = [];
            $res_count = count( $results );
            foreach ( $results as $id => $response ) {
                // Change "response-url-1" to "url-1".
                $local_id = substr( $id, 9 );
                if ( is_a( $response, 'Google_Service_Exception' ) ) {
                    $data[ $local_id ] = json_decode( $response->getMessage() );
                } else {
                    $data[ $local_id ] = (array) $response->toSimpleObject();
                }
                

                if ( ! $is_manual ) {
                    $auto_submission_log[] = [
                        'url'  => $url,
                        'time' => time(),
                        'manual_submission'=>1,
                        'result'=>$data[ $local_id ]
                    ];
                }else{
                    $auto_submission_log[] = [
                        'url'  => $url,
                        'time' => time(),
                        'manual_submission'=>0,
                        'result'=>$data[ $local_id ]
                    ];
                }

                if ( $res_count === 1 ) {
                    $data = $data[ $local_id ];
                }
            }

            if ( count( $auto_submission_log ) > 100 ) {
                    $auto_submission_log = array_slice( $auto_submission_log, -100, 100, true );
                }
                update_option( 'aiautotool_auto_submitindex_submissions', $auto_submission_log,null, 'no' );


        } else {
            // IndexNow submit URL.

            /**
             * Filter the URL to be submitted to IndexNow.
             * Returning false will prevent the URL from being submitted.
             *
             * @param bool   $is_manual Whether the URL is submitted manually by the user.
             */
            

            // if ( ! $is_manual ) {
            //  $logs = array_values( array_reverse( $this->rmapi->get_log() ) );
            //  if ( ! empty( $logs[0] ) && $logs[0]['url'] === $url_input[0] && time() - $logs[0]['time'] < self::THROTTLE_LIMIT ) {
            //      return false;
            //  }
            // }

            // $request = $this->rmapi->submit( $url_input, $is_manual );
            // if ( $request ) {
            //  $data = [
            //      'success' => true,
            //  ];
            // } else {
            //  $data = [
            //      'error' => [
            //          'code'    => $this->rmapi->get_response_code(),
            //          'message' => $this->rmapi->get_error(),
            //      ],
            //  ];
            // }

            // $action = 'indexnow_submit';
        }

        $this->log_request( $action, $urls_count );

        
            error_log( 'AI Auto Tool Instant Index: ' . $action . ' ' . $url_input[0] . ( count( $url_input ) > 1 ? ' (+)' : '' ) . "\n" . print_r( $data, true ) ); // phpcs:ignore
        

        return $data;
    }

    public function log_request( $type, $number = 1 ) {
        $requests_log = get_option(
            'aiautotool_submitindex_requests',
            [
                'update'      => [],
                'delete'      => [],
                'getstatus'   => [],
                'bing_submit' => [],
            ]
        );

        if ( ! isset( $requests_log[ $type ] ) ) {
            $requests_log[ $type ] = [];
        }

        $add = array_fill( 0, $number, time() );
        $requests_log[ $type ] = array_merge( $requests_log[ $type ], $add );
        if ( count( $requests_log[ $type ] ) > 600 ) {
            $requests_log[ $type ] = array_slice( $requests_log[ $type ], -600, 600, true );
        }
        update_option( 'aiautotool_submitindex_requests', $requests_log,null, 'no' );
    }
    public function displaylog($array) {
        if (!empty($array)) {
            $html = '<table class="aiautotool_table" border="1">';
            
            $html .= '<tr>';
            foreach ($array[0] as $key => $value) {
                $html .= '<th>' . htmlspecialchars($key) . '</th>';
            }
            $html .= '</tr>';

            foreach ($array as $item) {
                $html .= '<tr>';
                foreach ($item as $key => $value) {
                    if ($key == 'time') {
                        $formattedTime = date('Y-m-d H:i:s', $value);
                        $html .= '<td>' . htmlspecialchars($formattedTime) . '</td>';
                    } elseif ($key == 'manual_submission' && $value == 0) {
                        $html .= '<td>Auto</td>';
                    } elseif ($key == 'manual_submission' && $value == 1) {
                        $html .= '<td>Manual</td>';
                    }elseif($key=='result'){
                        // print_r($value);
                        $value = (array)$value;
                        if(isset($value['urlNotificationMetadata']->latestRemove)){
                            $html .= '<td>'.$value['urlNotificationMetadata']->latestRemove->type.'</td>';
                        }else{
                            $html .= '<td>'.$value['urlNotificationMetadata']->latestUpdate->type.'</td>';
                        }
                        
                    }else {
                        $html .= '<td>' . htmlspecialchars($value) . '</td>';
                    }
                }
                $html .= '</tr>';
            }

            $html .= '</table>';
            echo $html;
        } else {
            echo 'No Logs';
        }
    }

    
}
 ?>