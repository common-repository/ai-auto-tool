<?php
defined('ABSPATH') or die();

$aiautotool_config_settings = get_option('aiautotool_config_settings');
# Remove jquery-migrate
if(isset($aiautotool_config_settings['tool-playarticle'])){
     function add_play_button($content) {
        if (is_single()) {
            $play_button = '<button class="play-button">Play</button>';
            $content = $play_button . $content.get_script();
        }
        return $content;
    }

    function get_script() {
        ob_start();
        ?>
        <script>
        jQuery(document).ready(function($) {
            $('.play-button').on('click', function() {
                // alert('');
                var postContent = $('#tin-tuc').text();
                console.log(postContent);
                // Xác định ngôn ngữ sử dụng thư viện lang.js hoặc một thư viện tương tự
                // Ví dụ sử dụng lang.js: https://github.com/sprjr/lang.js
                var language = detectLanguage(postContent);

                // Kiểm tra xem trình duyệt có hỗ trợ Web Speech API không
                if ('speechSynthesis' in window) {
                    var utterance = new SpeechSynthesisUtterance(postContent);
                    utterance.lang = language;

                    // Thực hiện đọc âm thanh
                    speechSynthesis.speak(utterance);
                    console.log(postContent);
                } else {
                    console.log('Trình duyệt của bạn không hỗ trợ Web Speech API.');
                }
            });

            // Hàm xác định ngôn ngữ sử dụng thư viện lang.js (hoặc thư viện tương tự)
            function detectLanguage(text) {
                // Viết logic để xác định ngôn ngữ từ nội dung text ở đây
                // Ví dụ: sử dụng lang.js hoặc một thư viện phân loại ngôn ngữ khác
                // Ví dụ lang.js: https://github.com/sprjr/lang.js
                // Trả về mã ngôn ngữ, ví dụ 'en' cho tiếng Anh
                return 'vi';
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }

    add_filter('the_content',  'add_play_button');
    add_filter('get_the_content',  'add_play_button');
}


if(isset($aiautotool_config_settings['speed-off1'])){
    function aiautotool_extra_config_remove_jquery_migrate( $scripts ) {
       if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
            $script = $scripts->registered['jquery'];
       if ( $script->deps ) { 
            $script->deps = array_diff( $script->deps, array( 'jquery-migrate' ) );
     }
     }
     }
    add_action( 'wp_default_scripts', 'aiautotool_extra_config_remove_jquery_migrate' );
}

# tắt Gutenberg CSS o home
if(isset($aiautotool_config_settings['speed-off2'])){
    function aiautotool_extra_config_remove_wp_block_library_css() {
        if ( is_front_page() ) {
            wp_dequeue_style( 'wp-block-library' );
            wp_dequeue_style( 'wp-block-library-theme' );
            wp_dequeue_style( 'wc-blocks-style' );
        }
    }
add_action( 'wp_enqueue_scripts', 'aiautotool_extra_config_remove_wp_block_library_css', 100 );
}
# tắt Classic CSS o home
if(isset($aiautotool_config_settings['speed-off3'])){

    function aiautotool_extra_config_classic_styles_off() {
    	if ( is_front_page()) {
        wp_dequeue_style( 'classic-theme-styles' );
    	}
    }
    add_action( 'wp_enqueue_scripts', 'aiautotool_extra_config_classic_styles_off', 20 );

}
# tắt emoji 
if(isset($aiautotool_config_settings['speed-off4'])){
function aiautotool_extra_config_disable_emojis() {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );	
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );	
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );		
}
add_action( 'init', 'aiautotool_extra_config_disable_emojis' );
}
# Nén code thành 1 dòng duy nhất
if(isset($aiautotool_config_settings['speed-zip1'])){
class WP_HTML_Compression{
    protected $wp_compress_css = true;
    protected $wp_compress_js = true;
    protected $wp_info_comment = true;
    protected $wp_remove_comments = true;
    protected $html;
    public function __construct($html){
        if (!empty($html)) {
            $this->wp_parseHTML($html);
        }
    }
    public function __toString(){
        return $this->html;
    }
    protected function wp_bottomComment($raw, $compressed){
        $raw = strlen($raw);
        $compressed = strlen($compressed);
        $savings = ($raw - $compressed) / $raw * 100;
        $savings = round($savings, 2);
        return '<!-- Compress HTML, down ' . $savings . '%. from ' . $raw . ' bytes, have ' . $compressed . ' bytes. (Compress HTML, CSS, JS Aiautotool) -->';
    }
protected function wp_minifyHTML($html){
    $pattern = '/<(?<script>script).*?<\/script\s*>|<(?<style>style).*?<\/style\s*>|<!--(.*?)-->|<audio\b[^>]*>.*?<\/audio\s*>|<video\b[^>]*>.*?<\/video\s*>|<!(?<comment>--).*?-->|<(?<tag>[\/\w.:-]*)(?:".*?"|\'.*?\'|[^\'">]+)*>|(?<text>((<[^!\/\w.:-])?[^<]*)+)|/si';
    preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);
    $overriding = false;
    $raw_tag = false;
    $html = '';
    foreach ($matches as $token) {
        $tag = (isset($token['tag'])) ? strtolower($token['tag']) : null;
        $content = $token[0];
        $strip = true;
        if (is_null($tag)) {
            if (!empty($token['script'])) {
				// require_once( AIAUTOTOOL_DIR . 'inc/minify-js.php');
                if ($this->wp_compress_js) {
                    $content = Minifier::minify($content);
                }
                $strip = false; // loai bo ham wp_removeWhiteSpace() cho <script>
            } else if (!empty($token['style'])) {
                $strip = $this->wp_compress_css;
            } else if ($content == '<!--wp-html-compression no compression-->') {
                $overriding = !$overriding;
                continue;
            } else if ($this->wp_remove_comments) {
                if (!$overriding && $raw_tag != 'textarea') {
                    $content = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $content);
                }
            }
        } else {
            if ($tag == 'pre' || $tag == 'textarea') {
                $raw_tag = $tag;
            } else if ($tag == '/pre' || $tag == '/textarea') {
                $raw_tag = false;
            } else {
                if ($raw_tag || $overriding) {
                    $strip = false;
                } else {
                    $strip = true;
                    $content = preg_replace('/(\s+)(\w++(?<!\baction|\balt|\bcontent|\bsrc)="")/', '$1', $content);
                    $content = str_replace(' />', '/>', $content);
                }
            }
        }
        if ($strip) {
            $content = $this->wp_removeWhiteSpace($content);
        }
        $html .= $content;
    }
    return $html;
}

    public function wp_parseHTML($html){
        $this->html = $this->wp_minifyHTML($html);
        if ($this->wp_info_comment) {
            $this->html .= "\n" . $this->wp_bottomComment($html, $this->html);
        }
    }
    protected function wp_removeWhiteSpace($str){
        $str = str_replace("\t", ' ', $str);
        $str = str_replace("\n",  '', $str);
        $str = str_replace("\r",  '', $str);
        $str = str_replace(" This function requires postMessage and CORS (if the site is cross domain).", '', $str);
        while (stristr($str, '  ')) {
            $str = str_replace('  ', ' ', $str);
        }
        return $str;
    }
 }
 function wp_html_compression_finish($html){
    return new WP_HTML_Compression($html);
 }
 function wp_wp_html_compression_start(){
	if (!current_user_can('administrator')) {
    ob_start('wp_html_compression_finish');
	}
 }
 add_action('get_header', 'wp_wp_html_compression_start');
}


