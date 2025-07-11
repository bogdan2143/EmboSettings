<?php
namespace EmboSettings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Custom_CSS_Tab {

    /**
     * Option name used to store import lines and custom CSS.
     *
     * @var string
     */
    private $option_name = 'embo_custom_css_options';

    /**
     * Post loading type labels, populated in the constructor.
     *
     * @var array
     */
    private $load_types = [];

    /**
     * Constructor initializes radio button labels for load type.
     */
    public function __construct() {
        $this->load_types = [
            'ajax'       => __( 'Лінива підвантажка (Load more)', 'embo-settings' ),
            'pagination' => __( 'Посторінкова навігація', 'embo-settings' ),
        ];
    }

    /**
     * Register the settings. One option contains two fields.
     */
    public function register_settings() {
        register_setting(
            'embo_custom_css_group',
            $this->option_name,
            [ $this, 'sanitize_options' ]
        );
    }

    /**
     * Sanitize input (import and custom CSS).
     *
     * @param array $input
     * @return array
     */
    public function sanitize_options( $input ) {
        $output = [
            'import' => '',
            'css'    => '',
        ];
        // 0) Post loading type
        $output['load_type'] = isset( $input['load_type'] ) && isset( $this->load_types[ $input['load_type'] ] )
            ? $input['load_type']
            : 'ajax';
        if ( isset( $input['import'] ) ) {
            // Strip <style> tags and dangerous characters
            $imp = trim( str_replace( ['<style>','</style>'], '', $input['import'] ) );
            $output['import'] = wp_strip_all_tags( $imp );
        }
        if ( isset( $input['css'] ) ) {
            $css = trim( str_replace( ['<style>','</style>'], '', $input['css'] ) );
            $output['css'] = wp_strip_all_tags( $css );
        }
        return $output;
    }

    /**
     * Output two admin fields: "Import" and "Custom CSS".
     */
    public function render_settings_page() {
        $opts = wp_parse_args(
            get_option( $this->option_name, [] ),
            [ 'import' => '', 'css' => '' ]
        );
        ?>
        <!-- 0) Post loading type -->
        <tr>
            <th scope="row"><?php esc_html_e( 'Тип підвантаження постів', 'embo-settings' ); ?></th>
            <td>
                <?php foreach ( $this->load_types as $key => $label ) : ?>
                    <label style="margin-right:15px;">
                        <input
                            type="radio"
                            name="<?php echo esc_attr( $this->option_name ); ?>[load_type]"
                            value="<?php echo esc_attr( $key ); ?>"
                            <?php checked( $opts['load_type'], $key ); ?>
                        />
                        <?php echo esc_html( $label ); ?>
                    </label>
                <?php endforeach; ?>
                <p class="description">
                    <?php esc_html_e( 'Виберіть, як завантажувати пости: AJAX-кнопкою або класичною пагінацією.', 'embo-settings' ); ?>
                </p>
            </td>
        </tr>
        <!-- Field for @import lines -->
        <tr>
            <th scope="row">
                <label for="embo_custom_css_import">
                    <?php esc_html_e( 'Import (наприклад для шрифтів)', 'embo-settings' ); ?>
                </label>
            </th>
            <td>
                <textarea
                    id="embo_custom_css_import"
                    name="<?php echo esc_attr( $this->option_name ); ?>[import]"
                    rows="3"
                    style="width:100%; font-family: monospace;"
                ><?php echo esc_textarea( $opts['import'] ); ?></textarea>
                <p class="description">
                    <?php esc_html_e( 'Ці рядки виведуться першими в inline‑блоці.', 'embo-settings' ); ?>
                </p>
            </td>
        </tr>

        <!-- Field for custom CSS -->
        <tr>
            <th scope="row">
                <label for="embo_custom_css_css">
                    <?php esc_html_e( 'Custom CSS', 'embo-settings' ); ?>
                </label>
            </th>
            <td>
                <textarea
                    id="embo_custom_css_css"
                    name="<?php echo esc_attr( $this->option_name ); ?>[css]"
                    rows="10"
                    style="width:100%; font-family: monospace;"
                ><?php echo esc_textarea( $opts['css'] ); ?></textarea>
                <p class="description">
                    <?php esc_html_e( 'Ваш CSS буде йти після імпорту.', 'embo-settings' ); ?>
                </p>
            </td>
        </tr>
        <?php
    }

    /**
     * Print the Import block (e.g. @import fonts) first.
     */
    public function print_import_css() {
        $opts = wp_parse_args(
            get_option( $this->option_name, [] ),
            [ 'import' => '', 'css' => '' ]
        );

        if ( empty( $opts['import'] ) ) {
            return;
        }

        $lines = array_filter( array_map( 'trim', explode( "\n", $opts['import'] ) ) );
        $block = '';
        foreach ( $lines as $line ) {
            $block .= rtrim( $line, ';' ) . '; ';
        }

        echo "<style id=\"custom-css-import-inline-css\">{$block}</style>";
    }

    /**
     * Print main Custom CSS after all other inline blocks.
     */
    public function print_main_css() {
        $opts = wp_parse_args(
            get_option( $this->option_name, [] ),
            [ 'import' => '', 'css' => '' ]
        );

        if ( empty( $opts['css'] ) ) {
            return;
        }

        $css = trim( $opts['css'] );
        echo "<style id=\"custom-css-css-inline-css\">{$css}</style>";
    }
}
