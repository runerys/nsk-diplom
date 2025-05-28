/* Admin JavaScript for NSK Diplom Plugin */
jQuery(document).ready(function($) {
    
    // Media uploader for diploma image
    $('#upload_diplom_bilde').on('click', function(e) {
        e.preventDefault();
        
        var mediaUploader = wp.media({
            title: 'Velg bilde av diplom',
            button: {
                text: 'Bruk dette bildet'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });
        
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#nsk_diplom_bilde').val(attachment.id);
            $('#diplom_bilde_preview').html('<img src="' + attachment.url + '" style="max-width: 200px; height: auto;" />');
        });
        
        mediaUploader.open();
    });
    
    // Media uploader for team/organizer image
    $('#upload_lag_bilde').on('click', function(e) {
        e.preventDefault();
        
        var mediaUploader = wp.media({
            title: 'Velg bilde av stafettlag/arrangørgjeng',
            button: {
                text: 'Bruk dette bildet'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });
        
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#nsk_lag_bilde').val(attachment.id);
            $('#lag_bilde_preview').html('<img src="' + attachment.url + '" style="max-width: 200px; height: auto;" />');
        });
        
        mediaUploader.open();
    });
    
    // Remove image functionality
    $(document).on('click', '#diplom_bilde_preview img', function() {
        if (confirm('Vil du fjerne dette bildet?')) {
            $('#nsk_diplom_bilde').val('');
            $('#diplom_bilde_preview').html('');
        }
    });
    
    $(document).on('click', '#lag_bilde_preview img', function() {
        if (confirm('Vil du fjerne dette bildet?')) {
            $('#nsk_lag_bilde').val('');
            $('#lag_bilde_preview').html('');
        }
    });
    
    // Add hover effect to preview images
    $(document).on('mouseenter', '#diplom_bilde_preview img, #lag_bilde_preview img', function() {
        $(this).css({
            'opacity': '0.8',
            'cursor': 'pointer'
        });
    });
    
    $(document).on('mouseleave', '#diplom_bilde_preview img, #lag_bilde_preview img', function() {
        $(this).css('opacity', '1');
    });
});