# gioi han so ban ghi trong csdl 
if(isset($aiautotool_config_settings['speed-data1'])){
function aiautotool_extra_config_limit_post_revisions($num, $post) {
	$aiautotool_config_settings = get_option('aiautotool_config_settings');
	if(!empty($aiautotool_config_settings['speed-data11'])){
    $limit = $aiautotool_config_settings['speed-data11'];
	} else {
	$limit = 3;	
	}
    return $limit;
}
add_filter('wp_revisions_to_keep', 'aiautotool_extra_config_limit_post_revisions', 10, 2);	
}
# gioi han thoi gian luu bai viet tu dong pút
if(isset($aiautotool_config_settings['speed-data2'])){
	if (!defined('AUTOSAVE_INTERVAL')) {
		$secon = !empty($aiautotool_config_settings['speed-data21']) ? $aiautotool_config_settings['speed-data21'] : 1;
		define('AUTOSAVE_INTERVAL', $secon * MINUTE_IN_SECONDS);
	}
} 
# nhan nut xoa ban luu tu dong trong csdl
function aiautotool_extra_config_delete_auto_drafts() {
    global $wpdb;
    $sql = "DELETE FROM {$wpdb->posts} WHERE `post_status` = 'auto-draft'";
    try {
        $wpdb->query($sql);
        return true;
    } catch (Exception $e) {
        return 'Lỗi! ' . $wpdb->last_error;
    }
}
add_action('wp_ajax_delete_auto_drafts', 'aiautotool_extra_config_delete_auto_drafts');
add_action('wp_ajax_nopriv_delete_auto_drafts', 'aiautotool_extra_config_delete_auto_drafts');
# nhan nut xoa het ban ghi tam trong csdl làm sach csdl
function aiautotool_extra_config_delete_post_revisions() {
    global $wpdb;
    $sql = 'DELETE FROM `' . $wpdb->prefix . 'posts` WHERE `post_type` = %s;';
    try {
        $wpdb->query($wpdb->prepare($sql, array('revision')));
		return true;
    } catch (Exception $e) {
        return 'Error! ' . $wpdb->last_error;
    }
}
add_action('wp_ajax_delete_revisions', 'aiautotool_extra_config_delete_post_revisions');
add_action('wp_ajax_nopriv_delete_revisions', 'aiautotool_extra_config_delete_post_revisions');
# xoa tat ca bai trong thung rac
function aiautotool_extra_config_delete_all_trashed_posts() {
    global $wpdb;
    $sql = "DELETE FROM {$wpdb->posts} WHERE `post_status` = 'trash'";
    try {
        $wpdb->query($sql);
        return true;
    } catch (Exception $e) {
        return 'Error! ' . $wpdb->last_error;
    }
}
add_action('wp_ajax_delete_all_trashed_posts', 'aiautotool_extra_config_delete_all_trashed_posts');
add_action('wp_ajax_nopriv_delete_all_trashed_posts', 'aiautotool_extra_config_delete_all_trashed_posts');
# thu vien instant-page.js tai truoc link khi di chuot
if(isset($aiautotool_config_settings['speed-link1'])){
function aiautotool_extra_config_instantpage_scripts() {
  wp_enqueue_script( 'instantpage', AIAUTOTOOL_URI . 'js/instantpage.js', array(), '5.7.0', true );
}
add_action( 'wp_enqueue_scripts', 'aiautotool_extra_config_instantpage_scripts' );
function aiautotool_extra_config_instantpage_loader_tag( $tag, $handle ) {
  if ( 'instantpage' === $handle ) {
    if ( strpos( $tag, 'text/javascript' ) !== false ) {
      $tag = str_replace( 'text/javascript', 'module', $tag );
    }
    else {
      $tag = str_replace( '<script ', "<script type='module' ", $tag );
    }
  }
  return $tag;
}
add_filter( 'script_loader_tag', 'aiautotool_extra_config_instantpage_loader_tag', 10, 2 );
}
# cuon trang muot ma
if(isset($aiautotool_config_settings['speed-link2'])){
function aiautotool_extra_config_smooth_scripts() {
	wp_enqueue_script( 'smooth-scroll', AIAUTOTOOL_URI . 'js/smooth-scroll.min.js', array(), true );
}
add_action( 'wp_enqueue_scripts', 'aiautotool_extra_config_smooth_scripts' );
}


