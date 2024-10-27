<?php
defined('ABSPATH') or die();
class AI_AutoTool_Prompt_Customer {
    public $options;
    private $options_label;
    public function __construct() {
        add_action('wp_ajax_save_content_prompt', array($this, 'save_content_callback'));

        add_action('wp_ajax_nopriv_save_content_prompt', array($this, 'save_content_callback'));
         add_action('admin_footer', array($this, 'add_javascript'));
        add_action('admin_init', array($this, 'page_init'));
        $this->options = get_option('aiautotool_prompt_options');
        if (empty($this->options)) {
            $this->initialize_options();
        }
        
           
        $this->options_label = array(
            'aiautotool_prompt_artcile' => 'Prompt create Article Full',
            'aiautotool_prompt_suggest_title' => 'Prompt Suggest Title ',
            'aiautotool_prompt_outline' => 'Prompt Create Outline',
            'aiautotool_prompt_intro' => 'Prompt Create Intro',
            'aiautotool_prompt_conclusion' => 'Prompt Create Conclusion',
            'aiautotool_prompt_faq' => 'Prompt Create FAQ',
            'aiautotool_prompt_seo_review' => 'Prompt Seo Review ',
            // 'aiautotool_prompt_rewrite_short' => 'Prompt rewrite Short ',
            // 'aiautotool_prompt_rewrite_long' => 'Prompt rewrite Long ',
            // 'aiautotool_prompt_rewrite_pro' => 'Prompt rewrite Pro '
        );
    }

    private function initialize_options() {
        $default_options = array(
            'aiautotool_prompt_artcile' => 'Assume you are a proficient writer skilled in persuasive copywriting. Write a text that is at least 1,500 words long. When preparing the article, use bold for necessary words. Pretend that you can write content so good that it can outrank other websites. Write a long, fully markdown formatted article. Assume your output will be published on a WordPress blog. Use natural language only. Please use perplexity and burstiness in your output.

Use full markdown formatting, including relevant H2 headings. Do not number the headings or subheadings. Do not include "subheading" in the subheadings.

Output in the following order:
Use this as the Title: [%%title%%]
Write an Executive Summary, using that as the title of an H2 heading.
Introduction: Include a 50 to 100 word introduction.
Create 3 FAQ.
Find the top 5 subtopics. Insert an H2 heading using the name of the subtopic before explaining each subtopic. Include a description of the subtopic. Then bullet point 4-6 important pieces for each subtopic with brief explanations about each.
Include a 100 to 150 word conclusion.
Include 5 relevant keyword tags based on the topic and subtopics you found.
Write all output in [%%lang%%]. ',
            'aiautotool_prompt_suggest_title' => 'Please give me an article title in language [%%lang%%] about %title%, Write all output in [%%lang%%]',
            'aiautotool_prompt_outline' => 'E-A-T stands for Expertise, Authoritativeness, and Trustworthiness. Create Engaging, Unique, and SEO Optimized Blog Post Outline from [%%title%%] keep in mind E-A-T. 
            You will be writing an outline for a given topic. First, think through how the article should be structured, given the searcher intent for the keyword. Provide these thoughts inside <analysis></analysis> tags. Then, provide the outline itself inside <outline></outline> tags. 
Write all output in [%%lang%%]',
            'aiautotool_prompt_intro' => 'My name is %author%. In the context of {%%cate%%}, create an introduction of up to 50 words, in %%lang%%, for my blog {%%blog%%}, for an article about: {%%title%%}. Place the HTML tag   in the most important sentences of the text.',
            'aiautotool_prompt_conclusion' => 'Write a conclusion to end an article about: {%%title%%} in the context of {%%cate%%}. Place the HTML tag <strong> </strong> in the most important sentences of the text.',
            'aiautotool_prompt_faq' => 'create 5 FAQ for article [%%title%%]',
            'aiautotool_prompt_seo_review' => '',
            // 'aiautotool_prompt_rewrite_short' => '',
            // 'aiautotool_prompt_rewrite_long' => '',
            // 'aiautotool_prompt_artcile_pro' => ''
        );

        // foreach ($default_options as $option_key => $option_value) {
        //     if (!isset($this->options[$option_key])) {
        //         $this->options[$option_key] = $option_value;
        //     }elseif($this->options[$option_key]==''){
        //     	 $this->options[$option_key] = $option_value;
        //     }
        // }
        foreach ($default_options as $option_key => $option_value) {
            if (!isset($this->options[$option_key]) || !is_array($this->options[$option_key])) {
                $this->options[$option_key] = $option_value;
            } elseif ($this->options[$option_key] == '') {
                $this->options[$option_key] = $option_value;
            }
        }

        update_option('aiautotool_prompt_options', $this->options);
    }
    

