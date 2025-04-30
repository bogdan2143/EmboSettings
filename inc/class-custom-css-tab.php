<?php
namespace EmboSettings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Custom_CSS_Tab {

    /**
     * Назва опції для збереження імпорту і CSS.
     *
     * @var string
     */
    private $option_name = 'embo_custom_css_options';

    /**
     * Реєструє налаштування: одна опція — масив із двох полів.
     */
    public function register_settings() {
        register_setting(
            'embo_custom_css_group',
            $this->option_name,
            [ $this, 'sanitize_options' ]
        );
    }

    /**
     * Санітизує вхідні дані (імпорт + власний CSS).
     *
     * @param array $input
     * @return array
     */
    public function sanitize_options( $input ) {
        $output = [
            'import' => '',
            'css'    => '',
        ];
        if ( isset( $input['import'] ) ) {
            // Обрізаємо теги <style> та небезпечні символи
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
     * Виводить два поля в адмінці: «Імпорт» і «Custom CSS».
     */
    public function render_settings_page() {
        $opts = wp_parse_args(
            get_option( $this->option_name, [] ),
            [ 'import' => '', 'css' => '' ]
        );
        ?>
        <!-- Рядок для @import -->
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

        <!-- Рядок для власного CSS -->
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
     * Виводить блок Import (наприклад @import шрифтів) — першим.
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
     * Виводить основний Custom CSS — після всіх інших inline-блоків.
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