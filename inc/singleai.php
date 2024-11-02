<?php
defined('ABSPATH') or die();
class AIAutoToolsinglepost  extends rendersetting{

    public  $active = true;
    public  $active_option_name = 'AIAutoToolsinglepost_active';
     public  $usage_option_name = 'AI_post_usage';
    public  $icon = '<i class="fa-solid fa-robot"></i>';

    public $limit = AIAUTOTOOL_FREE;
    private $plan_limit_aiautotool ;
    public $name_plan ;
    public $config = array();
    public $notice ;
    public function __construct() {
        $this->name_plan =  __('Ai Post','ai-auto-tool');
        $this->plan_limit_aiautotool =  'plan_limit_aiautotool_'.$this->active_option_name;
        
        $this->notice = new aiautotool_Warning_Notice();

         $this->active = get_option($this->active_option_name, true);
        if ($this->active=='true') {
            $this->init();
        }
        add_action('wp_ajax_update_active_option_canonical_'.$this->active_option_name, array($this, 'update_active_option_callback'));
        add_action('wp_ajax_nopriv_update_active_option_canonical_'.$this->active_option_name, array($this, 'update_active_option_callback'));

        
      

    }

    public function aiautotool_meta_box() {
            $customPostTypes = get_post_types(['public' => true, '_builtin' => false], 'objects');
            $customPostTypeNames = array_keys($customPostTypes);
            foreach ($customPostTypeNames as $postType) {
                add_meta_box(
                    'aiautotool-meta-box', 
                    'Ai Auto Tool', 
                    array($this, 'aiautotool_meta_box_callback'), 
                    $postType, 
                    'side', 
                    'high' 
                );
            }
            add_meta_box(
                'aiautotool-meta-box', 
                'Ai Auto Tool ', 
                array($this,'aiautotool_meta_box_callback'), 
                array('post', 'page'),
                'side', 
                'high' 
            );

            add_meta_box(
                'aiautotool-aireview-meta-box', 
                'Ai Review', 
                array($this,'aiautotool_aireview_meta_box_callback'), 
                array('post', 'page'),
                'side', 
                'high' 
            );
        }