# Tắt API REST
if (isset($aiautotool_config_settings['scuri-off1'])){
add_filter( 'rest_authentication_errors', function( $result ) {
    if ( true === $result || is_wp_error( $result ) ) {
        return $result;
    }
    if ( ! is_user_logged_in() ) {
        return new WP_Error( 'rest_not_logged_in',  __('You are not currently logged in.'), array( 'status' => 401 ) );
    }
    return $result;
});
}
# Tắt  XML RPC
if (isset($aiautotool_config_settings['scuri-off2'])){
add_filter( 'wp_xmlrpc_server_class', '__return_false' );
add_filter('xmlrpc_enabled', '__return_false');
add_filter('pre_update_option_enable_xmlrpc', '__return_false');
add_filter('pre_option_enable_xmlrpc', '__return_zero');
}
# Xóa Wp-Embed
if (isset($aiautotool_config_settings['scuri-off3'])){
function aiautotool_extra_config_deregister_scripts(){
    wp_deregister_script( 'wp-embed' );
}
add_action( 'wp_footer', 'aiautotool_extra_config_deregister_scripts' );
}
# Xóa xpingback header
if (isset($aiautotool_config_settings['scuri-off4'])){
function aiautotool_extra_config_adminify_remove_pingback_head($headers){
    if (isset($headers['X-Pingback'])) {
        unset($headers['X-Pingback']);
    }
    return $headers;
}
add_filter('wp_headers', 'aiautotool_extra_config_adminify_remove_pingback_head');
}
# xóa tiêu đề ko cần thiết
if (isset($aiautotool_config_settings['scuri-off5'])){
function aiautotool_extra_config_remove_header_info() {
    remove_action('wp_head', 'feed_links_extra', 3);
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'start_post_rel_link');
    remove_action('wp_head', 'index_rel_link');
    remove_action('wp_head', 'parent_post_rel_link', 10, 0);
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head',10,0); 
}
add_action('init', 'aiautotool_extra_config_remove_header_info');
}
# xóa nguồn cấp dữ liệu khác
if (isset($aiautotool_config_settings['scuri-off6'])){
function aiautotool_extra_config_disable_feed() {
wp_die('<a href="'. get_bloginfo('url') .'">Home</a>!');
}
add_action('do_feed', 'aiautotool_extra_config_disable_feed', 1);
add_action('do_feed_rdf', 'aiautotool_extra_config_disable_feed', 1);
add_action('do_feed_rss', 'aiautotool_extra_config_disable_feed', 1);
add_action('do_feed_atom', 'aiautotool_extra_config_disable_feed', 1);
add_action('do_feed_rss2_comments', 'aiautotool_extra_config_disable_feed', 1);
add_action('do_feed_atom_comments', 'aiautotool_extra_config_disable_feed', 1);
}

