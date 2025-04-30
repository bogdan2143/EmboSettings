jQuery(document).ready(function($){
    // Ініціалізація WP Color Picker для полів з кольорами
    $('.embo-color-field').wpColorPicker();

    // Ініціалізація медіа-завантажувача (для логотипу)
    $('.embo-media-upload').on('click', function(e){
        e.preventDefault();
        var target = $(this).data('target');
        var file_frame;
        if ( file_frame ) {
            file_frame.open();
            return;
        }
        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Виберіть зображення логотипу',
            button: {
                text: 'Вибрати'
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