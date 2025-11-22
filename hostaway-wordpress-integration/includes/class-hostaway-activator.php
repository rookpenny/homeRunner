<?php
/**
 * Fired during plugin activation.
 */
class Hostaway_Activator {

    /**
     * Activate the plugin.
     */
    public static function activate() {
        // Register custom post type to flush rewrite rules
        require_once HOSTAWAY_INTEGRATION_PLUGIN_DIR . 'includes/class-hostaway-post-type.php';
        $post_type = new Hostaway_Post_Type();
        $post_type->register_post_type();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Set default options
        add_option( 'hostaway_account_id', '' );
        add_option( 'hostaway_secret_key', '' );
        add_option( 'hostaway_sync_enabled', 'no' );
        add_option( 'hostaway_auto_sync_interval', 'daily' );
        add_option( 'hostaway_last_sync', '' );

        // Schedule cron job for auto-sync if enabled
        if ( ! wp_next_scheduled( 'hostaway_auto_sync' ) ) {
            wp_schedule_event( time(), 'daily', 'hostaway_auto_sync' );
        }
    }
}
