<?php
/**
 * Plugin Name: EmboSettings
 * Plugin URI:  https://embo-studio.ua/
 * Description: Розширення налаштувань теми EmboTheme для інтеграції з Gutenberg.
 * Додає можливість налаштування кольорів, брендингу, меню, а також банера куків і коду аналітики.
 * Author: Pan Canon
 * Author URI: https://embo-studio.ua/
 * Version: 1.3
 * Text Domain: embo-settings
 * Domain Path: /languages
 *
 * @package EmboSettings
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Захист від прямого доступу
}

// Підключення файлів класів із логікою плагіна
require_once plugin_dir_path( __FILE__ ) . 'inc/class-colors-tab.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-branding-tab.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-visual-admin-settings.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-cookie-analytics-tab.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-settings-page.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-navigation-tab.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-custom-css-tab.php';

/**
 * Центральний клас плагіна, що ініціалізує всі модулі EmboSettings і реєструє
 * відповідні WordPress‑хуки.
 */
class EmboSettings_Plugin {

    /**
     * Об'єкт модуля для налаштувань кольорів теми.
     *
     * @var \EmboSettings\Colors_Tab
     */
    private $colors_tab;

    /**
     * Об'єкт модуля для налаштувань брендингу (логотипу).
     *
     * @var \EmboSettings\Branding_Tab
     */
    private $branding_tab;

    /**
     * Об'єкт модуля для візуальних налаштувань адмін‑панелі.
     *
     * @var \EmboSettings\Visual_Admin_Settings
     */
    private $visual_admin;

    /**
     * Об'єкт модуля для банера куків і коду аналітики.
     *
     * @var \EmboSettings\Cookie_Analytics_Tab
     */
    private $cookie_analytics_tab;

    /**
     * Об'єкт модуля для сторінки налаштувань із вкладками.
     *
     * @var \EmboSettings\Settings_Page
     */
    private $settings_page;

    /**
     * Об'єкт модуля для додавання вкладки «Меню» в Customizer.
     *
     * @var \EmboSettings\Navigation_Tab
     */
    private $navigation_tab;
    /**
     * @var \EmboSettings\Custom_CSS_Tab
     */
    private $custom_css_tab;

    /**
     * Конструктор. Ініціалізує всі модулі та реєструє хоки.
     */
    public function __construct() {
        // Ініціалізація модулів
        $this->colors_tab           = new \EmboSettings\Colors_Tab();
        $this->branding_tab         = new \EmboSettings\Branding_Tab();
        $this->visual_admin         = new \EmboSettings\Visual_Admin_Settings();
        $this->cookie_analytics_tab = new \EmboSettings\Cookie_Analytics_Tab();
        $this->custom_css_tab = new \EmboSettings\Custom_CSS_Tab();
        $this->settings_page        = new \EmboSettings\Settings_Page(
            $this->colors_tab,
            $this->branding_tab,
            $this->cookie_analytics_tab,
            $this->custom_css_tab
        );
        $this->navigation_tab       = new \EmboSettings\Navigation_Tab();

        // Реєстрація всіх хуків
        $this->init_hooks();
    }

    /**
     * Реєструє всі необхідні WordPress‑хуки для роботи модулів.
     */
    private function init_hooks() {
        // — Settings API —
        add_action( 'admin_init', [ $this->colors_tab,           'register_settings' ],        10 );
        add_action( 'admin_init', [ $this->branding_tab,         'register_settings' ],        10 );
        add_action( 'admin_init', [ $this->cookie_analytics_tab, 'register_settings' ],        10 );
        add_action( 'admin_init', [ $this->custom_css_tab, 'register_settings' ], 10 );

        // — Адмінка: скрипти й стилі —
        add_action( 'admin_enqueue_scripts', [ $this->colors_tab, 'enqueue_color_picker' ], 10 );

        // — Адмінка: візуальні правки меню —
        add_action( 'admin_menu',        [ $this->visual_admin, 'modify_admin_menu' ],     10 );
        add_action( 'load-nav-menus.php',[ $this->visual_admin, 'add_nav_menu_meta_box' ], 10 );

        // — Адмінка: сторінка налаштувань — 
        // Реєстрація підменю «Кастомізація» з вкладками виконується тут:
        add_action( 'admin_menu', [ $this->settings_page, 'register_submenu' ], 10 );

        // — Фронтенд: кольори теми —
        add_action( 'after_setup_theme',  [ $this->colors_tab, 'override_gutenberg_palette' ], 999 );
        add_action( 'wp_enqueue_scripts', [ $this->colors_tab, 'enqueue_frontend_colors' ],    100 );
        add_action( 'admin_init',         [ $this->colors_tab, 'handle_reset' ],             10 );
        add_action( 'admin_init',         [ $this->colors_tab, 'handle_pull_config' ],       10 );

        // — Фронтенд: банер куків та GA —
        add_action( 'wp_enqueue_scripts', [ $this->cookie_analytics_tab, 'enqueue_assets' ],     100 );
        add_action( 'wp_head',            [ $this->cookie_analytics_tab, 'print_ga_code' ],       1   );
        add_action( 'wp_footer',          [ $this->cookie_analytics_tab, 'render_cookie_banner' ],100 );

        // — Фронтенд: favicon tag —
        add_action( 'wp_head', [ $this->branding_tab, 'print_favicon_tag' ], 1 );

        // — Фронтенд: Custom CSS Import (в найперший блок) —
        add_action( 'wp_head', [ $this->custom_css_tab, 'print_import_css' ], 0 );
        // — Фронтенд: Custom CSS Main (в останній блок) —
        add_action( 'wp_head', [ $this->custom_css_tab, 'print_main_css' ], 1000 );

        // — Фронтенд: Custom text (Footer)
        add_action( 'init', [ $this, 'register_shortcodes' ], 10 );
    }

    /**
     * Реєструє всі шорткоди плагіна.
     */
    public function register_shortcodes() {
        add_shortcode( 'embo_footer_note', [ $this->branding_tab, 'render_footer_note' ] );
    }
}

/**
 * Ініціалізація плагіна після завантаження всіх плагінів.
 */
function embo_settings_init_plugin() {
    new EmboSettings_Plugin();
}
add_action( 'plugins_loaded', 'embo_settings_init_plugin', 10 );