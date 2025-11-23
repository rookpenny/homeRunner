<?php
/**
 * Hostaway Post Type Migration Tool
 *
 * Add this entire file to: wp-content/plugins/hostaway-migration.php
 * Then activate it in WordPress admin
 */

/*
Plugin Name: Hostaway Migration Tool
Description: One-click tool to move Hostaway listings to your existing listing post type
Version: 1.0
*/

// Add admin menu
add_action('admin_menu', function() {
    add_menu_page(
        'Hostaway Migration',
        'Hostaway Migration',
        'manage_options',
        'hostaway-migration',
        'hostaway_migration_page',
        'dashicons-upload',
        100
    );
});

function hostaway_migration_page() {
    // Handle migration
    if (isset($_POST['migrate_listings']) && wp_verify_nonce($_POST['_wpnonce'], 'migrate_hostaway')) {
        echo '<div class="wrap">';
        echo '<h1>Migration in Progress...</h1>';

        // Set the forced post type
        update_option('hostaway_force_post_type', 'listing');
        echo '<p>✅ Set future syncs to use "listing" post type</p>';

        // Get all hostaway_listing posts
        $posts = get_posts(array(
            'post_type' => 'hostaway_listing',
            'numberposts' => -1,
            'post_status' => 'any'
        ));

        echo '<p>Found ' . count($posts) . ' Hostaway listings to migrate...</p>';

        $success = 0;
        $failed = 0;

        foreach ($posts as $post) {
            $result = wp_update_post(array(
                'ID' => $post->ID,
                'post_type' => 'listing'
            ));

            if (!is_wp_error($result)) {
                $success++;
                echo '<p style="color:green;">✅ Migrated: ' . $post->post_title . '</p>';
            } else {
                $failed++;
                echo '<p style="color:red;">❌ Failed: ' . $post->post_title . ' - ' . $result->get_error_message() . '</p>';
            }
        }

        update_option('hostaway_migrated_to_listing', 'yes');

        echo '<hr>';
        echo '<h2>Migration Complete!</h2>';
        echo '<p><strong>Successfully migrated:</strong> ' . $success . ' listings</p>';
        echo '<p><strong>Failed:</strong> ' . $failed . ' listings</p>';
        echo '<p><a href="edit.php?post_type=listing" class="button button-primary">View Your Listings →</a></p>';
        echo '<p><em>You can now deactivate this plugin.</em></p>';
        echo '</div>';
        return;
    }

    // Show migration form
    ?>
    <div class="wrap">
        <h1>Hostaway Listings Migration</h1>

        <div class="card" style="max-width: 800px;">
            <h2>Current Status</h2>

            <?php
            $hostaway_count = wp_count_posts('hostaway_listing');
            $listing_count = wp_count_posts('listing');
            ?>

            <table class="widefat">
                <tr>
                    <th>Post Type</th>
                    <th>Count</th>
                    <th>Status</th>
                </tr>
                <tr>
                    <td><code>hostaway_listing</code> (Wrong location)</td>
                    <td><strong><?php echo $hostaway_count->publish ?? 0; ?></strong> published</td>
                    <td style="color:red;">❌ Needs migration</td>
                </tr>
                <tr>
                    <td><code>listing</code> (Your existing listings)</td>
                    <td><strong><?php echo $listing_count->publish ?? 0; ?></strong> published</td>
                    <td style="color:green;">✅ Correct location</td>
                </tr>
            </table>

            <hr>

            <h2>What This Will Do:</h2>
            <ol>
                <li>Move all <code>hostaway_listing</code> posts to <code>listing</code> post type</li>
                <li>Keep all data, images, and metadata intact</li>
                <li>Configure plugin to sync future updates to <code>listing</code></li>
                <li>Make Hostaway listings appear in your existing Listings section</li>
            </ol>

            <?php if (($hostaway_count->publish ?? 0) > 0): ?>
                <form method="post">
                    <?php wp_nonce_field('migrate_hostaway'); ?>
                    <p>
                        <button type="submit" name="migrate_listings" class="button button-primary button-hero">
                            🚀 Migrate <?php echo $hostaway_count->publish; ?> Listings Now
                        </button>
                    </p>
                </form>
            <?php else: ?>
                <p style="color:orange;">⚠️ No Hostaway listings found to migrate.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