# Xóa ver của css và js
if (isset($aiautotool_config_settings['scuri-verof1'])){
function aiautotool_extra_config_remove_css_js_version( $src ) {
    if( strpos( $src, '?ver=' ) )
    $src = remove_query_arg( 'ver', $src );
    return $src;
    }
add_filter( 'style_loader_src', 'aiautotool_extra_config_remove_css_js_version', 9999 );
add_filter( 'script_loader_src', 'aiautotool_extra_config_remove_css_js_version', 9999 );
}
# xóa ver wordpress
if (isset($aiautotool_config_settings['scuri-verof2'])){
function aiautotool_extra_config_remove_wpversion() {
    return '';
    }
add_filter('the_generator', 'aiautotool_extra_config_remove_wpversion');
}
# bảo mật dữ liệu truy cập
if (isset($aiautotool_config_settings['scuri-sql1'])){
function aiautotool_extra_config_security_check() {
    global $user_ID;
    if ($user_ID) {
        if (!current_user_can('administrator')) {
            if (strlen($_SERVER['REQUEST_URI']) > 255 ||
                stripos($_SERVER['REQUEST_URI'], "eval(") ||
                stripos($_SERVER['REQUEST_URI'], "CONCAT") ||
                stripos($_SERVER['REQUEST_URI'], "UNION+SELECT") ||
                stripos($_SERVER['REQUEST_URI'], "base64")) {
                    @header("HTTP/1.1 414 Request-URI Too Long");
                    @header("Status: 414 Request-URI Too Long");
                    @header("Connection: Close");
                    @exit;
            }
        }
    }
}
add_action('init', 'aiautotool_extra_config_security_check');
}


/*
Tool optime wp

*/
if (isset($aiautotool_config_settings['tool-edit1'])){
add_filter('use_block_editor_for_post', '__return_false');
}
# them chuc nang cho classic
if (isset($aiautotool_config_settings['tool-edit11'])){
function aiautotool_extra_config_mce_editor_buttons( $buttons ) {
    array_unshift( $buttons, 'fontselect' );
    array_unshift( $buttons, 'fontsizeselect' );
    array_push( $buttons,'unlink','separator', 'table' );
    return $buttons;
}
add_filter( 'mce_buttons_2', 'aiautotool_extra_config_mce_editor_buttons' );
function aiautotool_extra_config_add_the_table_plugin( $plugins ) {
    $plugin_url = AIAUTOTOOL_URI . 'js/tinyMCE/table/plugin.min.js';
    $plugins['table'] = $plugin_url;
    return $plugins;
}
add_filter( 'mce_external_plugins', 'aiautotool_extra_config_add_the_table_plugin' );
}

