function isEditOrNewPostPage() {
    var bodyClasses = document.body.classList;
    var isPostNewPage = bodyClasses.contains('post-new-php');
    var isPostPage = bodyClasses.contains('post-php');
    var isAiautotoolPage = bodyClasses.contains('aiautotool_input');
    
    var isAiPostInUrl = window.location.href.includes('ai_post');
    var isAiSinglePostInUrl = window.location.href.includes('ai_single_post');

    return isPostNewPage || isPostPage || isAiautotoolPage || isAiPostInUrl || isAiSinglePostInUrl;
}

function createButton(idbutton='',icon, text, clickHandler, buttonClass) {
    // Tạo một thẻ button
    var button = document.createElement("span");

    // Gán class cho button nếu được cung cấp
    if (buttonClass) {
        button.className = buttonClass;
    }
    button.id = idbutton;
    // Tạo một thẻ span để chứa icon
    var iconSpan = document.createElement("span");
    iconSpan.className = "icon"; // Thêm class "icon" để tùy chỉnh kiểu dáng icon
    button.type = 'button';
    // Thêm icon vào thẻ span
    iconSpan.innerHTML = icon+" ";

    // Tạo một thẻ span để chứa text
    var textSpan = document.createElement("span");
    textSpan.innerHTML = text;

    // Gắn icon và text vào button
    button.appendChild(iconSpan);
    button.appendChild(textSpan);

    // Gắn hàm xử lý sự kiện khi nút được click
    button.addEventListener("click", clickHandler);

    // Trả về button đã tạo
    return button;
}

if (isEditOrNewPostPage()) {

  jQuery(document).ready(function($) {
    $('#ai_autotool_generate_image').click(function(e) {
        e.preventDefault();
        var title = $('#title').val();
        var prompt = $('#ai_autotool_prompt').val();
        var model = $('#ai_autotool_model').val();
        var source_ai_model = $('#source_ai_model').val();
        if (!title) {
          if (typeof Swal !== 'undefined') {
                  Swal.fire({
                      title: 'Error!',
                      text: 'Please enter a title before generating the image.',
                      icon: 'error',
                      confirmButtonText: 'Close'
                  });
              } else {
                  alert('Please enter a title before generating the image.');
              }
            
            return;
        }
        showLoading();
        $.ajax({
            url: aiautotool_js_setting.ajax_url,
            type: 'POST',
            data: {
                action: 'aiautotool_cloudfalre_generate_image',
                security: aiautotool_js_setting.security,
                prompt: prompt,
                model: model,
                title:title,
                source_ai_model:source_ai_model
            },
            success: function(response) {
                hideLoading();
                var imageUrl = response;
                
                // Insert image into div
                $('#img_gen').html('<img src="' + imageUrl + '" alt="'+title+'" style="max-width: 100%; height: auto;" />');
                
                // Add button to insert image into editor
                $('#img_gen').append('<button id="add_to_content" class="button button-primary">Add to Content</button>');
                
                // Handle click event for the button
                $('#add_to_content').click(function(ev) {
                    ev.preventDefault();
                        
                        if (tinymce) {
                            var content = '<img src="' + imageUrl + '" alt="'+title+'" />';
                            tinymce.activeEditor.insertContent(content);
                        
                    } else {
                        alert('Editor not found.');
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error('Error generating image:', error);
                hideLoading();
            }
        });
    });

    $('#ai_autotool_suggest_prompt').click(function(e) {
      
        e.preventDefault();
        showLoading();
        var title = $('#title').val();
        if (!title) {
          if (typeof Swal !== 'undefined') {
                  Swal.fire({
                      title: 'Error!',
                      text: 'Please enter a title before generating the image.',
                      icon: 'error',
                      confirmButtonText: 'Close'
                  });
              } else {
                  alert('Please enter a title before generating the image.');
              }
            
            return;
        }
        $.ajax({
            url: aiautotool_js_setting.ajax_url,
            type: 'POST',
            data: {
                action: 'aiautotool_suggest_prompt',
                security: aiautotool_js_setting.security,
                title: title
            },
            success: function(response) {
              hideLoading();
              // var data = JSON.parse(response);
               var promptTextArea = document.querySelector('#ai_autotool_prompt');
                if (promptTextArea) {
                  var tempDiv = document.createElement('div');
                    tempDiv.innerHTML = response.prompt;
                    var plainText = tempDiv.textContent || tempDiv.innerText || '';

                    promptTextArea.value = plainText;
                } else {
                    console.error('Textarea with ID "ai_autotool_prompt" not found.');
                }
            },
            error: function(xhr, status, error) {
              hideLoading();
                console.error('Error suggesting prompt:', error);
            }
        });
    });
});
  

}

