<?php
/**
 * Displays the plugin settings page.
 *
 * Combines the "Theme Colors" and "Branding" tabs and links to the menu.
 *
 * @package EmboSettings
 */
namespace EmboSettings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Settings_Page {

    /**
     * Colors settings module instance.
     *
     * @var Colors_Tab
     */
    private $colors_tab;

    /**
     * Branding settings module instance.
     *
     * @var Branding_Tab
     */
    private $branding_tab;

    /**
     * Cookie and analytics settings module instance.
     *
     * @var Cookie_Analytics_Tab
     */
    private $cookie_analytics_tab;

    /**
     * Constructor.
     *
     * @param Colors_Tab          $colors_tab
     * @param Branding_Tab        $branding_tab
     * @param Cookie_Analytics_Tab $cookie_analytics_tab
     * @param Custom_CSS_Tab      $custom_css_tab
     */
    public function __construct(
        Colors_Tab $colors_tab,
        Branding_Tab $branding_tab,
        Cookie_Analytics_Tab $cookie_analytics_tab,
        Custom_CSS_Tab $custom_css_tab
    ) {
        $this->colors_tab           = $colors_tab;
        $this->branding_tab         = $branding_tab;
        $this->cookie_analytics_tab = $cookie_analytics_tab;
        $this->custom_css_tab       = $custom_css_tab;
    }

    /**
     * Register the plugin submenu in the admin.
     */
    public function register_submenu() {
        add_submenu_page(
            'themes.php',
            __( 'Кастомізація', 'embo-settings' ),
            __( 'Кастомізація', 'embo-settings' ),
            'manage_options',
            'embo-colors',
            [ $this, 'render_settings_page' ]
        );
    }

    /**
     * Render the main settings page with tabs.
     */
    public function render_settings_page() {
        settings_errors( 'embo_colors_options' );
        settings_errors( 'embo_branding_options' );
        settings_errors( 'embo_cookie_analytics_options' );
        settings_errors( 'embo_custom_css' );

        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'colors';
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Кастомізація', 'embo-settings' ); ?></h1>
            <h2 class="nav-tab-wrapper">
                <a href="?page=embo-colors&tab=colors" class="nav-tab <?php echo $active_tab === 'colors' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Налаштування кольорів теми', 'embo-settings' ); ?>
                </a>
                <a href="?page=embo-colors&tab=branding" class="nav-tab <?php echo $active_tab === 'branding' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Брендинг', 'embo-settings' ); ?>
                </a>
                <a href="?page=embo-colors&tab=cookies" class="nav-tab <?php echo $active_tab === 'cookies' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Cookies & Analytics', 'embo-settings' ); ?>
                </a>
                <a href="?page=embo-colors&tab=custom-css" class="nav-tab <?php echo $active_tab==='custom-css'?'nav-tab-active':''; ?>">
                    <?php esc_html_e( 'Custom CSS & Other', 'embo-settings' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>" class="nav-tab">
                    <?php esc_html_e( 'Меню', 'embo-settings' ); ?>
                </a>
            </h2>
            <?php
            if ( $active_tab === 'colors' ) {
                $this->colors_tab->render_colors_form();
            } elseif ( $active_tab === 'branding' ) {
                $this->branding_tab->render_branding_page();
            } elseif ( $active_tab === 'cookies' ) {
                $this->cookie_analytics_tab->render_settings_page();
            } elseif ( $active_tab === 'custom-css' ) {
                ?>
                <form method="post" action="options.php">
                    <?php
                    // Output nonce and hidden fields for option saving
                    settings_fields( 'embo_custom_css_group' );
                    // Errors will be displayed here if any
                    settings_errors( 'embo_custom_css' );
                    ?>
                    <table class="form-table">
                        <tbody>
                            <?php $this->custom_css_tab->render_settings_page(); ?>
                        </tbody>
                    </table>
                    <?php submit_button( __( 'Зберегти CSS', 'embo-settings' ) ); ?>
                </form>
                <?php
            }
            ?>
        </div>
        <?php
    }
}