<?php
/*
Plugin Name: ChatGPT Integration
Description: Integrates ChatGPT with WordPress using Python backend
Version: 1.0
Author: M Haroon Abbas
*/

if (!defined('ABSPATH')) exit;

class ChatGPT_Integration {

    private $options;

    public function __construct() {
        add_action('admin_menu', [$this, 'add_plugin_page']);
        add_action('admin_init', [$this, 'page_init']);
        add_shortcode('chatgpt', [$this, 'chatgpt_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_chatgpt_request', [$this, 'handle_ajax_request']);
        add_action('wp_ajax_nopriv_chatgpt_request', [$this, 'handle_ajax_request']);
    }

    public function add_plugin_page() {
        add_options_page(
            'ChatGPT Settings', 
            'ChatGPT', 
            'manage_options', 
            'chatgpt-setting-admin', 
            [$this, 'create_admin_page']
        );
    }

    public function create_admin_page() {
        $this->options = get_option('chatgpt_options');
        ?>
        <div class="wrap">
            <h1>ChatGPT Integration</h1>
            <form method="post" action="options.php">
            <?php
                settings_fields('chatgpt_option_group');
                do_settings_sections('chatgpt-setting-admin');
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    public function page_init() {
        register_setting(
            'chatgpt_option_group',
            'chatgpt_options',
            [$this, 'sanitize']
        );

        add_settings_section(
            'chatgpt_setting_section',
            'API Settings',
            [$this, 'print_section_info'],
            'chatgpt-setting-admin'
        );

        add_settings_field(
            'api_url',
            'API URL',
            [$this, 'api_url_callback'],
            'chatgpt-setting-admin',
            'chatgpt_setting_section'
        );

        add_settings_field(
            'api_key', 
            'API Key', 
            [$this, 'api_key_callback'], 
            'chatgpt-setting-admin', 
            'chatgpt_setting_section'
        );
    }

    public function sanitize($input) {
        $new_input = [];
        if (isset($input['api_url']))
            $new_input['api_url'] = esc_url_raw($input['api_url']);
        if (isset($input['api_key']))
            $new_input['api_key'] = sanitize_text_field($input['api_key']);
        return $new_input;
    }

    public function print_section_info() {
        print 'Enter your ChatGPT API settings below:';
    }

    public function api_url_callback() {
        printf(
            '<input type="text" id="api_url" name="chatgpt_options[api_url]" value="%s" class="regular-text" />',
            isset($this->options['api_url']) ? esc_attr($this->options['api_url']) : 'http://localhost:8000/chat'
        );
    }

    public function api_key_callback() {
        printf(
            '<input type="password" id="api_key" name="chatgpt_options[api_key]" value="%s" class="regular-text" />',
            isset($this->options['api_key']) ? esc_attr($this->options['api_key']) : ''
        );
    }

    public function enqueue_scripts() {
        wp_enqueue_style('chatgpt-css', plugin_dir_url(__FILE__) . 'styles.css');
        wp_enqueue_script('chatgpt-js', plugin_dir_url(__FILE__) . 'chatgpt.js', ['jquery'], '1.0', true);
        
        wp_localize_script('chatgpt-js', 'chatgpt_vars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('chatgpt_nonce')
        ]);
    }

    public function chatgpt_shortcode($atts = []) {
        $atts = shortcode_atts([
            'placeholder' => 'Ask me anything...',
            'button_text' => 'Send',
            'max_tokens' => 150,
            'temperature' => 0.7,
            'model' => 'gpt-3.5-turbo'
        ], $atts);

        ob_start();
        ?>
        <div class="chatgpt-container">
            <div class="chatgpt-response"></div>
            <textarea class="chatgpt-prompt" placeholder="<?php echo esc_attr($atts['placeholder']); ?>"></textarea>
            <input type="hidden" class="chatgpt-max-tokens" value="<?php echo esc_attr($atts['max_tokens']); ?>">
            <input type="hidden" class="chatgpt-temperature" value="<?php echo esc_attr($atts['temperature']); ?>">
            <input type="hidden" class="chatgpt-model" value="<?php echo esc_attr($atts['model']); ?>">
            <button class="chatgpt-submit"><?php echo esc_html($atts['button_text']); ?></button>
            <div class="chatgpt-loading" style="display:none;">Processing...</div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function handle_ajax_request() {
        check_ajax_referer('chatgpt_nonce', 'nonce');

        $options = get_option('chatgpt_options');
        $prompt = sanitize_text_field($_POST['prompt']);
        $max_tokens = intval($_POST['max_tokens']);
        $temperature = floatval($_POST['temperature']);
        $model = sanitize_text_field($_POST['model']);

        $args = [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-API-KEY' => $options['api_key']
            ],
            'body' => json_encode([
                'prompt' => $prompt,
                'max_tokens' => $max_tokens,
                'temperature' => $temperature,
                'model' => $model
            ]),
            'timeout' => 30
        ];

        $response = wp_remote_post($options['api_url'], $args);

        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('Invalid JSON response from API');
        }

        if (wp_remote_retrieve_response_code($response) !== 200) {
            wp_send_json_error($data['detail'] ?? 'Unknown API error');
        }

        wp_send_json_success($data);
    }
}

new ChatGPT_Integration();