<?php

defined('ABSPATH') or die();
class Auto_Install_Plugins extends rendersetting {
    public  $active = false;
    public  $active_option_name = 'Auto_Install_Plugins_active';

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
            update_option($this->active_option_name, $active);
            print_r($active);
        }

        wp_die();
    }
    public function init(){
        

        register_activation_hook(__FILE__, array($this, 'activate'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    public function render_setting() {

    }
    public function render_tab_setting() {
        
    }

    public function render_feature(){
        $autoToolBox = new AutoToolBox("Install Plugin", "Install plugin SEO, MMO ", "#", $this->active_option_name, $this->active,plugins_url('../images/logo.svg', __FILE__));

        echo $autoToolBox->generateHTML();
        
    }

    public function activate() {
        
    }

    public function add_admin_menu() {
        add_submenu_page(
                MENUSUBPARRENT,       // Parent menu slug (the menu slug of the parent menu)
                '<i class="fa-brands fa-instalod"></i> Auto Install Plugins',           // Page title
                '<i class="fa-brands fa-instalod"></i> Auto Install Plugins',           // Menu title
                'manage_options',              // Capability required to access the menu
                'auto-install-plugins',  // Menu slug (unique identifier for the submenu)
                array($this, 'admin_page_content')  // Callback function to display the page content
            );
        
    }

    public function admin_page_content() {
        ?>
        <div class="wrap">
            <h2>Auto Install Plugins</h2>
            <form method="post" action="">
                <?php
                $plugins_to_install = array(
                    'wordpress-seo' => 'Yoast SEO',
                    'ai-auto-tool' => 'AI Auto Tool',
                    'google-sitemap-generator' => 'Google XML Sitemaps',
                    'polylang'=>'polylang',
                    'wp-super-cache'=>'wp super cache',
                    'fv-top-level-cats'=>'fv top level cats',
                    'classic-editor'=>'classic editor'
                );

                foreach ($plugins_to_install as $plugin_slug => $plugin_name) {
                    if (!$this->is_plugin_installed($plugin_slug)) {
                        // $nonce  = wp_nonce_url(
                        //     add_query_arg(
                        //         array(
                        //             'action' => 'install-plugin',
                        //             'from'   => 'import',
                        //             'plugin' => $plugin_slug,
                        //         ),
                        //         network_admin_url( 'update.php' )
                        //     ),
                        //     'install-plugin_' . $slug
                        // );

                        echo '<label><input type="checkbox" name="plugins_to_install[]" value="' . esc_attr($plugin_slug) . '"> ' . esc_html($plugin_name) . '</label><br>';
                    }
                }
                ?>
                <p class="submit"><input type="submit" name="install_plugins" class="button-primary" value="Install Selected Plugins"></p>
            </form>
        </div>
        <?php

        if (isset($_POST['install_plugins'])) {
            $selected_plugins = isset($_POST['plugins_to_install']) ? $_POST['plugins_to_install'] : array();

            if (!empty($selected_plugins)) {
                foreach ($selected_plugins as $selected_plugin) {
                    $result = $this->download_and_install_plugin($selected_plugin);
                    if ($result) {
                        
                        error_log('Plugin installed successfully: ' . $selected_plugin);
                        echo '<div class="updated"><p>' . esc_html('Plugin installed successfully: ' . $selected_plugin) . '</p></div>';
                    } else {
                        error_log('Error installing plugin: ' . $selected_plugin);
                        echo '<div class="error"><p>' . esc_html('Error installing plugin: ' . $selected_plugin) . '</p></div>';
                    }
                }
            }
        }
    }

    private function is_plugin_installed($plugin_slug) {
        return is_plugin_active($plugin_slug . '/' . $plugin_slug . '.php');
    }

    private function download_and_install_plugin($plugin_slug) {
    $plugin_info = $this->get_plugin_info($plugin_slug);

    if ($plugin_info) {
        $plugin_url = $plugin_info['download_link'];

        // Sử dụng WP_Filesystem để tải và giải nén tệp tin
        $credentials = request_filesystem_credentials(site_url() . '/wp-admin/', '', false, false, array());
        WP_Filesystem($credentials);

        global $wp_filesystem;

        $tmpfname = download_url($plugin_url);

        if (!is_wp_error($tmpfname)) {
            $plugin_info = unzip_file($tmpfname, WP_PLUGIN_DIR);
            unlink($tmpfname);

            if (!is_wp_error($plugin_info)) {
                // Activate the installed plugin
                $plugin_basename = basename($plugin_info[0]['destination_name']);
                activate_plugin($plugin_basename);
                return true;
            } else {
                error_log('Error installing plugin: ' . $plugin_info->get_error_message());
                return false;
            }
        } else {
            error_log('Error downloading plugin: ' . $tmpfname->get_error_message());
            return false;
        }
    }

    return false;
}


    private function get_plugin_info($plugin_slug) {
        $response = wp_safe_remote_get('https://api.wordpress.org/plugins/info/1.0/' . $plugin_slug . '.json');

        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (isset($data['download_link'])) {
                return $data;
            }
        }

        // Return null if unable to retrieve plugin info
        return null;
    }
}

