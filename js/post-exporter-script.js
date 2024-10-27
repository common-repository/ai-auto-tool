function fetchPosts(idlist,divid) {
    var urlList = jQuery('#'+idlist).val().split('\n');
    showLoading();
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'fetch_posts_by_urls',
            security: postExporterData.nonce,
            urlList: urlList,
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                var posts = response.data;
                displayPosts(posts,divid);
            } else {
                // Xử lý lỗi nếu cần thiết
                console.log(response.data);
            }
        },
        error: function() {
            hideLoading();
            // Xử lý lỗi Ajax nếu cần thiết
        }
    });
}

function displayPosts(posts,divid) {
    jQuery('#'+divid).empty();
    let count = 1;
    jQuery('#'+divid).append('<button type="button" onclick="checkAll()">Check All</button><br>');
    posts.forEach(function(post) {
        var encodedContent = encodeURIComponent(post.content);
         var tagsString = post.tags.join(', ');
        var categoriesString = post.categories.join(', ');

        var checkbox = '<input type="checkbox" class="postCheckbox" ' +
            'data-title="' + post.title + '" ' +
            'data-post-id="' + post.id + '" ' +
            'data-content="' + encodedContent + '" ' +
            'data-tags="' + tagsString + '" ' +
            'data-slug="' + post.slug + '" ' +
            'data-language="' + post.language + '" ' +
            'data-categories="' + categoriesString + '"> ' +
            ' ' + count + ' - ' +
            post.title + '<br>';

        jQuery('#'+divid).append(checkbox);
        count =  count+1;
    });

    // Add the "Check All" button
    jQuery('#'+divid).append('<button type="button" onclick="checkAll()">Check All</button>');
}

function checkAll() {
    var checkboxes = jQuery('.postCheckbox');

    // Check if at least one checkbox is unchecked
    var isAtLeastOneUnchecked = checkboxes.is(':not(:checked)');

    // If at least one checkbox is unchecked, check all checkboxes; otherwise, uncheck all
    checkboxes.prop('checked', isAtLeastOneUnchecked);

}


function exportToJson() {
    var selectedPosts = jQuery('.postCheckbox:checked');
    var exportData = [];

    selectedPosts.each(function() {
        var title = jQuery(this).data('title');
        var postId = jQuery(this).data('post-id');
        var content = decodeURIComponent(jQuery(this).data('content'));
        // var decodedContent = decodeURIComponent(encodedContent);
        var slug = jQuery(this).data('slug');
        var tags = jQuery(this).data('tags');
        var categories = jQuery(this).data('categories');
        var language = jQuery(this).data('language');

        var post = {
            title:title,
            content:content,
            slug:slug,
            tags:tags,
            categories:categories,
            language:language,
        }
        exportData.push(post);

        
    });

    var jsonData = JSON.stringify(exportData);

    // Code để download JSON file
    var blob = new Blob([jsonData], { type: 'application/json' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'exported_posts.json';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

function importFromJson() {
    var jsonUrl = prompt('Nhập URL hoặc đường dẫn tới tệp JSON bạn muốn import:');
    if (jsonUrl) {
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'import_from_json',
            security: postExporterData.nonce,
                json_data_url: jsonUrl,
            },
            success: function(response) {
                console.log('Import successful!');
            },
            error: function() {
                console.log('Error importing posts from ' + jsonUrl);
            }
        });
    }
}

function importFromJsonFile() {
    var fileInput = document.getElementById('jsonFileInput');
    var file = fileInput.files[0];
    
    if (file) {
        showLoading();

        var reader = new FileReader();
        reader.onload = function(e) {
            var jsonData = JSON.parse(e.target.result);

            // Gọi hàm Ajax để tiến hành import vào WordPress
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'import_from_json_file',
                    security: postExporterData.nonce,
                    json_data: jsonData,
                },
                success: function(response) {
                    hideLoading();

                    if (response.success) {
                        console.log('Import successful!');
                    } else {
                        // Xử lý lỗi nếu cần thiết
                        console.log(response.data);
                    }
                },
                error: function() {
                    hideLoading();
                    
                    // Xử lý lỗi Ajax nếu cần thiết
                }
            });
        };

        reader.readAsText(file);
    }
}


function DeletePostex() {
    var selectedPosts = jQuery('.postCheckbox:checked');
    var exportData = [];

    selectedPosts.each(function() {
        var title = jQuery(this).data('title');
        var postId = jQuery(this).data('post-id');
        var content = decodeURIComponent(jQuery(this).data('content'));
        // var decodedContent = decodeURIComponent(encodedContent);
        var slug = jQuery(this).data('slug');
        var tags = jQuery(this).data('tags');
        var categories = jQuery(this).data('categories');
        var language = jQuery(this).data('language');

        var post = {
            id:postId,
        }
        exportData.push(post);

        
    });

    showLoading();
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'delete_post_not_in',
            security: postExporterData.nonce,
            exportData: exportData,
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
               alert(response.data);
            } else {
                // Xử lý lỗi nếu cần thiết
                console.log(response.data);
            }
        },
        error: function() {
            hideLoading();
            // Xử lý lỗi Ajax nếu cần thiết
        }
    });
    
}

function restoreTrashedPosts() {
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'restore_trashed_posts_action', // Đặt tên action AJAX của bạn
                    security: postExporterData.nonce, // Nonce for security
                },
                success: function(response) {
                    alert(response.data);
                    // You can perform additional actions after successfully restoring the posts
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }
