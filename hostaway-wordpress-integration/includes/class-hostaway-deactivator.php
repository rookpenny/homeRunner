<?php
/**
 * Fired during plugin deactivation.
 */
class Hostaway_Deactivator {

    /**
     * Deactivate the plugin.
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();

        // Clear scheduled cron jobs
        $timestamp = wp_next_scheduled( 'hostaway_auto_sync' );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'hostaway_auto_sync' );
        }

        // Clear transients
        delete_transient( 'hostaway_access_token' );
    }
}
