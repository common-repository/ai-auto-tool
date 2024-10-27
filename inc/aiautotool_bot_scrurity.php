<?php


class AIAutoToolSecurityBot  extends rendersetting{

    public  $active = false;
    public  $active_option_name = 'AIautotool_robotstxt_active';
    public $aiautotool_config_settings;
    public  $usage_option_name = 'AIautotool_robotstxt_AI_usage';
   
    public  $icon = '<i class="fa-solid fa-text-height"></i>';
    private $client = null;
    public $notices = [];
    public $limit = AIAUTOTOOL_FREE;
    private $plan_limit_aiautotool ;
    public $name_plan ;
    public $config = array();
    public $notice ;

    public $robotstext = '';
    private $aiautotool_user_agents_regex;
    public function __construct() {

        $this->name_plan =  __('Robots & Ads txt','ai-auto-tool');
        $this->plan_limit_aiautotool =  'plan_limit_aiautotool_'.$this->active_option_name;
        $this->robotstext = 'User-agent: GPTBot
Disallow: /

User-agent: CCbot
Disallow: /

User-agent: anthropic-ai
Disallow: /

User-agent: Claude-Web
Disallow: /

User-agent: Google-Extended
Disallow: /';
        
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

    public function init(){
        $this->aiautotool_user_agents_regex = $this->aiautotool_get_user_agents_regex();
        add_action('admin_init', array($this, 'page_init'));
        remove_all_filters('robots_txt');
       add_filter( 'robots_txt', array($this,'aiautotool_robots_ads_robots_txt'), 5, 2 );
       add_action( 'init',array($this, 'aiautotool_robots_ads_ads_txt') );
        add_action('init', array($this, 'aiautotool_block_user_agents'));
        add_action('login_init', array($this, 'aiautotool_login_auth_login_authentication'));
        add_action('wp_ajax_update_remote_txt', array($this, 'aiautotool_remote_content'));
        add_action('wp_ajax_nopriv_update_remote_txt', array($this, 'aiautotool_remote_content'));
                // Gọi hàm khi WordPress được khởi động
        add_action('init', array($this, 'add_rewrite_rules_to_htaccess'));
        add_action('admin_post_save_robots_content', array($this, 'save_robots_content'));
   
    }

public function save_robots_content() {
       if (!current_user_can('manage_options')) {
            return;
        }

        // Kiểm tra nonce để đảm bảo tính xác thực của dữ liệu
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'save_robots_content_nonce')) {
            return;
        }

        // Lấy dữ liệu từ form
        $options = isset($_POST['ai_auto_tool_security_bot_options']) ? $_POST['ai_auto_tool_security_bot_options'] : array();
        $robots_content = isset($options['robots_txt_content']) ? $options['robots_txt_content'] : '';
        $ads_content = isset($options['ads_txt_content']) ? $options['ads_txt_content'] : '';

        // Lưu nội dung robots.txt vào biến options
        $options['robots_txt_content'] = $robots_content;

        // Lưu nội dung ads.txt vào biến options
        $options['ads_txt_content'] = $ads_content;

        // Lưu nội dung robots.txt vào tệp
        $robots_file = ABSPATH . 'robots.txt';
        file_put_contents($robots_file, $robots_content);

        // Lưu nội dung ads.txt vào tệp
        $ads_file = ABSPATH . 'ads.txt';
        file_put_contents($ads_file, $ads_content);

        // Lưu biến options
        update_option('ai_auto_tool_security_bot_options', $options);
        
        wp_redirect(admin_url('admin.php?page=ai_auto_tool'));
        exit;
    }

    // Hàm để thêm quy tắc rewrite vào file .htaccess
