<?php

defined('ABSPATH') or die();
class Post_Exporter_Plugin extends rendersetting{

    public  $active = false;
    public  $active_option_name = 'Post_Exporter_Plugin_active';
    public $metadesc = <<<EOT
The "Post Exporter" plugin is an extension for WordPress designed to simplify the process of exporting and importing posts based on specific URLs. Below is a detailed description of the plugin's features:

Export & Import Posts from List URL:
    Users can input a list of URLs for posts they want to export or import using a simple interface within the WordPress admin panel.
    The plugin supports the export and import of posts through provided URLs.

Find and Fetch Posts:
    Provides a search and fetch functionality to retrieve detailed information about posts from the entered URLs.
    Supports users in previewing the content of posts and selecting the desired posts for further actions.

Delete Posts:
    Allows users to delete posts based on a list of URLs or post IDs.
    Offers the capability to delete all posts not included in the selected list.

Restore Deleted Posts:
    Integrates a feature to restore posts that have been deleted to the trash.
    Maintains a list of deleted posts for potential restoration in the future.

User-friendly Interface:
    The plugin features a user-friendly interface for easy navigation and direct interaction from the WordPress admin page.

Customization Options:
    Provides customization options allowing users to manage features and settings according to their preferences.

Security Measures:
    Implements security measures, such as nonces, to ensure safety when performing AJAX operations.

The "Post Exporter" plugin optimizes the post management process in WordPress, especially when dealing with bulk export, import, or deletion of posts based on specific URLs.
EOT;

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
         add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_import_from_json', array($this, 'import_posts_from_json'));
        add_action('wp_ajax_fetch_posts_by_urls', array($this, 'fetch_posts_by_urls_ajax'));
        add_action('wp_ajax_delete_post_not_in', array($this, 'delete_post_not_in_ajax'));
        add_action('wp_ajax_restore_trashed_posts_action', array($this, 'restore_trashed_posts_callback'));

        add_action('wp_ajax_get_post_details', array($this, 'get_post_details_ajax'));
        add_action('wp_ajax_import_from_json_file', array($this, 'import_from_json_file'));

