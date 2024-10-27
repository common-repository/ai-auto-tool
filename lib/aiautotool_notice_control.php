<?php 
defined('ABSPATH') or die();
class aiautotool_Warning_Notice {

    private $icon_url;
    private $title;
    private $content;
    private $button_text;
    private $button_url;

    private $dismissed_transient_key;
    private $notices = [];

    public function __construct($args = array()) {
        add_action('admin_notices', [$this, 'display_notices'], 10, 1);
        $this->icon_url    = isset($args['icon_url']) ? $args['icon_url'] : '';
        $this->title       = isset($args['title']) ? $args['title'] : '';
        $this->content     = isset($args['content']) ? $args['content'] : '';
        $this->button_text = isset($args['button_text']) ? $args['button_text'] : '';
        $this->button_url  = isset($args['button_url']) ? $args['button_url'] : '';
        $this->dismissed_transient_key = 'aiautotool_notice_dismissed';
        // Gọi hàm để hiển thị thông báo tùy chỉnh
        
        $dismissed = get_transient($this->dismissed_transient_key);

        // Add action only if the notice is not dismissed
        if (!$dismissed) {
            // add_action('admin_notices', array($this, 'create_custom_notice'));
        }

         add_action('wp_ajax_dismiss_aiautotool_notice', array($this, 'aiautotool_dismiss_notice_callback'));
    }
   
    public function add_notice($message, $class = '', $show_on = null, $persist = false, $id = '') {
        $notice = [
            'message' => $message,
            'class'   => $class . ' is-dismissible',
            'show_on' => $show_on,
        ];

        if (!$id) {
            $id = md5(serialize($notice));
        }

        if ($persist) {
            $notices        = get_option('aiautotool_notices', []);
            $notices[$id]   = $notice;
            update_option('aiautotool_notices', $notices, null, 'no');
            return;
        }

        $this->notices[$id] = $notice;
    }

    public function display_notices() {
        $screen        = get_current_screen();
        $stored        = get_option( 'aiautotool_notices', [] );
        $this->notices = array_merge( $stored, $this->notices );
        delete_option( 'aiautotool_notices' );
        foreach ( $this->notices as $notice ) {
            if ( ! empty( $notice['show_on'] ) && is_array( $notice['show_on'] ) && ! in_array( $screen->id, $notice['show_on'], true ) ) {
                return;
            }
            $class = 'notice instant-indexing-notice ' . $notice['class'];
            printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), wp_kses_post( $notice['message'] ) );
        }
    }

    public function aiautotool_dismiss_notice_callback() {
        $dismissed_transient_key = isset($_POST['dismissed_transient_key']) ? sanitize_key($_POST['dismissed_transient_key']) : '';

        if ($dismissed_transient_key) {
            set_transient($dismissed_transient_key, true, 7 * DAY_IN_SECONDS);
        }

        wp_die(); 
    }
    public function create_custom_notice() {
        // Tạo một thông báo tùy chỉnh
         $dismissed = get_transient($this->dismissed_transient_key);

        // Add action only if the notice is not dismissed
        if (!$dismissed) {
        
        ?>
        <div class="aiautotool-notification notice notice-warning is-dismissible">
            <img src="<?php echo esc_url($this->icon_url); ?>" class="aiautotool-seo-icon" width="60" height="60">
            <div class="aiautotool-seo-icon-wrap">
                <h2><?php echo esc_html($this->title); ?></h2>
                <p><?php echo esc_html($this->content); ?></p>
                <p><a class="aiautotool-button-upsell" href="<?php echo esc_url($this->button_url); ?>" target="_blank"><?php echo esc_html($this->button_text); ?><span class="screen-reader-text">(Opens in a new browser tab)</span><span aria-hidden="true" class="aiautotool-button-upsell__caret"></span></a></p>
            </div>
            <button type="button" class="notice-dismiss"  onclick="dismissCustomNotice();"><span class="screen-reader-text">Dismiss this notice.</span></button>
        </div>

        <script>
            function dismissCustomNotice() {
                jQuery.post(ajaxurl, {
                    action: 'dismiss_aiautotool_notice',
                    dismissed_transient_key: '<?php echo esc_js($this->dismissed_transient_key); ?>'
                });
                jQuery('.aiautotool-notification').fadeOut();
            }
        </script>
        <?php
            
        }
    }
}
 ?>