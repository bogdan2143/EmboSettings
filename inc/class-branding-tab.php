<?php
/**
 * Клас для налаштувань брендингу (логотипу).
 *
 * @package EmboSettings
 */
namespace EmboSettings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Branding_Tab {
    public function __construct() {
        // Добавляем размер для favicon сразу при её инициализации
        if ( function_exists( 'add_image_size' ) ) {
            add_image_size( 'embo_favicon', 32, 32, true );
        }
    }

    /**
     * Назва опції для збереження налаштувань брендингу.
     *
     * @var string
     */
    private $option_name = 'embo_branding_options';

    /**
     * Реєстрація налаштувань брендингу через Settings API.
     */
    public function register_settings() {
        register_setting(
            'embo_branding_group',
            $this->option_name,
            array( $this, 'sanitize_options' )
        );
    }

    /**
     * Санітує вхідні дані брендингу (логотип, favicon).
     *
     * @param array $input Вхідні дані з форми.
     * @return array {
     *     @type string logo               URL логотипу
     *     @type int    favicon_from_logo  1 — якщо потрібно згенерувати favicon з логотипу
     *     @type string favicon_custom     URL завантаженого вручну favicon
     *     @type string favicon            Остаточний URL favicon (згенерований або кастомний)
     * }
     */
    public function sanitize_options( $input ) {
        $sanitized = [];

        // 1) URL логотипу
        $sanitized['logo'] = ! empty( $input['logo'] )
            ? esc_url_raw( trim( $input['logo'] ) )
            : '';

        // 2) Чекбокс: згенерувати favicon з логотипу
        $sanitized['favicon_from_logo'] = ! empty( $input['favicon_from_logo'] ) ? 1 : 0;

        // 3) URL власного favicon
        $sanitized['favicon_custom'] = ! empty( $input['favicon_custom'] )
            ? esc_url_raw( trim( $input['favicon_custom'] ) )
            : '';

        // 4) Визначаємо, який favicon використовувати
        if ( $sanitized['favicon_from_logo'] && $sanitized['logo'] ) {
            $sanitized['favicon'] = $this->make_favicon_from_logo( $sanitized['logo'] );
        } else {
            $sanitized['favicon'] = $sanitized['favicon_custom'];
        }

        // 5) Кастомний текст для футера
        $sanitized['footer_note'] = isset($input['footer_note']) ? wp_kses_post($input['footer_note']) : '';

        return $sanitized;
    }

    /**
     * Повертає URL зрізу 32×32 (size 'embo_favicon') з переданого логотипу.
     *
     * @param string $logo_url URL оригінального логотипа (attachment URL).
     * @return string URL favicon (або '' якщо не вдалося).
     */
    private function make_favicon_from_logo( $logo_url ) {
        // Отримуємо ID вкладення по URL
        $attachment_id = attachment_url_to_postid( $logo_url );
        if ( ! $attachment_id ) {
            return '';
        }

        // Отримуємо src для size 'embo_favicon'
        $src = wp_get_attachment_image_src( $attachment_id, 'embo_favicon' );
        if ( ! $src || empty( $src[0] ) ) {
            return '';
        }

        return esc_url( $src[0] );
    }