        add_action('wp_ajax_aiautotool_backup_database', array($this, 'aiautotool_backup_database_ajax_handler'));
    }

    public function render_setting() {

    }
    public function render_tab_setting() {
        
    }

    public function render_feature(){
        $autoToolBox = new AutoToolBox("Post Exporter", "Export & Import Post from list Url", "#", $this->active_option_name, $this->active,plugins_url('../images/logo.svg', __FILE__));

        echo $autoToolBox->generateHTML();
        
    }
    public function add_menu() {
       

        add_submenu_page(
            MENUSUBPARRENT,
            '<i class="fa-solid fa-file-export"></i> Post Export',
            '<i class="fa-solid fa-file-export"></i> Post Export',
            'manage_options',
            'post_exporter_menu',
            array($this, 'render_page')
        );
    }

    public function enqueue_scripts($hook) {
        
            wp_enqueue_script('post-exporter-script', plugin_dir_url(__FILE__) . '../js/post-exporter-script.js', array('jquery'), '1.2'.rand(), true);
            wp_localize_script('post-exporter-script', 'postExporterData', array(
            'nonce' => wp_create_nonce('post_exporter_nonce'),
        ));
        
    }

     public function render_page() {
    ?>
    <style>
        #aiautotool_eximport_postForm {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        #aiautotool_eximport_postForm label,#aiautotool_eximport_postForm textarea{width:100%;}
        #aiautotool_eximport_postList {
            margin-top: 20px;
        }

        .aiautotool_eximport_loading-icon {
            display: none;
        }
    </style>
    <div class="wrap aiautotool_container">
                <div class="aiautotool_left">
                    <div class="ft-box">
            <div class="ft-menu">
                <div class="ft-logo"><img src="<?php echo plugins_url('../images/logo.svg', __FILE__); ?>">
                    <br>Backup & Restore</div>

              
                 <button href="#tab-setting" class="nav-tab sotabt "><i class="fa-solid fa-file-export"></i> Export / Restore</button>
               

                <button  href="#tab-delete"  class="nav-tab  sotab"><i class="fa-solid fa-trash-can"></i> <?php _e('Delete Post', 'ai-auto-tool'); ?></button>

                <button  href="#tab-backupdata"  class="nav-tab  sotab"><i class="fa-solid fa-trash-can"></i> <?php _e('Backup Database', 'ai-auto-tool'); ?></button>               

            </div>
            <div class="ft-main">
    <div class="wrap">
        <h1><i class="fa-solid fa-file-export"></i> Post Exporter</h1>
        <!-- <h2 class="nav-tab-wrapper">
            <a class="nav-tab nav-tab-active" id="tab1" href="#tab-setting">Export-Import</a>
            <a class="nav-tab" id="tab2" href="#tab-delete">Delete Post</a>
        </h2> -->
        <!-- Tab 1 content -->
        <div class="sotab-box ftbox tab-content" id="tab-backupdata">

            <form method="post" onsubmit="return backupDatabase();">

                <div class="ft-submit button-primary">
                <button type="submit" name="aiautotool_backup_database"><i class="fa-solid fa-file-export"></i> <?php _e('Export Database', 'ai-auto-tool'); ?></button>
            </div>

           
            </form>
            <script>
            function backupDatabase() {
                // Tạo một XMLHttpRequest để gửi yêu cầu AJAX
                var xhr = new XMLHttpRequest();
                var nonce = '<?php echo wp_create_nonce('backup_database_nonce'); ?>';
                // Thiết lập phương thức và URL của yêu cầu
                xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');

                xhr.onload = function() {
                    if (xhr.status === 200) {
                        // Tạo một liên kết tải về và thêm vào DOM
                        var downloadLink = document.createElement('a');
                        downloadLink.href = 'data:application/octet-stream,' + encodeURIComponent(xhr.responseText);
                        downloadLink.download = 'backup-<?php echo date('YmdHis'); ?>.sql';
                        downloadLink.style.display = 'none';
                        document.body.appendChild(downloadLink);
                        downloadLink.click();
                        document.body.removeChild(downloadLink);
                    }
                };

                // Gửi yêu cầu với dữ liệu form
                xhr.send('action=aiautotool_backup_database&_wpnonce=' + nonce);

                // Ngăn chặn gửi form truyền thống
                return false;
            }
        </script>
        </div>
        <div class="sotab-box ftbox tab-content" id="tab-setting">
            <!-- Existing content for Tab 1 -->
             <form id="aiautotool_eximport_postForm">
                <h3>Export :</h3>
                <label for="urlList">Url List:</label>

                <textarea class="ft-code-textarea" id="urlList" rows="4" cols="50"></textarea>

                <button class="btn" type="button" onclick="fetchPosts('urlList','postList')">
                    <span>Find Post</span>
                    <span class="aiautotool_eximport_loading-icon"></span>
                </button>

                <div id="postList"></div>

                <button class="ft-submit"  type="button" onclick="exportToJson()">Export JSON</button>
                <h3>Import JSON:</h3>
                <button type="button" onclick="importFromJson()">Import JSON</button>
                <br>
                <input type="file" id="jsonFileInput" accept=".json">
                <button type="button" class="ft-submit" onclick="importFromJsonFile()">Import JSON from File</button>
            </form>
            <div class="aiautotool_info" style="max-width: 600px;margin: 20px auto;padding: 20px;border: 1px solid #ccc;border-radius: 5px;"><?php echo nl2br(esc_html($this->metadesc,'Aiautotool'));?></div>
        </div>

        <div class="sotab-box ftbox tab-content" id="tab-delete">
            <form id="aiautotool_eximport_postForm">
                <label for="urlList2">Url List:</label>
                <textarea class="ft-code-textarea" id="urlList2" rows="4" cols="50"></textarea>

                <button type="button" class="btn" onclick="fetchPosts('urlList2','postList2')">
                    <span>Find Post</span>
                    <span class="aiautotool_eximport_loading-icon"></span>
                </button>

                <div id="postList2"></div>
                <button type="button" class="ft-submit" onclick="DeletePostex()">Delete Post Not in Selected</button>
            </form>
            <p>Restore posts from trash:</p>
            <button type="button" class="ft-submit" onclick="restoreTrashedPosts()">Restore</button>
        </div>
        
       
    </div>
