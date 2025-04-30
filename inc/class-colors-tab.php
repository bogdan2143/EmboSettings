<?php
/**
 * Клас для налаштувань кольорів – функціональність вкладки «Налаштування кольорів теми».
 *
 * @package EmboSettings
 */

namespace EmboSettings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Colors_Tab {

    /**
     * Назва опції для збереження налаштувань кольорів.
     *
     * @var string
     */
    private $option_name = 'embo_colors_options';

    /**
     * Масив дефолтних значень кольорів.
     *
     * @var array
     */
    private $default_options = array(
        'background_color'          => '#b22222',
        'text_color'                => '#000000',
        'link_color'                => '#1e73be',
        'button_color'              => '#32373c',
        'header_background_color'   => '#485fc7',
        'footer_background_color'   => '#333333',
        'aside_background_color'    => '#f1f1f1',

        // нові опції
        'header_menu_link_color'    => '#ffffff',
        'header_menu_hover_color'   => '#dddddd',
        'footer_menu_link_color'    => '#ffffff',
        'footer_menu_hover_color'   => '#dddddd',
        'tabs_link_color'           => '#000000',
        'tabs_active_link_color'    => '#485fc7',
        'comment_button_color'      => '#3273dc',
        'readmore_button_color'     => '#32373c',
        'loadmore_button_color'     => '#485fc7',
    );

    /**
     * Конструктор класу.
     */
    public function __construct() {
        // Логіка ініціалізації в головному класі EmboSettings_Plugin.
    }

    /**
     * Реєстрація налаштувань через Settings API.
     */
    public function register_settings() {
        register_setting(
            'embo_colors_group',
            $this->option_name,
            array( $this, 'sanitize_options' )
        );
    }

    /**
     * Санітує вхідні дані та об’єднує з дефолтними.
     *
     * @param array $input
     * @return array
     */
    public function sanitize_options( $input ) {
        $sanitized = array();

        foreach ( $this->default_options as $key => $default ) {
            if ( isset( $input[ $key ] ) ) {
                $sanitized[ $key ] = $this->custom_sanitize_hex_color( $input[ $key ], $default );
            } else {
                $sanitized[ $key ] = $default;
            }
        }

        return wp_parse_args( $sanitized, $this->default_options );
    }

    /**
     * Приватна функція для санітизації HEX-значень.
     *
     * @param string $color
     * @param string $default
     * @return string
     */
    private function custom_sanitize_hex_color( $color, $default ) {
        $color = trim( $color );
        if ( '' !== $color ) {
            if ( '#' !== substr( $color, 0, 1 ) ) {
                $color = '#' . $color;
            }
            $hex = sanitize_hex_color( $color );
            return $hex ? $hex : $default;
        }
        return $default;
    }

    /**
     * Скидання налаштувань до дефолтних.
     */
    public function handle_reset() {
        if ( isset( $_GET['reset_embo_colors'] ) && check_admin_referer( 'reset_embo_colors_nonce' ) ) {
            update_option( $this->option_name, $this->default_options );
            add_settings_error(
                'embo_colors_options',
                'settings_reset',
                __( 'Налаштування скинуті до дефолтних.', 'embo-settings' ),
                'updated'
            );
        }
    }

    /**
     * Тригер на підтягання з theme.json.
     */
    public function handle_pull_config() {
        if ( isset( $_GET['pull_embo_colors'] ) && check_admin_referer( 'pull_embo_colors_nonce' ) ) {
            $this->pull_config_values();
        }
    }

