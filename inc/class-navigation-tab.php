<?php
namespace EmboSettings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Adds the "Page Navigation" tab to the sidebar.
 */
class Navigation_Tab {

    /**
     * Registers all hooks for the navigation functionality.
     */
    public function register_hooks() {
        // 1) register the "enable_toc" term meta for categories
        add_action( 'init', [ $this, 'register_category_meta' ] );
        // 2) output the field when creating a category
        add_action( 'category_add_form_fields', [ $this, 'add_category_field' ] );
        // 3) output the field when editing a category
        add_action( 'category_edit_form_fields', [ $this, 'edit_category_field' ] );
        // 4) save term meta after creating a category
        add_action( 'created_category', [ $this, 'save_category_field' ] );
        // 4) save term meta after editing a category
        add_action( 'edited_category', [ $this, 'save_category_field' ] );
        // 5) filter to add ids to headings
        add_filter( 'the_content', [ $this, 'add_heading_ids' ], 20 );
        // 6) conditionally enqueue scripts/styles on the front end with priority 20
        //    so our script loads after screen-utils
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ], 20 );
    }

    /**
     * 1) Register the "enable_toc" term meta for categories.
     */
    public function register_category_meta() {
        register_term_meta( 'category', 'enable_toc', [
            'type'         => 'boolean',
            'description'  => __( 'Увімкнути навігацію за заголовками', 'embo-settings' ),
            'single'       => true,
            'show_in_rest' => false,
            'default'      => false,
        ] );
    }

    /**
     * 2) Add a checkbox when creating a category.
     */
    public function add_category_field( $taxonomy ) {
        ?>
        <div class="form-field term-group">
            <label for="enable_toc"><?php esc_html_e( 'Навігація за заголовками', 'embo-settings' ); ?></label>
            <input type="checkbox" name="enable_toc" id="enable_toc" value="1" />
            <p class="description"><?php esc_html_e( 'Показувати вкладку «Навігація по сторінці» в aside для записів цієї категорії.', 'embo-settings' ); ?></p>
        </div>
        <?php
    }

    /**
     * 3) Add a checkbox when editing a category.
     */
    public function edit_category_field( $term ) {
        $enabled = get_term_meta( $term->term_id, 'enable_toc', true );
        ?>
        <tr class="form-field term-group-wrap">
            <th scope="row"><label for="enable_toc"><?php esc_html_e( 'Навігація за заголовками', 'embo-settings' ); ?></label></th>
            <td>
                <input type="checkbox" name="enable_toc" id="enable_toc" value="1" <?php checked( $enabled, 1 ); ?> />
                <p class="description"><?php esc_html_e( 'Показувати вкладку «Навігація по сторінці» в aside для записів цієї категорії.', 'embo-settings' ); ?></p>
            </td>
        </tr>
        <?php
    }

    /**
     * 4) Save term meta.
     */
    public function save_category_field( $term_id ) {
        $enabled = isset( $_POST['enable_toc'] ) ? 1 : 0;
        update_term_meta( $term_id, 'enable_toc', $enabled );
    }

    /**
     * 5) Add ids to all h2–h6 headings in the content.
     */
    public function add_heading_ids( $content ) {
        if ( ! is_singular() ) {
            return $content;
        }
        libxml_use_internal_errors( true );
        $dom = new \DOMDocument;
        // ensure UTF-8 encoding isn't broken
        $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $content );
        foreach ( [ 'h2','h3','h4','h5','h6' ] as $tag ) {
            $els = $dom->getElementsByTagName( $tag );
            /** @var \DOMElement $el */
            foreach ( $els as $el ) {
                if ( ! $el->hasAttribute( 'id' ) ) {
                    $slug = sanitize_title( $el->textContent );
                    $el->setAttribute( 'id', $slug );
                }
            }
        }
        // extract body content
        $body = $dom->getElementsByTagName( 'body' )->item(0);
        $out  = '';
        foreach ( $body->childNodes as $child ) {
            $out .= $dom->saveHTML( $child );
        }
        return $out;
    }

    /**
     * 6) Conditionally enqueue assets on single post pages.
     */
    public function enqueue_frontend_assets() {
        if ( ! is_singular( 'post' ) ) {
            return;
        }
        global $post;

        // check if navigation is enabled in at least one category
        $show_nav = false;
        foreach ( get_the_category( $post->ID ) as $cat ) {
            if ( get_term_meta( $cat->term_id, 'enable_toc', true ) ) {
                $show_nav = true;
                break;
            }
        }
        if ( ! $show_nav ) {
            return;
        }

        // Script path and version
        $relative = 'js/embo-toc.js';
        $file_url = plugin_dir_url( __FILE__ ) . '../' . $relative;
        $version  = \EmboSettings\Asset_Loader::version( $relative );

        // Ensure that screen-utils is enqueued before our script
        if ( wp_script_is( 'screen-utils', 'registered' ) ) {
            wp_enqueue_script( 'screen-utils' );
        }

        // Enqueue our TOC script with the screen-utils dependency
        wp_enqueue_script(
            'embo-toc',
            $file_url,
            [ 'jquery', 'screen-utils' ], // depends on screen-utils
            $version,
            true
        );

        // Localize strings for the script
        wp_localize_script( 'embo-toc', 'EmboSettingsI18n', [
            'tabPosts' => __( 'Хронологія', 'embo-settings' ),
            'tabToc'   => __( 'Навігація по сторінці', 'embo-settings' ),
        ] );
    }
}