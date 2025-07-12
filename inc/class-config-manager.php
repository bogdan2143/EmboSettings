<?php
namespace EmboSettings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Manages reading and writing of the FSE configuration file.
 * Stores a backup of the original theme.json to allow restoring
 * and writes updated color values to the theme when options change.
 */
class Config_Manager {
    /**
     * Singleton instance.
     *
     * @var Config_Manager
     */
    private static $instance;

    /**
     * Path to the theme.json file of the active theme.
     *
     * @var string
     */
    private $theme_config;

    /**
     * Path to the backup file stored inside the plugin.
     *
     * @var string
     */
    private $backup_file;

    /**
     * Private constructor to enforce singleton.
     */
    private function __construct() {
        $this->theme_config = trailingslashit( get_template_directory() ) . 'theme.json';
        $this->backup_file  = plugin_dir_path( __DIR__ ) . 'config/theme.json';
        $this->ensure_backup_exists();
    }

    /**
     * Returns the singleton instance.
     *
     * @return Config_Manager
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Ensures that the backup of theme.json exists inside the plugin.
     */
    public function ensure_backup_exists() {
        if ( file_exists( $this->backup_file ) ) {
            return;
        }
        if ( ! file_exists( $this->theme_config ) ) {
            return;
        }
        if ( ! file_exists( dirname( $this->backup_file ) ) ) {
            wp_mkdir_p( dirname( $this->backup_file ) );
        }
        copy( $this->theme_config, $this->backup_file );
    }

    /**
     * Updates the theme.json palette with the provided options.
     *
     * @param array $options Color options from the plugin.
     */
    public function update_theme_palette( $options ) {
        if ( ! file_exists( $this->theme_config ) ) {
            return;
        }
        $data = json_decode( file_get_contents( $this->theme_config ), true );
        if ( empty( $data['settings']['color']['palette'] ) || ! is_array( $data['settings']['color']['palette'] ) ) {
            return;
        }
        foreach ( $data['settings']['color']['palette'] as &$item ) {
            switch ( $item['slug'] ) {
                case 'background':
                    $item['color'] = $options['background_color'];
                    break;
                case 'text':
                    $item['color'] = $options['text_color'];
                    break;
                case 'links':
                    $item['color'] = $options['link_color'];
                    break;
                case 'button':
                    $item['color'] = $options['button_color'];
                    break;
                case 'header-bg':
                    $item['color'] = $options['header_background_color'];
                    break;
                case 'footer-bg':
                    $item['color'] = $options['footer_background_color'];
                    break;
                case 'aside-bg':
                    $item['color'] = $options['aside_background_color'];
                    break;
                case 'header-menu-link':
                    $item['color'] = $options['header_menu_link_color'];
                    break;
                case 'header-menu-hover':
                    $item['color'] = $options['header_menu_hover_color'];
                    break;
                case 'footer-menu-link':
                    $item['color'] = $options['footer_menu_link_color'];
                    break;
                case 'footer-menu-hover':
                    $item['color'] = $options['footer_menu_hover_color'];
                    break;
                case 'tabs-link':
                    $item['color'] = $options['tabs_link_color'];
                    break;
                case 'tabs-active-link':
                    $item['color'] = $options['tabs_active_link_color'];
                    break;
                case 'comment-button':
                    $item['color'] = $options['comment_button_color'];
                    break;
                case 'readmore-button':
                    $item['color'] = $options['readmore_button_color'];
                    break;
                case 'loadmore-button':
                    $item['color'] = $options['loadmore_button_color'];
                    break;
            }
        }
        $json = wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        file_put_contents( $this->theme_config, $json );
    }

    /**
     * Restores the original theme.json from the backup.
     */
    public function restore_original() {
        if ( file_exists( $this->backup_file ) ) {
            copy( $this->backup_file, $this->theme_config );
        }
    }

    /**
     * Returns the path to the stored backup file.
     *
     * @return string
     */
    public function get_backup_path() {
        return $this->backup_file;
    }
}