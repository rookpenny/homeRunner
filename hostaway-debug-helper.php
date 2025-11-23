<?php
/**
 * Hostaway Debug Helper
 *
 * Add this to your theme's functions.php temporarily to debug the setup
 */

// Add admin notice to show detected post type
add_action('admin_notices', function() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Only show on Hostaway pages
    if (!isset($_GET['post_type']) || !in_array($_GET['post_type'], ['hostaway_listing', 'listing', 'listings', 'property'])) {
        return;
    }

    echo '<div class="notice notice-info"><p>';
    echo '<strong>Hostaway Debug Info:</strong><br>';

    // Show what post type the config is using
    if (class_exists('Hostaway_Config')) {
        $detected_type = Hostaway_Config::get_post_type();
        echo 'Detected Post Type: <code>' . $detected_type . '</code><br>';
        echo 'Should Create Own Type: ' . (Hostaway_Config::should_create_post_type() ? 'Yes' : 'No') . '<br>';
    }

    // Show all registered post types that might be listings
    echo '<br><strong>Registered Post Types (potential matches):</strong><br>';
    $post_types = get_post_types(array('public' => true), 'objects');
    foreach ($post_types as $slug => $post_type) {
        if (strpos(strtolower($post_type->labels->name), 'listing') !== false ||
            strpos(strtolower($post_type->labels->name), 'property') !== false ||
            in_array($slug, ['listing', 'listings', 'property', 'properties', 'estate_property'])) {
            echo '- <code>' . $slug . '</code> (' . $post_type->labels->name . ')<br>';
        }
    }

    // Show Hostaway synced items
    echo '<br><strong>Hostaway Synced Items:</strong><br>';
    $synced_count = 0;

    // Check multiple post types
    foreach (['hostaway_listing', 'listing', 'listings', 'property', 'properties'] as $type) {
        if (post_type_exists($type)) {
            $count = wp_count_posts($type);
            if ($count->publish > 0 || $count->draft > 0) {
                echo '- Post Type <code>' . $type . '</code>: ' . $count->publish . ' published, ' . $count->draft . ' drafts<br>';
                $synced_count += $count->publish + $count->draft;
            }
        }
    }

    if ($synced_count == 0) {
        echo '<em style="color:red;">No synced items found in any post type!</em><br>';
    }

    echo '</p></div>';
});

// Add a menu item to show debug info
add_action('admin_menu', function() {
    add_submenu_page(
        'edit.php?post_type=listing',
        'Hostaway Debug',
        'Hostaway Debug',
        'manage_options',
        'hostaway-debug',
        'hostaway_debug_page'
    );
});

function hostaway_debug_page() {
    ?>
    <div class="wrap">
        <h1>Hostaway Integration Debug</h1>

        <h2>Current Configuration</h2>
        <table class="widefat">
            <tr>
                <th>Setting</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>Use Existing Post Type</td>
                <td><code><?php echo get_option('hostaway_use_existing_post_type', 'yes'); ?></code></td>
            </tr>
            <tr>
                <td>Detected Post Type</td>
                <td><code><?php echo class_exists('Hostaway_Config') ? Hostaway_Config::get_post_type() : 'Config class not loaded'; ?></code></td>
            </tr>
            <tr>
                <td>Should Create Own Type</td>
                <td><?php echo class_exists('Hostaway_Config') ? (Hostaway_Config::should_create_post_type() ? 'Yes' : 'No') : 'Unknown'; ?></td>
            </tr>
        </table>

        <h2>Find Your Listings Post Type</h2>
        <p>Click on any existing listing in your "Listings" section, then look at the URL. It will show <code>post_type=XXXXX</code></p>
        <p><strong>Your post type slug is:</strong> <input type="text" id="detected-slug" size="30" placeholder="Look at URL: post_type=?" /></p>

        <h2>Force Use Specific Post Type</h2>
        <form method="post" action="">
            <?php wp_nonce_field('hostaway_force_post_type'); ?>
            <p>
                <label>Post Type Slug:</label>
                <input type="text" name="post_type_slug" value="listing" />
                <button type="submit" name="force_post_type" class="button button-primary">Set Post Type</button>
            </p>
        </form>

        <?php
        if (isset($_POST['force_post_type']) && wp_verify_nonce($_POST['_wpnonce'], 'hostaway_force_post_type')) {
            $slug = sanitize_text_field($_POST['post_type_slug']);
            update_option('hostaway_force_post_type', $slug);
            echo '<div class="notice notice-success"><p>Post type set to: <code>' . $slug . '</code>. Please re-sync your listings!</p></div>';
        }
        ?>

        <h2>All Registered Post Types</h2>
        <table class="widefat">
            <tr>
                <th>Slug</th>
                <th>Name</th>
                <th>Count</th>
            </tr>
            <?php
            $post_types = get_post_types(array('public' => true), 'objects');
            foreach ($post_types as $slug => $post_type) {
                $count = wp_count_posts($slug);
                echo '<tr>';
                echo '<td><code>' . $slug . '</code></td>';
                echo '<td>' . $post_type->labels->name . '</td>';
                echo '<td>' . ($count->publish ?? 0) . '</td>';
                echo '</tr>';
            }
            ?>
        </table>
    </div>
    <?php
}