    /**
     * Підтягує значення з theme.json та оновлює опції.
     */
    public function pull_config_values() {
        $file = get_template_directory() . '/theme.json';
        if ( ! file_exists( $file ) ) {
            return;
        }

        $data = json_decode( file_get_contents( $file ), true );
        if ( empty( $data['settings']['color']['palette'] ) || ! is_array( $data['settings']['color']['palette'] ) ) {
            return;
        }

        $new_options = array();
        foreach ( $data['settings']['color']['palette'] as $item ) {
            switch ( $item['slug'] ) {
                case 'background':
                    $new_options['background_color'] = $item['color'];
                    break;
                case 'text':
                    $new_options['text_color'] = $item['color'];
                    break;
                case 'links':
                    $new_options['link_color'] = $item['color'];
                    break;
                case 'button':
                    $new_options['button_color'] = $item['color'];
                    break;
                case 'header-bg':
                    $new_options['header_background_color'] = $item['color'];
                    break;
                case 'footer-bg':
                    $new_options['footer_background_color'] = $item['color'];
                    break;
                case 'aside-bg':
                    $new_options['aside_background_color'] = $item['color'];
                    break;

                // нові слаги
                case 'header-menu-link':
                    $new_options['header_menu_link_color'] = $item['color'];
                    break;
                case 'header-menu-hover':
                    $new_options['header_menu_hover_color'] = $item['color'];
                    break;
                case 'footer-menu-link':
                    $new_options['footer_menu_link_color'] = $item['color'];
                    break;
                case 'footer-menu-hover':
                    $new_options['footer_menu_hover_color'] = $item['color'];
                    break;
                case 'tabs-link':
                    $new_options['tabs_link_color'] = $item['color'];
                    break;
                case 'tabs-active-link':
                    $new_options['tabs_active_link_color'] = $item['color'];
                    break;
                case 'comment-button':
                    $new_options['comment_button_color'] = $item['color'];
                    break;
                case 'readmore-button':
                    $new_options['readmore_button_color'] = $item['color'];
                    break;
                case 'loadmore-button':
                    $new_options['loadmore_button_color'] = $item['color'];
                    break;
            }
        }

        if ( ! empty( $new_options ) ) {
            $merged = wp_parse_args( $new_options, $this->default_options );
            update_option( $this->option_name, $merged );
            add_settings_error(
                'embo_colors_options',
                'settings_pulled',
                __( 'Налаштування оновлено з файлу конфігу.', 'embo-settings' ),
                'updated'
            );
        }
    }

    /**
     * Підключає Color Picker та медіа на сторінці налаштувань.
     *
     * @param string $hook_suffix
     */
    public function enqueue_color_picker( $hook_suffix ) {
        if ( 'appearance_page_embo-colors' !== $hook_suffix ) {
            return;
        }
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_media();
        wp_enqueue_script(
            'embo-settings-js',
            plugin_dir_url( __FILE__ ) . '../js/embo-settings.js',
            array( 'wp-color-picker', 'jquery' ),
            '1.0',
            true
        );
    }

    /**
     * Підключає CSS на фронтенді за збереженими налаштуваннями.
     */
    public function enqueue_frontend_colors() {
        $options = wp_parse_args(
            get_option( $this->option_name, array() ),
            $this->default_options
        );
        $custom_css = '';

        // існуючі правила
        if ( ! empty( $options['background_color'] ) ) {
            $custom_css .= "body { background-color: {$options['background_color']}; }\n";
        }
        if ( ! empty( $options['text_color'] ) ) {
            $custom_css .= "body { color: {$options['text_color']}; }\n";
        }
        if ( ! empty( $options['link_color'] ) ) {
            $custom_css .= "a { color: {$options['link_color']}; }\n";
        }
        if ( ! empty( $options['button_color'] ) ) {
            $custom_css .= ".wp-block-button__link, .button { background-color: {$options['button_color']}; color: #fff; }\n";
        }
        if ( ! empty( $options['header_background_color'] ) ) {
            $custom_css .= ".navbar.is-primary { background-color: {$options['header_background_color']}; }\n";
        }
        if ( ! empty( $options['footer_background_color'] ) ) {
            $custom_css .= "footer.footer { background-color: {$options['footer_background_color']}; }\n";
        }
        if ( ! empty( $options['aside_background_color'] ) ) {
            $custom_css .= ".global-aside { background-color: {$options['aside_background_color']}; }\n";
        }

        // нові правила
        if ( ! empty( $options['header_menu_link_color'] ) ) {
            $custom_css .= ".navbar .navbar-item a { color: {$options['header_menu_link_color']}; }\n";
        }
        if ( ! empty( $options['header_menu_hover_color'] ) ) {
            $custom_css .= ".navbar .navbar-item a:hover { color: {$options['header_menu_hover_color']}; }\n";
        }
        if ( ! empty( $options['footer_menu_link_color'] ) ) {
            $custom_css .= "footer.footer .navbar-item a { color: {$options['footer_menu_link_color']}; }\n";
        }
        if ( ! empty( $options['footer_menu_hover_color'] ) ) {
            $custom_css .= "footer.footer .navbar-item a:hover { color: {$options['footer_menu_hover_color']}; }\n";
        }
        if ( ! empty( $options['tabs_link_color'] ) ) {
            $custom_css .= ".tabs a { color: {$options['tabs_link_color']}; }\n";
        }
        if ( ! empty( $options['tabs_active_link_color'] ) ) {
            $custom_css .= ".tabs .is-active a { color: {$options['tabs_active_link_color']}; }\n";
        }
        if ( ! empty( $options['comment_button_color'] ) ) {
            $custom_css .= "#submit.wp-block-button__link { background-color: {$options['comment_button_color']}; color: #fff !important; }\n";
        }
        if ( ! empty( $options['readmore_button_color'] ) ) {
            $custom_css .= ".wp-block-button.is-link .button.is-link { background-color: {$options['readmore_button_color']}; }\n";
        }
        if ( ! empty( $options['loadmore_button_color'] ) ) {
            $custom_css .= "#loadMoreButton.button.is-primary { background-color: {$options['loadmore_button_color']}; }\n";
        }

        if ( $custom_css ) {
            wp_add_inline_style( 'myblocktheme-style', $custom_css );
        }
    }

