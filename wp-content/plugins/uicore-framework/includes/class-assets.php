<?php
namespace UiCore;
defined('ABSPATH') || exit();

/**
 * Register Scripts and Styles Class
 *
 * @author Andrei Voica <andrei@uicore.co
 * @since 1.0.0
 */
class Assets
{
    /**
     * Add actions
     *
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function __construct()
    {
        if (is_admin()) {
            add_action('admin_enqueue_scripts', [$this, 'register_styles'], 5);
            add_action('admin_enqueue_scripts', [$this, 'register_scripts'], 9);
        } else {
            add_action('wp_enqueue_scripts', [$this, 'register_styles'], 5);
            add_action('wp_enqueue_scripts', [$this, 'register_scripts'], 9);
        }
    }

    /**
     * Register used scripts
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function register_scripts()
    {
        $scripts = $this->get_scripts();
        foreach ($scripts as $handle => $script) {
            $deps = isset($script['deps']) ? $script['deps'] : false;
            $in_footer = isset($script['in_footer']) ? $script['in_footer'] : false;
            $version = isset($script['version']) ? $script['version'] : UICORE_VERSION;

            wp_register_script($handle, $script['src'], $deps, $version, $in_footer);
        }
    }

    /**
     * Register used stylesheets
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function register_styles()
    {
        $styles = $this->get_styles();
        foreach ($styles as $handle => $style) {
            $deps = isset($style['deps']) ? $style['deps'] : false;
            $version =  isset($style['version']) ? $style['version'] : UICORE_VERSION;
            wp_register_style($handle, $style['src'], $deps, $version);
        }
    }

    /**
     * Define Script Array
     *
     * @return array $scripts
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function get_scripts()
    {
        // $upload_dir = wp_upload_dir();
        $version = Helper::get_option('settings_version', UICORE_VERSION);

        $prefix =  (( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) || defined('UICORE_LOCAL')) ? '' : '.min';
        $scripts = [
            'uicore-vendor' => [
                'src' => UICORE_ASSETS . '/js/vendor' . $prefix . '.js',
                'version' => filemtime(UICORE_PATH . '/assets/js/vendor' . $prefix . '.js'),
                'in_footer' => true,
            ],
            'uicore-frontend' => [
                'src' => UICORE_ASSETS . '/js/frontend' . $prefix . '.js',
                'deps' => ['jquery', 'uicore-manifest'],
                'version' => filemtime(UICORE_PATH . '/assets/js/frontend' . $prefix . '.js'),
                'in_footer' => true,
            ],
            'uicore-manifest' => [
                'src' => UICORE_ASSETS . '/js/manifest' . $prefix . '.js',
                'version' => filemtime(UICORE_PATH . '/assets/js/manifest' . $prefix . '.js'),
                'in_footer' => true,
            ],
            'uicore-admin' => [
                'src' => UICORE_ASSETS . '/js/admin' . $prefix . '.js',
                'deps' => ['uicore-manifest', 'uicore-vendor'],
                'version' => filemtime(UICORE_PATH . '/assets/js/admin' . $prefix . '.js'),
                'in_footer' => true,
            ],
            'uicore-grid' => [
                'src' => UICORE_ASSETS . '/js/uicore-grid.js',
                'deps' => ['jquery'],
                'version' => filemtime(UICORE_PATH . '/assets/js/uicore-grid.js'),
                'in_footer' => true,
            ],
            'ui-e-odometer' => [
                'src' => UICORE_ASSETS . '/js/lib/odometer.js',
                'deps' => ['ui-e-counter'],
                'version' => UICORE_VERSION,
                'in_footer' => true,
            ],
            'uicore_global' => [
                'src' => self::get_global("uicore-global.js"),
                'deps' => ['jquery'],
                'version' => $version,
                'in_footer' => true,
            ],
            'uicore-admin-menu' => [
                'src' => UICORE_ASSETS . '/js/admin-menu' . $prefix . '.js',
                'deps' => ['jquery','uicore-manifest', 'uicore-vendor'],
                'version' => UICORE_VERSION,
                'in_footer' => true,
            ],
            'uicore-ai' => [
                'src' => UICORE_ASSETS . '/js/ai' . $prefix . '.js',
                'deps' => ['uicore-manifest', 'uicore-vendor'],
                'version' => filemtime(UICORE_PATH . '/assets/js/ai' . $prefix . '.js'),
                'in_footer' => true,
            ],
            'uicore-swatches' => [
                'src' => UICORE_ASSETS . '/js/woocommerce/swatches' . $prefix . '.js',
                'deps' => ['jquery'],
                'version' => UICORE_VERSION,
                'in_footer' => true,
            ],
            'uicore-product-tabs' => [
                'src' => UICORE_ASSETS . '/js/woocommerce/product-tabs' . $prefix . '.js',
                'deps' => ['jquery', 'uicore-manifest'],
                'version' => UICORE_VERSION,
                'in_footer' => true,
            ],
            'uicore-admin-swatches' => [
                'src' => UICORE_ASSETS . '/js/admin-swatches' . $prefix . '.js',
                'deps' => ['jquery','uicore-manifest', 'uicore-vendor'],
                'version' => UICORE_VERSION,
                'in_footer' => true,
            ],
        ];

        return $scripts;
    }

    /**
     * Define Style Array
     *
     * @return array $styles
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function get_styles()
    {
        $upload_dir = wp_upload_dir();
        $version = Helper::get_option('settings_version', UICORE_VERSION);

        $theme_styles = [
            'uicore-frontend' => [
                'src' => UICORE_ASSETS . '/css/frontend.css',
            ],
            'uicore-admin' => [
                'src' => UICORE_ASSETS . '/css/admin.css',
            ],
            'uicore-admin-menu' => [
                'src' => UICORE_ASSETS . '/css/admin-menu.css',
            ],
            'uicore-admin-icons' => [
                'src' => UICORE_ASSETS . '/fonts/admin-icons.css',
            ],
            'uicore-admin-font' => [
                'src' => 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&display=swap',
            ],
            'uicore-icons' => [
                'src' => UICORE_ASSETS . '/fonts/uicore-icons.css',
            ],
            'uicore_global' => [
                'src' => self::get_global("uicore-global.css"),
                'version' => $version
            ],
            'uicore-blog-st' => [
                'src' => self::get_global("uicore-blog.css"),
                'version' => $version
            ],
            'uicore-portfolio-st' => [
                'src' => self::get_global("uicore-portfolio.css"),
                'version' => $version
            ],
            'uicore_rtl' => [
                'src' => UICORE_ASSETS . '/css/frontend-rtl.css'
            ],
            'uicore-ai' => [
                'src' => UICORE_ASSETS . '/css/ai.css'
            ],
            'uicore-admin-swatches' => [
                'src' => UICORE_ASSETS . '/css/admin-swatches.css'
            ],
        ];

        $woo_styles = [
            'uicore-product-tabs-horizontal' => [
                'src' => UICORE_ASSETS . '/css/woocommerce/tabs-horizontal.css',
            ],
            'uicore-product-tabs-vertical' => [
                'src' => UICORE_ASSETS . '/css/woocommerce/tabs-vertical.css',
            ],
            'uicore-product-tabs-sections' => [
                'src' => UICORE_ASSETS . '/css/woocommerce/tabs-sections.css',
            ],
            'uicore-product-tabs-accordion' => [
                'src' => UICORE_ASSETS . '/css/woocommerce/tabs-accordion.css',
            ],
            'uicore-product-gallery-thumbs' => [
                'src' => UICORE_ASSETS . '/css/woocommerce/gallery-thumbs.css',
            ],
            'uicore-product-gallery-columns' => [
                'src' => UICORE_ASSETS . '/css/woocommerce/gallery-columns.css',
            ],
        ];

        $styles = array_merge($theme_styles, $woo_styles);
        return $styles;
    }

    static function get_global($name,$type='url')
    {
        if($type === 'url'){
            $upload_dir = wp_upload_dir();
            $value = set_url_scheme($upload_dir['baseurl']."/".$name);
        }
        return $value;
    }
}
