<?php
/**
 * Plugin Name: EmboSettings
 * Plugin URI:  https://github.com/bogdan2143/EmboSettings
 * Description: EmboTheme extension that integrates with Gutenberg.
 * Adds color, branding, menu, cookie banner and analytics settings.
 * Author: Pan Canon
 * Author URI: https://embo-studio.ua/
 * Version: 1.3
 * Text Domain: embo-settings
 * Domain Path: /languages
 *
 * @package EmboSettings
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Prevent direct access
}

// Custom version constant as fallback
if ( ! defined( 'EMBO_SETTINGS_VERSION' ) ) {
    define( 'EMBO_SETTINGS_VERSION', '1.3' );
}

// Include plugin class files
require_once plugin_dir_path( __FILE__ ) . 'inc/class-asset-loader.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-colors-tab.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-branding-tab.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-visual-admin-settings.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-cookie-analytics-tab.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-settings-page.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-navigation-tab.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-custom-css-tab.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-config-manager.php';

/**
 * Main plugin class that initializes all modules and registers
 * the required WordPress hooks.
 */
class EmboSettings_Plugin {

    /**
     * Module instance for theme color settings.
     *
     * @var \EmboSettings\Colors_Tab
     */
    private $colors_tab;

    /**
     * Module instance for branding settings (logo).
     *
     * @var \EmboSettings\Branding_Tab
     */
    private $branding_tab;

    /**
     * Module instance for admin visual tweaks.
     *
     * @var \EmboSettings\Visual_Admin_Settings
     */
    private $visual_admin;

    /**
     * Module instance for cookie banner and analytics code.
     *
     * @var \EmboSettings\Cookie_Analytics_Tab
     */
    private $cookie_analytics_tab;

    /**
     * Module instance for the settings page with tabs.
     *
     * @var \EmboSettings\Settings_Page
     */
    private $settings_page;

    /**
     * Module instance that adds the "Navigation" tab in the Customizer.
     *
     * @var \EmboSettings\Navigation_Tab
     */
    private $navigation_tab;
    /**
     * Module instance for custom CSS settings.
     *
     * @var \EmboSettings\Custom_CSS_Tab
     */
    private $custom_css_tab;

    /**
     * Handles backup and updates for theme configuration.
     *
     * @var \EmboSettings\Config_Manager
     */
    private $config_manager;

    /**
     * Constructor. Initializes all modules and registers hooks.
     */
    public function __construct() {
        // Initialize modules
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
        $this->config_manager       = \EmboSettings\Config_Manager::instance();

        // Register all hooks for modules that require it
        $this->navigation_tab->register_hooks();

        // Register all hooks
        $this->init_hooks();
    }

    /**
     * Registers all necessary WordPress hooks for the modules.
     */
    private function init_hooks() {
        // — Settings API —
        add_action( 'admin_init', [ $this->colors_tab,           'register_settings' ],        10 );
        add_action( 'admin_init', [ $this->branding_tab,         'register_settings' ],        10 );
        add_action( 'admin_init', [ $this->cookie_analytics_tab, 'register_settings' ],        10 );
        add_action( 'admin_init', [ $this->custom_css_tab, 'register_settings' ], 10 );

        // — Admin: scripts and styles —
        add_action( 'admin_enqueue_scripts', [ $this->colors_tab, 'enqueue_color_picker' ], 10 );

        // — Admin: visual menu tweaks —
        add_action( 'admin_menu',        [ $this->visual_admin, 'modify_admin_menu' ],     10 );
        add_action( 'load-nav-menus.php',[ $this->visual_admin, 'add_nav_menu_meta_box' ], 10 );

        // — Admin: settings page —
        // The "Customization" submenu with tabs is registered here:
        add_action( 'admin_menu', [ $this->settings_page, 'register_submenu' ], 10 );

        // — Frontend: theme colors —
        add_action( 'after_setup_theme',  [ $this->colors_tab, 'override_gutenberg_palette' ], 999 );
        add_action( 'wp_enqueue_scripts', [ $this->colors_tab, 'enqueue_frontend_colors' ],    100 );
        add_action( 'admin_init',         [ $this->colors_tab, 'handle_reset' ],             10 );
        add_action( 'admin_init',         [ $this->colors_tab, 'handle_pull_config' ],       10 );

        // — Frontend: cookie banner and GA —
        add_action( 'wp_enqueue_scripts', [ $this->cookie_analytics_tab, 'enqueue_assets' ],     100 );
        add_action( 'wp_head',            [ $this->cookie_analytics_tab, 'print_ga_code' ],       1   );
        add_action( 'wp_footer',          [ $this->cookie_analytics_tab, 'render_cookie_banner' ],100 );

        // — Frontend: favicon tag —
        add_action( 'wp_head', [ $this->branding_tab, 'print_favicon_tag' ], 1 );

        // — Frontend: Custom CSS Import (first block) —
        add_action( 'wp_head', [ $this->custom_css_tab, 'print_import_css' ], 0 );
        // — Frontend: Custom CSS Main (last block) —
        add_action( 'wp_head', [ $this->custom_css_tab, 'print_main_css' ], 1000 );

        // — Frontend: Dynamic Footer Note Block —
        add_action( 'init', [ $this->branding_tab, 'register_footer_note_block' ], 20 );
        // Register shortcodes
        add_action( 'init', [ $this, 'register_shortcodes' ], 15 );
    }

    /**
     * Реєструє всі шорткоди плагіна.
     */
    public function register_shortcodes() {
        add_shortcode( 'embo_footer_note', [ $this->branding_tab, 'render_footer_note' ] );
    }
}

/**
 * Initialize the plugin after all plugins are loaded.
 */
function embo_settings_init_plugin() {
    new EmboSettings_Plugin();
}
add_action( 'plugins_loaded', 'embo_settings_init_plugin', 10 );