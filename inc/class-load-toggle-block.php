<?php
namespace EmboSettings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Динамічний блок для перемикання між AJAX-Load More і посторінковою пагінацією
 */
class Load_Toggle_Block {

    public function __construct() {
        add_action( 'init', [ $this, 'register_block' ] );
    }

    public function register_block() {
        register_block_type( 'embo/load-toggle', [
            'render_callback' => [ $this, 'render_toggle' ],
            'supports'        => [ 'html' => false ],
        ] );
    }

    public function render_toggle() {
        $opts = get_option( 'embo_custom_css_options', [] );
        $type = $opts['load_type'] ?? 'ajax';

        if ( $type === 'pagination' ) {
            $links = paginate_links([
                'type'      => 'list',
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
            ]);
            return '<div class="fallback-pagination">' . $links . '</div>';
        }

        $label = __( 'Завантажити старі пости', 'embo-settings' );
        return sprintf(
            '<div class="load-more"><button id="loadMoreButton" class="button is-primary">%s</button></div>',
            esc_html( $label )
        );
    }
}

new Load_Toggle_Block();