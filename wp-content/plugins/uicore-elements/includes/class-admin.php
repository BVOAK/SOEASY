<?php

namespace UiCoreElements;

/**
 * Admin Pages Handler
 */
class Admin
{
    /**
     * Constructor function to initialize hooks
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.0
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_init', [$this, 'init_hooks']);
    }

    /**
     * Add admin menu page
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.5
     */
    public function admin_menu()
    {
        // Settings page (only required if uicore framework is not active)
        // if (!\class_exists('\UiCore\Helper')) {
        $hook = add_submenu_page(
            'options-general.php',
            'UiCore Elements',
            'UiCore Elements',
            'manage_options',
            'uicore-elements',
            [$this, 'plugin_page']
        );

        // }

        // Connect handle
        // add_submenu_page(
        //     null,
        //     'UiCore Connect',
        //     'UiCore Connect',
        //     'manage_options',
        //     'uicore_connect_free',
        //     [$this, 'connect_page_callback']
        // );
    }

    /**
     * Initialize hooks for settings fields and sections
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.5
     */
    public function init_hooks()
    {
        register_setting('uicore_elements_recaptcha', 'uicore_elements_recaptcha_site_key', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('uicore_elements_recaptcha', 'uicore_elements_recaptcha_secret_key', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('uicore_elements_mailchimp', 'uicore_elements_mailchimp_secret_key', ['sanitize_callback' => 'sanitize_text_field']);

        add_settings_section('uicore_elements_recaptcha_section', 'reCAPTCHA Keys', [$this, 'recaptcha_section'], 'uicore_elements_recaptcha');
        add_settings_section('uicore_elements_mailchimp_section', 'Mailchimp Key', [$this, 'mailchimp_section'], 'uicore_elements_mailchimp');

        add_settings_field('uicore_elements_recaptcha_site_key', 'Site Key', [$this, 'site_key'], 'uicore_elements_recaptcha', 'uicore_elements_recaptcha_section');
        add_settings_field('uicore_elements_recaptcha_secret_key', 'Secret Key', [$this, 'secret_key'], 'uicore_elements_recaptcha', 'uicore_elements_recaptcha_section');
        add_settings_field('uicore_elements_mailchimp_secret_key', 'API Key', [$this, 'mailchimp_key'], 'uicore_elements_mailchimp', 'uicore_elements_mailchimp_section');
    }

    /**
     * Render plugin page
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.5
     */
    public function plugin_page()
    {
        // check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }


        // show error/update messages
        settings_errors('uicoreelements_messages');

        // display plugin page
?>
        <div class="wrap">
            <h1>UiCore Elements Settings</h1>

            <form method="post" action="options.php" style="margin-top:40px">
                <?php
                settings_fields('uicore_elements_recaptcha');
                do_settings_sections('uicore_elements_recaptcha');
                submit_button();
                ?>
            </form>

            <form method="post" action="options.php" style="margin-top:40px">
                <?php
                settings_fields('uicore_elements_mailchimp');
                do_settings_sections('uicore_elements_mailchimp');
                submit_button();
                ?>
            </form>

        </div>
<?php
    }

    /**
     * Render reCAPTCHA section description
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.5
     */
    public function recaptcha_section()
    {
        echo '<p class="description">Go to your Google <a href="https://www.google.com/recaptcha/admin/create" target="_blank">reCAPTCHA</a>, choose between V2 or V3 versions and create your API keys</p>';
    }
    /**
     * Render Mailchimp section description
     *
     * @return void
     * @author Lucas Marini Falbo <lucas@uicore.co>
     * @since 1.0.7
     */
    public function mailchimp_section()
    {
        echo "<p class='description'>If you don't have one yet, go to your <a href='https://admin.mailchimp.com/account/api/' target='_blank'>Mailchimp Dashboard</a> and create a new API key</p>";
    }

    /**
     * Render reCAPTCHA Site Key field
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.5
     */
    public function site_key()
    {
        $site_key = get_option('uicore_elements_recaptcha_site_key');
        echo '<input type="text" name="uicore_elements_recaptcha_site_key" value="' . esc_attr($site_key) . '" class="regular-text" />';
    }

    /**
     * Render reCAPTCHA Secret Key field
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.5
     */
    public function secret_key()
    {
        $secret_key = get_option('uicore_elements_recaptcha_secret_key');
        echo '<input type="text" name="uicore_elements_recaptcha_secret_key" value="' . esc_attr($secret_key) . '" class="regular-text" />';
    }

    /**
     * Render Mailchimp API Key
     *
     * @return void
     * @author Lucas Marini Falbo <lucas@uicore.co>
     * @since 1.0.5
     */
    public function mailchimp_key()
    {
        $secret_key = get_option('uicore_elements_mailchimp_secret_key');
        echo '<input type="text" name="uicore_elements_mailchimp_secret_key" value="' . esc_attr($secret_key) . '" class="regular-text" />';
    }
}
