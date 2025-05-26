<?php
namespace EmboSettings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Клас для завантаження asset-ів з автоматичним визначенням версії за filemtime.
 */
class Asset_Loader {

    /**
     * Повертає версію файлу в плагіні за відносним шляхом (з кореня плагіна).
     *
     * @param string $relative_path Шлях від кореня плагіна, наприклад 'js/embo-toc.js'.
     * @return int|string Час останньої модифікації або константа EMBO_SETTINGS_VERSION.
     */
    public static function version( $relative_path ) {
        $file = plugin_dir_path( __DIR__ ) . $relative_path;
        if ( file_exists( $file ) ) {
            return filemtime( $file );
        }
        return EMBO_SETTINGS_VERSION;
    }
}