<?php
namespace EmboSettings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Клас для додавання вкладки «Навігація по сторінці» в бокове меню.
 */
class Navigation_Tab {

    public function __construct() {
        // 1) реєструємо term-meta «enable_toc» для категорій
        add_action( 'init', [ $this, 'register_category_meta' ] );
        // 2) виводимо поле при створенні категорії
        add_action( 'category_add_form_fields', [ $this, 'add_category_field' ] );
        // 3) виводимо поле при редагуванні категорії
        add_action( 'category_edit_form_fields', [ $this, 'edit_category_field' ] );
        // 4) зберігаємо значення term-meta після створення категорії
        add_action( 'created_category', [ $this, 'save_category_field' ] );
        // 4) зберігаємо значення term-meta після редагування категорії
        add_action( 'edited_category', [ $this, 'save_category_field' ] );
        // 5) фільтр для додавання id заголовкам
        add_filter( 'the_content', [ $this, 'add_heading_ids' ], 20 );
        // 6) умовно підключаємо скрипти/стилі на frontend з пріоритетом 20,
        //    щоб наш скрипт завантажувався після screen-utils :contentReference[oaicite:0]{index=0}
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ], 20 );
    }

    /**
     * 1) Реєструємо term-meta «enable_toc» для категорій.
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
     * 2) Додаємо чекбокс при створенні категорії.
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
     * 3) Додаємо чекбокс при редагуванні категорії.
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
     * 4) Зберігаємо term-meta.
     */
    public function save_category_field( $term_id ) {
        $enabled = isset( $_POST['enable_toc'] ) ? 1 : 0;
        update_term_meta( $term_id, 'enable_toc', $enabled );
    }

    /**
     * 5) Додаємо id всім заголовкам h2–h6 у контенті.
     */
    public function add_heading_ids( $content ) {
        if ( ! is_singular() ) {
            return $content;
        }
        libxml_use_internal_errors( true );
        $dom = new \DOMDocument;
        // щоб не порушити кодування utf-8
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
        // витягуємо вміст body
        $body = $dom->getElementsByTagName( 'body' )->item(0);
        $out  = '';
        foreach ( $body->childNodes as $child ) {
            $out .= $dom->saveHTML( $child );
        }
        return $out;
    }

    /**
     * 6) Умовно підключаємо скрипти/стилі на single-сторінці поста.
     */
    public function enqueue_frontend_assets() {
        if ( ! is_singular( 'post' ) ) {
            return;
        }
        global $post;

        // перевіряємо, чи увімкнена навігація хоча б в одній категорії
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

        // Шлях та версія скрипта
        $relative = 'js/embo-toc.js';
        $file_url = plugin_dir_url( __FILE__ ) . '../' . $relative;
        $version  = \EmboSettings\Asset_Loader::version( $relative );

        // Переконаємось, що утиліта screen-utils підключена перед нашим скриптом
        if ( wp_script_is( 'screen-utils', 'registered' ) ) {
            wp_enqueue_script( 'screen-utils' );
        }

        // Підключаємо наш TOC-скрипт з новою залежністю від screen-utils
        wp_enqueue_script(
            'embo-toc',
            $file_url,
            [ 'jquery', 'screen-utils' ], // тепер залежить і від screen-utils :contentReference[oaicite:1]{index=1}
            $version,
            true
        );

        // Локалізація рядків для скрипта
        wp_localize_script( 'embo-toc', 'EmboSettingsI18n', [
            'tabPosts' => __( 'Хронологія', 'embo-settings' ),
            'tabToc'   => __( 'Навігація по сторінці', 'embo-settings' ),
        ] );
    }
}

// У головному файлі плагіна (наприклад, embo-settings.php) створіть екземпляр:
new Navigation_Tab();