    /**
     * Переоприділяє палітру Gutenberg відповідно до налаштувань.
     */
    public function override_gutenberg_palette() {
        $options = wp_parse_args(
            get_option( $this->option_name, array() ),
            $this->default_options
        );
        $palette = array();

        // існуючі кольори
        if ( ! empty( $options['background_color'] ) ) {
            $palette[] = array( 'name' => __( 'Фон', 'embo-settings' ),       'slug' => 'background',          'color' => $options['background_color'] );
        }
        if ( ! empty( $options['text_color'] ) ) {
            $palette[] = array( 'name' => __( 'Текст', 'embo-settings' ),      'slug' => 'text',                'color' => $options['text_color'] );
        }
        if ( ! empty( $options['link_color'] ) ) {
            $palette[] = array( 'name' => __( 'Посилання', 'embo-settings' ),  'slug' => 'links',               'color' => $options['link_color'] );
        }
        if ( ! empty( $options['button_color'] ) ) {
            $palette[] = array( 'name' => __( 'Кнопки', 'embo-settings' ),     'slug' => 'button',              'color' => $options['button_color'] );
        }
        if ( ! empty( $options['header_background_color'] ) ) {
            $palette[] = array( 'name' => __( 'Фон шапки', 'embo-settings' ),  'slug' => 'header-bg',           'color' => $options['header_background_color'] );
        }
        if ( ! empty( $options['footer_background_color'] ) ) {
            $palette[] = array( 'name' => __( 'Фон футера', 'embo-settings' ), 'slug' => 'footer-bg',           'color' => $options['footer_background_color'] );
        }
        if ( ! empty( $options['aside_background_color'] ) ) {
            $palette[] = array( 'name' => __( 'Фон сайдбара', 'embo-settings' ), 'slug' => 'aside-bg',           'color' => $options['aside_background_color'] );
        }

        // нові кольори
        if ( ! empty( $options['header_menu_link_color'] ) ) {
            $palette[] = array( 'name' => __( 'Меню (хедер)', 'embo-settings' ), 'slug' => 'header-menu-link',    'color' => $options['header_menu_link_color'] );
        }
        if ( ! empty( $options['header_menu_hover_color'] ) ) {
            $palette[] = array( 'name' => __( 'Меню hover (хедер)', 'embo-settings' ), 'slug' => 'header-menu-hover',   'color' => $options['header_menu_hover_color'] );
        }
        if ( ! empty( $options['footer_menu_link_color'] ) ) {
            $palette[] = array( 'name' => __( 'Меню (футер)', 'embo-settings' ), 'slug' => 'footer-menu-link',    'color' => $options['footer_menu_link_color'] );
        }
        if ( ! empty( $options['footer_menu_hover_color'] ) ) {
            $palette[] = array( 'name' => __( 'Меню hover (футер)', 'embo-settings' ), 'slug' => 'footer-menu-hover',   'color' => $options['footer_menu_hover_color'] );
        }
        if ( ! empty( $options['tabs_link_color'] ) ) {
            $palette[] = array( 'name' => __( 'Таби (звичайні)', 'embo-settings' ), 'slug' => 'tabs-link',           'color' => $options['tabs_link_color'] );
        }
        if ( ! empty( $options['tabs_active_link_color'] ) ) {  
            $palette[] = array( 'name' => __( 'Таби активні', 'embo-settings' ), 'slug' => 'tabs-active-link',    'color' => $options['tabs_active_link_color'] );
        }
        if ( ! empty( $options['comment_button_color'] ) ) {
            $palette[] = array( 'name' => __( 'Кнопка коментарів', 'embo-settings' ), 'slug' => 'comment-button',      'color' => $options['comment_button_color'] );
        }
        if ( ! empty( $options['readmore_button_color'] ) ) {
            $palette[] = array( 'name' => __( 'Кнопка Читати далі', 'embo-settings' ), 'slug' => 'readmore-button',     'color' => $options['readmore_button_color'] );
        }
        if ( ! empty( $options['loadmore_button_color'] ) ) {
            $palette[] = array( 'name' => __( 'Кнопка Загрузити старі пости', 'embo-settings' ), 'slug' => 'loadmore-button',     'color' => $options['loadmore_button_color'] );
        }

        remove_theme_support( 'editor-color-palette' );
        if ( ! empty( $palette ) ) {
            add_theme_support( 'editor-color-palette', $palette );
        }
    }

