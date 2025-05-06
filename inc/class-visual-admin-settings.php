<?php
/**
 * Клас для візуальних налаштувань адміністративної панелі.
 * Управляє видаленням стандартних підменю та додаванням додаткових метабоксів.
 *
 * @package EmboSettings
 */

namespace EmboSettings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Visual_Admin_Settings {

    /**
     * Видаляє стандартне підменю "Меню" з розділу "Вигляд".
     */
    public function modify_admin_menu() {
        remove_submenu_page( 'themes.php', 'nav-menus.php' );
        // Видаляємо підменю «Editor» (редактор сайтів Gutenberg) у розділі «Вигляд»
        remove_submenu_page( 'themes.php', 'site-editor.php' );
    }

    /**
     * Додає метабокс на сторінці "Меню" з посиланням на налаштування EmboSettings.
     */
    public function add_nav_menu_meta_box() {
        add_meta_box(
            'embo-settings-nav-link',
            __( 'EmboSettings', 'embo-settings' ),
            array( $this, 'render_nav_menu_plugin_link_metabox' ),
            'nav-menus',
            'side',
            'default'
        );
    }

    /**
     * Виводить контент метабоксу з посиланням на сторінку плагіна.
     */
    public function render_nav_menu_plugin_link_metabox() {
        ?>
        <p>
            <a href="<?php echo esc_url( admin_url( 'themes.php?page=embo-colors' ) ); ?>">
                <?php esc_html_e( 'Перейти до налаштувань EmboSettings', 'embo-settings' ); ?>
            </a>
        </p>
        <?php
    }
}