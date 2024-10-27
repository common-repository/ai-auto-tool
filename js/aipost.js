function isEditOrNewPostPage() {
    var bodyClasses = document.body.classList;
    var isPostNewPage = bodyClasses.contains('post-new-php');
    var isPostPage = bodyClasses.contains('post-php');
    var isAiautotoolPage = bodyClasses.contains('aiautotool_input');
    
    var isAiPostInUrl = window.location.href.includes('ai_post');
    var isAiSinglePostInUrl = window.location.href.includes('ai_single_post');

    return isPostNewPage || isPostPage || isAiautotoolPage || isAiPostInUrl || isAiSinglePostInUrl;
}


document.addEventListener('DOMContentLoaded', function () {
    // Chờ cho đến khi trang hoàn toàn tải xong
    window.onload = function () {
        // Khai báo editor sau khi trang đã tải xong
        if(typeof tinyMCE !== "undefined"){
            var editor = tinyMCE.activeEditor;
        console.log('co editor');
        // create_buttoneditor();

        if (editor) {
             var tabbar = document.createElement('div');
tabbar.id = 'tabbar';
tabbar.className = 'mce-toolbar-grp mce-inline-toolbar-grp  mce-panel ';
tabbar.style.display = 'none';
let svg_UIFindImage ='<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M7 7C5.34315 7 4 8.34315 4 10C4 11.6569 5.34315 13 7 13C8.65685 13 10 11.6569 10 10C10 8.34315 8.65685 7 7 7ZM6 10C6 9.44772 6.44772 9 7 9C7.55228 9 8 9.44772 8 10C8 10.5523 7.55228 11 7 11C6.44772 11 6 10.5523 6 10Z" fill="currentColor" /><path fill-rule="evenodd" clip-rule="evenodd" d="M3 3C1.34315 3 0 4.34315 0 6V18C0 19.6569 1.34315 21 3 21H21C22.6569 21 24 19.6569 24 18V6C24 4.34315 22.6569 3 21 3H3ZM21 5H3C2.44772 5 2 5.44772 2 6V18C2 18.5523 2.44772 19 3 19H7.31374L14.1924 12.1214C15.364 10.9498 17.2635 10.9498 18.435 12.1214L22 15.6863V6C22 5.44772 21.5523 5 21 5ZM21 19H10.1422L15.6066 13.5356C15.9971 13.145 16.6303 13.145 17.0208 13.5356L21.907 18.4217C21.7479 18.7633 21.4016 19 21 19Z" fill="currentColor" /></svg>';
    
var findImgButton = document.createElement('button');
 findImgButton.className = 'aiautotool_btn_v1 aiautotool_btn_v13';
findImgButton.id = 'find-img-button';
findImgButton.innerHTML = '<i class="fa-solid fa-image"></i> Find Img';

var youtubeButton = document.createElement('button');
youtubeButton.id = 'youtube-button';
youtubeButton.className = ' aiautotool_btn_v1 aiautotool_btn_v13';


youtubeButton.innerHTML = '<i class="fa-solid fa-video"></i> Find Video';

var writeButton = document.createElement('button');
writeButton.id = 'write-button';
writeButton.className = 'aiautotool_btn_v1 aiautotool_btn_v13 ';


writeButton.innerHTML = '<i class="fa-solid fa-pen-nib"></i> Write';


var divdrop = document.createElement('div');
divdrop.className = 'aiautotool_dropdown';

var bardrewrite = document.createElement('button');
bardrewrite.id = 'bardrewrite';
bardrewrite.className = ' aiautotool_btn_v1 aiautotool_btn_v13   ';


bardrewrite.innerHTML = '<i class="fa-solid fa-pen-nib"></i> Rewrite';


var submenu = document.createElement('div');
submenu.className = 'aiautotool_dropdown-content';
// submenu.style = "top: 12px !important;position: inherit;left: -15px;display:none;";
submenu.innerHTML = '' +
    '<div class=" column">' +

        '<div class="mce-menu-item" id="submenuItem1">Shorter</div>' +
        '<div class="mce-menu-item" id="submenuItem2">Longer</div>' +
        '<div class="mce-menu-item" id="submenuItem3">Professional</div>' +
    '</div>';

// Thêm submenu vào nút bardrewrite
bardrewrite.appendChild(submenu);

bardrewrite.addEventListener('mouseover', function() {
    submenu.style.display = 'block';
});

bardrewrite.addEventListener('mouseout', function() {
    submenu.style.display = 'none';
});

divdrop.appendChild(bardrewrite);

var chatgptButton = document.createElement('button');
chatgptButton.id = 'chatgpt-button';
chatgptButton.className = 'aiautotool_btn_v1 aiautotool_btn_v13';


chatgptButton.innerHTML = '<i class="fa-solid fa-pen-nib"></i> Gpt Write';


tabbar.appendChild(youtubeButton);
tabbar.appendChild(findImgButton);
tabbar.appendChild(writeButton);
tabbar.appendChild(chatgptButton);
tabbar.appendChild(divdrop);

document.body.appendChild(tabbar);



var submenuItem1 = document.getElementById('submenuItem1');
var submenuItem2 = document.getElementById('submenuItem2');
var submenuItem3 = document.getElementById('submenuItem3');
if (editor.selection) {
        var selectedText = editor.selection.getContent({ format: 'text' });
    
    submenuItem1.addEventListener('click', function() {
        // Thực hiện công việc khi bấm vào Submenu Item 1

        open_box_aiautotool();
        openTab('aiContentTab');
        var selectedText = '';
        if (editor.selection) {
                 selectedText  = editor.selection.getContent({ format: 'text' });
        }
        var titleValue = selectedText;

        var post_language = get_lang();

        if (post_language.length) {
            // Nếu tồn tại, lấy giá trị của #aiautotool_title
            post_language = post_language;

        } else {
             post_language = langcheck;
        }


        if (!selectedText.trim()) {
            if(Swal){
                 Swal.fire({
                          title: 'Error!',
                          text: 'Please select text in the content.',
                          icon: 'error',
                          confirmButtonText: 'Close'
                        });
             }else{
                alert('Please select text in the content');
             }
            
            return;
        }
          var divId = "outbard"; // Thay đổi ID của div tại đây
         

            if (languageCodes.hasOwnProperty(post_language)) {
               langcheck = languageCodes[post_language];
              
            } 
             
         sendbardToServerrewrite(titleValue, divId,'bardrewrite',langcheck,'Make text shorten.');
    });

    submenuItem2.addEventListener('click', function() {
        open_box_aiautotool();
        openTab('aiContentTab');
         var selectedText = '';
        if (editor.selection) {
                 selectedText  = editor.selection.getContent({ format: 'text' });
        }
        var titleValue = selectedText;

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
                          text: 'Please select text in the content.',
                          icon: 'error',
                          confirmButtonText: 'Close'
                        });
             }else{
                alert('Please select text in the content');
             }
            
            return;
        }
          var divId = "outbard"; // Thay đổi ID của div tại đây
         

            if (languageCodes.hasOwnProperty(post_language)) {
               langcheck = languageCodes[post_language];
              
            } 

          sendbardToServerrewrite(titleValue, divId,'bardrewrite',langcheck,'Make text longer tone of voice.');
    });

    submenuItem3.addEventListener('click', function() {
        open_box_aiautotool();
        openTab('aiContentTab');
         var selectedText = '';
        if (editor.selection) {
                 selectedText  = editor.selection.getContent({ format: 'text' });
        }
        var titleValue = selectedText;

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
                          text: 'Please select text in the content.',
                          icon: 'error',
                          confirmButtonText: 'Close'
                        });
             }else{
                alert('Please select text in the content');
             }
            
            return;
        }
          var divId = "outbard"; // Thay đổi ID của div tại đây
         

            if (languageCodes.hasOwnProperty(post_language)) {
               langcheck = languageCodes[post_language];
              
            } 

          sendbardToServerrewrite(titleValue, divId,'bardrewrite',langcheck,'Make text use professional tone of voice.');
    });
}
            editor.on('mouseup keyup', function() {
    // Kiểm tra xem có văn bản nào đang được chọn hay không
                if (editor.selection) {
                  var selectedText = editor.selection.getContent({ format: 'text' });
                  if(selectedText!=''){
                      
                      var Writingstyle =  jQuery('#Writingstyle').val();

                      var Writingtone =  jQuery('#Writingtone').val();

                      var selectionRange = editor.selection.getRng();
                      var selectionRect = selectionRange.getBoundingClientRect();

                      // Tính toán vị trí của tabbar
                      var editorRect = editor.getContainer().getBoundingClientRect();
                      var tabbarLeft = editorRect.left + selectionRect.left;
                      var tabbarTop = editorRect.top + selectionRect.bottom + window.pageYOffset;

                      // Hiển thị tabbar
                      tabbar.style.display = 'block';
                      tabbar.style.top = (tabbarTop-50) + 'px';
                      tabbar.style.left = tabbarLeft + 'px';



                      // Gắn sự kiện cho các nút trong tabbar
                      youtubeButton.addEventListener('click', function(event) {
                        open_box_aiautotool();
                        openTab('videoTab');
                        jQuery('.infodiv').hide();
                         event.stopPropagation();
                        console.log('Find Video clicked');
                        console.log('Selected text:', selectedText);
                        // Gọi API tìm ảnh
                        // jQuery('#video_list_find').html('<div id="loading-icon" class="loader" style="display:block;width:100% !important;:100% !important"></div>');
                        showLoading();
                        openTab('videoTab');
                       
                        jQuery.ajax({
                        url: 'https://bard.aitoolseo.com/youtubesearch',
                        type: 'GET',
                        contentType: 'application/json',
                        data: { q: selectedText },
                        success: function(r) {
                            if (r.result) {
                                hideLoading();
                                jQuery('#video_list_find').html('');

                                r.result.forEach(function(video) {
                                    console.log(video)
                                    jQuery('#video_list_find').prepend('<div data-id="'+video.id+'" class="Youtube_thumb "><img src="'+video.thumbnails[0].url+'" /></div>');
                                });
                            }
                        }
                    });
                         tabbar.style.display = 'none';
                      });

                      // Gắn sự kiện cho các nút trong tabbar
                      findImgButton.addEventListener('click', function(event) {
                        open_box_aiautotool();
                        openTab('imagesTab');
                        jQuery('.infodiv').hide();
                         event.stopPropagation();
                        console.log('Find Img clicked');
                        console.log('Selected text:', selectedText);
                        // Gọi API tìm ảnh
                        jQuery('#img_list_find').html('<div id="loading-icon" class="loader" style="display:block;width:100% !important;:100% !important"></div>');
                        openTab('imagesTab');
                       
                        jQuery.ajax({
                        url: 'https://bard.aitoolseo.com/searchimg',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({ question: selectedText }),
                        success: function(rrr) {
                            if (rrr.result) {
                                jQuery('#img_list_find').html('');

                                rrr.result.forEach(function(url) {
                                    jQuery('#img_list_find').prepend('<div data-src="'+url+'" class="img_search"><img src="'+url+'"></div>');
                                });
                            }
                        }
                    });

                        tabbar.style.display = 'none';
                      });
                      var aiautotool_btnwrite_content = '';
                      writeButton.addEventListener('click', function(event) {
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
                        open_box_aiautotool();
                        openTab('aiContentTab');
                        var titleValue = selectedText;
        
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
                                          text: 'Please select text in the content.',
                                          icon: 'error',
                                          confirmButtonText: 'Close'
                                        });
                             }else{
                                alert('Please select text in the content');
                             }
                            
                            return;
                        }
                          var divId = "outbard"; // Thay đổi ID của div tại đây
                         

                            if (languageCodes.hasOwnProperty(post_language)) {
                               langcheck = languageCodes[post_language];
                              
                            } 

                          sendbardToServer(titleValue, divId,'writemore',langcheck);
                        
                        
                      });

                      bardrewrite.addEventListener('click', function(event) {
                        // if(!check_aipost_premium())
                        // {
                        //      if(Swal){
                        //          Swal.fire({
                        //                   title: 'Error!',
                        //                   text: 'AI Post Limit Quota. Please Upgrade Pro.',
                        //                   icon: 'error',
                        //                   confirmButtonText: 'Close'
                        //                 });
                        //      }else{
                        //         alert('AI Post Limit Quota. Please Upgrade Pro.');
                        //      }
                            
                        //     return;
                        // }
                        // open_box_aiautotool();
                        // openTab('aiContentTab');
                        // var titleValue = selectedText;
        
                        // var post_language = get_lang();

                        // if (post_language.length) {
                        //     // Nếu tồn tại, lấy giá trị của #aiautotool_title
                        //     post_language = post_language;

                        // } else {
                        //      post_language = langcheck;
                        // }


                        // if (!titleValue.trim()) {
                        //     if(Swal){
                        //          Swal.fire({
                        //                   title: 'Error!',
                        //                   text: 'Please select text in the content.',
                        //                   icon: 'error',
                        //                   confirmButtonText: 'Close'
                        //                 });
                        //      }else{
                        //         alert('Please select text in the content');
                        //      }
                            
                        //     return;
                        // }
                        //   var divId = "outbard"; // Thay đổi ID của div tại đây
                         

                        //     if (languageCodes.hasOwnProperty(post_language)) {
                        //        langcheck = languageCodes[post_language];
                              
                        //     } 

                        //   sendbardToServer(titleValue, divId,'bardrewrite',langcheck);
                        
                        
                      });

                      chatgptButton.addEventListener('click', function(event) {
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
                        open_box_aiautotool();
                        openTab('aiContentTab');
                        event.stopPropagation();
                        var titleValue = selectedText;
                        var post_language = get_lang();
                        if (languageCodes.hasOwnProperty(post_language)) {
                           langcheck = languageCodes[post_language];
                          
                        } 
                          var divId = "outbard"; // Thay đổi ID của div tại đây
                          sendTextToServer(titleValue, divId,'writemore',langcheck);
                        
                        console.log('viết');
                        
                      });


                  }else{
                    tabbar.style.display = 'none';
                  }
                  

                } else {
                  // Ẩn tabbar nếu không có nội dung nào được chọn
                  tabbar.style.display = 'none';
                  dangviet = false;
                }
                event.stopPropagation();
              });
        }
        }
        
    };
});
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

