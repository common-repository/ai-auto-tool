<?php 
defined('ABSPATH') or die();
class AIAutotool_ThumbPlusSettings extends rendersetting{
	public  $active = false;
    public  $active_option_name = 'Aiautotool_ThumbPlusSettings_active';

    private $options;

    function __construct(){

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
    	$this->options = get_option('thumb_plus_option');
        add_action('admin_init', array($this, 'init_page'));
        add_filter('intermediate_image_sizes_advanced', array($this, 'disable_sizes'));
        if (isset($this->options['disable_scaled'])) {
            add_filter('big_image_size_threshold', array($this, 'disable_scaled_images'));
        }


        $next_scheduled = wp_next_scheduled('cron_create_thumb');
        if (!$next_scheduled || ($next_scheduled > time())) {
            wp_schedule_single_event(time(), 'cron_create_thumb');
        }
        add_action('cron_create_thumb', array($this, 'cron_create_thumb_action'));
    }

    public function render_tab_setting() {
        if ($this->active=='true') {
        echo '<button href="#tab-fixthumb-setting" class="nav-tab sotab"><i class="fa-solid fa-link"></i> Fix thumb 404 setting</button>';
        }
    }
    
    public function render_feature(){
        $autoToolBox = new AutoToolBox("<i class=\"fa-solid fa-link\"></i> Active Thumb Fix 404", __('If active it, auto fix thumb 404.','ai-auto-tool'), "https://aiautotool.com", $this->active_option_name, $this->active,plugins_url('../images/logo.svg', __FILE__));

        echo $autoToolBox->generateHTML();
        ?>
        

        <?php
    }


    public function render_setting(){

    	
        $disable_sizes  = isset($this->options['disable_sizes'])?$this->options['disable_sizes']:''; 
    	$sizes = [
            'thumbnail' => get_option('thumbnail_size_w') . 'x' . get_option('thumbnail_size_h'),
            'medium'    => get_option('medium_size_w') . 'x' . get_option('medium_size_h'),
            'large'     => get_option('large_size_w') . 'x' . get_option('large_size_h'),
        ];

        if (!empty($disable_sizes)) {
            foreach ($disable_sizes as $key => $value) {
                if (array_key_exists($key, $sizes)) {
                    $disable_sizes[$key] = $sizes[$key];
                }
            }
        }
    	?>
    	<div id="tab-fixthumb-setting" class="tab-content" style="display:none;">
    		<h2><i class="fa-solid fa-link"></i> Thumb fix 404 </h2>
    		 <form method="post" action="options.php">
    		 	<p class="ft-note">
                	<i class="fa-solid fa-lightbulb"></i>
                	<?php _e('Min Width: Only use images of minimum size.','ai-auto-tool'); ?> 
                </p>
                <input class="ft-input-big" placeholder="<?php _e('400', 'ai-auto-tool'); ?>" name="thumb_plus_option[min_width]" type="number" value="<?php if(!empty($this->options['min_width'])){echo sanitize_text_field($this->options['min_width']);} ?>"/>

                <p class="ft-note">
                	<i class="fa-solid fa-lightbulb"></i>
                	<?php _e('Max Width, dimensions exceed the maximum size. Setting = 0 will not resize.','ai-auto-tool'); ?> 
                </p>
                <?php 

                $max_width_value = $this->options !== null ? ($this->options['max_width'] ?? 0) : 0;
                

 ?>
                <input class="ft-input-big" placeholder="<?php _e('400', 'ai-auto-tool'); ?>" name="thumb_plus_option[max_width]" type="number" value="<?php echo $max_width_value; ?>"/>

                <p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php _e('Disable scaled images, Disable automatic resizing of images above the threshold 2560, keeping only the original images. Does not work with PNG images.', 'ai-auto-tool'); ?></p>

					<!-- tool class 1 -->
					<label class="nut-switch">
					<input type="checkbox" name="thumb_plus_option[disable_scaled]" value="1" <?php if ( isset( $this->options['disable_scaled']) && 1 ==  $this->options['disable_scaled'] ) echo 'checked="checked"'; ?> />
					<span class="slider"></span></label>

				<p class="ft-note"><i class="fa-solid fa-lightbulb"></i><?php _e('Disable image sizes', 'ai-auto-tool'); ?></p>


					<?php 
					 $all_sizes = $this->get_all_sizes();
                                foreach ($all_sizes as $type => $data) {
                                    $size = $data['w'] . 'x' . $data['h'];
                                    $cur_size = isset($disable_sizes[$type])?$disable_sizes[$type]:null;
                                    ?>
                                    <p>
                                    <label class="nut-switch">
									<input type="checkbox" name="thumb_plus_option[disable_sizes][<?php echo $type; ?>]" value="<?php echo $size;  ?>" <?php if ( $cur_size ==  $size ) echo 'checked="checked"'; ?> />
									<span class="slider"></span><?php echo  $type . ' (' . $size . ')'; ?></label>
									</p>
                                    <?php
                                }

					 ?>
					<!-- tool class 1 -->
					
                <?php  settings_fields('thumb_plus_option_group');
                do_settings_sections('thumb-plus');
                submit_button(__( 'Save all', 'ai-auto-tool' ),'ft-submit'); 
                 ?>
    		 </form>
    		</div>
    	<?php

        
    }

