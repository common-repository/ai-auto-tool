

document.querySelectorAll(".nav-tab").forEach(tab => {
    tab.addEventListener("click", function() {
        // Lưu trạng thái tab đã chọn vào localStorage
        localStorage.setItem("selectedTab", this.getAttribute("href"));
    });
});
function aiautotool_checklimit(config) {
    return (config.quota !== -1 && config.quota >= config.usage) || config.quota === -1;
}

function showLoading(title='') {
    
    Swal.fire({
            title: title,
            html:'<div class="loaderbar"></div>',
            icon: '',
            showConfirmButton: false,
            
        });
}

function hideLoading() {
    Swal.close();
}

function aiautotool_alert_success(title, message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: title || 'Success!',
            text: message || 'Success',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    } else {
        alert((title || 'Success!') + '\n' + (message || 'Success Ok.'));
    }
}


function aiautotool_alert_error(title, message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: title || 'Error!',
            text: message || 'An error has occurred. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    } else {
        alert((title || 'Error!') + '\n' + (message || 'An error has occurred. Please try again..'));
    }
}

function decodeHtmlEntities(text) {
    var entities = [
        ['amp', '&'],
        ['apos', '\''],
        ['lt', '<'],
        ['gt', '>'],
        ['quot', '"'],
        ['#39', '\''],
        ['#x27', '\''],
        ['#x2F', '/'],
        ['#x60', '`'],
        ['#x3D', '=']
    ];

    for (var i = 0; i < entities.length; i++) {
        text = text.replace(new RegExp('&'+entities[i][0]+';', 'g'), entities[i][1]);
    }

    return text;
}

function aiautotool_fix_question(prompt, title = '', lang = '', blog = '', cate = '', content = '') {
    // Create an object with keys being the placeholders and values being the actual values
    const replacements = {
        '%%title%%': title,
        '%%lang%%': lang,
        '%%blog%%': blog,
        '%%cate%%': cate,
        '%%content%%': content
    };

    // Replace placeholders in prompt with actual values
    let outprompt = prompt;
    for (const key in replacements) {
        if (Object.hasOwnProperty.call(replacements, key)) {
            outprompt = outprompt.replace(new RegExp(key, 'g'), replacements[key]);
        }
    }

    // Return the modified string
    return outprompt;
}

function check_aipost_premium() {
    if (typeof aiautotool_js_setting !== 'undefined' && aiautotool_js_setting.fsdata) {
        if (aiautotool_checklimit(aiautotool_js_setting.fsdata)) {
            return true;
        } else {
            return false;
        }
    } 
    return false;
}

function update_usage() {
    var data = {
        action: 'update_usage',
        security: aipost.security
    };

    jQuery.post(aipost.ajax_url, data, function (response) {
        if (response.success) {
            console.log('Update usage successfully.');
        } 
    });
}

let isStartSocket = 0;
let statussocket = false;
var socket = io(aiautotool_js_setting.gpt_api);
const languageCodes = aiautotool_js_setting.languageCodes;

let langcheck = aiautotool_js_setting.langcodedefault;
let hostname = getCurrentDomain();

var post = [];
let indexnow = 0;
let istrue = true;
let question = '';

var divids = 'outbard';
const url = aiautotool_js_setting.gemini_api;

function fttab(evt, tabname) {
  var i, x, sotab;
  x = document.getElementsByClassName("ftbox");
  for (i = 0; i < x.length; i++) {
    x[i].style.display = "none";  
  }
  sotab = document.getElementsByClassName("sotab");
  for (i = 0; i < x.length; i++) {
    sotab[i].className = sotab[i].className.replace(" sotab-select", "");
  }
  document.getElementById(tabname).style.display = "block";
  evt.currentTarget.className += " sotab-select";
  localStorage.setItem('selectedRank', tabname);
}
class ContentOb {
                    constructor(url, question, lang) {
                        this.url = url;
                        this.question = question;
                        this.lang = lang;
                        this.outline = [];
                        this.listhead = [];
                    }

                    async createOutline() {
                        try {
                            const response = await fetch(this.url + '/genoutline', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({ question: this.question, lang: this.lang }),
                            });

                            if (!response.ok) {
                                throw new Error(`HTTP error! Status: ${response.status}`);
                            }

                            const responseData = await response.json();

                            if (responseData.result) {
                                this.outline = JSON.parse(responseData.result);
                                isStartSocket = 1;
                                jQuery(".loadingprocess").addClass("d-none");
                                for (const key in this.outline) {
                                    if (this.outline.hasOwnProperty(key)) {
                                        var headertitle = this.outline[key];
                                        this.listhead.push(headertitle);
                                        var item = {
                                            key: key,
                                            h2: headertitle,
                                            desc: '',
                                            stext: ''
                                        };
                                        post.push(item);
                                    }
                                }
                                if (!Array.isArray(this.listhead)) {
                                    throw new Error('Invalid response data format: outline is not an array');
                                }
                            } else {
                                throw new Error('Invalid response data format');
                            }
                        } catch (error) {
                            console.error('Error creating outline:', error.message);
                        }
                    }
                }
function getCurrentDomain() {
    // Lấy đối tượng URL của trang hiện tại
    var currentUrl = new URL(window.location.href);

    // Lấy phần hostname từ URL
    var currentDomain = currentUrl.hostname;

    return currentDomain;
}


// Tìm div có class aiautotool_left
var aiautotoolLeft = document.querySelector('.aiautotool_left');