function get_title()
{
    var titleInput = document.getElementById('title');

// Kiểm tra xem phần tử có tồn tại hay không
if (titleInput) {
    console.log('Element with ID "title" exists.');

} else {
    console.log('Element with ID "title" does not exist.');

    titleInput = document.getElementById('aiautotool_title');

    if (titleInput) {
        console.log('Element with ID "aiautotool_title" exists.');
    } else {
        titleInput = document.querySelector("div.edit-post-header-toolbar__left");
        console.log('Element with ID "h1.wp-block-post-title" does not exist.');
    }
}
    return titleInput;
}



function get_lang()
{
    var post_language = document.getElementById('post_language');

// Kiểm tra xem phần tử có tồn tại hay không
if (post_language) {
    console.log('Element with ID "title" exists.');

} else {
    console.log('Element with ID "title" does not exist.');

    post_language = document.getElementById('post_language');

    if (post_language) {
        console.log('Element with ID "aiautotool_title" exists.');
    } else {
        console.log('Element with ID "aiautotool_title" does not exist.');
    }
}
    return post_language.value;
}
function scrollToBottom() {
    var outbard = document.getElementById("outbard");
    if(outbard){
        outbard.scrollTop = outbard.scrollHeight;
    }
        
    }

// Hàm xử lý sự kiện khi nút được click
function handleButtonClick() {
    open_box_aiautotool();
}

