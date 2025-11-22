<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 */
class Hostaway_Integration {

    /**
     * The loader that's responsible for maintaining and registering all hooks.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     */
    public function __construct() {
        $this->version = HOSTAWAY_INTEGRATION_VERSION;
        $this->plugin_name = 'hostaway-integration';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        require_once HOSTAWAY_INTEGRATION_PLUGIN_DIR . 'includes/class-hostaway-loader.php';
        require_once HOSTAWAY_INTEGRATION_PLUGIN_DIR . 'includes/class-hostaway-config.php';
        require_once HOSTAWAY_INTEGRATION_PLUGIN_DIR . 'includes/class-hostaway-api-client.php';
        require_once HOSTAWAY_INTEGRATION_PLUGIN_DIR . 'includes/class-hostaway-post-type.php';
        require_once HOSTAWAY_INTEGRATION_PLUGIN_DIR . 'includes/class-hostaway-widget.php';
        require_once HOSTAWAY_INTEGRATION_PLUGIN_DIR . 'includes/class-hostaway-blocks.php';
        require_once HOSTAWAY_INTEGRATION_PLUGIN_DIR . 'admin/class-hostaway-admin.php';
        require_once HOSTAWAY_INTEGRATION_PLUGIN_DIR . 'public/class-hostaway-public.php';

        $this->loader = new Hostaway_Loader();
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     */
    private function define_admin_hooks() {
        $plugin_admin = new Hostaway_Admin( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        // AJAX handlers
        $this->loader->add_action( 'wp_ajax_hostaway_sync_listings', $plugin_admin, 'sync_listings' );
        $this->loader->add_action( 'wp_ajax_hostaway_test_connection', $plugin_admin, 'test_connection' );
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     */
    private function define_public_hooks() {
        $plugin_public = new Hostaway_Public( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );

        // Register custom post type
        $post_type = new Hostaway_Post_Type();
        $this->loader->add_action( 'init', $post_type, 'register_post_type' );
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}