// Kiểm tra xem có div aiautotool_left không
if (aiautotoolLeft) {
    // Lấy giá trị top của div aiautotool_left
    var topPosition = aiautotoolLeft.getBoundingClientRect().top;

    // Tìm div có class aiautotool-fixed
    var aiautotoolFixed = document.querySelector('.aiautotool-fixed');

    // Kiểm tra xem có div aiautotool-fixed không
    if (aiautotoolFixed) {
        // Đặt giá trị top của div aiautotool-fixed
        if(topPosition<=0){
            topPosition = 50;
        }
        aiautotoolFixed.style.top =  '50px';
    }
}

var aiautotoolFixed = document.querySelector('.aiautotool-fixed');

// Xác định màn hình có thay đổi kích thước khi cuộn hay không
var isScreenResized = false;

window.addEventListener('resize', function () {
    isScreenResized = true;
});

window.addEventListener('scroll', function () {
    if(aiautotoolFixed){
        var topPosition = aiautotoolFixed.getBoundingClientRect().top;

        // Kiểm tra xem top của aiautotool-fixed có cao hơn chiều cao của màn hình không
        var isTopHigherThanScreen = topPosition < 0;

        // Nếu màn hình đã thay đổi kích thước (resize) hoặc top của aiautotool-fixed cao hơn màn hình
        if (isScreenResized || isTopHigherThanScreen) {
            // Đặt position là 'absolute'
            aiautotoolFixed.style.position = 'absolute';
        } else {
            // Đặt position là 'fixed'
            aiautotoolFixed.style.position = 'fixed';
        }

        // Đặt lại biến isScreenResized
        isScreenResized = false;
    }
    // Lấy giá trị top của div aiautotool-fixed
    
});
 var aiautotool_content = '';
function openTab(tabName) {
    // Check if a tab with the given ID exists
    if (document.getElementById(tabName)) {
        // Declare all variables
        var i, tabcontent, tablinks;

        // Get all elements with class="tabcontent" and hide them
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }

        // Get all elements with class="tablinks" and remove the class "active"
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }

        // Show the current tab, and add an "active" class to the button that opened the tab
        document.getElementById(tabName).style.display = "block";
        jQuery('[data-tab="' + tabName + '"]').addClass("active");
    } else {
        
        console.log('Tab with ID "' + tabName + '" does not exist.');
        // Handle the case where the tab doesn't exist (e.g., show an error message)
    }
}

jQuery(document).ready(function($) {
    // Ẩn toàn bộ các tab content
    const selectedTab = localStorage.getItem("selectedTab");

    $('.tab-content').hide();

    // Hiển thị tab content đầu tiên
    if (selectedTab) {
        var selectedTabWithoutHash = selectedTab.replace("#", "");
        if ($("#" + selectedTabWithoutHash).length) {
            $('.tab-content').hide();
            $("#" + selectedTabWithoutHash).show();
            var selector = `button[href="${selectedTab}"]`;
            $(selector).addClass('nav-tab-active');
        } else {
            // Nếu không tồn tại id selectedTab, tìm button đầu tiên trong div ft-menu và click vào nó
            var firstButton = $('.ft-menu button').first();
            // firstButton.click();
            var firstTab = $(firstButton.attr('href'));

            firstTab.show();
            firstButton.addClass('nav-tab-active');
            console.log(firstButton.attr('href'));
        }
    } else {
        
        if (document.getElementById('tab-setting')) {
            document.getElementById('tab-setting').style.display = "block";
        }
    }

    $('.nav-tab').click(function(event) {
        event.preventDefault();
        var tabText = $(this).text();
        $('#titlehead').text(tabText);

        var tab_id = $(this).attr('href');

        $('.tab-content').hide();

        $(tab_id).show();
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
    });
});




 function bard_gen_content(button) {
    var post_id = button.getAttribute('data-id');
    var customButton = button;
    var loadingButton = document.getElementById('loading-icon');
    customButton.classList.add('disabled');
    customButton.style.display = 'none';
    loadingButton.style.display = 'block';
  jQuery.ajax({
        url: '/wp-json/aiautotool/v1/createcontentbard', 
        method: 'POST',
        data: {
            post_id: post_id
        },
        success: function(response) {
            // Xử lý phản hồi từ REST route
            // var responseData = JSON.parse(response);
            var url = response.data;

            // Mở tab mới với URL
            // window.open(url, '_blank');
             loadingButton.style.display = 'none';
            customButton.classList.remove('disabled');
            customButton.style.display = 'block';

             Swal.fire({
                    title: 'Thành công',
                    html:
                      '<a class="btn btn-block btn-danger btn-sm" target="_blank" href="'+url+'">Xem Bài viết</a>',
                    icon: 'success',
                    confirmButtonText: 'Đóng'
                  }).then((result) => {
                     
                  })
        },
        error: function(error) {
            // Xử lý lỗi nếu có
            alert('Lỗi: ' + error.responseText);
             loadingButton.style.display = 'none';
            customButton.classList.remove('disabled');
            customButton.style.display = 'block';
        }
    });
}



document.addEventListener('DOMContentLoaded', function () {
    var titleInput = document.getElementById('aiautotool_title');
    var slugInput = document.getElementById('aiautotool_slug');

    if (titleInput && slugInput) {
        titleInput.addEventListener('blur', function () {
            var titleValue = titleInput.value;
            var slugValue = sanitizeTitle(titleValue);
           
        });

        function sanitizeTitle(title) {
            // Gửi AJAX request đến WordPress để lấy slug
            return wp.ajax.post('aiautotool_get_slug', {
                title: title,
            }).done(function (response) {
                slugInput.value = response.slug;
                
            });
        }
    }
});