    public function render_setting() {
        ?>
        <div class="wrap">
            <h2>AI AutoTool Prompt Settings</h2>
           <form method="post" action="options.php" id="aiautotool-prompt-form">
             
                <?php
                settings_fields('aiautotool_prompt_group');
                // do_settings_sections('aiautotool-prompt-settings');
                foreach ($this->options as $key => $value) {

                    $this->prompt_callback(array('option_name' => $key));
                   
                }
                
                ?>
                <button type="submit" name="save" class=" ft-submit"><?php _e('Save Prompt','ai-auto-tool'); ?></button>
            </form>
        </div>
        <?php
    }

    public function page_init() {
        register_setting(
            'aiautotool_prompt_group',
            'aiautotool_prompt_options',
            array($this, 'sanitize')
        );

        add_settings_section(
            'setting_section_id',
            'Edit Prompt Content',
            array($this, 'print_section_info'),
            'aiautotool-prompt-settings'
        );

        
    }

    public function print_section_info() {
        print 'Enter your desired prompt content below:';
    }

    public function prompt_callback($args) {
        $option_name = $args['option_name'];
        $option_value = isset($this->options[$option_name]) ? $this->options[$option_name] : '';

        // Thay thế các chuỗi dạng %xxx% thành thẻ span token
        $option_value = preg_replace('/%%([^%]+)%%/', '<span contenteditable="false" class="token">%%$1%%</span>', $option_value);
        
        ?>
        <p class="ft-note"><i class="fa-solid fa-lightbulb"></i>
                    <?php echo $this->options_label[$option_name]; ?>                  </p>
        
        <div>
            <button class=" aiautotool_btn_v1 aiautotool_btn_v12" onclick="insertSpan('%%title%%', event)"><span class="dashicons dashicons-plus-alt2"></span>Title</button>
            <button class=" aiautotool_btn_v1 aiautotool_btn_v12" onclick="insertSpan('%%cate%%', event)"><span class="dashicons dashicons-plus-alt2"></span> Category</button>
            <!-- <button class=" aiautotool_btn_v1 aiautotool_btn_v12" onclick="insertSpan('%%topic%%', event)"><span class="dashicons dashicons-plus-alt2"></span> Topic</button> -->
            <button class=" aiautotool_btn_v1 aiautotool_btn_v12" onclick="insertSpan('%%lang%%', event)"><span class="dashicons dashicons-plus-alt2"></span> Language</button>
            <button class=" aiautotool_btn_v1 aiautotool_btn_v12" onclick="insertSpan('%%content%%', event)"><span class="dashicons dashicons-plus-alt2"></span> Content</button>
        </div>
        <div contenteditable="true" class="editable ft-code-textarea" data-option="<?php echo $option_name; ?>"><?php echo stripslashes(nl2br($option_value)); ?></div>
       
        
        
        <?php
    }

