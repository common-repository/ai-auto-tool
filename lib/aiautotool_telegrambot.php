<?php 
defined('ABSPATH') or die();
class aiautotool_Telegram_Notifications {

    private $telegram_bot_token;
    private $telegram_chat_id;

    public function __construct($bot_token, $chat_id) {
        $this->telegram_bot_token = $bot_token;
        $this->telegram_chat_id = $chat_id;

        
    }

    public function send_bot_message($message) {
        // Telegram API URL
        $telegram_api_url = "https://api.telegram.org/bot" . $this->telegram_bot_token . "/sendMessage";

        // Parameters for the HTTP request
        $params = array(
            'chat_id' => $this->telegram_chat_id,
            'text' => $message,
        );

        // Make the POST request using wp_remote_post
        $response = wp_remote_post($telegram_api_url, array(
            'body' => $params,
        ));

        // Check for errors
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            // Handle error accordingly, e.g., log it
            error_log("Telegram API request error: $error_message");
        } else {
            // Get the response body
            $body = wp_remote_retrieve_body($response);
            // You can do something with $body if needed
        }
    }


    // public function send_bot_message($message) {
    //     // Telegram API URL
    //     $telegram_api_url = "https://api.telegram.org/bot" . $this->telegram_bot_token . "/sendMessage";

    //     // Parameters for the HTTP request
    //     $params = array(
    //         'chat_id' => $this->telegram_chat_id,
    //         'text' => $message,
    //     );

    //     // Initialize cURL session
    //     $ch = curl_init($telegram_api_url);

    //     // Set cURL options
    //     curl_setopt($ch, CURLOPT_POST, 1);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    //     // Execute cURL session
    //     $result = curl_exec($ch);

    //     // Close cURL session
    //     curl_close($ch);
    // }

    
}
 ?>