# bật widget classic
if (isset($aiautotool_config_settings['tool-widget1'])){
add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );
add_filter( 'use_widgets_block_editor', '__return_false' );
}
# chuyển link 404 về trang chủ
if (isset($aiautotool_config_settings['tool-mana1'])){
function aiautotool_extra_config_redirect_404_to_home() {
    if (is_404()) {
        wp_redirect(home_url());
        exit();
    }
}
add_action('template_redirect', 'aiautotool_extra_config_redirect_404_to_home');
}
# chăn copy nội dung khoa tat ca cac phim
function aiautotool_extra_config_lockcop_scripts() {
  $aiautotool_config_settings = get_option('aiautotool_config_settings');
  if (!is_admin() && isset($aiautotool_config_settings['tool-mana2'])){
  wp_enqueue_script( 'lockcop', AIAUTOTOOL_URI . 'js/lockcop.js', array(), AIAUTOTOOL_VS);
  wp_enqueue_style( 'lockcop', AIAUTOTOOL_URI . 'js/lockcop.css', array(), AIAUTOTOOL_VS);
  }
}
add_action( 'wp_enqueue_scripts', 'aiautotool_extra_config_lockcop_scripts' );
# tắt những công cụ không cần thiết
function aiautotool_extra_config_remove_appwp_admin(){
   $aiautotool_config_settings = get_option('aiautotool_config_settings');
    // print_r($aiautotool_config_settings);
    if (isset($aiautotool_config_settings['tool-hiden1'])){
        remove_menu_page( 'index.php' );
    }
    if (isset($aiautotool_config_settings['tool-hiden2'])){
        remove_menu_page( 'edit.php' );
    }
    if (isset($aiautotool_config_settings['tool-hiden3'])){
        remove_menu_page( 'edit.php?post_type=page' );
    }
    if (isset($aiautotool_config_settings['tool-hiden4'])){
        remove_menu_page( 'edit-comments.php' );
    }
    if (isset($aiautotool_config_settings['tool-hiden5'])){
        remove_menu_page( 'upload.php' );
    }
    if (isset($aiautotool_config_settings['tool-hiden6'])){
        remove_menu_page( 'themes.php' );
    }
    if (isset($aiautotool_config_settings['tool-hiden7'])){
        remove_menu_page( 'plugins.php' );
    }
    if (isset($aiautotool_config_settings['tool-hiden8'])){
        remove_menu_page( 'users.php' );
    }
    if (isset($aiautotool_config_settings['tool-hiden9'])){
        remove_menu_page( 'tools.php' );
    }
    if (isset($aiautotool_config_settings['tool-hiden10'])){
        remove_menu_page( 'options-general.php' );
    }
    if (isset($aiautotool_config_settings['tool-hifox1'])){
        remove_menu_page( 'ai-auto-tool' );
    }
}
add_action( 'admin_menu', 'aiautotool_extra_config_remove_appwp_admin', 99999);
# tắt tự động cập nhật
if (isset($aiautotool_config_settings['tool-upload1'])){
    add_filter('auto_update_core', '__return_false');
    add_filter('pre_site_transient_update_core', '__return_null');
}
if (isset($aiautotool_config_settings['tool-upload2'])){
    add_filter('auto_update_translation', '__return_false');
    add_filter('auto_update_translation', '__return_false');
}
if (isset($aiautotool_config_settings['tool-upload3'])){
    add_filter('auto_update_plugin', '__return_false');
    add_filter('site_transient_update_plugins', '__return_null');
}
if (isset($aiautotool_config_settings['tool-upload4'])){
    add_filter('auto_update_theme', '__return_false');
    add_filter('site_transient_update_themes', '__return_null');
}