if (isEditOrNewPostPage()) {
    // Lấy input title
    var titleInput = get_title();

    // Tạo button
    var moretool = createButton('open-modal','<i class="mce-ico mce-i-dashicon dashicons-edit"></i>', "More tool", null, " aiautotool_btn_v1 aiautotool_btn_v11");
    var bardbtn = createButton('bard-write','<i class="mce-ico mce-i-dashicon dashicons-edit"></i>', "Gemini write", handleButtonClick, " aiautotool_btn_v1 aiautotool_btn_v12 btn_writer bard-write");

    var chatgpt = createButton('chatgpt-write','<i class="mce-ico mce-i-dashicon dashicons-edit"></i>', "Chatgpt write", handleButtonClick, " aiautotool_btn_v1 aiautotool_btn_v13 btn_bardWriter chatgpt-write");
    var yourprompt = createButton('your-prompt','<i class="fa-solid fa-robot"></i>', "Ask Assistant", handleButtonClick, " aiautotool_btn_v1 aiautotool_btn_v13 btn_askassistant ");
   var suggetstitle = createButton('btn_suggettitle','<i class="fa-solid fa-lightbulb"></i> ', "Suggest title ", null, " aiautotool_btn_v1 aiautotool_btn_v16 btn_bardWriter btn_suggettitle");
    var selectBox = document.createElement('select');
    selectBox.name = 'post_language';
    selectBox.id = 'post_language';
    for (var code in aiautotool_js_setting.languageCodes) {
    if (aiautotool_js_setting.languageCodes.hasOwnProperty(code)) {
            var option = document.createElement('option');
            option.value = code;
            if (langcheck === code) {
                option.selected = true;
            }
            option.text = aiautotool_js_setting.languageCodes[code];
            selectBox.appendChild(option);
        }
    }
    // Chèn button vào sau input title

    titleInput.parentNode.insertBefore(moretool, titleInput.nextSibling);
    titleInput.parentNode.insertBefore(yourprompt, titleInput.nextSibling);
    titleInput.parentNode.insertBefore(bardbtn, titleInput.nextSibling);
    titleInput.parentNode.insertBefore(chatgpt, titleInput.nextSibling);
    titleInput.parentNode.insertBefore(suggetstitle, titleInput.nextSibling);


    var titleInput = document.getElementById('title');

    if (document.getElementById('title')) {
       titleInput.parentNode.insertBefore(selectBox, titleInput.nextSibling);

    }
    




    const aiautotoolDiv = document.createElement('div');
        aiautotoolDiv.id = 'aiautotool_bar_right';
        
        document.body.appendChild(aiautotoolDiv);

        // Kiểm tra xem có phần tử có id là "aiautotool-meta-box" hay không
        var metaBoxElement = document.getElementById("aiautotool-meta-box");

        // Kiểm tra xem phần tử có tồn tại hay không
        if (metaBoxElement) {
            // Nếu tồn tại, thực hiện các thao tác khác ở đây

            metaBoxElement.parentNode.removeChild(metaBoxElement);
            var contentToMove = metaBoxElement.innerHTML;

            // Đặt nội dung vào div có id là "aiautotool_bar_right"
            
            aiautotoolDiv.innerHTML = contentToMove;
        } else {
            // Nếu không tồn tại, thông báo hoặc thực hiện các thao tác khác ở đây
            aiautotoolDiv.innerHTML = '';
            console.log("Không tìm thấy phần tử có id là 'aiautotool-meta-box'");
        }

        // Tạo và thêm button vào trang
        const toggleButton = document.createElement('div');
        toggleButton.id = 'toggleButton';
        toggleButton.addClass = 'aiautotool_btn_menu';
        
        toggleButton.innerText = 'Open Aiautotool';
        document.body.appendChild(toggleButton);

        const sidebar = document.getElementById('aiautotool_bar_right');

        toggleButton.addEventListener('click', () => {
            const sidebar = document.getElementById('aiautotool_bar_right');
            var toggleButton = document.getElementById('toggleButton');
            const isOpen = sidebar.style.right === '0px';
            
            if (isOpen) {
                sidebar.style.right = '-300px';
                toggleButton.classList.remove('open');
                toggleButton.innerText = 'Open Aiautotool';
            } else {
                sidebar.style.right = '0';
                toggleButton.classList.add('open');
                toggleButton.innerText = 'Close Aiautotool';
            }
        });

       
        function open_box_aiautotool(){
            const sidebar = document.getElementById('aiautotool_bar_right');
            var toggleButton = document.getElementById('toggleButton');
            const isOpen = sidebar.style.right === '0px';
            
            if (isOpen) {
                
            } else {
                sidebar.style.right = '0';
                toggleButton.classList.add('open');
                toggleButton.innerText = 'Close Aiautotool';
            }
        }









                

                function updatepost(keyToUpdate, data) {
                    var stext = '';
                    var desc = '';
                    if (data?.status == 'writing') {
                        post[keyToUpdate].stext += data.sText;
                    } else if (data?.status == 'complete') {
                        if (post[keyToUpdate].desc == '') {
                            post[keyToUpdate].desc = data.text;
                        }
                    }
                }

                function displayAllPosts(divId) {
                    let containerDiv = document.getElementById(divId);

                    if (!containerDiv) {
                        console.error(`Div with id ${divId} not found.`);
                        return;
                    }
                   
                    let stringhtml = '';

                    for (let i = 0; i < post.length; i++) {
                        let postItem = post[i];
                        if (postItem.desc == '') {
                            if (postItem.stext != '') {
                                stringhtml += '<h2>' + postItem.h2 + '</h2>' + markdown(removeMaskData(postItem.stext), false);
                            }
                        } else {
                            stringhtml += '<h2>' + postItem.h2 + '</h2>' + markdown(removeMaskData(postItem.desc), false);
                        }
                    }
                    aiautotool_content = stringhtml;
                    jQuery("#" + divId).html(stringhtml);
                    

                    scrollToBottom()
                }

                function checkpost() {
                    if (post.length > 0) {
                        console.log(indexnow, post.length);
                        if (indexnow < (post.length - 1)) {
                            if (istrue) {
                                for (let i = 0; i < post.length; i++) {
                                    let postItem = post[i];
                                    if (postItem.desc == '') {
                                        istrue = false;
                                        indexnow = i;
                                        runpost(indexnow);
                                        break;
                                    }
                                }
                            }
                        } else {
                            clearInterval(checkpostin);
                            statussocket = false;
                            jQuery('.btnaddpost').show();
                            console.log('kết thúc');
                            
                        }
                    }
                }

                function runpost(index) {
                    idhead = Math.floor(Math.random() * 1000);
                    headtitle = post[index].h2;
                    
                    
                    var post_language = get_lang();
                    if (languageCodes.hasOwnProperty(post_language)) {
                       langcheck = languageCodes[post_language];
                      
                    } 
                      var divId = "outbard"; 

                    var socketObj = {
                    text: '' + headtitle + ', ' + question,
                    option:'writemorelong',
                    language: langcheck,
                    domain:hostname
                  };

                    socket.emit('aiautotool-writefull', socketObj);
                    statussocket = true;
                    socket.on("aiautotool-writefull-return", function (data) {
                        isStartSocket = 1;
                        data = JSON.parse(data);
                        updatepost(Number(index), data);
                        displayAllPosts(divids);
                        if (data?.status == 'complete') {
                            istrue = true;

                        }
                    });
                }

                var checkpostin = setInterval(function () {
                    checkpost();
                }, 1000);
}




