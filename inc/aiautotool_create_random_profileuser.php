<?php
defined('ABSPATH') or die();

class aiautotool_AutoGenerateUsername extends rendersetting{

     public  $active = true;
    public  $active_option_name = 'Aiautotool_tool_create_random_profileuser_active';
    public $aiautotool_config_settings;

    private $user_count_option = 'auto_generate_user_count';

    public function __construct() {

         $this->active = get_option($this->active_option_name, true);
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
        
        

        add_action('admin_init', array($this, 'handle_create_users_submission'));
    }

     

   

    public function register_settings() {
        register_setting('auto_generate_username_settings', $this->user_count_option);
        add_settings_section('auto_generate_username_section', __('Create user random','ai-auto-tool'), array($this, 'section_callback'), 'auto-generate-username-settings');
        add_settings_field('user_count', __('Number user:','ai-auto-tool'), array($this, 'user_count_callback'), 'auto-generate-username-settings', 'auto_generate_username_section');
    }

    public function section_callback() {
        echo __('Create user random','ai-auto-tool');
    }

    public function user_count_callback() {
        $count = get_option($this->user_count_option, 1);
        echo '<input type="number" name="' . $this->user_count_option . '" value="' . $count . '" />';
    }

    public function render_setting() {
        ?>

         <div id="tool-create_user-random"  class="tab-content" style="display:none;">
            <h1><i class="fa-solid fa-shield-halved"></i> <?php _e('Auto create author', 'ai-auto-tool'); ?></h1>
        <div class="wrap">
           <h3><i class="fa-regular fa-pen-to-square"></i> <?php _e('Input number author ', 'ai-auto-tool') ?></h3>
            <form method="post" action="">
                <p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php _e('Quantity.', 'ai-auto-tool'); ?></p>
                <input type="number" name="quantity" id="quantity" required min="1" value="1">
                
                <br><br>

                <p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php _e('Password.', 'ai-auto-tool'); ?></p>
                <input type="password" name="password" id="password" required>

                <br><br>

                <?php wp_nonce_field('create_users_nonce', 'create_users_nonce'); ?>
                <input type="submit" name="create_users_submit" class="button button-primary" value="Create Users">
            </form>
            </div>
        </div>
        <?php
        self::display_created_users_list();
    }

    public function display_created_users_list() {
    $created_users = get_transient('created_users_list');

    if ($created_users && is_array($created_users)) {
        ?>
        <div class="updated">
            <p>Authors created successfully:</p>
            <ul>
                <?php foreach ($created_users as $user) : ?>
                    <li><?php echo esc_html($user); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php

        // Clear the transient after displaying the list
        delete_transient('created_users_list');
    }
}
    // Function to handle form submission
    public function handle_create_users_submission() {
        $created_users = array();
        if (isset($_POST['create_users_submit'])) {
            // Verify nonce
            if (!isset($_POST['create_users_nonce']) || !wp_verify_nonce($_POST['create_users_nonce'], 'create_users_nonce')) {
                die('Security check failed');
            }

            // Get input values
            $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
            $password = isset($_POST['password']) ? sanitize_text_field($_POST['password']) : '';
            $domain = preg_replace('#^https?://#', '', get_site_url());

            // Create users
            for ($i = 1; $i <= $quantity; $i++) {
                $username = $this->gen_authorname($i);
                $user_id = username_exists($username);

                if (!$user_id && email_exists($username . '@' . $domain) == false) {
                    $user_id = wp_create_user($username, $password, $username . '@' . $domain);
                    wp_update_user(array('ID' => $user_id, 'role' => 'author'));
                     $created_users[] = $username;
                }
            }
            set_transient('created_users_list', $created_users, 60);
            // Redirect after submission
            $redirect_url = admin_url('admin.php?page=ai_auto_tool&success=true');
            wp_redirect($redirect_url);
            exit;
        }
    }
    private function gen_authorname($index) {
        $names = array(
            'John', 'Jane', 'Michael', 'Emma', 'Samuel',
            'Olivia', 'William', 'Ava', 'James', 'Isabella',
            'Liam', 'Sophia', 'Logan', 'Mia', 'Benjamin',
            'Amelia', 'Lucas', 'Evelyn', 'Oliver', 'Ella',
            'Noah', 'Charlotte', 'Sebastian', 'Grace', 'Alexander',
            'Harper', 'Ethan', 'Stella', 'Daniel', 'Scarlett',
            'Henry', 'Lily', 'Jackson', 'Chloe', 'Elijah',
            'Piper', 'Carter', 'Zoe', 'Aiden', 'Nora'
        );

        $middle_names = array(
            'Smith', 'Doe', 'Johnson', 'Williams', 'Jones',
            'Brown', 'Davis', 'Miller', 'Wilson', 'Moore',
            'Taylor', 'Anderson', 'Thomas', 'Jackson', 'White',
            'Harris', 'Martin', 'Thompson', 'Garcia', 'Martinez',
            'Robinson', 'Clark', 'Rodriguez', 'Lewis', 'Lee',
            'Walker', 'Hall', 'Allen', 'Young', 'Hernandez',
            'King', 'Wright', 'Lopez', 'Hill', 'Scott',
            'Green', 'Adams', 'Baker', 'Evans', 'Fisher'
        );

        $username = $names[$index % count($names)] . '' . $middle_names[$index % count($middle_names)] ;
        return sanitize_user($username);
    }
    public function render_tab_setting() {
        if ($this->active=='true') {
         echo '<button href="#tool-create_user-random" class="nav-tab sotab"><i class="fa-solid fa-shield-halved"></i> '.__('Create profile user author','ai-auto-tool').'</button>';
        }
    }

    public function render_feature() {

       $autoToolBox = new AutoToolBox(__('Tool Create Random user','ai-auto-tool'), __('auto Create Random user Author','ai-auto-tool'), "#", $this->active_option_name, $this->active,plugins_url('../images/logo.svg', __FILE__));

        echo $autoToolBox->generateHTML();
    }

    public function auto_generate_username($user_id) {
        $user_count = get_option($this->user_count_option, 1);

        for ($i = 0; $i < $user_count; $i++) {
            $user_info = get_userdata($user_id);

            if (empty($user_info->user_login)) {
                $username = $this->generate_username($user_info->user_email);
                wp_update_user(array('ID' => $user_id, 'user_login' => $username));
            }
        }
    }

    private function generate_username($email) {
        $username = strstr($email, '@', true);
        $username_exists = username_exists($username);
        $i = 1;

        while ($username_exists) {
            $new_username = $username . $i;
            $username_exists = username_exists($new_username);
            $i++;
        }

        return $new_username;
    }
}

?>
