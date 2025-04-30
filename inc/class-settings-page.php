<?php
/**
 * Клас для відображення сторінки налаштувань плагіна.
 *
 * Об'єднує вкладки «Налаштування кольорів теми» та «Брендинг», а також посилання на меню.
 *
 * @package EmboSettings
 */
namespace EmboSettings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Settings_Page {

    /**
     * Об'єкт класу для налаштувань кольорів.
     *
     * @var Colors_Tab
     */
    private $colors_tab;

    /**
     * Об'єкт класу для налаштувань брендингу.
     *
     * @var Branding_Tab
     */
    private $branding_tab;

    /**
     * Об'єкт класу для налаштувань куків і аналітики.
     *
     * @var Cookie_Analytics_Tab
     */
    private $cookie_analytics_tab;

    /**
     * Конструктор класу.
     *
     * @param Colors_Tab   $colors_tab
     * @param Branding_Tab $branding_tab
     * @param Cookie_Analytics_Tab $cookie_analytics_tab
     */
    public function __construct(
        Colors_Tab $colors_tab,
        Branding_Tab $branding_tab,
        Cookie_Analytics_Tab $cookie_analytics_tab,
        Custom_CSS_Tab $custom_css_tab   // <— добавлено
    ) {
        $this->colors_tab           = $colors_tab;
        $this->branding_tab         = $branding_tab;
        $this->cookie_analytics_tab = $cookie_analytics_tab;
        $this->custom_css_tab       = $custom_css_tab;    // <— сохраним
    }

    /**
     * Реєструє підменю плагіна в адмінці.
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
     * Відображає головну сторінку налаштувань з вкладками.
     */
    public function render_settings_page() {
        settings_errors( 'embo_colors_options' );
        settings_errors( 'embo_branding_options' );
        settings_errors( 'embo_cookie_analytics_options' );
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
                <a href="?page=embo-colors&tab=custom-css" class="nav-tab <?php echo $active_tab === 'custom-css' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Custom CSS', 'embo-settings' ); ?>
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
                    // Виводимо nonce і приховані поля для збереження опції
                    settings_fields( 'embo_custom_css_group' );
                    // Тут же виведуться помилки, якщо вони є
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