const modalContainer = document.querySelector(".aiautotool-modal-container");
const openModalButton = document.querySelector("#open-modal");
const closeModalButton = document.querySelector(".aiautotool-modal-close");

if (openModalButton) {
    openModalButton.addEventListener("click", () => {
        modalContainer.style.display = "block";
    });
}
if (closeModalButton) {
closeModalButton.addEventListener("click", () => {
    modalContainer.style.display = "none";
});
}





document.addEventListener('DOMContentLoaded', function() {
    // Tạo một button mới
    var suggestTagButton = document.createElement('button');
    suggestTagButton.innerHTML = '<i class="fa-solid fa-tag"></i> AI Suggest Tag';
    suggestTagButton.setAttribute('type', 'button');
    suggestTagButton.setAttribute('id', 'ai-suggest-tag-button');
    suggestTagButton.setAttribute('class', 'button');

    // Lấy phần tử có id 'new-tag-post_tag'
    var newTagInput = document.getElementById('new-tag-post_tag');
    if (newTagInput) {
        
        newTagInput.parentNode.insertBefore(suggestTagButton, newTagInput.nextSibling);

        suggestTagButton.addEventListener('click', function() {
            // Lấy giá trị của trường tiêu đề
            var titleInput = document.getElementById('title');
            var titleValue = titleInput.value.trim(); // Xóa khoảng trắng ở đầu và cuối chuỗi

            // Kiểm tra xem tiêu đề có rỗng không
            if (titleValue === '') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please enter the title of the post before suggesting tags.',
                        icon: 'error',
                        confirmButtonText: 'Close'
                    });
                } else {
                    alert('Please enter the title of the post before suggesting tags.');
                }
                return;
            } else {
               
                suggestTags();
            }
        });
    } 
    
});