function add_rewrite_rules_to_htaccess() {
    $htaccess_file = ABSPATH . '.htaccess'; // Đường dẫn đến file .htaccess

    // Kiểm tra nếu file .htaccess có tồn tại và có thể ghi
    if (file_exists($htaccess_file) && is_writable($htaccess_file)) {
        // Tạo nội dung quy tắc rewrite
        $rewrite_rules = "
RewriteEngine On
RewriteRule ^/ads.txt$ /index.php?ads=1 [L]
RewriteRule ^/robots.txt$ /index.php?robots=1 [L]
";

        // Thêm quy tắc vào cuối file .htaccess
        file_put_contents($htaccess_file, $rewrite_rules, FILE_APPEND);
    }
}

    public static function get_robots_data() {
        global $wp_filesystem;
        $public        = absint( get_option( 'blog_public' ) );

        $default  = '# This file is automatically added by Ai auto tool plugin to help a website index better';
        
        $default .= "User-Agent: *\n";
        if ( 0 === $public ) {
            $default .= "Disallow: /\n";
        } else {
            $default .= "Disallow: /wp-admin/\n";
            $default .= "Allow: /wp-admin/admin-ajax.php\n";
        }
        $default = apply_filters( 'robots_txt', $default, $public );

        if ( empty( $wp_filesystem )) {
            return [
                'exists'   => false,
                'default'  => $default,
                'public'   => $public,
                'writable' => false,
            ];
        }

        if ( $wp_filesystem->exists( ABSPATH . 'robots.txt' ) ) {
            return [
                'exists'  => true,
                'default' => $wp_filesystem->get_contents( ABSPATH . 'robots.txt' ),
                'public'  => $public,
            ];
        }

        return [
            'exists'  => false,
            'default' => $default,
            'public'  => $public,
        ];
    }


    public function aiautotool_remote_content(){
        check_ajax_referer('aiautotool_nonce', 'security');

        $url = esc_url_raw($_POST['url']);
        
        echo $this->aiautotool_remote_txt_content($url);
        wp_die();
    }
    public function aiautotool_remote_txt_content($url) {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            return false;
        }
        $content = wp_remote_retrieve_body(wp_remote_get($url));
        
        if (empty($content)) {
            return false;
        }
        
        return $content;
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
                                sprintf(
                                    __( 'AI Auto Tool Limit Quota. Please <a class="aiautotool_btn_upgradepro aiautotool_red" href="%s" target="_blank"><i class="fa-solid fa-unlock-keyhole"></i> Upgrade Pro</a>', 'ai-auto-tool' ),
                                    aiautotool_premium()->get_upgrade_url()
                                ),
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
            

            $expiration = strtotime('+1 month', strtotime($current_date));
            $expiration = date('Y-m-d', $expiration);
            update_option($this->plan_limit_aiautotool, array( 'start_date' => $current_date,'expiration'=>$expiration));
            update_option($this->usage_option_name, 0,null, 'no');
        }

        
    }
   public function render_tab_setting() {
        
        if ($this->active=='true') {
        echo '<button href="#tab-robots_txt-setting" class="nav-tab sotab"> '. wp_kses_post($this->icon).' '.__('Robots & block','ai-auto-tool').'</button>';
        }
    }
    public function render_setting() {
        $options = get_option('ai_auto_tool_security_bot_options');

        $robots = $this->get_robots_data();


        // Check if $options is an array
        if (!is_array($options)) {
            $options = array(); // Initialize $options as an array if it's not already
        }

        if($robots['exists']){
            $options['robots_txt_content'] = $robots['default'];
        }else{
             if (!isset($options['robots_txt_content'])) {
                $options['robots_txt_content'] = $this->robotstext;
            }
        }

       

        if (empty($options['robots_txt_content'])) {
            $options['robots_txt_content'] = $this->robotstext;
        }

        if (!isset($options['ads_txt_content'])) {
            $options['ads_txt_content'] = '';
        }
       
       $username = get_option('aiautotool_login_auth_login_username');
       $password = get_option('aiautotool_login_auth_login_password');
        ?>
        <div id="tab-robots_txt-setting" class="tab-content" style="display:none;">
            <div class="aiautotool_tab_v2_wrapper">
                  <div class="icon"><i id="left" class="fa-solid fa-angle-left"></i></div>
                  <ul class="tabs-box">
                    <li class="tab active" data-id="#robotsx">Robots & ads txt</li>
                    <li class="tab " data-id="#blockspam">Block Bot spam</li>
                    <li class="tab" data-id="#loginauth">Login Security</li>
                  </ul>
                  <div class="icon"><i id="right" class="fa-solid fa-angle-right"></i></div>
                </div>
                
            <div id="robotsx" class="navtab">
            <h2><?php echo wp_kses_post($this->icon); ?> <?php _e('Robots & ads txt','ai-auto-tool'); ?></h2>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
             <input type="hidden" name="action" value="save_robots_content">
               <?php wp_nonce_field('save_robots_content_nonce'); ?>
                <?php
                //settings_fields('ai_auto_tool_security_bot_group');
                // do_settings_sections('ai_auto_tool_security_bot_page');
                ?>
                <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                   <?php _e('Robots.txt content','ai-auto-tool'); ?>: <a href="/robots.txt?v=<?php echo rand();?>" target="_blank">View Robots.txt</a>. <br> <span>If system for website using nginx, please config rewrite : <code>rewrite ^/robots.txt$ /index.php?robots=1 last;</code></span>
                    </p>
                    <span class=" aiautotool_btn_v1 aiautotool_btn_v16 btn_getdatatxt " id="get_robotsai" data-url="https://aiautotool.com/plugin/txt/robotsai.txt" data-editor="robots_txt_content" ><span class="icon"><i class="fa-solid fa-lightbulb"></i>  </span><span>Disallow Bot AI</span></span>

                    <!-- <span class=" aiautotool_btn_v1 aiautotool_btn_v16 " id="get_robotsai" ><span class="icon"><i class="fa-solid fa-lightbulb"></i>  </span><span>Disallow folder wp </span></span>   -->                  <?php
                 echo '<textarea class="ft-code-textarea" id="robots_txt_content" name="ai_auto_tool_security_bot_options[robots_txt_content]" rows="5" cols="50">' . esc_textarea($options['robots_txt_content']) . '</textarea>';
                  ?>
                <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                   <?php _e('ads.txt content','ai-auto-tool'); ?>: <a href="/ads.txt?v=<?php echo rand();?>" target="_blank">View ads.txt</a><br>
                   <span>If system for website using nginx, please config rewrite : <code>rewrite ^/ads.txt$ /index.php?ads=1 last;</code></span>
                    </p>
                    <?php
                 echo '<textarea id="ads_txt_content" class="ft-code-textarea" name="ai_auto_tool_security_bot_options[ads_txt_content]" rows="5" cols="50">' . esc_textarea($options['ads_txt_content']) . '</textarea>';
                
                ?>
                 <input type="submit" value="Save all" class="ft-submit">
                 <?php //submit_button(__( 'Save all', 'ai-auto-tool' ),'ft-submit'); ?>
            </form>
            </div>
            <div id="blockspam" class="navtab">
            <h2><?php echo wp_kses_post($this->icon); ?> <?php _e('Block spam','ai-auto-tool'); ?></h2>
             <form method="post" action="options.php">
                <?php settings_fields('aiautotool_user_agents_settings_group'); ?>
                <?php do_settings_sections('aiautotool_user_agents_settings_group'); ?>

                        <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                   <?php _e('Enter the list of user agents (one user agent per line):','ai-auto-tool'); ?>
                    </p>
                    <span class=" aiautotool_btn_v1 aiautotool_btn_v16 btn_getdatatxt "  data-url="https://aiautotool.com/plugin/txt/useagentblacklist.txt" data-editor="aiautotool_user_agents_list" ><span class="icon"><i class="fa-solid fa-lightbulb"></i>  </span><span>List Bot user agen </span></span>
                      <textarea class="ft-code-textarea" id="aiautotool_user_agents_list" name="aiautotool_user_agents_list" rows="10" cols="50"><?php echo esc_attr(get_option('aiautotool_user_agents_list')); ?></textarea>
                    
                <?php submit_button(__( 'Save all', 'ai-auto-tool' ),'ft-submit'); ?>
            </form>
            </div>
            <div id="loginauth" class="navtab">
            <h2><?php echo wp_kses_post($this->icon); ?> <?php _e('Auth Login wp','ai-auto-tool'); ?></h2>
             <form method="post" action="options.php">
                <?php  settings_fields('wp_login_security_settings');?>
                        <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                   <?php _e('UserName','ai-auto-tool'); ?>
                    </p>
                    <?php 
                      echo "<input type='text' id='aiautotool_login_auth_login_username' name='aiautotool_login_auth_login_username' value='$username' />";
                      ?>
                       <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                   <?php _e('Pass','ai-auto-tool'); ?>
                    </p>
                      <?php
                      echo "<input type='password' id='aiautotool_login_auth_login_password' name='aiautotool_login_auth_login_password' value='$password' />";
                     ?>
                <?php submit_button(__( 'Save all', 'ai-auto-tool' ),'ft-submit'); ?>
            </form>
            </div>
        </div>
        <script type="text/javascript">const tabsBox = document.querySelector(".tabs-box"),
allTabs = tabsBox.querySelectorAll(".tab"),
alldivtab = document.querySelector(".navtab"),
arrowIcons = document.querySelectorAll(".icon i");

let isDragging = false;
jQuery('.navtab').hide();
jQuery('#robotsx').show();
const handleIcons = (scrollVal) => {
    let maxScrollableWidth = tabsBox.scrollWidth - tabsBox.clientWidth;
    arrowIcons[0].parentElement.style.display = scrollVal <= 0 ? "none" : "flex";
    arrowIcons[1].parentElement.style.display = maxScrollableWidth - scrollVal <= 1 ? "none" : "flex";
}

arrowIcons.forEach(icon => {
    icon.addEventListener("click", () => {
        // if clicked icon is left, reduce 350 from tabsBox scrollLeft else add
        let scrollWidth = tabsBox.scrollLeft += icon.id === "left" ? -340 : 340;
        handleIcons(scrollWidth);
    });
});

allTabs.forEach(tab => {
    tab.addEventListener("click", () => {
        tabsBox.querySelector(".active").classList.remove("active");
        tab.classList.add("active");
        const dataId = tab.getAttribute('data-id');
        // jQuery('.navtab').hide();
        // jQuery(dataId).show();
        jQuery('.navtab').fadeOut(300, function() {
            // Sau khi ẩn xong tất cả, hiển thị navtab tương ứng với dataId
            jQuery(dataId).fadeIn(300);
            // updateCodeMirrorSize();
            
        });
       
    });
});




const dragging = (e) => {
    if(!isDragging) return;
    tabsBox.classList.add("dragging");
    tabsBox.scrollLeft -= e.movementX;
    handleIcons(tabsBox.scrollLeft)
}

const dragStop = () => {
    isDragging = false;
    tabsBox.classList.remove("dragging");
}

tabsBox.addEventListener("mousedown", () => isDragging = true);
tabsBox.addEventListener("mousemove", dragging);
document.addEventListener("mouseup", dragStop);

jQuery(document).ready(function($) {
    $('.btn_getdatatxt').on('click', function() {
        var button = $(this);
        var url = button.data('url');
        var editorId = button.data('editor');
       
        showLoading();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'html',
            data: {
                action: 'update_remote_txt',
                security: aiautotool_js_setting.security,
                url: url
            },
            beforeSend: function() {},
            success: function(response) {
                hideLoading();
                if (response) {
                   $('#' + editorId).html($('#' + editorId).html() + "\n" + response);
                  

                }
            },
            error: function(xhr, textStatus, errorThrown) {
                hideLoading();
                console.error('Error: ' + textStatus, errorThrown);
            }
        });
    });
});


