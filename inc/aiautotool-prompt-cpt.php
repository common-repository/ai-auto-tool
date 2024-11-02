<?php

class AIautotool_Prompt_CPT extends rendersetting{
    public  $active = true;
    public  $active_option_name = 'Aiautotool_prompt_manager_active';
    public $aiautotool_config_settings;
    public  $usage_option_name = 'prompt_manager_usage';
   
    public  $icon = '<i class="fa-brands fa-cloudflare"></i>';
    private $client = null;
    public $notices = [];
    public $limit = AIAUTOTOOL_FREE;
    private $plan_limit_aiautotool ;
    public $name_plan ;
    public $config = array();
    public $notice ;
    public $aiautotool_setting_cloudflare_image;

     public function __construct() {
        $this->name_plan =  __('Prompt Manager','ai-auto-tool');
        $this->plan_limit_aiautotool =  'plan_limit_aiautotool_'.$this->active_option_name;
       
        
        $this->notice = new aiautotool_Warning_Notice();
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
        add_action('init', [$this, 'register_prompt_cpt']);
        add_action('admin_menu', [$this, 'add_prompt_to_menu']);

        add_action('add_meta_boxes', [$this, 'add_metabox']);
        add_action('save_post', [$this, 'save_metabox']);
        add_action('wp_ajax_aiautotool_load_customer_prompt_properties', [$this, 'aiautotool_load_customer_prompt_properties']);
        
    }


