<?php
namespace UiCoreElements\Utils;

class Email_Exception extends \Exception {}
class Redirect_Exception extends \Exception {}
class Submit_Exception extends \Exception {}
class Mailchimp_Exception extends \Exception {}

defined('ABSPATH') || exit();

/**
 * Handles the form submissions and responses
 */

class Contact_Form_Service {

    protected $form_data,
              $settings,
              $files;

    public function __construct($form_data, $settings, $files) {
        $this->form_data = $form_data;
        $this->settings = $settings;
        $this->files = $files;
    }

    public function handle() {

        $data = [];
        $responses = [];

        // Checks for reCAPTCHA validation
        if (isset($this->form_data['grecaptcha_token']) && !empty($this->form_data['grecaptcha_token'])) {

            $recaptcha = $this->validate_recaptcha($this->form_data['grecaptcha_token'], $this->form_data['grecaptcha_version']);

            if(!$recaptcha['success']){
                return [
                    'status' => 'error',
                    'data' => [
                        'message' => esc_html__('reCAPTCHA validation failed.', 'uicore-elements'),
                    ]
                ];
            }
        }

        // Check for honeypot spam
        if(!$this->validate_spam()){
            return [
                'status' => 'success',
                'data' => [
                    'message' => $this->get_response_message('success') // Fakes a successfull submission
                ]
            ];
        }

        // Run all registered submit actions
        if (isset($this->settings['submit_actions']) && !empty($this->settings['submit_actions'])) {
            foreach ($this->settings['submit_actions'] as $action) {
                try {
                    switch ($action) {
                        case 'email':
                            $data = $this->send_mail($action);
                            $responses['email'] = $data['response'];
                            break;

                        case 'email_2' :
                            $data = $this->send_mail($action, $data);
                            $responses['email'] = $data['response'];
                            break;

                        case 'redirect':
                            $responses['redirect'] = $this->redirect();
                            break;

                        case 'mailchimp':
                            $responses['mailchimp'] = $this->mailchimp();
                            break;

                        default:
                            throw new Submit_Exception(esc_html__('Unknown submit action: ', 'uicore-elements') . $action . esc_html__('. Check your settings.', 'uicore-elements'));
                    }
                } catch (Email_Exception $e) {
                    $responses['email'] = [
                        'status' => false,
                        'message' => $e->getMessage()
                    ];
                } catch (Redirect_Exception $e) {
                    $responses['redirect'] = [
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                } catch (Mailchimp_Exception $e) {
                    $responses['mailchimp'] = [
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                } catch (Submit_Exception $e) {
                    $responses['submit'] = [
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                }
                // We're avoiding throwing exception for mailchimp because would require a specific validation function, and is to much for now
            }

        // There's no need to continue without a submit action enabled
        } else {
            return [
                'status' => 'error',
                'data' => [
                    'message' => esc_html__('No submit action enabled.', 'uicore-elements')
                ]
            ];
        }

        // Consider `current_user_can( 'manage_options' )` as filter to return more specific messages on frontend (not tested)

        // Since attachments may be used up to two times (both emails), they need to be deleted only after processing submits
        if ( isset($data['attachments']) && !empty($data['attachments']['files']) ) {
            register_shutdown_function('unlink', $data['attachments']['files']);
        }

        $output = $this->build_frontend_responses($responses);

        return [
            'status' => $output['status'],
            'data' => $output['data'],
        ];
    }

    /**
     * Mail submition
     */
    protected function send_mail(string $action, array $data = []){

        $attachments = isset($data['attachments']) ? $data['attachments'] : []; // Check if there's attachments from previous mail submit action

        $mail_data = $this->compose_mail_data($action, $attachments); // build mail data

        // Check if there's any attachment error before sending mail
        if (!empty($mail_data['attachments']['errors'])) {
            // throwing exceptions here will block proper data flow. Is best directly returning the error on email action
            return [
                'response' => [
                    'status' => false,
                    'message' => $mail_data['attachments']['errors']
                ],
            ];
        }

        $email = wp_mail(
            $mail_data['email']['to'],
            $mail_data['email']['subject'],
            $mail_data['email']['message'],
            $mail_data['email']['headers'],
            $mail_data['email']['attachments']
        );

        return [
            'response' => [
                'status' => $email ? 'success' : 'error',
                'message' => $email ? $this->get_response_message('success') : $this->get_response_message('mail_error')
            ],
            'attachments' => $mail_data['attachments'] // Return attachments for deletion and error handling
        ];
    }
    protected function compose_mail_data(string $action, array $attachments = []) {

        // Set short vars for the data
        $settings = $this->settings;
        $data = $this->form_data;
        $files = $this->files;

        $slug = $action == 'email_2' ? '_2' : ''; // Update controls slugs based on the mail submit type
        $line_break = $settings['email_content_type'.$slug] === 'html' ? '<br>' : "\n"; // Set line break type

        // Replace shortcodes by form data
        $content = $this->replace_content_shortcode( $settings['email_content'.$slug], $line_break );

        // Adds the metadata to content
        $content = $this->compose_metadata($content, $settings['form_metadata'.$slug], $line_break);

        // Set empty attachments to avoid undefined errors for widgets without attachment options
        if($data['widget_type'] !== 'contact-form') {
            $attachments = [ 'files' => '', 'errors' => '' ];
        } else {
            $attachments = !empty($attachments) ? $attachments : $this->prepare_attachments($files); // If theres attachments from previous submit action, use it, otherwhise prepare it from $files,
        }

        // Validate and replace fields shortcodes
        $mail_to = $this->replace_content_shortcode( $this->validate_field($settings['email_to'.$slug], 'Recipient (to)'));
        $mail_subject = $this->replace_content_shortcode( $this->validate_field($settings['email_subject'.$slug], 'Subject'));
        $mail_name = $this->replace_content_shortcode( $this->validate_field($settings['email_from_name'.$slug], 'From Name'));
        $mail_from = $this->replace_content_shortcode( $this->validate_field($settings['email_from'.$slug], 'From'));
        $mail_reply = $this->replace_content_shortcode( $this->validate_field($settings['email_reply_to'.$slug], 'Reply To'));

        // Build the data
        $mail_data = [
            'to' => $mail_to,
            'subject' => $mail_subject,
            'message' => $content,
            'headers' => [
                'Content-Type: text/' . $settings['email_content_type'.$slug] . '; charset=UTF-8',
                'From: ' . $mail_name . ' <'.$mail_from.'>',
                'Reply-To: ' . $mail_reply,
            ],
            'attachments' => $attachments['files']
        ];

        // Build optional data
        if (!empty($settings['email_to_cc'.$slug])) {
            $mail_data['headers'][] = 'Cc: ' . $settings['email_to_cc'];
        }
        if (!empty($settings['email_to_bcc'.$slug])) {
            $mail_data['headers'][] = 'Bcc: ' . $settings['email_to_bcc'];
        }

        return [
            'email' => $mail_data,
            'attachments' => $attachments
        ];
    }
    protected function replace_content_shortcode(string $content, string $line_break = ''){

        // Set short vars for the data
        $fields = $this->get_setting_fields();
        $form_data = $this->form_data;

        // [all-fieds] shortcode replacement
        if ( false !== strpos( $content, '[all-fields]' ) ) {
            $text = '';
            // Return formated text as key: value
            foreach ( $form_data['form_fields'] as $key => $field ) {
                $field_value = is_array($field) ? implode(', ', $field) : $field;
                $text .= !empty($field_value) ? sprintf('%s: %s', $key, $field_value) . $line_break : '';
            }
            $content = str_replace( '[all-fields]', $text, $content );
        }

        // Custom [field id="{id}"] shortcode replacement
        foreach ($fields as $field) {
            $shortcode = '[field id="' . $field['custom_id'] . '"]';
            $value = isset($form_data['form_fields'][$field['custom_id']]) ? $form_data['form_fields'][$field['custom_id']] : '';
            $value = is_array($value) ? implode(', ', $value) : $value;
            $content = str_replace($shortcode, $value, $content);
        }

        // Replaces all manual line breaks from content
        if(!empty($line_break)){
            $content = str_replace( array( "\r\n", "\r", "\n" ), $line_break, $content );
        }

        return $content;
    }
    protected function prepare_attachments(array $files) {
        $attachments = [];
        $errors = '';

        if( !isset($files['form_fields']) || empty($files['form_fields']) ) {
            return [
                'files' => '',
                'errors' => ''
            ];
        }

        // Requires wp_handle_upload() file if unavailable
        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }

        // Check if theres a valid file to upload
        foreach ($files['form_fields']['tmp_name'] as $input => $value) {
            if ($files['form_fields']['error'][$input] !== UPLOAD_ERR_NO_FILE) {
                $file = [
                    'name' => $files['form_fields']['name'][$input],
                    'type' => $files['form_fields']['type'][$input],
                    'tmp_name' => $files['form_fields']['tmp_name'][$input],
                    'error' => $files['form_fields']['error'][$input],
                    'size' => $files['form_fields']['size'][$input],
                ];

                // Handle the file upload
                $uploaded_file = wp_handle_upload($file, ['test_form' => false]);

                if (!isset($uploaded_file['error'])) {
                    $attachments = $uploaded_file['file'];
                } else {
                    // Since throwing exceptions here will block the proper data flow, we return the error and let send_mail() handle it
                    $errors = esc_html__('Failed to upload file: ', 'uicore-elements') . $uploaded_file['error'];
                }

                // Break after processing the first valid file
                break;
            }
        }

        return [
            'files' => $attachments,
            'errors' => $errors
        ];
    }
    protected function compose_metadata(string $content, array $metadada, string $line_break){

        if (empty($metadada)) {
            return $content;
        }

        $content = $content . $line_break . $line_break . '--' . $line_break . $line_break; // Adds spacing between content and metadata

        foreach ($metadada as $meta) {
            switch($meta){
                case 'date':
                    $content .= sprintf( '%s: %s', 'Date', gmdate('Y-m-d') . $line_break);
                    break;

                case 'time' :
                    $content .= sprintf( '%s: %s', 'Time', gmdate('H:i:s') . $line_break);
                    break;

                case 'remote_ip':
                    $content .= isset($_SERVER['REMOTE_ADDR'])
                        ? sprintf( '%s: %s', 'IP', sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])). $line_break)
                        : '';
                    break;

                case 'user_agent':
                    $content .= isset($_SERVER['HTTP_USER_AGENT'])
                        ?  sprintf( '%s: %s', 'User Agent', sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) . $line_break)
                        : '';
                    break;

                case 'page_url':
                    $content .= isset($_SERVER['HTTP_REFERER'])
                        ?  sprintf( '%s: %s', 'Page URL', sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER'])) . $line_break)
                        : '';
                    break;
            }
        }

