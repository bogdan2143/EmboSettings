<?php
/**
 * Visual tweaks for the admin panel.
 * Manages removal of default submenus and adds custom metaboxes.
 *
 * @package EmboSettings
 */

namespace EmboSettings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Visual_Admin_Settings {

    /**
     * Remove the default "Menus" submenu from the Appearance section.
     */
    public function modify_admin_menu() {
        remove_submenu_page( 'themes.php', 'nav-menus.php' );
        // Remove the "Editor" submenu (Gutenberg site editor) under Appearance
        remove_submenu_page( 'themes.php', 'site-editor.php' );
    }

    /**
     * Add a metabox on the "Menus" page linking to EmboSettings.
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
     * Render the metabox content linking to the plugin page.
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