    public function disable_sizes($sizes) {
        $disable_sizes = isset($this->options['disable_sizes'])?$this->options['disable_sizes']:'';
        if (!empty($disable_sizes)) {
            $sizes_to_remove = array_keys($disable_sizes);
            $new_sizes = array_diff_key($sizes, array_flip($sizes_to_remove));
            return $new_sizes;
        }
        return $sizes;  
    }

    public function get_all_sizes(){
        global $_wp_additional_image_sizes;
        $sizes = array();

        foreach( get_intermediate_image_sizes() as $_size ){
            if( in_array( $_size, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ){
                $sizes[ $_size ]['w'] = get_option( "{$_size}_size_w" );
                $sizes[ $_size ]['h'] = get_option( "{$_size}_size_h" );
            } elseif( isset( $_wp_additional_image_sizes[ $_size ] ) ){
                $sizes[ $_size ] = array(
                    'w' => $_wp_additional_image_sizes[ $_size ]['width'],
                    'h' => $_wp_additional_image_sizes[ $_size ]['height'],
                );
            }
        }

        return $sizes;
    }

    public function disable_scaled_images($threshold) {
        return 0;
    }

    

    public function sanitize($input){
        $new_input = array();

        if(isset($input['min_width'])){
            $min_width = $input['min_width'];
            if ($min_width == '' || $min_width < 400) {
                $min_width = 400;
            }
            $new_input['min_width'] = $min_width;
        }
        if(isset($input['max_width'])){
            $new_input['max_width'] = sanitize_text_field($input['max_width']);
        }
        if(isset($input['save_post'])){
            $new_input['save_post'] = sanitize_text_field($input['save_post']);
        }
        if(isset($input['auto'])){
            $new_input['auto'] = sanitize_text_field($input['auto']);
        }
        if(isset($input['exclude_cat'])){
            $exclude_cat = implode(',', $input['exclude_cat']);
            $new_input['exclude_cat'] = $exclude_cat;
        }
        if(isset($input['disable_sizes'])){
            $new_input['disable_sizes'] = $input['disable_sizes'];
        }
        if(isset($input['disable_scaled'])){
            $new_input['disable_scaled'] = sanitize_text_field($input['disable_scaled']);
        }

        return $new_input;
    }

    public function init_page(){
        register_setting(
            'thumb_plus_option_group',
            'thumb_plus_option',
            array($this, 'sanitize')
        );

        add_settings_section(
            'section_id',
            '',
            array($this, 'section_info'),
            'thumb-plus'
        );
    }

    public function callback_normal($args){
        
    }

    public function section_info(){

    }

    public function cron_create_thumb_action() {
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 20,
            'meta_query'     => array(
                array(
                    'key'     => '_thumbnail_id',
                    'compare' => 'NOT EXISTS',
                ),
            ),
        );

        if (isset($this->options['exclude_cat'])) { 
            $args['category__not_in'] = explode(',', $this->options['exclude_cat']);
        }

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            $posts = $query->posts;
            foreach ($posts as $post) {
                 $this->create_thumb_by_post_id($post->ID);
            }
        }