        public function aiautotool_aireview_meta_box_callback($post) {
            // Retrieve any saved meta box data
            $custom_data = get_post_meta($post->ID, 'custom_data_key', true);
            ?>
            <span class=" aiautotool_btn_v1 aiautotool_btn_v15 btn_bardWriter btn_summary" id="seoreview"  ><span class="icon"><i class="fa-solid fa-list-check"></i>  </span><span>SEO Review by AI </span></span>
            <table id="vertical-table"></table>
            <div id="table-container">
                
            </div>
            <script type="text/javascript">
                 jQuery(document).ready(function($) {
    $('#seoreview').click(function() {
        // Lấy tiêu đề bài viết
        var postTitle = $('#title').val();
        var post_language = get_lang();
        if (languageCodes.hasOwnProperty(post_language)) {
            langcheck = languageCodes[post_language];
        }
        // Kiểm tra xem có sử dụng trình soạn thảo TinyMCE không
        if (typeof tinyMCE !== "undefined") {
            var editor = tinyMCE.activeEditor;
            if (editor) {
                console.log('Có trình soạn thảo TinyMCE');
                // Lấy nội dung từ trình soạn thảo
                var postContent = editor.getContent();
                // Kiểm tra xem tiêu đề và nội dung có rỗng không
                if (postTitle.trim() !== '' && postContent.trim() !== '') {
                    // Tiến hành gọi API bằng AJAX
                    showLoading('Bot Analyzing....');
                    $.ajax({
                        type: 'POST',
                        url: aiautotool_js_setting.ajax_url,
                        data: {
                            action: 'seoreview_ajax',
                            security: aiautotool_js_setting.security, // Sử dụng nonce tương ứng
                            title: postTitle,
                            content: postContent,
                            lang:langcheck
                        },
                        dataType: 'json',
                        success: function(response) {
                            // Xử lý kết quả từ API nếu cần
                            console.log(response);
                            if(response.data){
                                jQuery('#table-container').html(response.data);
                            }
                            
                            hideLoading();

                        },
                        error: function(xhr, status, error) {
                            hideLoading();
                            console.error('Error ' + error);
                        }
                    });
                } else {
                    if(Swal){
                         Swal.fire({
                                  title: 'Error!',
                                  text: 'Title and Content is Null!!.',
                                  icon: 'error',
                                  confirmButtonText: 'Close'
                                });
                     }else{
                        alert('Title and Content is Null!!.');
                     }
                    console.log('Title and Content is Null!!.');
                }
            } else {
                if(Swal){
                         Swal.fire({
                                  title: 'Error!',
                                  text: 'No TinyMCE!.',
                                  icon: 'error',
                                  confirmButtonText: 'Close'
                                });
                     }else{
                        alert('No TinyMCE.');
                     }
                console.log('No TinyMCE ');
            }
        } else {
            if(Swal){
                         Swal.fire({
                                  title: 'Error!',
                                  text: 'TinyMCE not work.',
                                  icon: 'error',
                                  confirmButtonText: 'Close'
                                });
                     }else{
                        alert('TinyMCE not work!!.');
                     }
            console.log('TinyMCE not work.');
        }
    });

     });
   
            </script>
            <?php
        }
        public function aiautotool_meta_box_callback($post) {
            // Retrieve any saved meta box data
            $custom_data = get_post_meta($post->ID, 'custom_data_key', true);

            // Output the HTML markup for your custom meta box
            ?>
            <div class="aiautotool-modal-container">
        <div class="aiautotool-modal-overlay"></div>
        <div class="aiautotool-modal-content">
            <h2><?php esc_html_e('All tool for AI Post','ai-auto-tool');  ?></h2>
            <p>
                <span class=" aiautotool_btn_v1 aiautotool_btn_v16 btn_bardWriter btnoutline" id="btn_outline"  onclick="changeType('Outline')"><span class="icon"><i class="fa-solid fa-lightbulb"></i>  </span><span>Outline </span></span>
                
                <span class=" aiautotool_btn_v1 aiautotool_btn_v11 btn_bardWriter btn_intro" id=""  onclick="changeType('Introduction')"><span class="icon"><i class="fa-solid fa-info"></i>  </span><span>Introduction </span></span>
                <span class=" aiautotool_btn_v1 aiautotool_btn_v13 btn_bardWriter btn_conclusion" id=""  onclick="changeType('Conclusion')"><span class="icon"><i class="fa-solid fa-check-double"></i>  </span><span>Conclusion </span></span>
                <span class=" aiautotool_btn_v1 aiautotool_btn_v12 btn_bardWriter btn_faq" id=""  onclick="changeType('FAQ')"><span class="icon"><i class="fa-solid fa-question"></i>  </span><span>Create FAQ </span></span>
                
            </p>
            <div class=" aiautotool_container aiautotool_modal_content">
                <div id="outline">
                    <h3 id="current_type">Suggest Outline By Ai Auto Tool</h3>
                        <input type="hidden" name="type" id="type" value="Outline">
                    <div class="aiautotool_input2">
                      <input type="text" name="moretool" class="aiautotool_input2_input" placeholder="Enter your Keyword for ">
                      <button class="aiautotool_input2_button" id="moretool" type="submit">Create </button>  

                    </div>
                </div>
                <div id="Summary" style="display:none">
                    <div class="aiautotool_input2">
                      <input type="text" class="aiautotool_input2_input" placeholder="Enter your Keyword for ">
                      <button class="aiautotool_input2_button" type="submit" id="createButton">Create </button>  
                    </div>
                </div>
                
                <div id="Summary"></div>
                <div id="Introduction"></div>
                <div id="Conclusion"></div>
                <div id="Create FAQ"></div>
                <script type="text/javascript">

                  



                    function changeType(newType) {
                        document.getElementById('current_type').innerText = newType;
                        document.getElementsByName('type')[0].value = newType;
                        const createButton = document.getElementById('moretool');
                        createButton.innerText = 'Create ' + newType;
                    }
                    jQuery(document).ready(function(){
                        jQuery("#moretool").click(function(){
                            var type = document.getElementsByName('type')[0].value;
                            var moretoolText = jQuery("input[name='moretool']").val();
                            var post_language = get_lang();
                            var langcheck = '';
                            if(moretoolText.trim() === "") {
                                if(Swal){
                                     Swal.fire({
                                              title: 'Error!',
                                              text: 'Please fill Keyword in request.',
                                              icon: 'error',
                                              confirmButtonText: 'Close'
                                            });
                                 }else{
                                    alert('Please fill Keyword in request.');
                                 }
                            } else {


                                open_box_aiautotool();
                            jQuery('.aiautotool-modal-close').click();

                            if (languageCodes.hasOwnProperty(post_language)) {
                               langcheck = languageCodes[post_language];
                              
                            } 
                            var divId = "outbard";

                            switch(type) {
                                case "Outline":
                                    text = 'Create an outline for a {'+moretoolText+'} , with main topics and subtopics organized in a hierarchical numbered list format.'; 
                                    

                                    sendbardToServer(text, divId,'writemore',langcheck);
                                    break;
                                case "Summary":
                                    // Xử lý khi type là Summary
                                    sendbardToServer(moretoolText, divId, langcheck); // Điều chỉnh hàm và tham số phù hợp
                                    break;
                                case "Introduction":
                                    text = 'Write an introduction for the article title ['+moretoolText+'] , Include an introduction of 50 to 100 words.'; 
                                    sendbardToServer(text, divId,'writemore', langcheck); // Điều chỉnh hàm và tham số phù hợp
                                    break;
                                case "Conclusion":
                                    // Xử lý khi type là Conclusion
                                    text = 'Viết phần kết bài cho bài viết ['+moretoolText+']. ';
                                    sendbardToServer(text , divId,'writemore', langcheck); // Điều chỉnh hàm và tham số phù hợp
                                    break;
                                case "FAQ":
                                    // Xử lý khi type là Create FAQ
                                    text = 'I want to create an FAQ section for my ['+moretoolText+']. Can you help me come up with a list of frequently asked questions and answers that will provide helpful information for my customers?';
                                    sendbardToServer(text, divId,'writemore', langcheck); // Điều chỉnh hàm và tham số phù hợp
                                    break;
                                default:
                                    // Xử lý khi type không khớp với bất kỳ trường hợp nào
                                    console.error("Unknown type: " + type);
                            }
                            
                            
                            }
                        });
                    });
                </script>
            </div>
            <button class="aiautotool-modal-close"><i class="fa-regular fa-rectangle-xmark"></i></button>
        </div>
    </div>
            <div class="aiautotool_box1 ">
                        <div class="aiautotool_box_head">
                               <img src="<?php echo esc_url(plugins_url('../images/logo.svg', __FILE__)); ?>" width="16px" height="16px">
                            <?php esc_html_e('Ai auto Tool','ai-auto-tool'); ?> </div>
                                <style type="text/css">
                                    

                                </style>
                                <div class="askassistant" >
                                    <textarea id="promptask" rows="2" placeholdertext="<?php esc_html_e('Type your promp...','ai-auto-tool');?>" placeholder="<?php esc_html_e('Type your promp...','ai-auto-tool');?>"></textarea>
                                   <div class="select-and-button">
                                        <select id="askAI">
                                            <option value="chatgpt">Chatgpt</option>
                                            <option value="gemini">Gemini AI</option>
                                        </select>
                                        <button id="askprompt"><?php esc_html_e('<i class="fa-solid fa-robot"></i> Ask Assistant','ai-auto-tool'); ?></button>
                                    </div>
                                </div>
                                <div class="aiautotool_form">
                                    <div class="aiautotool_tab">
                                        <button type="button" data-tab="aiContentTab" class="tablinks" onclick="openTab(event, 'aiContentTab')"><?php esc_html_e('AI Content','ai-auto-tool'); ?></button>

                                        <button type="button" data-tab="imagesTab" class="tablinks" onclick="openTab(event, 'imagesTab')"><?php esc_html_e('Images','ai-auto-tool'); ?></button>
                                        <button type="button" data-tab="videoTab" class="tablinks" onclick="openTab(event, 'videoTab')"><?php esc_html_e('Video','ai-auto-tool'); ?></button>
                                        
                                    </div>

                                    <!-- AI Content Tab -->
                                    <div id="aiContentTab" class="tabcontent">
                                        <div id="info_content" placeholdertext="<?php esc_html_e('Input title for post Then Click gen Article,Select a phrase and click the Write button to use this feature','ai-auto-tool'); ?>"  ></div>
                                        
                                        <div class="loadingprocess p-5 aiautotool-text-center div_proccess_1 d-none div_proccess">
                                        <div id="loading-icon" class="loader" style="display:block"></div> <?php esc_html_e('Start socket','ai-auto-tool'); ?> <span id="proccess_1" class="process_loading badge badge-soft-primary"></span>
                                        </div>
                                        <div class="div_proccess_error aiautotool-text-center div_proccess d-none">
                                        <div class="aiautotool-pt-5"> <span><?php esc_html_e('Start socket Error, Please F5 to reload.','ai-auto-tool'); ?></span></div>
                                        
                                        </div>
                                        <!-- Content for AI Content tab goes here -->
                                        <div id="outbard">
                                            
                                            <center>
                                            <?php esc_html_e('Select a phrase and click the <b>Write</b> button to use this feature','ai-auto-tool'); ?>
                                            <br>
                                            <img src="<?php echo esc_url(plugins_url('../images/find1.png', __FILE__)); ?>" width="150px"  /></center></div>
                                        <button class="btn btnaddpost aiautotool_button" style="display:none" ><?php esc_html_e('Add To Post','ai-auto-tool'); ?></button>
                                    </div>

                                    <!-- Images Tab -->
                                    <div id="imagesTab" class="tabcontent">
                                        <div class="infodiv">
                                        <div id="info_img" placeholdertext="<?php esc_html_e('Select a phrase and click the Find Image button to use this feature','ai-auto-tool'); ?>"  ></div>
                                        <center>
                                            <?php esc_html_e('Select a phrase and click the <b>Find Image</b> button to use this feature','ai-auto-tool'); ?>
                                            <br>
                                            <img src="<?php echo esc_url(plugins_url('../images/find1.png', __FILE__)); ?>" width="150px"  /></center>
                                        </div>
                                        
                                        <div id="img_list_find" class="img_list_find"></div>
                                        <!-- Content for Images tab goes here -->
                                    </div>

                                    <!-- Images Tab -->
                                    <div id="videoTab" class="tabcontent">
                                        <div class="infodiv">
                                        <div id="info_img" placeholdertext="<?php esc_html_e('Select a phrase and click the Find Image button to use this feature','ai-auto-tool'); ?>"  ></div>
                                        <center>
                                            <?php esc_html_e('Select a phrase and click the <b>Find Image</b> button to use this feature','ai-auto-tool'); ?>
                                            <br>
                                            <img src="<?php echo esc_url(plugins_url('../images/find1.png', __FILE__)); ?>" width="150px"  /></center>
                                        </div>
                                        
                                        <div id="video_list_find" class="video_list_find"></div>
                                        <!-- Content for Images tab goes here -->
                                    </div>

                                    
                                </div>
                            </div>
            <?php
        }
    private function aiautotool_has_plugin_data() {
        return get_option($this->plan_limit_aiautotool) !== false;
    }
    private function aiautotool_initialize_plugin_data() {
        $current_date = date('Y-m-d');
        
        $expiration = strtotime('+1 month', strtotime($current_date));
        $expiration = date('Y-m-d', $expiration);
        update_option($this->plan_limit_aiautotool, array( 'start_date' => $current_date,'expiration'=>$expiration));
    }
    public function aiautotool_check_post_limit() {
        $stored_data = get_option($this->plan_limit_aiautotool, array());
        $current_date = date('Y-m-d');
        
        if ($this->is_new_month($current_date, $stored_data['start_date'])) {
            // Nếu đã qua một tháng, đặt lại số lượng bài đăng và ngày bắt đầu

            $expiration = strtotime('+1 month', strtotime($current_date));
            $expiration = date('Y-m-d', $expiration);
            update_option($this->plan_limit_aiautotool, array( 'start_date' => $current_date,'expiration'=>$expiration));
            update_option($this->usage_option_name, 0,null, 'no');
        }

        
    }

