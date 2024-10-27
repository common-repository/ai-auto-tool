<?php
defined('ABSPATH') or die();

class Image_URL_Checker {

    public function __construct() {
        // add_action('save_post', array($this, 'check_and_update_images'));
    }

    private function image_exists($url) {
        $headers = get_headers($url);
        return stripos($headers[0], "200 OK") ? true : false;
    }

    
    public function check_and_update_images($post_id) {
        
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id) || !get_post_status($post_id)) {
            return;
        }

        
        $content = get_post_field('post_content', $post_id);
        $pattern = '/<img\s[^>]*?src=[\'"]([^\'"]*?)[\'"][^>]*?>/is';
        preg_match_all($pattern, $content, $matches);

        if (!empty($matches[0])) {
            foreach ($matches[0] as $match) {
                // Lấy URL của hình ảnh từ match
                preg_match('/src=[\'"]([^\'"]*?)[\'"]/i', $match, $srcMatch);
                $imageUrl = isset($srcMatch[1]) ? $srcMatch[1] : '';

                if ($imageUrl && !$this->image_exists($imageUrl)) {
                    // Nếu không tồn tại, gỡ bỏ thẻ <img> ra khỏi nội dung
                    $content = str_replace($match, '', $content);
                }
            }

            // Cập nhật nội dung của bài viết
            wp_update_post(array('ID' => $post_id, 'post_content' => $content));
        }
        // else{
        //     $Imagescontent = new Imagescontent();
        //     $bardGenContent = new BardGenContent();
        //     $listimg = $bardGenContent->searchimg($title);
        //     if ($listimg) {
        //         $listimg10 = array_slice($listimg, 0, 10);
        //         shuffle($listimg10);
        //         $updated_content = $Imagescontent->insertImages($content, $listimg10);
        //         wp_update_post(array('ID' => $post_id, 'post_content' => $updated_content));
        //     }else{
        //         $updated_content =  $updated_content .'khong co hinh';
        //         wp_update_post(array('ID' => $post_id, 'post_content' => $updated_content));
        //     }
        // }
    }
}



$aiautotool_config_settings = get_option('aiautotool_config_settings');
if(isset($aiautotool_config_settings['tool-404img'])){
        $Image_URL_Checker = new Image_URL_Checker();
        
}


if(isset($aiautotool_config_settings['tool-schedulecheckimg404'])){
        
    // Thêm action để thực hiện công việc kiểm tra
add_action('check_images_cron', 'check_images_in_posts');

function check_images_in_posts() {
    
    $transient_key = 'checked_img404_post_ids';
        $checked_post_ids = get_transient($transient_key);

        if (empty($checked_post_ids)) {
            $checked_post_ids = array();
        }

        $args = array(
            'post_type' => 'post',
            'posts_per_page' => 2,
            'post__not_in' => $checked_post_ids,
            'orderby' => 'date',
            'order' => 'DESC',
        );

        $posts = get_posts($args);
         $checker = new Image_URL_Checker();


        foreach ($posts as $post) {
            
            $checker->check_and_update_images($post->ID);

            // Thêm ID bài viết đã kiểm tra vào danh sách
            $checked_post_ids[] = $post->ID;
        }

        // Lưu danh sách ID bài viết đã kiểm tra vào transient
        set_transient($transient_key, $checked_post_ids, 60 * 60 * 24 * 7); // 1 tuần
}

register_activation_hook(__FILE__, 'schedule_image_check');

function schedule_image_check() {
    // Lập lịch kiểm tra hình ảnh mỗi ngày
    if (!wp_next_scheduled('check_images_cron')) {
        wp_schedule_event(time(), '5min', 'check_images_cron');
    }
}

// Thêm action cho sự kiện hủy kích hoạt plugin
register_deactivation_hook(__FILE__, 'unschedule_image_check');

function unschedule_image_check() {
    // Hủy lịch kiểm tra hình ảnh khi plugin bị tắt
    wp_clear_scheduled_hook('check_images_cron');
}

//end if(isset($aiautotool_config_settings['tool-schedulecheckimg404'])){  
}




?>