    /**
     * Відображає HTML форму для налаштувань кольорів.
     */
    public function render_colors_form() {
        $options = wp_parse_args(
            get_option( $this->option_name, array() ),
            $this->default_options
        );
        ?>
        <form method="post" action="options.php">
            <?php settings_fields( 'embo_colors_group' ); ?>
            <table class="form-table">

                <tr>
                    <th scope="row"><label for="background_color"><?php esc_html_e( 'Колір фону', 'embo-settings' ); ?></label></th>
                    <td><input type="text" class="embo-color-field" id="background_color"
                               name="<?php echo esc_attr( $this->option_name ); ?>[background_color]"
                               value="<?php echo esc_attr( $options['background_color'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="text_color"><?php esc_html_e( 'Колір тексту', 'embo-settings' ); ?></label></th>
                    <td><input type="text" class="embo-color-field" id="text_color"
                               name="<?php echo esc_attr( $this->option_name ); ?>[text_color]"
                               value="<?php echo esc_attr( $options['text_color'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="link_color"><?php esc_html_e( 'Колір посилань', 'embo-settings' ); ?></label></th>
                    <td><input type="text" class="embo-color-field" id="link_color"
                               name="<?php echo esc_attr( $this->option_name ); ?>[link_color]"
                               value="<?php echo esc_attr( $options['link_color'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="button_color"><?php esc_html_e( 'Колір кнопок', 'embo-settings' ); ?></label></th>
                    <td><input type="text" class="embo-color-field" id="button_color"
                               name="<?php echo esc_attr( $this->option_name ); ?>[button_color]"
                               value="<?php echo esc_attr( $options['button_color'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="header_background_color"><?php esc_html_e( 'Колір фону шапки', 'embo-settings' ); ?></label></th>
                    <td><input type="text" class="embo-color-field" id="header_background_color"
                               name="<?php echo esc_attr( $this->option_name ); ?>[header_background_color]"
                               value="<?php echo esc_attr( $options['header_background_color'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="footer_background_color"><?php esc_html_e( 'Колір фону футера', 'embo-settings' ); ?></label></th>
                    <td><input type="text" class="embo-color-field" id="footer_background_color"
                               name="<?php echo esc_attr( $this->option_name ); ?>[footer_background_color]"
                               value="<?php echo esc_attr( $options['footer_background_color'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="aside_background_color"><?php esc_html_e( 'Колір фону сайдбара', 'embo-settings' ); ?></label></th>
                    <td><input type="text" class="embo-color-field" id="aside_background_color"
                               name="<?php echo esc_attr( $this->option_name ); ?>[aside_background_color]"
                               value="<?php echo esc_attr( $options['aside_background_color'] ); ?>" /></td>
                </tr>

                <!-- нові поля -->
                <tr>
                    <th scope="row"><label for="header_menu_link_color"><?php esc_html_e( 'Колір посилань меню (хедер)', 'embo-settings' ); ?></label></th>
                    <td><input type="text" class="embo-color-field" id="header_menu_link_color"
                               name="<?php echo esc_attr( $this->option_name ); ?>[header_menu_link_color]"
                               value="<?php echo esc_attr( $options['header_menu_link_color'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="header_menu_hover_color"><?php esc_html_e( 'Колір hover меню (хедер)', 'embo-settings' ); ?></label></th>
                    <td><input type="text" class="embo-color-field" id="header_menu_hover_color"
                               name="<?php echo esc_attr( $this->option_name ); ?>[header_menu_hover_color]"
                               value="<?php echo esc_attr( $options['header_menu_hover_color'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="footer_menu_link_color"><?php esc_html_e( 'Колір посилань меню (футер)', 'embo-settings' ); ?></label></th>
                    <td><input type="text" class="embo-color-field" id="footer_menu_link_color"
                               name="<?php echo esc_attr( $this->option_name ); ?>[footer_menu_link_color]"
                               value="<?php echo esc_attr( $options['footer_menu_link_color'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="footer_menu_hover_color"><?php esc_html_e( 'Колір hover меню (футер)', 'embo-settings' ); ?></label></th>
                    <td><input type="text" class="embo-color-field" id="footer_menu_hover_color"
                               name="<?php echo esc_attr( $this->option_name ); ?>[footer_menu_hover_color]"
                               value="<?php echo esc_attr( $options['footer_menu_hover_color'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="tabs_link_color"><?php esc_html_e( 'Колір табів (звичайний)', 'embo-settings' ); ?></label></th>
                    <td><input type="text" class="embo-color-field" id="tabs_link_color"
                               name="<?php echo esc_attr( $this->option_name ); ?>[tabs_link_color]"
                               value="<?php echo esc_attr( $options['tabs_link_color'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="tabs_active_link_color"><?php esc_html_e( 'Колір активного таба', 'embo-settings' ); ?></label></th>
                    <td><input type="text" class="embo-color-field" id="tabs_active_link_color"
                               name="<?php echo esc_attr( $this->option_name ); ?>[tabs_active_link_color]"
                               value="<?php echo esc_attr( $options['tabs_active_link_color'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="comment_button_color"><?php esc_html_e( 'Колір кнопки «Опублікувати коментар»', 'embo-settings' ); ?></label></th>
                    <td><input type="text" class="embo-color-field" id="comment_button_color"
                               name="<?php echo esc_attr( $this->option_name ); ?>[comment_button_color]"
                               value="<?php echo esc_attr( $options['comment_button_color'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="readmore_button_color"><?php esc_html_e( 'Колір кнопки «Читати далі»', 'embo-settings' ); ?></label></th>
                    <td><input type="text" class="embo-color-field" id="readmore_button_color"
                               name="<?php echo esc_attr( $this->option_name ); ?>[readmore_button_color]"
                               value="<?php echo esc_attr( $options['readmore_button_color'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="loadmore_button_color"><?php esc_html_e( 'Колір кнопки «Завантажити старі пости»', 'embo-settings' ); ?></label></th>
                    <td><input type="text" class="embo-color-field" id="loadmore_button_color"
                               name="<?php echo esc_attr( $this->option_name ); ?>[loadmore_button_color]"
                               value="<?php echo esc_attr( $options['loadmore_button_color'] ); ?>" /></td>
                </tr>
            </table>

            <?php submit_button( __( 'Зберегти налаштування', 'embo-settings' ) ); ?>

            <?php
            $reset_url = add_query_arg( array(
                'reset_embo_colors' => '1',
                '_wpnonce'          => wp_create_nonce( 'reset_embo_colors_nonce' )
            ) );
            ?>
            <a href="<?php echo esc_url( $reset_url ); ?>" class="button button-secondary" style="margin-left:10px;">
                <?php esc_html_e( 'Скинути налаштування', 'embo-settings' ); ?>
            </a>

            <?php
            $pull_url = add_query_arg( array(
                'pull_embo_colors' => '1',
                '_wpnonce'         => wp_create_nonce( 'pull_embo_colors_nonce' )
            ) );
            ?>
            <a href="<?php echo esc_url( $pull_url ); ?>" class="button button-secondary" style="margin-left:10px;">
                <?php esc_html_e( 'Підтягнути з конфігу', 'embo-settings' ); ?>
            </a>
        </form>
        <?php
    }
}