    private function is_new_month($current_date, $start_date) {
        $current_month = date('m', strtotime($current_date));
        $start_month = date('m', strtotime($start_date));

        return ($current_month != $start_month);
    }
    public function aiautotool_checklimit($config) {
        // return ($config['number_post'] !== -1 && $config['number_post'] >= $config['usage']) || $config['number_post'] === -1;
            return $this->check_quota();
    }

    public function aiautotool__update_usage() {
        $this->aiautotool_check_post_limit();
        // Get the current usage value
        $current_value = get_option($this->usage_option_name, 0);

        // Increment the value by 1
        $new_value = $current_value + 1;

        // Update the option with the new value and set autoload to 'no'
        update_option($this->usage_option_name, $new_value, 'no');

        // Optionally, return the updated value
        return $new_value;
    }
    
    public function add_aiautotool_aipost_js() {
         wp_register_script('kct_aipost', plugin_dir_url( __FILE__ ) .'../js/aipost.js', array('jquery'), '1.2'.rand(), true);
         wp_localize_script( 'kct_aipost', 'aipost',array( 'ajax_url' => admin_url( 'admin-ajax.php'),'config'=>$this->config,'security' => wp_create_nonce('aiautotool_aipost_nonce') ));
         wp_enqueue_script('kct_aipost');
    }
    