        wp_schedule_single_event(time(), 'cron_create_thumb');
    }


    public function create_thumb_by_post_id($post_id) {
        set_time_limit(0);
        if (get_post_type($post_id) != 'post') {
            return;
        }
        $upload_dir = wp_upload_dir();

        $post = get_post($post_id);
        $content = $post->post_content;
        $title = $post->post_title;
        $slug = $post->post_name;
        
        if (has_post_thumbnail($post_id)) {
            $thumbnail_url = get_the_post_thumbnail_url($post_id);
            $path_thumb = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $thumbnail_url);
            if (file_exists($path_thumb)) {
                $check_thumb = true;
            }else{
                $thumbnail_id = get_post_thumbnail_id($post_id);
                delete_post_thumbnail($thumbnail_id);
                $check_thumb = false;
            }
        }else{
            global $wpdb;
            $check_attachment_id = false;
            $allowed_exts = array('jpg', 'jpeg', 'png', 'webp');

            foreach ($allowed_exts as $ext) {
                $check_url_img = $upload_dir['url'] . '/' . $slug . '.' . $ext;
                $check_attachment_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'attachment' AND guid='%s';", $check_url_img));

                if ($check_attachment_id) {
                    set_post_thumbnail($post_id, $check_attachment_id);
                    return true;
                }
            }

            $check_thumb = false;
        }

        if ($check_thumb) {
            return;
        }

        $pattern = '/<img[^>]*\bsrc=["\']([^"\']*\.((jpg|jpeg|png|webp)))["\']/i';
        $matches = [];
        $count = 0;

        if (preg_match_all($pattern, $content, $matches)) {
            require_once ABSPATH . 'wp-admin/includes/image.php';

            if (isset($matches[1])) {
                foreach ($matches[1] as $url) {
                    $info = pathinfo($url);
                    $path = $this->create_img($url, $slug);
                    if ($path && $path != '' && file_exists($path)){
                        $filetype = wp_check_filetype(basename($path), null);
                        $url_new = $upload_dir['url'] . '/' . basename($path);
                        $attachment = array(
                            'guid'           => $url_new, 
                            'post_mime_type' => $filetype['type'],
                            'post_title'     => $title,
                            'post_content'   => $title,
                            'post_excerpt'   => $title,
                            'post_status'    => 'inherit',
                            'post_author'    => 1
                        );

                        $attachment_id = wp_insert_attachment($attachment, $path, $post_id);

                        if ($attachment_id) {
                            $attach_data = wp_generate_attachment_metadata($attachment_id, $path);
                            wp_update_attachment_metadata($attachment_id, $attach_data);
                            update_post_meta($attachment_id, '_wp_attachment_image_alt', $title);

                            $content = str_replace($url, $url_new, $content);
                            $args = array(
                                'ID'                => $post_id,
                                'post_content'      => $content,
                                'post_date'         => current_time('mysql'),
                                'post_date_gmt'     => current_time('mysql', 1),
                                'post_modified'     => current_time('mysql'),
                                'post_modified_gmt' => current_time('mysql', 1)
                            );
                            $updated = wp_update_post($args);
                            if ($updated) {
                                set_post_thumbnail($post_id, $attachment_id);
                                return true;
                            }else{
                                wp_delete_attachment($attachment_id, true);
                                delete_post_thumbnail($post_id);
                            }
                        }
                    }
                    
                    $count++;

                    if ($count >= count($matches[1])) {
                        break;
                    }
                }
            }
        }
    }

    public function create_img($url, $slug){
        if (!wp_http_validate_url($url)) {
            return false;
        }

        $upload_dir = wp_upload_dir();
        $info = pathinfo($url);
        $ext = strtolower($info['extension']);
        $path = $upload_dir['path'] . '/' . $slug . '.' . $ext;

        $url_info = parse_url($url);
        $current_host = $_SERVER['HTTP_HOST'];

        if (isset($url_info['host']) && $url_info['host'] === $current_host) {
            if (file_exists($path)) {
                return $path;
            }else{
                return false;
            }
        }else{
            if (!function_exists('download_url')){
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            $min_width = isset($this->options['min_width'])?$this->options['min_width']:400;
            $max_width = isset($this->options['max_width'])?$this->options['max_width']:0;

            $tmp = download_url($url);
            if(is_wp_error($tmp)){
                return false;
            }
            
            $options = [
                'http' => [
                    'method' => 'GET',
                    'timeout' => 0.2,
                ],
            ];
            $context = stream_context_create($options);
            $size = @getimagesize($url, $context);

            if ($size && is_array($size) && count($size) >= 2) {
                if ($size[0] < $min_width) {
                    @unlink($tmp);
                    return false;
                }
            }else{
                @unlink($tmp);
                return false;
            }

            if ($max_width > 0 && $size[0] > $max_width) {
                $editor = wp_get_image_editor($tmp);
                if (!is_wp_error($editor)) {
                    if (method_exists($editor, 'resize')) {
                        $editor->resize($max_width, 0);
                        $resized_file = $editor->save();
                        $tmp = $resized_file['path'];
                    }
                }
            }

            if (copy($tmp, $path)) {
                @unlink($tmp);
                return $path;
            }else{
                @unlink($tmp);
                return false;
            }
        }
    }

}