        return $content;
    }

    /**
     * Extra submissions
     */
    protected function redirect() {

        $validation = $this->validate_url( $this->settings['redirect_to'] );

        // Above function exception blocks this execution
        return [
            'status' => 'success',
            'url' => esc_url( $validation['url'] ),
            'delay' => 1500,
            'message' => esc_html( $this->get_response_message('redirect') )
        ];
    }
    protected function mailchimp() {

        // Get API data
        $key     = get_option('uicore_elements_mailchimp_secret_key');
        $list_id = $this->settings['mailchimp_audience_id'];
        $server  = explode('-', $key)[1]; // Server value can be found on API Key after the dash

        $this->validate_mailchimp($key, $list_id);

        // Check the widget type to determine the fields, before getting form data
        if( $this->form_data['widget_type'] === 'contact-form' ) {

            // Since contact form has custom IDs, we need to get the ID value from the settings
            $settings = $this->settings;

            $email    = $this->form_data['form_fields'][$settings['mailchimp_email_id']];
            $merge_fields = [
                'FNAME' => isset( $this->form_data['form_fields'][$settings['mailchimp_fname_id']] ) ? $this->form_data['form_fields'][$settings['mailchimp_fname_id']] : "",
                'LNAME' => isset( $this->form_data['form_fields'][$settings['mailchimp_lname_id']] ) ? $this->form_data['form_fields'][$settings['mailchimp_lname_id']] : "",
                'PHONE' => isset( $this->form_data['form_fields'][$settings['mailchimp_phone_id']] ) ? $this->form_data['form_fields'][$settings['mailchimp_phone_id']] : "",
                'BIRTHDAY' => isset( $this->form_data['form_fields'][$settings['mailchimp_birthday_id']] ) ? $this->form_data['form_fields'][$settings['mailchimp_birthday_id']] : ""
            ];

        // Else can only be newsletter widget
        } else {

            // Since Newsletter has fixed field IDs, we get them directly
            $email = $this->form_data['form_fields']['email'];
            $merge_fields = [
                'FNAME' => isset( $this->form_data['form_fields']['name'] ) ? $this->form_data['form_fields']['name'] : ""
            ];
        }

        // Build the request
        $url  = 'https://' . esc_html($server) . '.api.mailchimp.com/3.0/lists/' . esc_html($list_id) . '/members/';
        $data = [
            "email_address" => $email,
            "status" => "subscribed",
            "merge_fields" => $merge_fields
        ];

        $response = wp_remote_post($url, [
            'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode('anystring:' . $key)
            ],
            'body' => json_encode($data),
            'timeout' => 15,
        ]);

        if ( is_wp_error($response) ) {
            return [
                'status' => 'error',
                'message' => $response->get_error_message()
            ];
        }

        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        return $result;
    }

    /**
     * Validations
     */
    protected function validate_recaptcha(string $token, string $version) {

        // Check if secret and site key are set
        if (!get_option('uicore_elements_recaptcha_secret_key') || !get_option('uicore_elements_recaptcha_site_key')) {
            return [
                'success' => false,
                'message' => esc_html__('reCAPTCHA API keys are not set.', 'uicore-elements')
            ];
        }

        $data = [
            'secret' => get_option('uicore_elements_recaptcha_secret_key'),
            'response' => sanitize_text_field($token)
        ];

        $response = wp_remote_post("https://www.google.com/recaptcha/api/siteverify", [
            'body' => $data,
            'timeout' => 15,
        ]);

        if ( is_wp_error($response) ) {
            return [
                'success' => false,
                'message' => $response->get_error_message()
            ];
        }

        $captcha = json_decode( wp_remote_retrieve_body($response));

        if ($version === 'V3') {
            return ['success' => ($captcha->success && $captcha->score >= 0.5) ? true : false];
        }

        // V2 default
        return ['success' => $captcha->success];
    }
    protected function validate_spam() {
        // `ui-e-h-p` is the key for the honeypot
        return ( isset($this->form_data['ui-e-h-p']) && !empty($this->form_data['ui-e-h-p']) ) ? false : true;
    }
    protected function validate_url(string $url) {
        if (empty($url)) {
            throw new Redirect_Exception( esc_html( $this->get_response_message('redirect_no_url')));
        }

        return [
            'status' => true,
            'url' => $url,
        ];
    }
    protected function validate_field(string $field, string $label) {
        if (empty($field)) {
            throw new Submit_Exception( esc_html( $this->get_response_message('empty_field', esc_html($label))));
        }
        return $field;
    }
    protected function validate_mailchimp(string $key, string $list_id) {
        if (empty($key)) {
            throw new Mailchimp_Exception(esc_html__('Mailchimp API key is not set. Check Uicore Elements settings.', 'uicore-elements'));
        } else if (empty($list_id)) {
            throw new Mailchimp_Exception(esc_html__('Audience ID control is not set. Check your widget settings.', 'uicore-elements'));
        }
    }

    /**
     * Helpers
     */
    protected function get_setting_fields() {
        // Used to determine if the widget fields are repeaters with custom IDS or fixed fields, and if fixed fields
        // compose them into an array similar to repeaters, to simplify shortcode replacement function

        switch ($this->form_data['widget_type']) {

            case 'newsletter':
                return [
                    ['custom_id' => 'email'],
                    ['custom_id' => 'name'],
                ];
                break;

            // Dynamic fields values
            default:
                return $this->settings['form_fields'];
                break;
        }
    }
    protected function all_submissions_succedded($responses) {
        foreach ($responses as $submission => $data) {
            // Redirect action failure shouldn't return `error` to main status because is not properly a submission action, so we skip it.
            if( $submission == 'redirect') {
                continue;
            }
            // Failed submissions returns `error` or bool `false` status
            if( $data['status'] === 'error' || $data['status'] === false ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Responses
     */
    // Also used by form widget(s), therefore public and static
    public static function get_default_messages(){
        return [
            // main messages
			'success'    => esc_html__( 'Your submission was successful.', 'uicore-elements' ),
			'error'      => esc_html__( 'Your submission failed because of an error.', 'uicore-elements' ),
            'mail_error' => esc_html__( 'Failed to send email.', 'uicore-elements' ),
            'required'   => esc_html__( 'Fill all required fields.', 'uicore-elements' ),
            'redirect'   => esc_html__( 'Redirecting...', 'uicore-elements' ),
		];
    }
    protected function get_response_message(string $status, string $dinamic_data = '') {
        // non-customizable messages (for settings debugging only)
        $default_messages = [
            'invalid_status'    => esc_html__( 'Invalid status message.', 'uicore-elements' ),
            'redirect_no_url'   => esc_html__( 'Redirection failed. No URL set.', 'uicore-elements' ),
            'empty_field'       => esc_html__( 'The following field is empty: ', 'uicore-elements') . $dinamic_data,
        ];

        if($this->settings['custom_messages'] === 'yes') {
            $messages = [
                'success' => esc_html($this->settings['success_message']),
                'error' => esc_html($this->settings['error_message']),
                'mail_error' => esc_html($this->settings['mail_error_message']),
                'redirect' =>esc_html( $this->settings['redirect_message']),
            ];
        } else {
            $messages = self::get_default_messages();
        }

        $messages = array_merge($default_messages, $messages);

        return isset($messages[$status]) ? $messages[$status] : $messages['invalid_status'];
    }
    protected function build_frontend_responses($responses) {

        $data = [];

        // Mail response
        if ( isset($responses['email']) && $responses['email']['status'] !== 'success' ) {
            $data['email'] = $responses['email'];
        }

        // Mail attachment response - is always an error
        if ( isset($responses['email']) && isset($responses['email']['attachment']) ) {
            $data['attachment'] = $responses['attachment'];
        }

        // Mailchimp response - Integer is also an error
        if ( isset($responses['mailchimp']) ) {

            // If Integer, is an error from mailchimp API so we pass their response
            if( is_int($responses['mailchimp']['status'])){
                $data['mailchimp'] = [
                    'status' => 'error',
                    /* translators: 1: Mailchimp HTTP status code, 2: Mailchimp status response */
                    'message' => sprintf( esc_html__('Mailchimp HTTP "%1$s," - "%2$s,."', 'uicore-elements'), $responses['mailchimp']['status'], $responses['mailchimp']['detail'])
                ];

            // If error is from our validation
            } else if ( $responses['mailchimp']['status'] === 'error' ) {
                $data['mailchimp'] = [
                    'status' => $responses['mailchimp']['status'],
                    'message' => $responses['mailchimp']['message']
                ];
            }
        }

        // Submit Actions response - is always an error
        if ( isset($responses['submit']) ) {
            $data['submit'] = $responses['submit'];
        }

        // Main response
        $status = $this->all_submissions_succedded($responses) ? 'success' : 'error';
        $data['message'] = $this->get_response_message($status);

        // Redirect response (should work only if all previous submitions hasn't failed)
        if ( isset($responses['redirect']) && $status === 'success' ) {
            $data['redirect'] = $responses['redirect'];
        }

        // The only successfull response that should be sent is 'main' and 'redirect'
        return [
            'status' => $status,
            'data' => $data
        ];
    }

}