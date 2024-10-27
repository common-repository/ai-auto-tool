
jQuery(document).ready(function($) {
    $(document).on('click', '.custom-tag-create-desc', function(e) {
        e.preventDefault();
        showLoading();
        var tag_id = $(this).data('tag-id');
        $.ajax({
            url: customTagButtonAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'custom_tag_button_create_desc',
                tag_id: tag_id,
                
            },
            success: function(response) {
                hideLoading();
                if (response === 'success') {
                    alert('Tag description created successfully.');
                } else {
                    alert('Failed to create tag description.');
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                alert('Error: ' + xhr.responseText);
            }
        });
    });
});
