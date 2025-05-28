/* Frontend JavaScript for NSK Diplom Plugin */
jQuery(document).ready(function($) {
    
    // Image lightbox functionality
    $('.nsk-diplom-image img, .nsk-lag-image img').on('click', function() {
        var imgSrc = $(this).attr('src');
        var imgAlt = $(this).attr('alt');
        
        // Create lightbox
        var lightbox = $('<div class="nsk-lightbox">' +
            '<div class="nsk-lightbox-content">' +
                '<span class="nsk-lightbox-close">&times;</span>' +
                '<img src="' + imgSrc + '" alt="' + imgAlt + '">' +
            '</div>' +
        '</div>');
        
        // Add lightbox styles
        lightbox.css({
            'position': 'fixed',
            'top': '0',
            'left': '0',
            'width': '100%',
            'height': '100%',
            'background-color': 'rgba(0,0,0,0.8)',
            'z-index': '9999',
            'display': 'flex',
            'align-items': 'center',
            'justify-content': 'center'
        });
        
        lightbox.find('.nsk-lightbox-content').css({
            'position': 'relative',
            'max-width': '90%',
            'max-height': '90%'
        });
        
        lightbox.find('img').css({
            'max-width': '100%',
            'max-height': '100%',
            'height': 'auto',
            'border-radius': '5px'
        });
        
        lightbox.find('.nsk-lightbox-close').css({
            'position': 'absolute',
            'top': '-30px',
            'right': '0',
            'color': 'white',
            'font-size': '28px',
            'font-weight': 'bold',
            'cursor': 'pointer',
            'background': 'rgba(0,0,0,0.5)',
            'border-radius': '50%',
            'width': '30px',
            'height': '30px',
            'display': 'flex',
            'align-items': 'center',
            'justify-content': 'center'
        });
        
        // Add to body
        $('body').append(lightbox);
        
        // Close lightbox functionality
        lightbox.on('click', function(e) {
            if (e.target === this || $(e.target).hasClass('nsk-lightbox-close')) {
                lightbox.fadeOut(300, function() {
                    lightbox.remove();
                });
            }
        });
        
        // Close on Escape key
        $(document).on('keyup.lightbox', function(e) {
            if (e.keyCode === 27) {
                lightbox.fadeOut(300, function() {
                    lightbox.remove();
                    $(document).off('keyup.lightbox');
                });
            }
        });
    });
    
    // Form validation
    $('.nsk-diplom-form').on('submit', function(e) {
        var title = $('#diplom_title').val().trim();
        var date = $('#tildeling_dato').val();
        
        if (!title) {
            alert('Vennligst fyll inn tittel på diplom/pris.');
            e.preventDefault();
            return false;
        }
        
        if (!date) {
            alert('Vennligst velg dato for tildeling/arrangement.');
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        $(this).find('input[type="submit"]').val('Legger til...').prop('disabled', true);
    });
    
    // File upload preview
    $('#diplom_bilde, #lag_bilde').on('change', function() {
        var input = this;
        var targetId = $(this).attr('id') + '_preview';
        
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                var preview = $('#' + targetId);
                if (preview.length === 0) {
                    preview = $('<div id="' + targetId + '" style="margin-top: 10px;"></div>');
                    $(input).after(preview);
                }
                
                preview.html('<img src="' + e.target.result + '" style="max-width: 200px; height: auto; border: 1px solid #ddd; border-radius: 3px;">');
            };
            
            reader.readAsDataURL(input.files[0]);
        }
    });
    
    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        
        var target = this.hash;
        var $target = $(target);
        
        if ($target.length) {
            $('html, body').animate({
                'scrollTop': $target.offset().top - 100
            }, 300);
        }
    });
    
    // Add loading animation for images
    $('.nsk-diplom-image img, .nsk-lag-image img').each(function() {
        var $img = $(this);
        var $parent = $img.parent();
        
        // Add loading placeholder
        $parent.css('position', 'relative');
        var $loader = $('<div class="nsk-image-loader">Laster...</div>');
        $loader.css({
            'position': 'absolute',
            'top': '50%',
            'left': '50%',
            'transform': 'translate(-50%, -50%)',
            'background': 'rgba(255,255,255,0.9)',
            'padding': '10px',
            'border-radius': '3px',
            'font-size': '12px',
            'color': '#666'
        });
        
        $parent.prepend($loader);
        
        $img.on('load', function() {
            $loader.fadeOut(200, function() {
                $loader.remove();
            });
        });
        
        // If image is already loaded
        if (this.complete) {
            $loader.remove();
        }
    });
});
