<?php
namespace EmboSettings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Helper class to load assets with a version based on file modification time.
 */
class Asset_Loader {

    /**
     * Returns the version of a plugin file based on modification time.
     *
     * @param string $relative_path Path relative to the plugin root, e.g. 'js/embo-toc.js'.
     * @return int|string File modification time or EMBO_SETTINGS_VERSION.
     */
    public static function version( $relative_path ) {
        $file = plugin_dir_path( __DIR__ ) . $relative_path;
        if ( file_exists( $file ) ) {
            return filemtime( $file );
        }
        return EMBO_SETTINGS_VERSION;
    }
}