function create_buttoneditor(){
   


}

if (document.getElementById('post-titles') != null) {

                                            placeholderStreaming('post-titles');

                                        }



function checkAndCallPlaceholderStreaming() {
  // Lấy tất cả các thẻ HTML
  var allElements = document.getElementsByTagName("*");

  // Duyệt qua từng thẻ để kiểm tra
  for (var i = 0; i < allElements.length; i++) {
    var element = allElements[i];

    // Kiểm tra xem thẻ có tồn tại id và thuộc tính placeholderText hay không
    if (element.id && element.getAttribute("placeholderText")) {
      // Gọi hàm placeholderStreaming với id của thẻ
      placeholderStreaming(element.id);
    }
  }
}

checkAndCallPlaceholderStreaming();

function placeholderStreaming(outputElement= 'prompt-input', speed = 50, timeOut = 10000) {

        if (document.getElementById(outputElement) == null){
            return;
        }


         var placeholders = document.getElementById(outputElement).getAttribute('placeholdertext');
         placeholders = placeholders.split(',');
        
        

        var rand = aiautotool_rand2(0, (placeholders.length - 1));
        var placeholder_init_text = aiwa_removeNumbers2(placeholders[rand]).trim();
        

        document.getElementById(outputElement).setAttribute('placeholder', '');
        for (let i = 0; i < placeholder_init_text.length; i++) {
            setTimeout(function () {
                var placeholder = document.getElementById(outputElement).getAttribute('placeholder');
                document.getElementById(outputElement).setAttribute('placeholder', placeholder + placeholder_init_text[i]);
            }, i * speed);
        }


        var AutoRefresh = setInterval(function () {
            var rand = aiautotool_rand2(0, (placeholders.length - 1));
            aiautotool_replace_placeholder_like_stream(aiwa_removeNumbers2(placeholders[rand]).trim(), outputElement, speed);
        }, timeOut);
    }

function aiautotool_rand2(min, max) { // min and max included
    return Math.floor(Math.random() * (max - min + 1) + min)
}

function aiwa_removeNumbers2(list) {
    return list.replace(/\d\.|\d\d\.+/g, "");
}
function aiautotool_replace_placeholder_like_stream(string, id = 'prompt-input', speed = 50) {
    var prompt_input = document.getElementById(id);

    // Check if the element is an input or a div
    if (prompt_input.tagName.toLowerCase() === 'input') {
        prompt_input.setAttribute('placeholder', '');
        for (let i = 0; i < string.length; i++) {
            setTimeout(function () {
                var placeholder = prompt_input.getAttribute('placeholder');
                prompt_input.setAttribute('placeholder', placeholder + string[i]);
            }, i * speed);
        }
    } else if (prompt_input.tagName.toLowerCase() === 'textarea') {
        prompt_input.setAttribute('placeholder', '');
        for (let i = 0; i < string.length; i++) {
            setTimeout(function () {
                var placeholder = prompt_input.getAttribute('placeholder');
                prompt_input.setAttribute('placeholder', placeholder + string[i]);
            }, i * speed);
        }
    } else if (prompt_input.tagName.toLowerCase() === 'div') {
        prompt_input.innerHTML = ''; // Clear existing content
        for (let i = 0; i < string.length; i++) {
            setTimeout(function () {
                prompt_input.innerHTML += string[i];
            }, i * speed);
        }
    }
}


function setcontent(content){
     var activeEditor = tinyMCE.get('content');
    // var content = 'HTML or plain text content here...';
    if(jQuery('#wp-content-wrap').hasClass('html-active')){ // We are in text mode
        jQuery('#content').val(content); // Update the textarea's content
    } else { // We are in tinyMCE mode
        var activeEditor = tinyMCE.get('content');
        if(activeEditor!==null){ // Make sure we're not calling setContent on null
            activeEditor.setContent(content); // Update tinyMCE's content
        }
    }
  }