    /**
     * Відображає HTML‑форму для брендингу: логотип і налаштування favicon.
     */
    public function render_branding_page() {
        $branding_options = get_option( $this->option_name, [
            'logo'               => '',
            'favicon_from_logo'  => 0,
            'favicon_custom'     => '',
            'favicon'            => '',
        ] );
        ?>
        <form method="post" action="options.php">
            <?php settings_fields( 'embo_branding_group' ); ?>
            <table class="form-table">

                <!-- 1) Логотип -->
                <tr>
                    <th scope="row">
                        <label for="embo_branding_logo">
                            <?php esc_html_e( 'Логотип', 'embo-settings' ); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="embo_branding_logo"
                            name="<?php echo esc_attr( $this->option_name ); ?>[logo]"
                            value="<?php echo esc_attr( $branding_options['logo'] ); ?>"
                            style="width:60%;"
                        />
                        <input
                            type="button"
                            class="button embo-media-upload"
                            data-target="#embo_branding_logo"
                            value="<?php esc_attr_e( 'Завантажити логотип', 'embo-settings' ); ?>"
                        />
                        <?php if ( $branding_options['logo'] ) : ?>
                            <div style="margin-top:10px;">
                                <img
                                    src="<?php echo esc_url( $branding_options['logo'] ); ?>"
                                    alt="<?php esc_attr_e( 'Логотип', 'embo-settings' ); ?>"
                                    style="max-width:150px; height:auto;"
                                />
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- 2) Генерація favicon з логотипу -->
                <tr>
                    <th scope="row">
                        <label for="favicon_from_logo">
                            <?php esc_html_e( 'Generate favicon from logo', 'embo-settings' ); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            type="checkbox"
                            id="favicon_from_logo"
                            name="<?php echo esc_attr( $this->option_name ); ?>[favicon_from_logo]"
                            value="1"
                            <?php checked( $branding_options['favicon_from_logo'], 1 ); ?>
                        />
                    </td>
                </tr>

                <!-- 3) Власний favicon -->
                <tr>
                    <th scope="row">
                        <label for="favicon_custom">
                            <?php esc_html_e( 'Custom favicon (32×32 png)', 'embo-settings' ); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="favicon_custom"
                            name="<?php echo esc_attr( $this->option_name ); ?>[favicon_custom]"
                            value="<?php echo esc_attr( $branding_options['favicon_custom'] ); ?>"
                            style="width:60%;"
                        />
                        <input
                            type="button"
                            class="button embo-media-upload"
                            data-target="#favicon_custom"
                            value="<?php esc_attr_e( 'Upload favicon', 'embo-settings' ); ?>"
                        />
                        <?php if ( ! empty( $branding_options['favicon'] ) ) : ?>
                            <div style="margin-top:10px;">
                                <img
                                    src="<?php echo esc_url( $branding_options['favicon'] ); ?>"
                                    alt="<?php esc_attr_e( 'Favicon preview', 'embo-settings' ); ?>"
                                    style="width:32px; height:32px;"
                                />
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- 4) Примітка в футері -->
                <tr>
                    <th scope="row">
                        <label for="footer_note"><?php esc_html_e( 'Футерна примітка', 'embo-settings' ); ?></label>
                    </th>
                    <td>
                        <textarea
                            id="footer_note"
                            name="<?php echo esc_attr( $this->option_name ); ?>[footer_note]"
                            rows="4"
                            style="width:60%;"><?php echo esc_textarea( $branding_options['footer_note'] ?? '' ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'Цей текст буде показаний у футері сайту.', 'embo-settings' ); ?></p>
                    </td>
                </tr>

            </table>
            <?php submit_button( __( 'Зберегти налаштування', 'embo-settings' ) ); ?>
        </form>
        <?php
    }

    /**
     * Виводить у <head> тег для favicon.
     */
    public function print_favicon_tag() {
        $opts = get_option( $this->option_name, [] );
        if ( empty( $opts['favicon'] ) ) {
            return;
        }
        $url = esc_url( $opts['favicon'] );
        echo "<link rel=\"icon\" href=\"{$url}\" sizes=\"32x32\" />\n";
        echo "<link rel=\"shortcut icon\" href=\"{$url}\" />\n";
    }

    /**
     * Render callback for footer-note block.
     *
     * @param array  $attributes Block attributes (unused).
     * @return string HTML for footer note.
     */
    public function render_footer_note( $attributes = [] ) {
        $opts = get_option( $this->option_name, [] );
        if ( ! empty( $opts['footer_note'] ) ) {
            // Обрабатываем шорткоды внутри текста, затем безопасно выводим HTML
            $content = do_shortcode( $opts['footer_note'] );
            return '<div class="embo-footer-note">' . wp_kses_post( $content ) . '</div>';
        }
        return sprintf(
            '<p>&copy; %1$s %2$s</p>',
            date( 'Y' ),
            esc_html( get_bloginfo( 'name' ) )
        );
    }

    /**
     * Виводити у футері текст.
     */
    public function register_footer_note_block() {
        register_block_type( 'myblocktheme/footer-note', [
            'render_callback' => [ $this, 'render_footer_note' ],
        ] );
    }
}