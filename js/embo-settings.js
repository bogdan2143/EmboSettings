jQuery(document).ready(function ($) {
    // Initialize WP Color Picker for color fields
    $('.embo-color-field').each(function() {
        var $field = $(this);
        var args = {};

        // Add a custom default swatch for the sidebar background field
        if ( $field.attr('id') === 'aside_background_color' ) {
            args.palettes = [ '#F7F9FA' ];
        }

        // Launch the color picker with provided settings
        $field.wpColorPicker( args );
    });

    // Initialize the media uploader for the logo
    $('.embo-media-upload').on('click', function(e){
        e.preventDefault();
        var target = $(this).data('target');
        var file_frame;
        if ( file_frame ) {
            file_frame.open();
            return;
        }
        file_frame = wp.media.frames.file_frame = wp.media({
            title: EmboSettingsAdmin.logoTitle,
            button: {
                text: EmboSettingsAdmin.logoButton
            },
            multiple: false
        });
        file_frame.on( 'select', function() {
            var attachment = file_frame.state().get('selection').first().toJSON();
            $(target).val( attachment.url );
        });
        file_frame.open();
    });
});