function suggestTags() {
    // Lấy giá trị của trường tiêu đề
    var titleInput = document.getElementById('title');
    var lang = get_lang();
    var titleValue = titleInput.value.trim(); 

    var data = {
        'action': 'aiautotool_suggest_tag',
        'title': titleValue,
        'lang': lang,
        'security': aiautotool_js_setting.security 
    };
    showLoading('AI create tag for: '+ titleInput.value);
    jQuery.ajax({
        url: aiautotool_js_setting.ajax_url,
        type: 'POST',
        data: data,
        beforeSend: function() {
            
        },
        success: function(response) {
            hideLoading();

            var tags = response.data.map(function(item) {
                return item.tag; 
            }).join(', ');

            jQuery('#new-tag-post_tag').val(tags);
            console.log(tags);
        },
        error: function(xhr, status, error) {
            
            hideLoading();

            alert('Error: ' + error);
        }
    });
}


// const modalContainer = document.querySelector(".aiautotool-modal-container");
// const openModalButton = document.querySelector("#open-modal");
// const closeModalButton = document.querySelector(".aiautotool-modal-close");
// const modalOverlay = document.querySelector(".aiautotool-modal-overlay");
// const modalContent = document.querySelector(".aiautotool-modal-content");

// openModalButton.addEventListener("click", () => {
//     modalContainer.style.visibility = "visible";
//     modalOverlay.style.opacity = "1";
//     modalContent.style.opacity = "1";
    
// });

// closeModalButton.addEventListener("click", () => {
//     modalOverlay.style.opacity = "0";
//     modalContent.style.opacity = "0";
//     setTimeout(() => {
//         modalContainer.style.visibility = "hidden";
//     }, 300); // Thời gian transition là 0.3s
// });