jQuery(document).ready(function ($) {

    async function checkAndDoSomething() {

        if(statussocket){
        if(Swal){
                 Swal.fire({
                          title: 'Error!',
                          text: 'AI is writing',
                          icon: 'error',
                          confirmButtonText: 'Close'
                        });
             }else{
                alert('AI is writing');
             }
        
    }else{
                    open_box_aiautotool();
                    question = get_title().value;

                    if (question.trim() === '') {
                         if(Swal){
                                 Swal.fire({
                                                  title: 'Error!',
                                                  text: 'Please fill a title:\n' ,
                                                  icon: 'error',
                                                  confirmButtonText: 'Close'
                                                });
                                     }else{
                                         alert('Please enter a title!');
                                     }
                       
                    } else {

                        var post_language = jQuery('#post_language').val();

                          lang = get_lang();
                            if (languageCodes.hasOwnProperty(post_language)) {
                               lang = languageCodes[post_language];
                              
                            } 

                        openTab('aiContentTab');
                        // showProcess();
                        showLoading();
                        const contentOb = new ContentOb(url, question, lang);
                        await contentOb.createOutline();
                        console.log(contentOb.listhead);
                    }
                }
    }
   
    // Event listener for the "Bard write" button
    $('.bard-write').on('click', function () {

        if(!check_aipost_premium())
            {
                 if(Swal){
                     Swal.fire({
                              title: 'Error!',
                              text: 'AI Post Limit Quota. Please Upgrade Pro.',
                              icon: 'error',
                              confirmButtonText: 'Close'
                            });
                 }else{
                    alert('AI Post Limit Quota. Please Upgrade Pro.');
                 }
                
                return;
            }
        // Check if the title is filled
        var titleValue = get_title().value;
        
        var post_language = get_lang();

        if (post_language.length) {
            // Nếu tồn tại, lấy giá trị của #aiautotool_title
            post_language = post_language;

        } else {
             post_language = langcheck;
        }


        if (!titleValue.trim()) {
            if(Swal){
                 Swal.fire({
                          title: 'Error!',
                          text: 'Please fill in the title.',
                          icon: 'error',
                          confirmButtonText: 'Close'
                        });
             }else{
                alert('Please fill in the title.');
             }
            
            return;
        }
         // $(this).prop('disabled', true);

         

          var divId = "outbard"; // Thay đổi ID của div tại đây
          var post_language = jQuery('#post_language').val();

            if (languageCodes.hasOwnProperty(post_language)) {
               langcheck = languageCodes[post_language];
              
            } 
          var prompt_article = aiautotool_js_setting.prompt.aiautotool_prompt_artcile;
          prompt_article = aiautotool_fix_question(prompt_article,titleValue,langcheck);
          sendbardToServer(prompt_article, divId,'writefull',langcheck);
          
    });


    $('.chatgpt-write').on('click', function () {
        if(!check_aipost_premium())
                        {
                             if(Swal){
                                 Swal.fire({
                                          title: 'Error!',
                                          text: 'AI Post Limit Quota. Please Upgrade Pro.',
                                          icon: 'error',
                                          confirmButtonText: 'Close'
                                        });
                             }else{
                                alert('AI Post Limit Quota. Please Upgrade Pro.');
                             }
                            
                            return;
                        }
        // Check if the title is filled
        var titleValue = $('#aiautotool_title').val();
        if ($('#aiautotool_title').length) {
            // Nếu tồn tại, lấy giá trị của #aiautotool_title
            titleValue = $('#aiautotool_title').val();

        } else {
            // Nếu không tồn tại, thay thế bằng #title
             titleValue = $('#title').val();
        }
        var post_language = $('#post_language').val();

        if ($('#post_language').length) {
            // Nếu tồn tại, lấy giá trị của #aiautotool_title
            post_language = $('#post_language').val();

        } else {
             post_language = langcheck;
        }

        if (!titleValue.trim()) {
            if(Swal){
                 Swal.fire({
                          title: 'Error!',
                          text: 'Please fill in the title.',
                          icon: 'error',
                          confirmButtonText: 'Close'
                        });
             }else{
                alert('Please fill in the title.');
             }
            
            return;
        }
         
         // var text = 'Viết một bài viết về tiêu đề "' + titleValue + '" bằng ngôn ngữ "Vietnamese", có chứa ít nhất 3 thẻ h2, viết định dạng maskdown. the end content has create FAQ for title';
          var divId = "outbard"; // Thay đổi ID của div tại đây
          var post_language = jQuery('#post_language').val();

            if (languageCodes.hasOwnProperty(post_language)) {
               langcheck = languageCodes[post_language];
              
            } 
          var prompt_article = aiautotool_js_setting.prompt.aiautotool_prompt_artcile;
          prompt_article = aiautotool_fix_question(prompt_article,titleValue,langcheck);
         
          
          sendTextToServer(prompt_article, divId,'writefull',langcheck);
    });

    $('#askprompt').on('click', function () {
        if(!check_aipost_premium())
                        {
                             if(Swal){
                                 Swal.fire({
                                          title: 'Error!',
                                          text: 'AI Post Limit Quota. Please Upgrade Pro.',
                                          icon: 'error',
                                          confirmButtonText: 'Close'
                                        });
                             }else{
                                alert('AI Post Limit Quota. Please Upgrade Pro.');
                             }
                            
                            return;
                        }

        var titleValue = $('#promptask').val();
        
        var post_language = get_lang();

        if (post_language.length) {
            // Nếu tồn tại, lấy giá trị của #aiautotool_title
            post_language = post_language;

        } else {
             post_language = langcheck;
        }

        var askAI = $('#askAI').val();

        if (!titleValue.trim()) {
            if(Swal){
                 Swal.fire({
                          title: 'Error!',
                          text: 'Please fill in Your Prompt.',
                          icon: 'error',
                          confirmButtonText: 'Close'
                        });
             }else{
                alert('Please fill in Your Prompt.');
             }
            
            return;
        }
         // $(this).prop('disabled', true);

         

          var divId = "outbard"; // Thay đổi ID của div tại đây
          var post_language = jQuery('#post_language').val();

            if (languageCodes.hasOwnProperty(post_language)) {
               langcheck = languageCodes[post_language];
              
            } 
        if (askAI =='chatgpt') {
            
            sendTextToServer(titleValue, divId,'writemore',langcheck);
        }else{

          sendbardToServer(titleValue, divId,'writemore',langcheck);
        }
        
    });
     $('.chatgpt-long-write').on('click', function () {
        // Check if the title is filled

        checkAndDoSomething();
    });


     $('.btn_suggettitle').on('click', function () {
        // Check if the title is filled
        if(!check_aipost_premium())
                        {
                             if(Swal){
                                 Swal.fire({
                                          title: 'Error!',
                                          text: 'AI Post Limit Quota. Please Upgrade Pro.',
                                          icon: 'error',
                                          confirmButtonText: 'Close'
                                        });
                             }else{
                                alert('AI Post Limit Quota. Please Upgrade Pro.');
                             }
                            
                            return;
                        }
        var titleValue = get_title();
        var post_language = get_lang();
        console.log(post_language);
        if (!titleValue.value.trim()) {
            if(Swal){
                 Swal.fire({
                          title: 'Error!',
                          text: 'Please fill Key for sugget in input title.',
                          icon: 'error',
                          confirmButtonText: 'Close'
                        });
             }else{
                alert('Please fill in the title.');
             }
            
            return;
        }
         $(this).prop('disabled', true);
         showLoading('Suggest title for: '+titleValue);
        $.ajax({
            url: aiautotool_data.ajax_url,
            method: 'POST',
            data: {
                 action: 'bard_content',
                title: titleValue.value,
                post_language:post_language,
                ac:'suggettitle'
                // Add any other data you need to send to the API
            },
            success: function (response) {
                // Display the result in the 'outbard' div
               console.log(response);
               titleValue.value = response.data.content;
               isStartSocket = 1;
               hideLoading();
               
            },
            error: function () {
                hideLoading();
                alert('An error occurred during the API call.');
            },
            complete: function () {
                // Re-enable the button after AJAX call is complete
                hideLoading();
                $(this).prop('disabled', false);
                // $('#loading-icon').remove();
            }
        });
    });


    $('.btnaddpost').on('click', function () {
        // Check if the title is filled
       
      event.preventDefault();

          $('#outbard').html('');
             
          
          tinymce.activeEditor.insertContent(aiautotool_content);
          $('.btnaddpost').hide();
          return false;
    });
 
    openTab('aiContentTab');
    // Event listeners for tab buttons
    $('.aiautotool_tab button').on('click', function () {
        var tabName = $(this).data('tab');
        openTab(tabName);
    });



 $("body").on("click", ".img_search", function(res) {
    res.preventDefault();
    let imgSrc = $(this).attr("data-src");
    let imgBox = `<p class="imageBox"><img class="aligncenter" src="${imgSrc}"></p>`;
    tinymce.activeEditor.insertContent(imgBox);
    

    
});

});