    public function aiautotool_load_customer_prompt_properties() {
        
        $arr_replace_prompt = ['%TITLE%','%CONTENT%','%AUTHOR%','%CATEGORIES_NAME%','%TAGS_NAME%'];
        $prompt_id = isset($_POST['prompt_id']) ? sanitize_text_field($_POST['prompt_id']) : '';
        $postprompt = get_post($prompt_id);
        if (!empty($prompt_id)) {
           $params = get_post_meta($prompt_id, '_aiautotool_prompt_params', true);
           $arr = array();
           $arr['prompt_content'] = esc_html($postprompt->post_content);
           $arr['prompt_params'] = $params;
           $arr['prompt_arr_replace'] = esc_html($arr_replace_prompt);
           echo wp_send_json_success($arr);
           

           

            wp_die(); 
        }

        wp_die('Invalid prompt ID'); 
    }
    public function render_select_prompt(){
        $allprompt = get_option('aiautotool_prompt_options',array());
        $artcle_prompt = '';
        if(isset($allprompt['aiautotool_prompt_artcile']))
        {
            $artcle_prompt = $allprompt['aiautotool_prompt_artcile'];
        }
        ?>
        <p for="customer_prompt" class="ft-note"><i class="fa-solid fa-lightbulb"></i> Prompt : access code : %%title%% , %%lang%%, %%blog%%, %%cate%%, %%content%%</p>
        <input type="hidden" id="aiautotool_original_prompt_content" value="">

          <textarea id="aiautotool_prompt" name="aiautotool_prompt" class="ft-code-textarea" rows="5" style="height:200px" placeholder="Enter Prompt"><?php echo esc_html($artcle_prompt); ?></textarea>
         <p for="customer_prompt" class="ft-note"><i class="fa-solid fa-lightbulb"></i> Select Your Prompts in List: <?php echo '<a href="' . esc_url(admin_url('post-new.php?post_type=aiautotool_prompts')) . '" class="button">' . esc_html(__('Add Prompt','ai-auto-tool')) . '</a>'; ?></p>
                                        <select id="customer_prompt" name="customer_prompt">
                                            <option value="">Select Prompt</option>
                                            <?php
                                            $args = array(
                                                'post_type' => 'aiautotool_prompts',
                                                'posts_per_page' => -1,
                                                'post_status' => 'publish',
                                            );
                                            $prompts = new WP_Query($args);
                                            if ($prompts->have_posts()) {
                                                while ($prompts->have_posts()) {
                                                    $prompts->the_post();
                                                    ?>
                                                    <option value="<?php the_ID(); ?>"><?php the_title(); ?></option>
                                                    <?php
                                                }
                                                wp_reset_postdata();
                                            }
                                            ?>
                                        </select>
                                        <div id="prompt_properties"></div>
                                         <script type="text/javascript">
                                            jQuery(document).ready(function($) {
        
        $('#customer_prompt').change(function() {
            var selectedPrompt = $(this).val(); 

            
            if (selectedPrompt) {
                $.ajax({
                    url: ajaxurl, 
                    type: 'POST',
                    data: {
                        action: 'aiautotool_load_customer_prompt_properties',
                        prompt_id: selectedPrompt
                    },
                    success: function(response) {
                        if(response.success){
                            var data = response.data;
                            console.log(data.prompt_content);
                            $('#aiautotool_prompt').val(data.prompt_content);
                            $('#aiautotool_original_prompt_content').val(data.prompt_content);
                             $('#prompt_properties').empty();
                             
                        }
                        
                    },
                    error: function() {
                        aiautotool_alert_error('Có lỗi xảy ra khi tải thuộc tính.');
                    }
                });
            }
        });

    });

   

                                        </script>
        <?php
      
                    
                    
    }
    public static function register_prompt_cpt() {
        $labels = [
            'name'               => __('Prompts', 'ai-auto-tool'),
            'singular_name'      => __('Prompt', 'ai-auto-tool'),
            'menu_name'          => __('Prompts', 'ai-auto-tool'),
            'name_admin_bar'     => __('Prompt', 'ai-auto-tool'),
            'add_new'            => __('Add New', 'ai-auto-tool'),
            'add_new_item'       => __('Add New Prompt', 'ai-auto-tool'),
            'new_item'           => __('New Prompt', 'ai-auto-tool'),
            'edit_item'          => __('Edit Prompt', 'ai-auto-tool'),
            'view_item'          => __('View Prompt', 'ai-auto-tool'),
            'all_items'          => __('All Prompts', 'ai-auto-tool'),
            'search_items'       => __('Search Prompts', 'ai-auto-tool'),
            'not_found'          => __('No Prompts found.', 'ai-auto-tool'),
            'not_found_in_trash' => __('No Prompts found in Trash.', 'ai-auto-tool'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'aiautotool_prompts',
            'query_var'          => true,
            'rewrite'            => ['slug' => 'aiautotool_prompts'],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => MENUSUBPARRENT,
            'supports'           => ['title', 'editor'],
        ];

        register_post_type('aiautotool_prompts', $args);
    }
    public static function add_prompt_to_menu() {

        add_submenu_page(
                MENUSUBPARRENT,       
                '<i class="fa-solid fa-link"></i>Prompts Manager',          
                '<i class="fa-solid fa-link"></i>Prompts Manager',         
                'manage_options',             
                'edit.php?post_type=aiautotool_prompts'
            );
        
    }

    public function render_setting() {
        if($this->active!="true"){
            return '';
        }


    }

    public function render_tab_setting() {
        if($this->active=="true"){

         echo '<button href="#tool-cloudflareai" class="nav-tab sotab"> '.esc_attr($this->icon).esc_html__(' Prompt Manager','ai-auto-tool').'</button>';
        }
    }

    public function render_feature() {

       $autoToolBox = new AutoToolBox($this->icon.' '.$this->name_plan, __('Prompt Manager.','ai-auto-tool'), "#", $this->active_option_name, $this->active,plugins_url('../images/logo.svg', __FILE__));

        echo ($autoToolBox->generateHTML());
    }

    public static function add_metabox() {
        add_meta_box(
            'aiautotool_prompt_params',
            __('Prompt Parameters', 'ai-auto-tool'),
            [self::class, 'render_metabox'],
            'aiautotool_prompts',
            'normal',
            'high'
        );
    }

    public static function render_metabox($post) {
        wp_nonce_field('aiautotool_prompt_save_meta', 'aiautotool_prompt_meta_nonce');

        $params = get_post_meta($post->ID, '_aiautotool_prompt_params', true);
        
        echo '<div id="aiautotool-prompt-params-wrap">';
        if (!empty($params)) {
            foreach ($params as $param) {
                echo '<div class="aiautotool-prompt-param">';
                echo '<label>' . esc_html($param['label']) . '</label>';
                echo '<input type="text" name="aiautotool_prompt_params[]" value="' . esc_attr($param['value']) . '" />';
                echo '<select name="aiautotool_prompt_param_types[]">
                        <option value="input_text"' . selected($param['type'], 'input_text', false) . '>Input Text</option>
                        <option value="textarea"' . selected($param['type'], 'textarea', false) . '>Textarea</option>
                      </select><button type="button" class="aiautotool-remove-param button">Remove</button>';
                echo '</div>';
            }
        }
        echo '</div>';
        echo '<button type="button" id="aiautotool-add-param" class="button">' . esc_html(__('Add Parameter', 'ai-auto-tool')) . '</button>';
        ?>
        <script type="text/javascript">jQuery(document).ready(function ($) {
    let paramIndex = 0;

    $('#aiautotool-add-param').on('click', function () {
        paramIndex++;
        const newParam = `
            <div class="aiautotool-prompt-param">
                <label>Code : %code%</label>
                <input type="text" name="aiautotool_prompt_params[]" value="" placeholder="%title%" />
                <select name="aiautotool_prompt_param_types[]">
                    <option value="input_text">Input Text</option>
                    <option value="textarea">Textarea</option>
                </select>
                <button type="button" class="aiautotool-remove-param button">Remove</button>
            </div>
        `;
        $('#aiautotool-prompt-params-wrap').append(newParam);
    });

    $(document).on('click', '.aiautotool-remove-param', function () {
        $(this).closest('.aiautotool-prompt-param').remove();
    });
});
</script><?php
    }


    public static function save_metabox($post_id) {
        
       if (isset($_POST['aiautotool_prompt_meta_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aiautotool_prompt_meta_nonce'])), 'aiautotool_prompt_save_meta')) {
            
           
            if (isset($_POST['post_type']) && 'aiautotool_prompts' !== sanitize_text_field(wp_unslash($_POST['post_type']))) {
               
                return;
            }
            

            if (isset($_POST['aiautotool_prompt_params']) && isset($_POST['aiautotool_prompt_param_types'])) {
                
                if (is_array($_POST['aiautotool_prompt_params']) && is_array($_POST['aiautotool_prompt_param_types'])) {
                    $params = array();
                    $param_values = array_map('sanitize_text_field', wp_unslash($_POST['aiautotool_prompt_params']));
                    $param_types = array_map('sanitize_text_field', wp_unslash($_POST['aiautotool_prompt_param_types']));

                    
                    if (count($param_values) === count($param_types)) {
                        foreach ($param_values as $index => $value) {
                            $params[] = array(
                                'label' => $value,
                                'value' => $value,
                                'type' => isset($param_types[$index]) ? $param_types[$index] : '',
                            );
                        }

                        update_post_meta($post_id, '_aiautotool_prompt_params', $params);
                    } else {
                        
                    }
                } else {
                   
                }
            } else {
                
                delete_post_meta($post_id, '_aiautotool_prompt_params');
            }

                
            
        } else {
           
            return;
        }
    }

}



