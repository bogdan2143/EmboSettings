<?php
namespace EmboSettings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Cookie_Analytics_Tab {

    /**
     * Option name used to store cookies and analytics settings.
     *
     * @var string
     */
    private $option_name = 'embo_cookie_analytics_options';

    /**
     * Default options populated in the constructor.
     *
     * @var array
     */
    private $default_options = [];

    /**
     * Constructor initializes the default options with translated strings.
     */
    public function __construct() {
        $this->default_options = [
            'embo_cookie_message' => __( 'Цей сайт використовує cookies для покращення користувацького досвіду. <a href="#">Дізнатися більше</a>.', 'embo-settings' ),
            'cookie_button_text'  => __( 'Прийняти', 'embo-settings' ),
            'ga_code'             => '',
        ];
    }

    /**
     * Register settings using the Settings API.
     */
    public function register_settings() {
        register_setting(
            'embo_cookie_analytics_group',
            $this->option_name,
            [ $this, 'sanitize_options' ]
        );
    }

    /**
     * Sanitize input data.
     */
    public function sanitize_options( $input ) {
        $sanitized = [];
        $sanitized['embo_cookie_message']     = isset( $input['embo_cookie_message'] )
            ? wp_kses_post( trim( $input['embo_cookie_message'] ) )
            : $this->default_options['embo_cookie_message'];
        $sanitized['cookie_button_text'] = isset( $input['cookie_button_text'] )
            ? sanitize_text_field( $input['cookie_button_text'] )
            : $this->default_options['cookie_button_text'];
        $sanitized['ga_code']            = isset( $input['ga_code'] )
            ? trim( $input['ga_code'] )
            : '';
        return wp_parse_args( $sanitized, $this->default_options );
    }

    /**
     * Display the settings form in the admin area.
     */
    public function render_settings_page() {
        // Retrieve saved options or defaults
        $opts = get_option( $this->option_name, $this->default_options );
        ?>
        <form method="post" action="options.php">
            <?php settings_fields( 'embo_cookie_analytics_group' ); ?>
            <table class="form-table">

                <!-- 1) Cookie Banner Message -->
                <tr>
                    <th>
                        <label for="embo_cookie_message">
                            <?php esc_html_e( 'Cookie Banner Message', 'embo-settings' ); ?>
                        </label>
                    </th>
                    <td>
                        <textarea
                            id="embo_cookie_message"
                            name="<?php echo esc_attr( $this->option_name ); ?>[embo_cookie_message]"
                            rows="4"
                            style="width:100%;"
                        ><?php echo esc_textarea( $opts['embo_cookie_message'] ); ?></textarea>
                    </td>
                </tr>

                <!-- 2) Accept Button Text -->
                <tr>
                    <th>
                        <label for="cookie_button_text">
                            <?php esc_html_e( 'Accept Button Text', 'embo-settings' ); ?>
                        </label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="cookie_button_text"
                            name="<?php echo esc_attr( $this->option_name ); ?>[cookie_button_text]"
                            value="<?php echo esc_attr( $opts['cookie_button_text'] ); ?>"
                            class="regular-text"
                        />
                    </td>
                </tr>

                <!-- 3) Google Analytics Code -->
                <tr>
                    <th>
                        <label for="ga_code">
                            <?php esc_html_e( 'Google Analytics Code', 'embo-settings' ); ?>
                        </label>
                    </th>
                    <td>
                        <textarea
                            id="ga_code"
                            name="<?php echo esc_attr( $this->option_name ); ?>[ga_code]"
                            rows="6"
                            style="width:100%;"
                            placeholder="<?php esc_attr_e( '<script>…</script>', 'embo-settings' ); ?>"
                        ><?php echo esc_textarea( $opts['ga_code'] ); ?></textarea>
                    </td>
                </tr>

            </table>
            <?php submit_button( __( 'Save Cookies & GA Settings', 'embo-settings' ) ); ?>
        </form>
        <?php
    }

    /**
     * Enqueue frontend styles for the banner (inline CSS or Bulma).
     */
    public function enqueue_assets() {
        wp_add_inline_style( 'myblocktheme-style', '
            #cookie-banner {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                padding: 1em;
                background: rgba(0,0,0,0.8);
                color: #fff;
                text-align: center;
                z-index: 9999;
            }
            #cookie-banner a { color: #ffd; text-decoration: underline; }
            #cookie-accept-btn {
                margin-left: 1em;
                background: #3273dc;
                color: #fff;
                border: none;
                padding: .5em 1em;
                cursor: pointer;
                border-radius: 4px;
            }
        ' );
    }

    /**
     * Output GA code inside <head> before closing tag.
     */
    public function print_ga_code() {
        $opts = get_option( $this->option_name, $this->default_options );
        if ( ! empty( $opts['ga_code'] ) ) {
            echo $opts['ga_code'];
        }
    }

    /**
     * Render the cookie banner on the front end if consent is not stored.
     */
    public function render_cookie_banner() {
        $opts = get_option( $this->option_name, $this->default_options );
        ?>
        <script>
        (function(){
            if ( ! localStorage.getItem('cookieAccepted') ) {
                var banner = document.createElement('div');
                banner.id = 'cookie-banner';
                banner.innerHTML = '<?php echo esc_js( $opts['embo_cookie_message'] ); ?>'
                                 + ' <button id="cookie-accept-btn"><?php echo esc_js( $opts['cookie_button_text'] ); ?></button>';
                document.body.appendChild(banner);
                document.getElementById('cookie-accept-btn').addEventListener('click', function(){
                    localStorage.setItem('cookieAccepted', '1');
                    banner.style.display = 'none';
                });
            }
        })();
        </script>
        <?php
    }
}