jQuery(document).ready(function ($) {
    var postForm = $('#aiautotool_post_form');

    postForm.submit(function (event) {
        event.preventDefault(); // Prevent the default form submission

        // Check if required fields are filled
        var missingFields = checkFields();

        if (missingFields.length > 0) {
            // Alert with specific missing fields
            if(Swal){
                 Swal.fire({
                                  title: 'Error!',
                                  text: 'Please fill in the following fields:\n' + missingFields.join(', '),
                                  icon: 'error',
                                  confirmButtonText: 'Close'
                                });
                     }else{
                        alert('Please fill in the following fields:\n' + missingFields.join(', '));
                     }
            return;
        }
        Swal.fire({
                title:"", 
                text:"Loading...",
                icon: "https://www.boasnotas.com/img/loading2.gif",
                buttons: false,      
                closeOnClickOutside: false,
                timer: 3000,
                //icon: "success"
            });
        // Retrieve form data
        var formData = postForm.serialize();

        // Example: Add additional data to formData if needed
        formData += '&additional_key=additional_value';

        // Add the security nonce
        formData += '&security=' + aiautotool_js_setting.security;
         formData += '&action=save_post_data';
        // Call your plugin's AJAX endpoint
        $.ajax({
            url: aiautotool_js_setting.ajax_url,
            type: 'POST',
            data: formData,
            success: function (response) {
                // Handle the success response from the server
                console.log(response);
                if (response.success) {
                    // Success response
                    if(Swal){

                        Swal.fire({
                            title: 'Thành công',
                            html:
                              'Post successfully saved and published. <a class="btn btn-block btn-danger btn-sm" target="_blank" href="'+response.data.post_url+'">View Post</a>',
                            icon: 'success',
                            confirmButtonText: 'Đóng'
                          }).then((result) => {
                             
                          })

                         
                             }else{
                                alert('Post successfully saved and published!');
                             }
                    
                    // Display the post URL
                    console.log('Post URL:', response.data.post_url);
                } else {
                    // Error response
                    alert('Error: ' + response.data);
                }
            },
            error: function (error) {
                // Handle errors
                console.error('Error:', error);
            }
        });
    });

    // Function to check if required fields are filled
     function checkFields() {
        var titleInput = document.getElementById('aiautotool_title');
        var categoriesCheckboxes = document.querySelectorAll('[name="post_category[]"]');
        var contentInput = document.getElementById('aiautotool_content');

        var missingFields = [];

        // Check each field and add to missingFields if empty
        if (!titleInput.value.trim()) {
            missingFields.push('Title');
        }

        var selectedCategories = Array.from(categoriesCheckboxes).some(checkbox => checkbox.checked);

        if (!selectedCategories) {
            missingFields.push('Categories');
        }

        if (!contentInput.value.trim()) {
            missingFields.push('Content');
        }

        return missingFields;
    }
});




// chatgpt config