    public function sanitize($input) {
        $sanitized_input = array();
        foreach ($input as $key => $value) {
            $sanitized_input[$key] = strip_tags($value);
        }
        return $sanitized_input;
    }
    public function add_javascript() {
        ?>
         <style type="text/css">
            .editable {
    border: 1px solid #ccc;
    padding: 5px;
    margin-bottom: 10px;

    min-height: 100px;
    max-height: 100px; /* đảm bảo độ cao tối thiểu */
    font-family: Arial, sans-serif; /* chọn font chữ */
    font-size: 14px; /* kích thước font */
    line-height: 1.5; /* độ cao của dòng */
    border-radius: 5px; /* bo tròn góc */
    background-color: #fff; /* màu nền */
    resize: vertical; /* cho phép thay đổi kích thước dọc */
    overflow-y: auto; /* hiển thị thanh cuộn dọc nếu cần */
    box-sizing: border-box;
     white-space: pre-wrap;
}

span.token {
    background-color: #4CAF50; /* Màu nền xanh */
    color: white; /* Màu chữ trắng */
    border-radius: 3px; /* Bo tròn góc */
    padding: 2px 5px; /* Khoảng cách giữa nội dung và biên */
    margin: 0 2px; /* Khoảng cách giữa các thẻ span */
    cursor: pointer; /* Con trỏ chuột khi rê chuột qua */

    --tw-bg-opacity: 1;
--tw-text-opacity: 1;
border-radius: 9999px;
display: inline-block;
font-size: .75rem;
font-weight: 500;
line-height: 1.25;
margin-left: .125rem;
margin-right: .125rem;
padding: .125rem .5rem;
}
.aiautotool_btn_prompt{
    padding: 5px 10px;
font-size: 10px;
user-select: none;
display: inline-flex;
text-transform: uppercase;
align-items: center;
border-color: var(--aiautotool-color);
color: var(--colorDark);
cursor: pointer;
font-weight: 500;
border-radius: 4px;
transition: all 0.3s linear;
font-family: inherit;
}
</style>
        <script>
            function saveContent() {
            	showLoading();
                var data = {};
                jQuery('.editable').each(function() {
                    var optionName = jQuery(this).data('option');
                    var content = jQuery(this).html();
                    // alert(content);
                    data[optionName] = content;
                });

                jQuery.post(ajaxurl, {
                    action: 'save_content_prompt',
                    content: data,
                    security:aiautotool_js_setting.security
                }, function(response) {
                	hideLoading();
                    Swal.fire({
                            title: 'Success!',
                            html:
                              'Save Success!!!',
                            icon: 'success',
                            confirmButtonText: 'Đóng'
                          }).then((result) => {
                             
                          })
                });
            }

            function insertSpan(value, event) {
                event.preventDefault();
                var range = window.getSelection().getRangeAt(0);
                var newNode = document.createElement('span');
                newNode.className = 'token';
                newNode.textContent = value;
                newNode.contentEditable = false;
                range.collapse(false);
                range.insertNode(newNode);
            }

            jQuery(document).ready(function() {
                jQuery('#aiautotool-prompt-form').submit(function(event) {
                    event.preventDefault();
                    saveContent();
                });
            });

            var editableDivs = document.querySelectorAll('[contenteditable="true"]');
            editableDivs.forEach(function(div) {
            div.addEventListener('keydown', function(event) {
                // Kiểm tra nếu phím nhấn là Enter (mã 13)
                if (event.keyCode === 13) {
                    // Ngăn chặn hành động mặc định của phím Enter (xuống dòng)
                    event.preventDefault();

                    // Thêm kí tự \n vào vị trí hiện tại của văn bản
                    var selection = window.getSelection();
                    var range = selection.getRangeAt(0);
                    var newline = document.createTextNode('\n');
                    range.insertNode(newline);
                    range.setStartAfter(newline);
                    range.setEndAfter(newline);
                    selection.removeAllRanges();
                    selection.addRange(range);
                }
            });
        });
        </script>
        <?php
    }

    public function save_content_callback() {
        check_ajax_referer('aiautotool_nonce', 'security');
        if (isset($_POST['content'])) {
            $content = $_POST['content'];
            foreach ($content as &$item) {
                $item = nl2br(wp_slash($item));
            }
            update_option('aiautotool_prompt_options', $content);
            wp_die();
        }
    }
}