</script>
        <?php
    }
    public function render_feature(){
        $autoToolBox = new AutoToolBox(wp_kses_post($this->icon).' '.__('Robots&ads txt','ai-auto-tool'), "Change robots txt and ads txt.", "#", $this->active_option_name, $this->active,plugins_url('../images/logo.svg', __FILE__));

        echo $autoToolBox->generateHTML();
        
        
    }
    public function page_init() {

         register_setting('aiautotool_user_agents_settings_group', 'aiautotool_user_agents_list');

          register_setting('wp_login_security_settings', 'aiautotool_login_auth_login_username');
        register_setting('wp_login_security_settings', 'aiautotool_login_auth_login_password');

        register_setting(
            'ai_auto_tool_security_bot_group',
            'ai_auto_tool_security_bot_options',
            array($this, 'sanitize')
        );

        add_settings_section(
            'setting_section_id',
            'Settings',
            array($this, 'print_section_info'),
            'ai_auto_tool_security_bot_page'
        );

        add_settings_field(
            'robots_txt_content',
            '<p>Robots.txt Content</p>',
            array($this, 'robots_txt_content_callback'),
            'ai_auto_tool_security_bot_page',
            'setting_section_id'
        );

        add_settings_field(
            'ads_txt_content',
            '<p>Ads.txt Content</p>',
            array($this, 'ads_txt_content_callback'),
            'ai_auto_tool_security_bot_page',
            'setting_section_id'
        );
    }

    public function sanitize($input) {
        $sanitized_input = array();
        if (isset($input['robots_txt_content'])) {
            $sanitized_input['robots_txt_content'] = wp_kses_post($input['robots_txt_content']);
        }
        if (isset($input['ads_txt_content'])) {
            $sanitized_input['ads_txt_content'] = wp_kses_post($input['ads_txt_content']);
        }
        return $sanitized_input;
    }

    public function print_section_info() {
        // echo 'Enter the content for Robots.txt and Ads.txt below:';
    }

    public function robots_txt_content_callback() {
       
    }

    public function ads_txt_content_callback() {
        
    }

    public function aiautotool_robots_ads_robots_txt() {
        if (!is_admin()) {
            $options = get_option('ai_auto_tool_security_bot_options');
            $robots_content = isset($options['robots_txt_content']) ? $options['robots_txt_content'] : '';
            
            header('Content-Type: text/plain; charset=utf-8');
            echo $robots_content;
            exit;
        }
    }

    public function aiautotool_robots_ads_ads_txt() {
        $request = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : false;
    if ( '/ads.txt' === $request || '/ads.txt?' === substr( $request, 0, 9 ) ) {
            header( 'X-Ads-Txt-Generator: ai-auto-tool' );
            $options = get_option('ai_auto_tool_security_bot_options');
            $ads_content = isset($options['ads_txt_content']) ? $options['ads_txt_content'] : '';

            header('Content-Type: text/plain; charset=utf-8');
            echo $ads_content;
            exit;
        }
    }


    public function aiautotool_get_user_agents_regex() {
        $user_agents = get_option('aiautotool_user_agents_list', ''); 
        if (!empty($user_agents)) {
            $user_agents_array = explode("\n", $user_agents);
            
            $user_agents_array = array_map('trim', $user_agents_array);

            $user_agents_array = array_filter($user_agents_array); 

            $user_agents_regex = '/(' . implode('|', array_map('preg_quote', $user_agents_array)) . ')/i';
           
            return $user_agents_regex;
        } else {
            return '/^$/';
        }
    }


    public function aiautotool_block_user_agents() {

        if (!is_admin()) {
            $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
           
            if(!empty($this->aiautotool_user_agents_regex)){
                if (preg_match($this->aiautotool_user_agents_regex, $user_agent)) {
                    header("HTTP/1.1 403 Forbidden");
                    exit;
                }
            }
        }
    }

     public function aiautotool_login_auth_login_authentication() {
        $username = get_option('aiautotool_login_auth_login_username');
        $password = get_option('aiautotool_login_auth_login_password');
        if (!empty($username) && !empty($password)) {
            if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
                $_SERVER['PHP_AUTH_USER'] != $username || $_SERVER['PHP_AUTH_PW'] != $password) {
                header("WWW-Authenticate: Basic realm=\"Login Security\"");
                header("HTTP/1.0 401 Unauthorized");

                echo <<<EOB
                <html><body>
                <h1>Rejected!</h1>
                <big>Wrong Username or Password!</big>
                </body></html>
    EOB;
                exit;
            }
        }
    }


}