function removeMaskData(dataText){if(dataText){if(typeof dataText==='string'||dataText instanceof String){dataText=dataText.replaceAll('Stop article','');dataText=dataText.replaceAll('{stop article}','');dataText=dataText.replaceAll('{stop}','');dataText=dataText.replaceAll('{start}','');dataText=dataText.replaceAll('{done}','');dataText=dataText.replaceAll('{---end---}','');dataText=dataText.replaceAll('---end---','');dataText=dataText.replaceAll('--end--','');dataText=dataText.replaceAll('-end-','');dataText=dataText.replaceAll('ChatGPT','');dataText=dataText.replaceAll('chatGPT','');return dataText;}}}
function replaceH1WithH2(text) {
  // Sử dụng biểu thức chính quy (regular expression) để thay thế tất cả các thẻ <h1> thành <h2>
  return text.replace(/<h1>/g, "<h2>").replace(/<\/h1>/g, "</h2>");
}
function sendTextToServer(text, divId,option,lang) {
    if(!check_aipost_premium())
    {
         if(Swal){
             Swal.fire({
                      title: 'Error!',
                      text: 'AI Post Limit Quota. Please Upgrade Pro.',
                      icon: 'error',
                      confirmButtonText: 'Close'
                    });
         }else{
            alert('AI Post Limit Quota. Please Upgrade Pro.');
         }
        
        return;
    }
    if(statussocket){
        console.log(statussocket);
        if(Swal){
                 Swal.fire({
                          title: 'Error!',
                          text: 'AI is writing',
                          icon: 'error',
                          confirmButtonText: 'Close'
                        });
             }else{
                alert('AI is writing');
             }
        
    }else{

           
          // var socket = io("https://bard.aiautotool.com");
          let currentText = "";

          // Xử lý sự kiện khi dữ liệu trả về từ máy chủ
          socket.on("aiautotool-content-return", function(data) {
            data = JSON.parse(data);
            isStartSocket = 1;
            jQuery(".loadingprocess").addClass("d-none");
            hideLoading();
            // Xử lý dữ liệu theo nhu cầu của bạn
            if (data?.status == 'writing') {
              currentText = currentText + data.sText;
              jQuery(`#${divId}`).html(markdown(currentText));
              scrollToBottom();
            } else if (data?.status == 'complete') {
                
                 aiautotool_content = replaceH1WithH2(markdown(removeMaskData(currentText)));
              jQuery(`#${divId}`).html(`<div class="">${replaceH1WithH2(markdown(removeMaskData(currentText)))}</div>`);
              scrollToBottom();
              jQuery('.btnaddpost').show();

              statussocket = false;
              update_usage();
            }

            // console.log("Dữ liệu được trả về từ máy chủ:", data);
          });

          // Tạo đối tượng socketObj theo yêu cầu
          var socketObj = {
            text: text,
            option:option,
            language: lang,
            domain:hostname,
            info:aiautotool_js_setting.fsdata
          };
          showLoading();
          // Gửi dữ liệu lên máy chủ
          socket.emit('aiautotool-write', socketObj);
          statussocket = true;

   }
}

function sendbardToServer(text, divId,option,lang) {
    if(!check_aipost_premium())
    {
         if(Swal){
             Swal.fire({
                      title: 'Error!',
                      text: 'AI Post Limit Quota. Please Upgrade Pro.',
                      icon: 'error',
                      confirmButtonText: 'Close'
                    });
         }else{
            alert('AI Post Limit Quota. Please Upgrade Pro.');
         }
        
        return;
    }


    var dataToSend = {
      question: text,
      lang: lang,
      domain:hostname,
      info:aiautotool_js_setting.fsdata
    };
    var jsonData = JSON.stringify(dataToSend);
    showLoading();
    

    var urlapi  = '/bardcontent';
    switch(option){
        case 'writefull':
            urlapi  = '/bardcontentmore';
            break;
        case 'writemore':
            urlapi  = '/bardcontentmore';
            break;

        case 'writebard':
            urlapi  = '/bardcontent';
            break;

        case 'bardrewrite':
            urlapi  = '/bardrewrite';
            
            break;
        default:
            urlapi  = '/bardcontent';
            break;
    }
    urlapi = aiautotool_js_setting.gemini_api + urlapi;
    
   jQuery.ajax({
      type: "POST",
      url: urlapi,
      data: jsonData,
      contentType: "application/json",
      success: function(data) {
         hideLoading();
        if(data.code == '200'){
            aiautotool_content = replaceH1WithH2(markdown(removeMaskData(data.result)));
            // jQuery(`#${divId}`).innerHTML = aiautotool_content;
            jQuery(`#${divId}`).html(aiautotool_content);
            scrollToBottom();
            statussocket = false;
            jQuery('.btnaddpost').show();
            jQuery(".div_proccess").addClass("d-none");
            jQuery(".loadingprocess").addClass("d-none");
           
            update_usage();
        }else{
            if(Swal){
                 Swal.fire({
                          title: 'Error!',
                          text: data.msg,
                          icon: 'error',
                          confirmButtonText: 'Close'
                        });
             }else{
                alert(data.msg);
             }
        }
        
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.error("Lỗi khi gửi yêu cầu: " + textStatus, errorThrown);
      }
    });
  
}


function reformatHTML(htmlRes){
            if (typeof htmlRes === 'string' || htmlRes instanceof String){
                htmlRes = htmlRes.replaceAll('```html\n','');
                htmlRes = htmlRes.replaceAll('```','');
                htmlRes = htmlRes.trim();
                if (htmlRes.substr(0,1) == '"') htmlRes = htmlRes.substr(1);
                if (htmlRes.slice(-1) == '"') htmlRes = htmlRes.slice(0,-1);

                if (htmlRes.indexOf("</") == -1){
                    if (htmlRes.indexOf("\n") > -1){
                        htmlRes = "<p>" + htmlRes + "</p>";
                        htmlRes = htmlRes.replace(/\r\n\r\n/g, "</p><p>").replace(/\n\n/g, "</p><p>");
                        htmlRes = htmlRes.replace(/\r\n/g, "<br />").replace(/\n/g, "<br />");
                    }
                }
                return htmlRes;
            }else{
                return htmlRes;
            }
        }

