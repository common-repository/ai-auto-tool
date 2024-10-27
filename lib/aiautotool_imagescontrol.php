<?php 
defined('ABSPATH') or die();
class KCT_AIAutoTool_ImageUploader {
    public function saveImage($imgURL, $post_title) {
        $image_name = basename($imgURL);
        $filetype = wp_check_filetype($image_name);
        $upload_dir = wp_upload_dir();

        $extension = $filetype['ext'] ? $filetype['ext'] : "jpg";
        if (empty($extension)) $extension = "jpg";

        // $unique_file_name = $this->sanitize($post_title) . "-" . uniqid() . "." . $extension;
        $unique_file_name =  uniqid() . "." . $extension;
        $filename = $upload_dir['path'] . '/' . $unique_file_name;
        $baseurl = $upload_dir['baseurl'] . $upload_dir['subdir'] . '/' . $unique_file_name;

        $ch = curl_init($imgURL);
        $fp = fopen($filename, 'wb');

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        curl_exec($ch);
        $rescode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        if ($rescode == 200 && filesize($filename) > 100) {
            $wp_filetype = wp_check_filetype(basename($filename), null);

            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => sanitize_file_name($post_title),
                'post_content' => '',
                'post_status' => 'inherit',
            );

            $attach_id = wp_insert_attachment($attachment, $filename);
            $imagenew = get_post($attach_id);
            $fullsizepath = get_attached_file($imagenew->ID);

            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attach_id, $fullsizepath);
            wp_update_attachment_metadata($attach_id, $attach_data);

            $output['url'] = $imgURL;
            $output['file_name'] = $unique_file_name;
            $output['path'] = $filename;
            $output['baseurl'] = $baseurl;
            $output['attach_id'] = $attach_id;
            $output['url_out'] = $baseurl;

            return $output;
        } else {
            return null;
        }
    }

    private function sanitize($title) {
        // Custom sanitize logic if needed
        // You can modify this function based on your requirements
        return sanitize_title($title);
    }
}
 ?>