</div>
</div>
    <script>
        function showLoading() {
            jQuery('.aiautotool_eximport_loading-icon').html('<span class="spinner is-active"></span>');
            jQuery('.aiautotool_eximport_loading-icon').show();
        }

        function hideLoading() {
            jQuery('.aiautotool_eximport_loading-icon').hide();
        }
    </script>
    <?php
}



    public function import_posts_from_json() {
        check_ajax_referer('post_exporter_nonce', 'security');

        $json_data_url = $_POST['json_data_url'];
        $json_data = $this->get_json_data($json_data_url);

        foreach ($json_data as $post_data) {
            $post_id = wp_insert_post(array(
                'post_title' => $post_data['title'],
                'post_content' => $post_data['content'],
                'post_status' => 'publish',
                'post_type' => 'post',
            ));

            if ($post_id) {
                // Thêm tags và chuyên mục cho bài viết
                wp_set_post_terms($post_id, $post_data['tags'], 'post_tag');
                wp_set_post_categories($post_id, $post_data['categories']);
                
                // Thêm xử lý bổ sung tùy theo nhu cầu của bạn
            }
        }

        wp_die();
    }

    public function import_from_json_file() {
        check_ajax_referer('post_exporter_nonce', 'security');

        $json_data = $_POST['json_data'];
        $imported_posts = array();

        foreach ($json_data as $post_data) {
            $post_id = wp_insert_post(array(
                'post_title' => $post_data['title'],
                'post_name' => $post_data['slug'],
                'post_content' => $post_data['content'],
                'post_status' => 'publish',
                'post_type' => 'post',
            ));

            if ($post_id) {
                // Thêm tags và chuyên mục cho bài viết
                wp_set_post_terms($post_id, $post_data['tags'], 'post_tag');
                wp_set_post_categories($post_id, $post_data['categories']);

                // Thêm xử lý bổ sung tùy theo nhu cầu của bạn

                $imported_posts[] = $post_id;
            }
        }

        if (!empty($imported_posts)) {
            wp_send_json_success('Import thành công. Bài viết đã được thêm vào với ID: ' . implode(', ', $imported_posts));
        } else {
            wp_send_json_error('Không có bài viết nào được import.');
        }

        wp_die();
    }

    public function fetch_posts_by_urls_ajax() {
        check_ajax_referer('post_exporter_nonce', 'security');

        $url_list = $_POST['urlList'];
        $posts = array();

        foreach ($url_list as $url) {
            $post = $this->search_posts_by_url($url);
            if ($post) {
                if (function_exists('pll_get_post_language')) {
                    
                    $language = pll_get_post_language($post->ID);
                } else {
                    
                    $language = get_bloginfo("language"); 
                }
                $posts[] = array(
                    'title' => $post->post_title,
                    'id' => $post->ID,
                    'content' => $post->post_content,
                    'slug' => $post->post_name,
                    'tags' => wp_get_post_tags($post_id, array('fields' => 'names')),
                    'categories' => wp_get_post_categories($post_id, array('fields' => 'names')),
                    'language' => $language,
                    
                );
            }
        }

        if (!empty($posts)) {
            wp_send_json_success($posts);
        } else {
            wp_send_json_error('Không có bài viết nào được tìm thấy.');
        }

        wp_die();
    }

    public function delete_post_not_in_ajax() {
        check_ajax_referer('post_exporter_nonce', 'security');
        global $wpdb;
        // $exportData = $_POST['exportData'];

         // $exportData = isset($_POST['exportData']) ? array_map('absint', array_keys($_POST['exportData'])) : array();
        $exportData = isset($_POST['exportData']) ? $_POST['exportData'] : array();


        $posts = array();
         if (!empty($exportData)) {
            $tmpid = array();
            foreach($exportData as $item){
                $tmpid[] = $item['id'];
            }
            $previouslyTrashedIDs = $wpdb->get_col(
                    "SELECT ID
                    FROM $wpdb->posts
                    WHERE post_type = 'post' AND post_status = 'trash'"
                );

            // Xây dựng danh sách ID cần giữ
            $keepPostIDs = implode(',', $tmpid);

            // // Sử dụng SQL để đặt bài viết không nằm trong danh sách vào thùng rác
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $wpdb->posts
                    SET post_status = 'trash'
                    WHERE post_type = 'post' AND ID NOT IN ($keepPostIDs)"
                )
            );

             $currentlyTrashedIDs = $wpdb->get_col(
                "SELECT ID
                FROM $wpdb->posts
                WHERE post_type = 'post' AND post_status = 'trash'"
            );
            $newlyTrashedIDs = array_diff($currentlyTrashedIDs, $previouslyTrashedIDs);
            $trashedLog = get_option('aiautotool_trashed_log', array());
            $trashedLog = array_merge($trashedLog, $newlyTrashedIDs);

            // Lưu lại danh sách ID vào biến option
            update_option('aiautotool_trashed_log', $trashedLog,null, 'no');
            wp_send_json_success('The posts not included in the export list have been successfully deleted.'.$keepPostIDs);
        } else {
            wp_send_json_error('Error');
        }
        
        wp_die();
    }

    public function get_post_details_ajax() {
        check_ajax_referer('post_exporter_nonce', 'security');

        $post_id = $_POST['post_id'];
        $post = get_post($post_id);

        if ($post) {
            $post_details = array(
                'title' => $post->post_title,
                'content' => $post->post_content,
                'slug' => $post->post_name,
                'tags' => wp_get_post_tags($post_id, array('fields' => 'names')),
                'categories' => wp_get_post_categories($post_id, array('fields' => 'names')),
                // Thêm các thông tin khác nếu cần
            );

            wp_send_json_success($post_details);
        } else {
            wp_send_json_error('Không thể lấy thông tin chi tiết của bài viết.');
        }

        wp_die();
    }

    private function search_posts_by_url($url) {
        $post_id = url_to_postid($url);

        if ($post_id) {
            $post = get_post($post_id);
            return $post;
        } else {
            return null;
        }
    }

    private function get_json_data($json_url) {
        $response = wp_remote_get($json_url);
        $body = wp_remote_retrieve_body($response);
        $json_data = json_decode($body, true);

        return $json_data;
    }
    function restore_trashed_posts_callback() {
        check_ajax_referer('post_exporter_nonce', 'security');

        // Lấy danh sách ID từ biến option trashed_log
        $trashedLog = get_option('aiautotool_trashed_log', array());

        if (!empty($trashedLog)) {
            global $wpdb;

            $postIDsString = implode(',', array_map('absint', $trashedLog));

            // Sử dụng SQL để cập nhật trạng thái của bài viết thành "publish"
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $wpdb->posts
                    SET post_status = 'publish'
                    WHERE ID IN ($postIDsString)"
                )
            );

            wp_send_json_success('Posts restored successfully.');

        } else {
            wp_send_json_error('No posts to restore.');
        }

        wp_die();
    }


    public function aiautotool_backup_database_ajax_handler() {
        if (
        isset($_POST['action']) &&
        $_POST['action'] == 'aiautotool_backup_database' &&
        isset($_POST['_wpnonce']) &&
        wp_verify_nonce($_POST['_wpnonce'], 'backup_database_nonce')
            ) {
                ob_start(); 

                global $wpdb;

                $query = "SHOW TABLES";
                $tables = $wpdb->get_results($query, ARRAY_N);

                foreach ($tables as $table) {
                    $table = $table[0];
                    $result = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);

                    echo "-- Table structure for table $table\n\n";
                    echo "DROP TABLE IF EXISTS $table;\n";
                    $create_table = $wpdb->get_row("SHOW CREATE TABLE $table", ARRAY_N);
                    echo $create_table[1] . ";\n\n";

                    echo "-- Data for table $table\n\n";
                    foreach ($result as $row) {
                        $row = array_map('addslashes', $row);
                        echo "INSERT INTO $table VALUES ('" . implode("','", $row) . "');\n";
                    }
                    echo "\n\n";
                }

                $backup_content = ob_get_clean(); 

                // mở ra khi cần sendmail

                //  $filename = WP_CONTENT_DIR.'/uploads/backup-' . date('YmdHis') . '.sql';

                // file_put_contents($filename, $backup_content);

                // sendmail($filename);
                echo $backup_content; 
            } else {
                
                echo 'Invalid nonce or action.';
            }

            exit();
    }

    
}