function sendbardToServerrewrite(text, divId,option,lang,tone='') {

    if(!check_aipost_premium())
    {
         if(Swal){
             Swal.fire({
                      title: 'Error!',
                      text: 'AI Post Limit Quota. Please Upgrade Pro.',
                      icon: 'error',
                      confirmButtonText: 'Close'
                    });
         }else{
            alert('AI Post Limit Quota. Please Upgrade Pro.');
         }
        
        return;
    }
    showProcess();
    var urlapi  = '/bardcontent';
    switch(option){
        case 'writefull':
            urlapi  = '/bardcontentmore';
            break;
        case 'writemore':
            urlapi  = '/bardcontentmore';
            break;

        case 'writebard':
            urlapi  = '/bardcontent';
            break;

        case 'bardrewrite':
            urlapi  = '/bardrewrite';
            
            break;
        default:
            urlapi  = '/bardcontent';
            break;
    }
    urlapi = aiautotool_js_setting.gemini_api + urlapi;
    var dataToSend = {
      question: text,
      lang: lang,
      toneOfVoice:tone,
      domain:aiautotool_js_setting.fsdata.domain,
      info:aiautotool_js_setting.fsdata
    };
    console.log(dataToSend);
    var jsonData = JSON.stringify(dataToSend);
   jQuery.ajax({
      type: "POST",
      url: urlapi,
      data: jsonData,
      contentType: "application/json",
      success: function(data) {
        aiautotool_content = decodeHtmlEntities(replaceH1WithH2(markdown(removeMaskData(reformatHTML(data.result)))));
        // jQuery(`#${divId}`).innerHTML = aiautotool_content;
        jQuery(`#${divId}`).html(aiautotool_content);
        scrollToBottom();
        jQuery('.btnaddpost').show();
        jQuery(".div_proccess").addClass("d-none");
        jQuery(".loadingprocess").addClass("d-none");
        hideLoading();
        update_usage();
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.error("Lỗi khi gửi yêu cầu: " + textStatus, errorThrown);
      }
    });
  
}

