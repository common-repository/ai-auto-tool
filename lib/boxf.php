<?php
defined('ABSPATH') or die();
class AutoToolBox {
    private $title;
    private $img;
    private $content;
    private $link;
    private $active_option_name;
    private $active;
    private $premium;

    public function __construct($title, $content, $link, $active_option_name, $active,$img='',$premium=false) {
    	$this->title = $title;
        $this->title = $title;
        $this->content = $content;
        $this->link = $link;
        $this->active_option_name = $active_option_name;
        $this->active = $active;
        $this->img = $img;
        $this->premium = $premium;
    }

    public function generateHTML() {
        ?>
        
        <?php
        $new_badge = '';
        $box_head = '<div class="aiautotool_box_f_box-head "><img src="'.$this->img.'" width="16px" height="16px" /></div>';
        $box_content = '
            <div class="aiautotool_box_f_box-content ">
                <h3>' . $this->title . '</h3>
                <p>' . $this->content . '</p>
                <div class="aiautotool_box_f_box-link">
                    <a target="_blank" href="' . $this->link . '">Read</a>
                </div>
            </div>
        ';

        if($this->premium==true){
            if ( !aiautotool_premium()->is__premium_only() ) {
                $active_checkbox = '<div class="aiautotool_box_f_box-footer">';
                if ( aiautotool_premium()->is_not_paying() ) {
                    $active_checkbox .= '<section>';
                    $active_checkbox .= '<a href="' . aiautotool_premium()->get_upgrade_url() . '">' .
                        __('Upgrade Now!', 'ai-auto-tool') .
                        '</a>';
                    $active_checkbox .= '
                </section>';
                }
                 $active_checkbox .='   </div>
                ';
            }else{
                $active_checkbox = '<div class="aiautotool_box_f_box-footer">
                <label class="aiautotool_box_f_radio-label" for="' . $this->active_option_name . '_box_f_toggle2"></label>
                <label class="aiautotool_box_f_toggle-switch">
                    <input type="checkbox" id="' . esc_html($this->active_option_name) . '_box_f_feature2" name="' . esc_html($this->active_option_name) . '_box_f_feature2" ' . ($this->active == 'true' ? 'checked' : '') . '>
                    <span class="aiautotool_box_f_toggle-slider"></span>
                </label>
                </div>
            ';
            }
        }else{
            $active_checkbox = '<div class="aiautotool_box_f_box-footer">
                <label class="aiautotool_box_f_radio-label" for="' . $this->active_option_name . '_box_f_toggle2"></label>
                <label class="aiautotool_box_f_toggle-switch">
                    <input type="checkbox" id="' . esc_html($this->active_option_name) . '_box_f_feature2" name="' . esc_html($this->active_option_name) . '_box_f_feature2" ' . ($this->active == 'true' ? 'checked' : '') . '>
                    <span class="aiautotool_box_f_toggle-slider"></span>
                </label>
                </div>
            ';
        }
        
        

        $script = '
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const checkbox = document.getElementById("' . esc_html($this->active_option_name) . '_box_f_feature2");

                checkbox.addEventListener("change", function () {
                    const active = this.checked;
                    
                    const data = {
                        action: "update_active_option_canonical_' . $this->active_option_name . '",
                        active: active,
                        security: aiautotool_js_setting.security
                    };

                    fetch(aiautotool_js_setting.ajax_url, {
                        method: "POST",
                        body: new URLSearchParams(data),
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        }
                    })
                    .then(response => {
                       
                        if (response.ok) {
                            
                            location.reload();
                        } else {
                            console.error("Failed to update option");
                        }
                    })
                    .catch(error => {
                        console.error("Error during AJAX request:", error);
                    });
                });
            });
        </script>
        ';

        $html = '
        <div class="aiautotool_box_f_box ">
            ' . $new_badge . '
            ' . $box_head . '
            ' . $box_content . '
            ' . $active_checkbox . '
        </div>
        ' . $script;

        return $html;
    }
}

