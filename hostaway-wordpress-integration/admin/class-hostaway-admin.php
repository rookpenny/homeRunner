<?php
/**
 * The admin-specific functionality of the plugin.
 */
class Hostaway_Admin {

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            HOSTAWAY_INTEGRATION_PLUGIN_URL . 'admin/css/hostaway-admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            HOSTAWAY_INTEGRATION_PLUGIN_URL . 'admin/js/hostaway-admin.js',
            array( 'jquery' ),
            $this->version,
            false
        );

        wp_localize_script( $this->plugin_name, 'hostawayAdmin', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'hostaway_admin_nonce' ),
        ) );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu is already created by the custom post type
        // Add settings submenu
        add_submenu_page(
            'edit.php?post_type=hostaway_listing',
            'Hostaway Settings',
            'Settings',
            'manage_options',
            'hostaway-settings',
            array( $this, 'display_settings_page' )
        );

        // Add sync submenu
        add_submenu_page(
            'edit.php?post_type=hostaway_listing',
            'Sync Listings',
            'Sync Listings',
            'manage_options',
            'hostaway-sync',
            array( $this, 'display_sync_page' )
        );

        // Add migration submenu
        add_submenu_page(
            'edit.php?post_type=hostaway_listing',
            'Move to Listings',
            'Move to Listings',
            'manage_options',
            'hostaway-migrate',
            array( $this, 'display_migrate_page' )
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting( 'hostaway_settings', 'hostaway_account_id' );
        register_setting( 'hostaway_settings', 'hostaway_secret_key' );
        register_setting( 'hostaway_settings', 'hostaway_auto_sync_interval' );
        register_setting( 'hostaway_settings', 'hostaway_sync_enabled' );

        add_settings_section(
            'hostaway_api_settings',
            'API Configuration',
            array( $this, 'api_settings_section_callback' ),
            'hostaway-settings'
        );

        add_settings_field(
            'hostaway_account_id',
            'Account ID',
            array( $this, 'account_id_field_callback' ),
            'hostaway-settings',
            'hostaway_api_settings'
        );

        add_settings_field(
            'hostaway_secret_key',
            'Secret Key',
            array( $this, 'secret_key_field_callback' ),
            'hostaway-settings',
            'hostaway_api_settings'
        );

        add_settings_field(
            'hostaway_auto_sync_interval',
            'Auto Sync Interval',
            array( $this, 'auto_sync_interval_field_callback' ),
            'hostaway-settings',
            'hostaway_api_settings'
        );

        add_settings_field(
            'hostaway_sync_enabled',
            'Enable Auto Sync',
            array( $this, 'sync_enabled_field_callback' ),
            'hostaway-settings',
            'hostaway_api_settings'
        );
    }

    public function api_settings_section_callback() {
        echo '<p>Enter your Hostaway API credentials. You can find these in your Hostaway Dashboard under Settings > Hostaway API.</p>';
    }

    public function account_id_field_callback() {
        $value = get_option( 'hostaway_account_id' );
        echo '<input type="text" name="hostaway_account_id" value="' . esc_attr( $value ) . '" class="regular-text" />';
    }

    public function secret_key_field_callback() {
        $value = get_option( 'hostaway_secret_key' );
        echo '<input type="password" name="hostaway_secret_key" value="' . esc_attr( $value ) . '" class="regular-text" />';
    }

    public function auto_sync_interval_field_callback() {
        $value = get_option( 'hostaway_auto_sync_interval', 'daily' );
        ?>
        <select name="hostaway_auto_sync_interval">
            <option value="hourly" <?php selected( $value, 'hourly' ); ?>>Hourly</option>
            <option value="twicedaily" <?php selected( $value, 'twicedaily' ); ?>>Twice Daily</option>
            <option value="daily" <?php selected( $value, 'daily' ); ?>>Daily</option>
            <option value="weekly" <?php selected( $value, 'weekly' ); ?>>Weekly</option>
        </select>
        <?php
    }

    public function sync_enabled_field_callback() {
        $value = get_option( 'hostaway_sync_enabled', 'no' );
        echo '<input type="checkbox" name="hostaway_sync_enabled" value="yes" ' . checked( $value, 'yes', false ) . ' />';
        echo '<label>Automatically sync listings on schedule</label>';
    }

    /**
     * Display settings page
     */
    public function display_settings_page() {
        ?>
        <div class="wrap">
            <h1>Hostaway Integration Settings</h1>

            <form method="post" action="options.php">
                <?php
                settings_fields( 'hostaway_settings' );
                do_settings_sections( 'hostaway-settings' );
                submit_button();
                ?>
            </form>

            <hr>

            <h2>Test Connection</h2>
            <p>Click the button below to test your API connection.</p>
            <button type="button" id="test-connection" class="button button-secondary">Test Connection</button>
            <div id="connection-result"></div>
        </div>
        <?php
    }

    /**
     * Display sync page
     */
    public function display_sync_page() {
        $last_sync = get_option( 'hostaway_last_sync' );
        ?>
        <div class="wrap">
            <h1>Sync Hostaway Listings</h1>

            <?php if ( $last_sync ) : ?>
                <p><strong>Last Sync:</strong> <?php echo esc_html( date( 'F j, Y g:i a', strtotime( $last_sync ) ) ); ?></p>
            <?php else : ?>
                <p><strong>Last Sync:</strong> Never</p>
            <?php endif; ?>

            <p>Click the button below to manually sync all listings from Hostaway.</p>
            <p><em>Note: This process may take a few minutes depending on the number of listings.</em></p>

            <button type="button" id="sync-listings" class="button button-primary">Sync All Listings</button>

            <div id="sync-progress" style="display:none; margin-top: 20px;">
                <div class="sync-spinner">
                    <span class="spinner is-active"></span>
                    <span id="sync-status">Syncing listings...</span>
                </div>
            </div>

            <div id="sync-result" style="margin-top: 20px;"></div>
        </div>
        <?php
    }

    /**
     * AJAX handler for testing connection
     */
    public function test_connection() {
        check_ajax_referer( 'hostaway_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
        }

        $api_client = new Hostaway_API_Client();
        $result = $api_client->test_connection();

        if ( $result ) {
            wp_send_json_success( array( 'message' => 'Connection successful!' ) );
        } else {
            wp_send_json_error( array( 'message' => 'Connection failed. Please check your credentials.' ) );
        }
    }

    /**
     * AJAX handler for syncing listings
     */
    public function sync_listings() {
        check_ajax_referer( 'hostaway_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
        }

        // Increase execution time for large imports
        set_time_limit( 300 );

        $api_client = new Hostaway_API_Client();
        $listings = $api_client->get_all_listings();

        if ( is_wp_error( $listings ) ) {
            wp_send_json_error( array(
                'message' => 'Failed to fetch listings: ' . $listings->get_error_message()
            ) );
        }

        $synced_count = 0;
        $errors = array();

        foreach ( $listings as $listing ) {
            $result = Hostaway_Post_Type::save_listing_from_api( $listing );

            if ( is_wp_error( $result ) ) {
                $errors[] = 'Failed to save listing ' . $listing['id'] . ': ' . $result->get_error_message();
            } else {
                $synced_count++;
            }
        }

        // Update last sync time
        update_option( 'hostaway_last_sync', current_time( 'mysql' ) );

        $message = sprintf(
            'Successfully synced %d listing(s).',
            $synced_count
        );

        if ( ! empty( $errors ) ) {
            $message .= ' ' . count( $errors ) . ' error(s) occurred.';
        }

        wp_send_json_success( array(
            'message' => $message,
            'synced_count' => $synced_count,
            'errors' => $errors,
        ) );
    }

    /**
     * Display migration page
     */
    public function display_migrate_page() {
        // Handle migration POST request
        if ( isset( $_POST['migrate_now'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'migrate_hostaway' ) ) {
            $this->do_migration();
            return;
        }

        // Show migration form
        $hostaway_count = wp_count_posts( 'hostaway_listing' );
        $listing_count = wp_count_posts( 'listing' );
        ?>
        <div class="wrap">
            <h1>Move Hostaway Listings to Your Listings Section</h1>

            <div class="notice notice-info">
                <p><strong>What this does:</strong> Moves all Hostaway listings from the separate "Hostaway" section into your main "Listings" section where they belong.</p>
            </div>

            <table class="widefat" style="max-width: 600px; margin: 20px 0;">
                <thead>
                    <tr>
                        <th>Location</th>
                        <th>Count</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Hostaway Section</strong> (separate menu)</td>
                        <td><?php echo $hostaway_count->publish ?? 0; ?> listings</td>
                        <td><span style="color: red;">❌ Wrong location</span></td>
                    </tr>
                    <tr>
                        <td><strong>Listings Section</strong> (your main listings)</td>
                        <td><?php echo $listing_count->publish ?? 0; ?> listings</td>
                        <td><span style="color: green;">✅ Correct location</span></td>
                    </tr>
                </tbody>
            </table>

            <?php if ( ( $hostaway_count->publish ?? 0 ) > 0 ) : ?>
                <form method="post" onsubmit="return confirm('This will move <?php echo $hostaway_count->publish; ?> listings. Continue?');">
                    <?php wp_nonce_field( 'migrate_hostaway' ); ?>
                    <p>
                        <button type="submit" name="migrate_now" class="button button-primary button-hero">
                            🚀 Move <?php echo $hostaway_count->publish; ?> Listings to "Listings" Section
                        </button>
                    </p>
                </form>

                <p><em>This will:</em></p>
                <ul>
                    <li>✅ Move all Hostaway listings to your main Listings section</li>
                    <li>✅ Keep all data, images, and metadata intact</li>
                    <li>✅ Configure future syncs to go directly to Listings</li>
                    <li>✅ Make them appear in your theme's existing listing displays</li>
                </ul>
            <?php else : ?>
                <div class="notice notice-warning">
                    <p>No Hostaway listings found to migrate. They may already be in the correct location!</p>
                    <p><a href="edit.php?post_type=listing">Check your Listings section →</a></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Perform the actual migration
     */
    private function do_migration() {
        ?>
        <div class="wrap">
            <h1>Migration in Progress...</h1>

            <?php
            // Force future syncs to use 'listing'
            update_option( 'hostaway_force_post_type', 'listing' );
            echo '<p style="color: green;">✅ Configured future syncs to use "listing" post type</p>';

            // Get all hostaway_listing posts
            $posts = get_posts( array(
                'post_type'   => 'hostaway_listing',
                'numberposts' => -1,
                'post_status' => 'any',
            ) );

            echo '<p>Found ' . count( $posts ) . ' listings to migrate...</p>';
            echo '<div style="max-height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; margin: 20px 0;">';

            $success = 0;
            $failed = 0;

            foreach ( $posts as $post ) {
                $result = wp_update_post( array(
                    'ID'        => $post->ID,
                    'post_type' => 'listing',
                ) );

                if ( ! is_wp_error( $result ) && $result !== 0 ) {
                    $success++;
                    echo '<p style="color: green;">✅ Migrated: ' . esc_html( $post->post_title ) . '</p>';
                } else {
                    $failed++;
                    $error_msg = is_wp_error( $result ) ? $result->get_error_message() : 'Unknown error';
                    echo '<p style="color: red;">❌ Failed: ' . esc_html( $post->post_title ) . ' - ' . esc_html( $error_msg ) . '</p>';
                }
            }

            echo '</div>';

            update_option( 'hostaway_migrated_to_listing', 'yes' );

            echo '<hr>';
            echo '<h2 style="color: green;">✅ Migration Complete!</h2>';
            echo '<p><strong>Successfully migrated:</strong> ' . $success . ' listings</p>';
            if ( $failed > 0 ) {
                echo '<p style="color: red;"><strong>Failed:</strong> ' . $failed . ' listings</p>';
            }
            echo '<p style="font-size: 16px; margin: 30px 0;">';
            echo '<a href="edit.php?post_type=listing" class="button button-primary button-hero">View Your Listings →</a>';
            echo '</p>';
            echo '<p><em>All Hostaway listings are now in your main Listings section!</em></p>';
            ?>
        </div>
        <?php
    }
}