function markdown(src,showImage=true){src=src.replace(/(#+)(\w+)/g,'\n$1 $2');var rx_lt=/</g;var rx_gt=/>/g;var rx_space=/\t|\r|\uf8ff/g;var rx_escape=/\\([\\\|`*_{}\[\]()#+\-~])/g;var rx_hr=/^([*\-=_] *){3,}$/gm;var rx_blockquote=/\n *&gt; *([^]*?)(?=(\n|$){2})/g;var rx_list=/\n( *)(?:[*\-+]|((\d+)|([a-z])|[A-Z])[.)]) +([^]*?)(?=(\n|$){2})/g;var rx_listjoin=/<\/(ol|ul)>\n\n<\1>/g;var rx_highlight=/(^|[^A-Za-z\d\\])(([*_])|(~)|(\^)|(--)|(\+\+)|`)(\2?)([^<]*?)\2\8(?!\2)(?=\W|_|$)/g;var rx_code=/\n((```|~~~).*\n?([^]*?)\n?\2|((    .*?\n)+))/g;var rx_link=/((!?)\[(.*?)\]\((.*?)( ".*")?\)|\\([\\`*_{}\[\]()#+\-.!~]))/g;var rx_table=/\n(( *\|.*?\| *\n)+)/g;var rx_thead=/^.*\n( *\|( *\:?-+\:?-+\:? *\|)* *\n|)/;var rx_row=/.*\n/g;var rx_cell=/\||(.*?[^\\])\|/g;var rx_heading=/(?=^|>|\n)([>\s]*?)(#{1,6}) (.*?)( #*)? *(?=\n|$)/g;var rx_para=/(?=^|>|\n)\s*\n+([^<]+?)\n+\s*(?=\n|<|$)/g;var rx_stash=/-\d+\uf8ff/g;function replace(rex,fn){src=src.replace(rex,fn);}
function element(tag,content){return '<'+tag+'>'+content+'</'+tag+'>';}
function blockquote(src){return src.replace(rx_blockquote,function(all,content){return element('blockquote',blockquote(highlight(content.replace(/^ *&gt; */gm,''))));});}
function list(src){return src.replace(rx_list,function(all,ind,ol,num,low,content){var entry=element('li',highlight(content.split(RegExp('\n ?'+ind+'(?:(?:\\d+|[a-zA-Z])[.)]|[*\\-+]) +','g')).map(list).join('</li><li>')));return '\n'+(ol?'<ol start="'+(num?ol+'">':parseInt(ol,36)-9+'" style="list-style-type:'+(low?'low':'upp')+'er-alpha">')+entry+'</ol>':element('ul',entry));});}
function highlight(src){return src.replace(rx_highlight,function(all,_,p1,emp,sub,sup,small,big,p2,content){return _+element(emp?(p2?'strong':'em'):sub?(p2?'s':'sub'):sup?'sup':small?'small':big?'big':'code',highlight(content));});}
function unesc(str){return str.replace(rx_escape,'$1');}
var stash=[];var si=0;src='\n'+src+'\n';replace(rx_lt,'&lt;');replace(rx_gt,'&gt;');replace(rx_space,'  ');src=blockquote(src);replace(rx_hr,'<hr/>');src=list(src);replace(rx_listjoin,'');replace(rx_code,function(all,p1,p2,p3,p4){stash[--si]=element('pre',element('code',p3||p4.replace(/^    /gm,'')));return si+'\uf8ff';});replace(rx_link,function(all,p1,p2,p3,p4,p5,p6){stash[--si]=p4?p2?(showImage==true?(p4.indexOf("http")>-1?'<img src="'+p4+'" alt="'+p3+'" onerror="this.style.display=\'none\'"/>':''):''):'<a href="'+p4+'" alt="'+p3+'">'+unesc(highlight(p3))+'</a>':p6;return si+'\uf8ff';});replace(rx_table,function(all,table){var sep=table.match(rx_thead)[1];return '\n'+element('table',table.replace(rx_row,function(row,ri){return row==sep?'':element('tr',row.replace(rx_cell,function(all,cell,ci){return ci?element(sep&&!ri?'th':'td',unesc(highlight(cell||''))):''}))}))});replace(rx_heading,function(all,_,p1,p2){return _+element('h'+p1.length,unesc(highlight(p2)))});replace(rx_para,function(all,content){return element('p',unesc(highlight(content)))});replace(rx_stash,function(all){return stash[parseInt(all)]});return src.trim();};


async function showProcess(totalTime=20000){
    showLoading();
        // jQuery(".div_proccess_0").addClass("d-none");
        // jQuery(".div_proccess_1").removeClass("d-none");
        // let t_1 = await showAProcess("proccess_1",totalTime);
        // if (isStartSocket == 0){
        //     jQuery(".div_proccess_error").removeClass("d-none");
        //     jQuery(".div_proccess_1").addClass("d-none");
        // }
        
    }
    async function showAProcess(process_id,loadingTime=200000){
        // return await new Promise(resolve => {
        //     jQuery(".div_proccess").addClass("d-none");
        //     jQuery(".div_"+process_id).removeClass("d-none");
        //     let interval = loadingTime/100;
        //     let percent = 0;
        //     myInterval = setInterval(() => {
        //         percent +=1;
        //         jQuery("#" + process_id).html(`${percent} %`);
        //         if (percent > 99){
        //             clearInterval(myInterval);
        //             resolve(1);
        //         }
                
        //     }, interval);
        // });
    }





jQuery(document).ready(function($){
    $('#upload-custom-thumbnail').on('click', function(e){
        e.preventDefault();

        var custom_uploader = wp.media({
            title: 'Choose Custom Thumbnail',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        custom_uploader.on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            $('#custom-thumbnail-id').val(attachment.id);
            $('#custom-thumbnail-preview').html('<img src="' + attachment.url + '">');
        });

        custom_uploader.open();
    });
});





const postTagsElement = document.getElementById('post_tags');

if (postTagsElement) {
    postTagsElement.addEventListener('paste', function (event) {
        
        event.preventDefault();

        const pastedData = (event.clipboardData || window.clipboardData).getData('text');

        const replacedData = pastedData.replace(/\n/g, ',');

        this.value = replacedData;
    });
} 



// lay images tu media
jQuery(document).ready(function($) {
    $('.ft-selec').click(function(e) {
        e.preventDefault();
        var inputId = $(this).data('input-id');
        openMediaUploader(inputId);
    });

    function openMediaUploader(inputId) {
        var customUploader = wp.media({
            title: 'Select image',
            button: {
                text: 'Select'
            },
            multiple: false
        });

        customUploader.on('select', function() {
            var attachment = customUploader.state().get('selection').first().toJSON();
            var imageUrl = attachment.url;
            $('#' + inputId).val(imageUrl);
        });

        customUploader.open();
    }
});


jQuery(document).ready(function($) {
        $('form input[type="checkbox"]').change(function() {
            var currentForm = $(this).closest('form');
            $.ajax({
                type: 'POST',
                url: currentForm.attr('action'), 
                data: currentForm.serialize(), 
                success: function(response) {
                    console.log('Turn on successfully');
                },
                error: function() {
                    console.log('Error in AJAX request');
                }
            });
        });
    });



function initializetextare() {
    jQuery('.aiautotool_container textarea').each(function() {
        //var textarea = jQuery(this);
        var editor = CodeMirror.fromTextArea(this, {
            lineNumbers: true,
            lineWrapping: true,
            matchBrackets: true,
            mode: 'text/x-perl',
            theme: 'cobalt',
        });
        jQuery(this).data('CodeMirrorInstance', editor);
       

        jQuery(document).on('keyup', '.CodeMirror-code', function(){
                editor.codemirror.save();
                textarea.html(editor.codemirror.getValue());
                textarea.trigger('change');
            });

    });
}

function updateCodeMirrorSize() {
    jQuery('.aiautotool_container textarea').each(function() {
        var textarea = jQuery(this);
        var editor = wp.codeEditor._instances[textarea.attr('id')];

        // Update CodeMirror's size
        editor.codemirror.setSize("100%", editor.codemirror.getWrapperElement().clientHeight);
    });
}


jQuery(document).ready(function($) {
    // initializetextare();
});




jQuery("body").on("click", ".Youtube_thumb", function(res) {
        res.preventDefault();
        let yId = jQuery(this).attr("data-id");
        let yTubeHTML = `<div class="youtubeVideo"><iframe src="https://www.youtube.com/embed/${yId}" width="560" height="314" allowfullscreen="allowfullscreen"></iframe></div>`;
        tinymce.activeEditor.insertContent(yTubeHTML);
        jQuery("input[name=video]").val("https://www.youtube.com/watch?v=" + yId);
        
    })