    public function aiautotool_update_usage() {
        $this->aiautotool_check_post_limit();
        
        // Get the current usage value
        $current_value = get_option($this->usage_option_name, 0);

        // Increment the value by 1
        $new_value = $current_value + 1;
        if($this->config['number_post']!=-1){
            if($this->config['number_post'] > $new_value){
                update_option($this->usage_option_name, $new_value, 'no');
            }
        }else{
            update_option($this->usage_option_name, $new_value, 'no');
        }
        
        
        return $new_value;
    }
    public function render_plan(){
         if ($this->active=='true') {
             $quota = $this->config['number_post'] == -1 ? 'Unlimited' : esc_html($this->config['number_post']);
echo '<p>' . esc_html($this->icon) . ' ' . esc_html($this->name_plan) . ': <strong> Usage: ' . esc_html($this->config['usage']) . '</strong></p>';

       

        }
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
        $configs = get_option($this->plan_limit_aiautotool, array());
         

        
        if (!$this->aiautotool_has_plugin_data()) {
            $this->aiautotool_initialize_plugin_data();
            $configs = get_option($this->plan_limit_aiautotool, array());
        }
        if(isset($configs['expiration'])){
            $this->config  = array(
                'number_post'=>$this->limit,
                'usage'=>get_option($this->usage_option_name, 0, 'no'),
                'time_exprice'=>$configs['expiration']
            );
        }else{
            $this->config  = array(
                'number_post'=>$this->limit,
                'usage'=>get_option($this->usage_option_name, 0, 'no'),
                'time_exprice'=>''
            );
        }
        

        if($this->is_premium()->get_plan_name()=='aiautotoolpro'||$this->is_premium()->get_plan_name()=='premium'){
           
            $this->config  = array(
                'number_post'=>-1,
                'usage'=>get_option($this->usage_option_name, 0, 'no'),
                'time_exprice'=>$this->is_premium()->_get_license()->expiration
            );
        }
        add_action('admin_menu', array($this, 'add_menu'));

        add_action('admin_init', array($this, 'aiautotool_check_post_limit'));
        
        add_action('wp_ajax_aiautotool_get_slug', array($this, 'aiautotool_get_slug'));
        add_action('wp_ajax_nopriv_aiautotool_get_slug', array($this, 'aiautotool_get_slug'));

        add_action('wp_ajax_bard_content', array($this, 'bard_content_callback'));
        add_action('wp_ajax_nopriv_bard_content', array($this, 'bard_content_callback'));


        add_action('wp_ajax_save_post_data', array($this, 'save_post_data'));
        add_action('wp_ajax_nopriv_save_post_data', array($this, 'save_post_data'));

         add_action( 'admin_footer', array( $this, 'add_aiautotool_aipost_js' ) );

        add_action('wp_ajax_update_usage', array($this, 'aiautotool_update_usage_callback'));
        add_action('wp_ajax_nopriv_update_usage', array($this, 'aiautotool_update_usage_callback'));


             add_action('wp_ajax_seoreview_ajax', array($this, 'aiautotool_seoreview_ajax_callback'));
        add_action('wp_ajax_nopriv_seoreview_ajax', array($this, 'aiautotool_seoreview_ajax_callback'));
        add_action('add_meta_boxes', array( $this, 'aiautotool_meta_box' ));

        add_action('wp_ajax_aiautotool_suggest_tag', array($this, 'aiautotool_suggest_tag_callback'));
   

    }
      public function aiautotool_suggest_tag_callback() {
            // Kiểm tra nonce
            if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'aiautotool_nonce' ) ) {
                wp_send_json_error( 'Invalid nonce' );
            }
            $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
            $lang = isset($_POST['lang']) ? sanitize_text_field($_POST['lang']) : $this->setting['lang'];
            
            $tag = $this->aiautotool_AIprecreatetagname($title,$lang);
            if(isset($tag)){
                wp_send_json_success( $tag );
            }else{
                wp_send_json_error( 'no tag' );
            }
            
        }

    public function aiautotool_AIprecreatetagname($title, $lang) {
        $bardGenContent = new BardGenContent();
        $question = "The most important: The results must only be in JSON format, the response must be in the SAME LANGUAGE as the original text (text between \"======\").
    Create %%NUMMBERCOMMENT%% tag for the article.
    tag only 2 or 3 word
    tag must have a ====== %%TITLTE%%  ======  intellectual level.
    The post\'s content is between \"=========\". 
    The results must only be in JSON format, with this exact format, you have to fill empty values,Each item in tag has the form { \"tag\": \"\" }
    ";
        $question = str_replace('%%TITLTE%%', $title, $question);
        $question = str_replace('%%NUMMBERCOMMENT%%', rand(8, 15), $question);

        $json = $bardGenContent->bardcontentmore($question, $lang);

        return $this->aiautotool_fixjsonreturn($json);
    }
    
     public function aiautotool_seoreview_ajax_callback() {
        // Kiểm tra xác thực nonce để đảm bảo an toàn
        //check_ajax_referer('aiautotool_nonce', 'security');

        // Lấy dữ liệu gửi từ client
        $post_title = $_POST['title'];
        $post_content = $_POST['content'];
        
        $lang = $_POST['lang'];

        $question = '
        Tên của bạn bây giờ sẽ là AIAutoTool.
        Mở đầu kết quả trả về bạn phải tự giới thiệu về mình là : AIAutoTool, sau đây là phần đánh giá bài viết của bạn.
        Ngôn ngữ trả về phải giống với ngôn ngữ có trong nội dung (Nội dung bài viết nằm giữa =======)
        hãy thống kê từ khoá chính, từ khoá phụ, dự đoán từ khoá cùng vị trí có thể lên top google trong tương lai đối với nội dung bài viết có trong nội dung bài viết sau.
        Dựa vào bảng checklist dưới đây, đánh giá bài viết có (nội dung bài viết nằm giữa =======)
    - title Chứa từ khóa chính (khoảng 5-7 từ)
    - tiêu đề Dưới 70 ký tự (bao gồm cả khoảng trắng)
    - Sử dụng giọng văn Thu hút và khơi gợi sự tò mò
    - Sử dụng chữ hoa và số đếm hợp lý, các con số thống kê nên được đưa ra.
    - Mô tả ngắn của bài viết nên Chứa từ khóa chính (khoảng 10-15 từ).Dưới 160 ký tự (bao gồm cả khoảng trắng)
    - Tóm tắt nội dung bài viết một cách súc tích và hấp dẫn
    - Sử dụng Call to Action (CTA)
    - Cung cấp thông tin hữu ích và giá trị cho người đọc
    - Đáp ứng đúng ý định tìm kiếm của người dùng
    - Sử dụng từ khóa chính một cách tự nhiên và mật độ phù hợp (khoảng 1-2%)
    - Chia bài viết thành các đoạn ngắn, sử dụng headings và subheadings hợp lý
    - Bổ sung hình ảnh, video và infographic để minh họa cho nội dung
    - Sử dụng các liên kết nội bộ và liên kết ngoài chất lượng
    - Chứa từ khóa chính và biến thể của từ khóa mục tiêu.
    - Có tổ chức logic, dễ đọc và dễ tiếp cận.
    - Chứa thông tin giá trị và hữu ích cho người đọc.
    - Sử dụng các tiêu đề con (H2, H3) để phân đoạn nội dung.
    - Bài viết cần được kiểm tra lỗi chính tả và ngữ pháp
Trên đây là những yêu cầu tối thiểu cho một bài viết blog, bạn hãy đóng vai trò người SEOER đánh giá nội dung sau đây có được bao nhiêu điều thoả mãi checklist seo ở trên. Nội dung có trong bài viết sau:. ======= %%CONTENT%% =======';
        //$question = 'Please review the above article and HTML code so that it has [No. Headings] headings using the [Heading Tag] HTML tag. Revise the text in the ['.$lang.'] language. The Article is : %%CONTENT%%';
                // $question = str_replace('%%TITLTE%%',$post_title,$question);
                $question = str_replace('%%CONTENT%%',$post_content,$question);
                 $bardGenContent = new BardGenContent();
                 $json = $bardGenContent->bardcontentmore($question,$lang);
                 // print_r( $json);
                 // $newcontent = $this->aiautotool_fixjsonreturn($json);
                 if ($json) {
                    
                     wp_send_json_success($json);
                       wp_die(); 
                     // echo json_encode($newcontent);
                 }
        // Xử lý dữ liệu ở đây và trả về kết quả
        // echo json_encode(array('result' => 'Thành công'));
                 wp_send_json_error($newcontent);
        wp_die(); 
    }

    public function aiautotool_fixjsonreturn($result){
        $pattern = '/\{(?:[^{}]|(?R))*\}/'; 
        preg_match_all($pattern, $result, $matches);
        $arritem = array();
        // print_r($result);
        foreach ($matches[0] as $jsonString) {
            
            $decodedJson = json_decode($jsonString, true);
            
            
            if ($decodedJson !== null) {
                if(isset($decodedJson['tags']))
                {
                    foreach($decodedJson['tags'] as $item){
                        $arritem[] = $item;
                    }
                }else{
                    $arritem[] = $decodedJson;
                }
            } else {
                return null;
            }
        }
        // print_r($arritem);
        return $arritem;
    }

    function aiautotool_update_usage_callback() {
        // Kiểm tra Nonce
        check_ajax_referer('aiautotool_aipost_nonce', 'security');
        $this->aiautotool_update_usage();
        wp_send_json_success(array('success'=>true));
        wp_die();
    }

    function save_post_data() {
        // Check nonce for security
        check_ajax_referer('aiautotool_nonce', 'security');
        
        $languageCodes = $this->languageCodes;
        // Retrieve and sanitize form data
        $title = sanitize_text_field($_POST['aiautotool_title']);
        $slug = sanitize_text_field($_POST['aiautotool_slug']);
        $categories = array_map('absint', $_POST['post_category']); // Ensure categories are integers
        $tags = sanitize_text_field($_POST['aiautotool_tags']);
        $content = wp_kses_post($_POST['aiautotool_content']);

        //xu ly time 
        $publish_year = sanitize_text_field($_POST['publish_year']);
        $publish_month = sanitize_text_field($_POST['publish_month']);
        $publish_day = sanitize_text_field($_POST['publish_day']);
        $publish_hour = sanitize_text_field($_POST['publish_hour']);
        $publish_minute = sanitize_text_field($_POST['publish_minute']);
        $attachThumbnail = sanitize_text_field($_POST['custom-thumbnail-id']);

        $post_language = sanitize_text_field($_POST['post_language']);

        $publish_timestamp = strtotime("$publish_year-$publish_month-$publish_day $publish_hour:$publish_minute");

        $current_time = current_time('timestamp');
        if ($publish_timestamp <= $current_time) {
            
            $post_status = 'publish';
            $post_date = date('Y-m-d H:i:s', $publish_timestamp);
        } else {
            
            $post_status = 'future'; 
            $post_date = date('Y-m-d H:i:s', $publish_timestamp);
        }

        $post_data = array(
        'post_title' => $title,
        'post_name' => $slug,
        'post_content' => $content, 
        'post_status' => $post_status,
        'post_date' => $post_date,
        'post_category' => $categories,
        'tags_input' => $tags,
    );

        $post_id = wp_insert_post($post_data);

        $selectedLanguageName = 'Vietnamese';
             if(array_key_exists($post_language, $languageCodes)) {
                $selectedLanguageName = $languageCodes[$post_language];
                if(in_array('polylang/polylang.php', apply_filters('active_plugins', get_option('active_plugins')))){ 
                   pll_set_post_language($post_id, $post_language);
                }

               
            }
            update_post_meta($post_id, 'lang', $selectedLanguageName);

        // update content when save img and set thumb

        $html = stripslashes($content);
                    preg_match_all('/<img[^>]+src\s*=\s*["\']([^"\']+)["\']/i', $html, $matches);
                    $listimg1 =  $matches[1]; 
                    $imgUploaded = array();
                    
                     if (!empty($listimg1)){
                        foreach ($listimg1 as $post_image_url){
                            try {
                                            $image_url_new = $this->kct_aiautotool_save_image($post_image_url,$postname);
                                            if (!empty($image_url_new)){
                                                $imgUploaded[] = $image_url_new;
                                                if ($attachThumbnail == 0) {
                                                    $attachThumbnail = $image_url_new['attach_id'];
                                                }
                                                 
                                            }
                                        }catch (Exception $e) {
                                        }
                        }
                     }
                     foreach ($imgUploaded as $img){
                        $content = str_replace($img['url'],$img['baseurl'],$content);
                    }
                            
                    
                    if ($attachThumbnail != 0) {
                        set_post_thumbnail($post_id, $attachThumbnail);
                    }
                    $post_data = array(
                        'ID' => $post_id,
                        'post_title'=>$title,
                        'post_content' => $content,
                        'post_status'=>'publish'
                    );

                    $post_id = wp_update_post($post_data);

        if ($post_id) {
            $post_url = get_permalink($post_id);
            $edit_url = get_edit_post_link($post_id);
            // Post successfully created, send success response
             wp_send_json_success(array(
                'post_id' => $post_id,
                'post_url' => $post_url,
                'edit_url' => $edit_url,
                'message' => 'Post successfully saved and published!',
            ));
        } else {
            // Error in creating the post, send error response
            wp_send_json_error(array(
                'message' => 'Error saving and publishing the post.',
            ));
        }

        // Perform your data saving logic here (example: save to database)

        // Send a response back to the client
        wp_send_json_success(array('content' => $_POST));
        // or wp_send_json_error('Error saving data!'); in case of an error
    }

    public function kct_aiautotool_save_image($imgURL,$post_title){
            $image_name = basename( $imgURL );
            $filetype  = wp_check_filetype($image_name);
            $upload_dir = wp_upload_dir();
            
            $extension = $filetype['ext']?$filetype['ext']:"jpg";
            if (empty($extension)) $extension = "jpg";
            $unique_file_name = sanitize_title($post_title)."-".uniqid().".".$extension;
            $filename = $upload_dir['path'] . '/' . $unique_file_name;
            $baseurl = $upload_dir['baseurl'] .$upload_dir['subdir']. '/' . $unique_file_name;
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
            if ($rescode == 200  && filesize($filename) > 100){

                 $wp_filetype = wp_check_filetype(basename($filename), null);
                
                $attachment = array(
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title' => sanitize_file_name($post_title),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );
                
                $attach_id    = wp_insert_attachment($attachment, $filename);
                $imagenew     = get_post($attach_id);
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
            }else{
                return null;
            }
        }
    public function bard_content_callback(){

    	$languageCodes = $this->languageCodes;


         $bardGenContent = new BardGenContent();
    	 // $lang = 'Vietnamese';
    	 $title = sanitize_text_field($_POST['title']);
         $post_language = sanitize_text_field($_POST['post_language']);

         if(array_key_exists($post_language, $languageCodes)) {
                $lang = $languageCodes[$post_language];
            }
    	 $ac = sanitize_text_field($_POST['ac']);
    	 switch($ac){
    	 	case 'bardcontent':
    	 		$newcontent = $bardGenContent->bardcontentmore($title, $lang);
    	 		break;
    	 	case 'suggettitle':
                $allprompt = get_option('aiautotool_prompt_options',array());
                $prompt_suggest_title = $allprompt['aiautotool_prompt_suggest_title'];
                 $content='';
                 $cate = '';
                 $blogtitle = '';

                 $prompt = $this->aiautotool_fix_question($prompt_suggest_title,$title,$lang,$cate,$blogtitle,$content);

    	 		$newcontent = $bardGenContent->gentitle($prompt, $lang);
               
    	 		break;
    	 	default:
    	 		$newcontent = $bardGenContent->bardcontentmore($title, $lang);
    	 		break;
    	 }

    	 
    	 
	    wp_send_json_success(array('content' => $newcontent,'lang'=>$lang,'lg'=>$post_language,'prompt'=>$prompt));
	    wp_die();
    }
    public function aiautotool_get_slug() {
		    $title = sanitize_title($_POST['title']);
		    wp_send_json_success(array('slug' => $title));
		    wp_die();
		}

    public function add_menu() {
        add_submenu_page(
            MENUSUBPARRENT,
            '<i class="fa-solid fa-robot"></i> AI Single Post',
            '<i class="fa-solid fa-robot"></i> AI Single Post',
            'manage_options',
            'ai_single_post',
            array($this, 'render_form')
        );
        add_submenu_page(
            'edit.php',
            '<i class="fa-solid fa-robot"></i> AI Post',
            '<i class="fa-solid fa-robot"></i> AI Post',
            'manage_options',
            'ai_post',
            array($this, 'render_form')
        );
    }
    public function render_setting() {

    }
    public function render_tab_setting() {
    	
    }

    public function render_feature(){
        $autoToolBox = new AutoToolBox($this->icon.' '.$this->name_plan, "An intelligent editor that supports image searching, real-time content writing in multiple languages", "https://doc.aiautotool.com/", $this->active_option_name, $this->active,plugins_url('../images/logo.svg', __FILE__));
    echo $autoToolBox->generateHTML();

    }


    
   

    public function render_form() {
        $current_time = current_time('mysql');
        $datetime = new DateTime($current_time);
        $year = $datetime->format('Y');  // Lấy năm
        $month = $datetime->format('m'); // Lấy tháng
        $day = $datetime->format('d');   // Lấy ngày
        $hour = $datetime->format('H');  // Lấy giờ
        $minute = $datetime->format('i'); // Lấy phút

       

        $language_code = explode('_',get_locale());
        $language_code = $language_code[0];
            // Mảng mã ngôn ngữ và tên tương ứng
            $languages = $this->languageCodes;

           
            // $post = new SinglePost('','vi');
            // print_r($post->getPost());
        ?>
        <h1 class="wp-heading-inline">  <img src="<?php echo esc_url(plugins_url('../images/logo.svg', __FILE__)); ?>" width="16px" height="16px"  /> AI Autotool Single Post</h1>
        <div >
            <form method="post" action="" id="aiautotool_post_form" class="wrap aiautotool_container">
                <div class="aiautotool_left ">
                	<div class="aiautotool_box ">
                		<div class="aiautotool_box_head">
                
			                     <img src="<?php echo esc_url(plugins_url('../images/logo.svg', __FILE__)); ?>" width="16px" height="16px"  />

			                <span id="titlehead">Ai Bard content</span>  
			            </div>
                        <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                                    <?php esc_html_e('Language','ai-auto-tool'); ?>
                                </p>
                        <?php  echo '<select name="post_language" id="post_language">';
            foreach ($languages as $code => $name) {
                $is_selected = selected($language_code, $code, false);
                echo '<option value="' . esc_attr($code) . '" ' . esc_attr($is_selected ). '>' . esc_attr($name ). '</option>';
            }
            echo '</select>';
            ?>
                        <br>
                        <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                                    <?php esc_html_e('Title','ai-auto-tool'); ?>
                                </p>
	                    <input placeholdertext="Input title for post Then Click gen Article." type="text" id="aiautotool_title" class=" ft-input-big" name="aiautotool_title">

    <hr>
         <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                                    <?php esc_html_e('Slug:','ai-auto-tool'); ?>
                                </p>
	                    <input type="text" id="aiautotool_slug" class="ft-input-big" name="aiautotool_slug">

	                     <div class="aiautotool_form">
					        
					        
					    </div>
	                    
	                    <div class="aiautotool_form">
                            <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                                    <?php esc_html_e('Categories:','ai-auto-tool'); ?>
                                </p>
		                    <div class="aiautotool_categories">
		                    	 
		                        <?php wp_category_checklist(); ?>
		                    </div>
                             <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                                    <?php esc_html_e('Tags:','ai-auto-tool'); ?>
                                </p>
		                    <input placeholdertext="Input tag for post." type="text" id="aiautotool_tags" class="ft-input-big" name="aiautotool_tags">
		                 </div>
                         <?php 
                        $thumbnail_id = 0;
        $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'thumbnail');
        ?>
        <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                                    <?php esc_html_e('Thumbnail:','ai-auto-tool'); ?>
                                </p>
        <?php
        echo '<input type="hidden" id="custom-thumbnail-id" name="custom-thumbnail-id" value="' . esc_attr($thumbnail_id) . '">';
        echo '<div id="custom-thumbnail-preview">';
        if ($thumbnail_url) {
            echo '<img src="' . esc_url($thumbnail_url) . '">';
        }
        echo '</div>';
        echo '<button type="button" id="upload-custom-thumbnail" class=" ft-submit">Upload Custom Thumbnail</button>';
        ?>
	                    <?php wp_editor('', 'aiautotool_content', array('textarea_name' => 'aiautotool_content')); ?>
	                    


	                   
               		 </div>
                </div>

                <div class="aiautotool_right ">
                	<div class="aiautotool-fixed">
                		<div class="aiautotool_navpublic">
                        <label for="publish_datetime">Publish Date and Time:</label>
                        <div class="publish-date-time-row">
                            <select id="publish_month" name="publish_month" class="aiautotool_box_time_select">
                                <!-- Thêm các tùy chọn tháng tại đây -->
                                <option <?php selected($month, '01'); ?> value="01">January</option>
                                <option <?php selected($month, '02'); ?> value="02">February</option>
                                <option <?php selected($month, '03'); ?> value="03">March</option>
                                <option <?php selected($month, '04'); ?> value="04">April</option>
                                <option <?php selected($month, '05'); ?> value="05">May</option>
                                <option <?php selected($month, '06'); ?> value="06">June</option>
                                <option <?php selected($month, '07'); ?> value="07">July</option>
                                <option <?php selected($month, '08'); ?> value="08">August</option>
                                <option <?php selected($month, '09'); ?> value="09">September</option>
                                <option <?php selected($month, '10'); ?> value="10">October</option>
                                <option <?php selected($month, '11'); ?> value="11">November</option>
                                <option <?php selected($month, '12'); ?> value="12">December</option>
                            </select>
                            
                            <input value="<?php echo esc_attr($year); ?>" type="text" id="publish_year" name="publish_year" class="aiautotool_box_time_input">
                            
                            <input value="<?php echo esc_attr($day); ?> "type="text" id="publish_day" name="publish_day" class="aiautotool_box_time_input">
                            
                            <label for="publish_hour" class="aiautotool_box_time_label"> - </label>
                            <input value="<?php echo esc_attr($hour); ?>" type="text" id="publish_hour" name="publish_hour" class="aiautotool_box_time_input">
                            
                            <input value="<?php echo esc_attr($minute); ?>" type="text" id="publish_minute" name="publish_minute" class="aiautotool_box_time_input">
                        </div>
                		<button type="" class="aiautotool_button save-single-generation ft-submit">Publish</button>


                        
			           </div>
	                	
					<!-- end box -->
						<div id="aiautotool-meta-box" class="aiautotool_box ">
			            <div class="aiautotool_box_head">
					           <img src="https://dichvuxetai.com/wp-content/plugins/ai-auto-tool/images/logo.svg" width="16px" height="16px">
					        Ai auto Tool </div>
					            <div class="askassistant" >
                                    <textarea id="promptask" rows="2" placeholdertext="<?php esc_html_e('Type your promp...','ai-auto-tool');?>" placeholder="<?php esc_html_e('Type your promp...','ai-auto-tool');?>"></textarea>
                                   <div class="select-and-button">
                                        <select id="askAI">
                                            <option value="chatgpt">Chatgpt</option>
                                            <option value="gemini">Gemini AI</option>
                                        </select>
                                        <button id="askprompt"><?php esc_html_e('<i class="fa-solid fa-robot"></i> Ask Assistant','ai-auto-tool'); ?></button>
                                    </div>
                                </div>
					            <div class="aiautotool_form">
							       	<div class="aiautotool_tab">
							            <button type="button" data-tab="aiContentTab" class="tablinks" onclick="openTab(event, 'aiContentTab')">AI Content</button>
							            <button type="button" data-tab="imagesTab" class="tablinks" onclick="openTab(event, 'imagesTab')">Images</button>
							            
							        </div>

							        <!-- AI Content Tab -->
							        <div id="aiContentTab" class="tabcontent">
							           	<div id="info_content" placeholdertext="Input title for post Then Click gen Article,Select a phrase and click the Write button to use this feature"  ></div>
                                        
                                        <div class="loadingprocess p-5 text-center div_proccess_1 d-none div_proccess">
                                        <div id="loading-icon" class="loader" style="display:block"></div> Start socket <span id="proccess_1" class="process_loading badge badge-soft-primary"></span>
                                        </div>
                                        <div class="div_proccess_error text-center div_proccess d-none">
                                        <div class="pt-5"> <span>Start socket Error, Please "F5" to reload.</span></div>
                                        
                                        </div>
							            <!-- Content for AI Content tab goes here -->
							            <div id="outbard">
                                            
                                            <center>
                                            Select a phrase and click the <b>Write</b> button to use this feature
                                            <br>
                                            <img src="<?php echo esc_url(plugins_url('../images/find1.png', __FILE__)); ?>" width="150px"  /></center></div>
							            <button class="btn btnaddpost aiautotool_button" style="display:none" >Add To Post</button>
							        </div>

							        <!-- Images Tab -->
							        <div id="imagesTab" class="tabcontent">
                                        <div class="infodiv">
                                        <div id="info_img" placeholdertext="Select a phrase and click the Find Image button to use this feature"  ></div>
                                        <center>
                                            Select a phrase and click the <b>Find Image</b> button to use this feature
                                            <br>
                                            <img src="<?php echo esc_url(plugins_url('../images/find1.png', __FILE__)); ?>" width="150px"  /></center>
                                        </div>
							            
							            <div id="img_list_find" class="img_list_find"></div>
							            <!-- Content for Images tab goes here -->
							        </div>

							        
							    </div>
							</div>
		        </div>
                    
                </div>
                <!-- end right -->
            </form>
            <div class="aiautotool-modal-container">
        <div class="aiautotool-modal-overlay"></div>
        <div class="aiautotool-modal-content">
            <h2><?php esc_html_e('All tool for AI Post','ai-auto-tool');  ?></h2>
            <p>
                <span class=" aiautotool_btn_v1 aiautotool_btn_v16 btn_bardWriter btnoutline" id="btn_outline"  onclick="changeType('Outline')"><span class="icon"><i class="fa-solid fa-lightbulb"></i>  </span><span>Outline </span></span>
                
                <span class=" aiautotool_btn_v1 aiautotool_btn_v11 btn_bardWriter btn_intro" id=""  onclick="changeType('Introduction')"><span class="icon"><i class="fa-solid fa-info"></i>  </span><span>Introduction </span></span>
                <span class=" aiautotool_btn_v1 aiautotool_btn_v13 btn_bardWriter btn_conclusion" id=""  onclick="changeType('Conclusion')"><span class="icon"><i class="fa-solid fa-check-double"></i>  </span><span>Conclusion </span></span>
                <span class=" aiautotool_btn_v1 aiautotool_btn_v12 btn_bardWriter btn_faq" id=""  onclick="changeType('FAQ')"><span class="icon"><i class="fa-solid fa-question"></i>  </span><span>Create FAQ </span></span>
                <!-- <span class=" aiautotool_btn_v1 aiautotool_btn_v15 btn_bardWriter btn_summary" id=""  onclick="changeType('Summary')"><span class="icon"><i class="fa-solid fa-list-check"></i>  </span><span>Summary </span></span> -->
            </p>
            <div class=" aiautotool_container aiautotool_modal_content">
                <div id="outline">
                    <h3 id="current_type">Suggest Outline By Ai Auto Tool</h3>
                        <input type="hidden" name="type" id="type" value="Outline">
                    <div class="aiautotool_input2">
                      <input type="text" name="moretool" class="aiautotool_input2_input" placeholder="Enter your Keyword for ">
                      <button class="aiautotool_input2_button" id="moretool" type="submit">Create </button>  

                    </div>
                </div>
                <div id="Summary" style="display:none">
                    <div class="aiautotool_input2">
                      <input type="text" class="aiautotool_input2_input" placeholder="Enter your Keyword for ">
                      <button class="aiautotool_input2_button" type="submit" id="createButton">Create </button>  
                    </div>
                </div>
                <div id="Summary"></div>
                <div id="Introduction"></div>
                <div id="Conclusion"></div>
                <div id="Create FAQ"></div>
                <script type="text/javascript">
                    function changeType(newType) {
                        document.getElementById('current_type').innerText = newType;
                        document.getElementsByName('type')[0].value = newType;
                        const createButton = document.getElementById('moretool');
                        createButton.innerText = 'Create ' + newType;
                    }
                    jQuery(document).ready(function(){
                        jQuery("#moretool").click(function(){
                            var type = document.getElementsByName('type')[0].value;
                            var moretoolText = jQuery("input[name='moretool']").val();
                            var post_language = get_lang();
                            var langcheck = '';
                            if(moretoolText.trim() === "") {
                                if(Swal){
                                     Swal.fire({
                                              title: 'Error!',
                                              text: 'Please fill Keyword in request.',
                                              icon: 'error',
                                              confirmButtonText: 'Close'
                                            });
                                 }else{
                                    alert('Please fill Keyword in request.');
                                 }
                            } else {


                                open_box_aiautotool();
                            jQuery('.aiautotool-modal-close').click();

                            if (languageCodes.hasOwnProperty(post_language)) {
                               langcheck = languageCodes[post_language];
                              
                            } 
                            var divId = "outbard";
                            var prompt = '';
                            switch(type) {
                                case "Outline":
                                    prompt = aiautotool_js_setting.prompt.aiautotool_prompt_outline;
                                    prompt = aiautotool_fix_question(prompt,moretoolText,langcheck);
                                    

                                    sendbardToServer(prompt, divId,'writemore',langcheck);
                                    break;
                                case "Summary":
                                    // Xử lý khi type là Summary
                                    sendbardToServer(moretoolText, divId, langcheck); // Điều chỉnh hàm và tham số phù hợp
                                    break;
                                case "Introduction":
                                    prompt = aiautotool_js_setting.prompt.aiautotool_prompt_intro;
                                    prompt = aiautotool_fix_question(prompt,moretoolText,langcheck);
                                    
                                    sendbardToServer(prompt, divId,'writemore', langcheck); // Điều chỉnh hàm và tham số phù hợp
                                    break;
                                case "Conclusion":
                                    // Xử lý khi type là Conclusion
                                    prompt = aiautotool_js_setting.prompt.aiautotool_prompt_conclusion;
                                    prompt = aiautotool_fix_question(prompt,moretoolText,langcheck);
                                    
                                    sendbardToServer(prompt , divId,'writemore', langcheck); // Điều chỉnh hàm và tham số phù hợp
                                    break;
                                case "FAQ":
                                    // Xử lý khi type là Create FAQ
                                    prompt = aiautotool_js_setting.prompt.aiautotool_prompt_faq;
                                    prompt = aiautotool_fix_question(prompt,moretoolText,langcheck);
                                    
                                    sendbardToServer(prompt, divId,'writemore', langcheck); // Điều chỉnh hàm và tham số phù hợp
                                    break;
                                default:
                                    // Xử lý khi type không khớp với bất kỳ trường hợp nào
                                    console.error("Unknown type: " + type);
                            }
                            
                            
                            }
                        });
                    });
                </script>
            </div>
            <button class="aiautotool-modal-close"><i class="fa-regular fa-rectangle-xmark"></i></button>
        </div>
    </div>
        </div>
        <script>
            

        </script>
        <?php
    }
}