# thêm tiny editor vao description
if ( isset($aiautotool_config_settings['tool-mana3'])){
function aiautotool_extra_config_tiny_description($tag){
    ?>
    <table class="form-table">
        <tr class="form-field">
            <th scope="row" valign="top"><label for="description"></label></th>
            <td>
                <?php
                    $settings = array('wpautop' => true, 'media_buttons' => true, 'quicktags' => true, 'textarea_rows' => '15', 'textarea_name' => 'description' );
                    wp_editor(wp_kses_post($tag->description , ENT_QUOTES, 'UTF-8'), 'aiautotool_extra_config_tiny_description', $settings);
                ?>
                <br />
                <span class="description"></span>
            </td>
        </tr>
    </table>
    <?php
}
add_filter('category_edit_form_fields', 'aiautotool_extra_config_tiny_description');
add_filter('product_cat_edit_form_fields', 'aiautotool_extra_config_tiny_description');
// xoa mac dinh
function aiautotool_extra_config_remove_default_category_description(){
    global $current_screen;
    if ($current_screen->taxonomy == 'category' || $current_screen->taxonomy == 'product_cat') {
    echo '<style>textarea#description{display:none}</style>';
    }
}
add_action('admin_head', 'aiautotool_extra_config_remove_default_category_description');
// xoa loc html khi luu
remove_filter('pre_term_description', 'wp_filter_kses');
remove_filter('term_description', 'wp_kses_data');
}




# css
function aiautotool_show_css() {
    $aiautotool_code_config = get_option('aiautotool_code_settings',array());
    if (!empty($aiautotool_code_config['code1'])){
        echo '<style>' . $aiautotool_code_config['code1'] . '</style>';
    }
    if (!empty($aiautotool_code_config['code11'])){
        echo '<style>@media (max-width: 849px){' . stripslashes($aiautotool_code_config['code11']) . '}</style>';
    }
    if (!empty($aiautotool_code_config['code12'])){
        echo '<style>@media (max-width: 549px){' . stripslashes($aiautotool_code_config['code12']) . '}</style>';
    }
}
add_action('wp_head', 'aiautotool_show_css');
# head
function aiautotool_header_script() {
    $aiautotool_code_config = get_option('aiautotool_code_settings',array());
    echo '<!-- Aiautotool header -->';
    if (!empty($aiautotool_code_config['code2'])){
        echo stripslashes($aiautotool_code_config['code2']);
    }
    echo '<!-- end Aiautotool header -->';
}
add_action('wp_head', 'aiautotool_header_script');
# body
function aiautotool_body_script() {
    $aiautotool_code_config = get_option('aiautotool_code_settings',array());
    echo '<!-- Aiautotool body -->';
    if (!empty($aiautotool_code_config['code3'])) {
        echo stripslashes($aiautotool_code_config['code3']);
    }
    echo '<!-- end Aiautotool body -->';
}
add_action('wp_body_open', 'aiautotool_body_script');
# footer
function aiautotool_footer_script() {
   $aiautotool_code_config = get_option('aiautotool_code_settings',array());
   echo '<!-- Aiautotool Footer -->';
    if (!empty($aiautotool_code_config['code4'])){
        echo stripslashes($aiautotool_code_config['code4']);
    }
    echo '<!-- End Aiautotool Footer -->';
}
add_action('wp_footer', 'aiautotool_footer_script');

function aiautotool_remoteimage( $url ) {

  $aiautotool_mediaremoteimage = new aiautotool_mediaremoteimage( $url );
  $attachment_id         = $aiautotool_mediaremoteimage->download();

  if ( ! $attachment_id ) {
    return false; 
  }

  $attachment_url = wp_get_attachment_url($attachment_id);
  return $attachment_url;
}

function aiautotool_setthumb( $post_id, $url, $attachment_data = array() ) {

  $aiautotool_mediaremoteimage = new aiautotool_mediaremoteimage( $url, $attachment_data );
  $attachment_id         = $aiautotool_mediaremoteimage->download();

  if ( ! $attachment_id ) {
    return false; 
  }

  return set_post_thumbnail( $post_id, $attachment_id );
}


if (isset($aiautotool_config_settings['tool-blockcomment'])){

    function disable_comments_everywhere_disable_comments() {
        return false;
    }

    // Hook to disable comments before they are approved
    add_filter('pre_comment_approved', 'disable_comments_everywhere_disable_comments');

    // Disable comments for all new posts
    function disable_comments_everywhere_disable_comments_on_post_save($data, $postarr) {
        if ($data['post_type'] == 'post') {
            $data['comment_status'] = 'closed';
        }
        return $data;
    }

    // Hook to disable comments for new posts
    add_filter('wp_insert_post_data', 'disable_comments_everywhere_disable_comments_on_post_save', 10, 2);
}


