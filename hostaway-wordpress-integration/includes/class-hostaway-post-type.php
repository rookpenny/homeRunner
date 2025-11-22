<?php
/**
 * Custom Post Type for Hostaway Listings
 */
class Hostaway_Post_Type {

    /**
     * Register the custom post type for listings
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => 'Hostaway Listings',
            'singular_name'         => 'Listing',
            'menu_name'             => 'Hostaway',
            'name_admin_bar'        => 'Listing',
            'add_new'               => 'Add New',
            'add_new_item'          => 'Add New Listing',
            'new_item'              => 'New Listing',
            'edit_item'             => 'Edit Listing',
            'view_item'             => 'View Listing',
            'all_items'             => 'All Listings',
            'search_items'          => 'Search Listings',
            'parent_item_colon'     => 'Parent Listings:',
            'not_found'             => 'No listings found.',
            'not_found_in_trash'    => 'No listings found in Trash.',
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'listing' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-admin-home',
            'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
            'show_in_rest'       => true,
        );

        register_post_type( 'hostaway_listing', $args );

        // Register custom taxonomies
        $this->register_taxonomies();
    }

    /**
     * Register custom taxonomies for listings
     */
    private function register_taxonomies() {
        // Property Type taxonomy
        register_taxonomy(
            'property_type',
            'hostaway_listing',
            array(
                'label'        => 'Property Type',
                'rewrite'      => array( 'slug' => 'property-type' ),
                'hierarchical' => true,
                'show_in_rest' => true,
            )
        );

        // Amenities taxonomy
        register_taxonomy(
            'amenity',
            'hostaway_listing',
            array(
                'label'        => 'Amenities',
                'rewrite'      => array( 'slug' => 'amenity' ),
                'hierarchical' => false,
                'show_in_rest' => true,
            )
        );

        // Location taxonomy
        register_taxonomy(
            'location',
            'hostaway_listing',
            array(
                'label'        => 'Location',
                'rewrite'      => array( 'slug' => 'location' ),
                'hierarchical' => true,
                'show_in_rest' => true,
            )
        );
    }

    /**
     * Save listing data from Hostaway API
     */
    public static function save_listing_from_api( $listing_data ) {
        // Check if listing already exists
        $existing_post = get_posts( array(
            'post_type'  => 'hostaway_listing',
            'meta_key'   => '_hostaway_id',
            'meta_value' => $listing_data['id'],
            'numberposts' => 1,
        ) );

        $post_data = array(
            'post_type'    => 'hostaway_listing',
            'post_title'   => sanitize_text_field( $listing_data['name'] ?? 'Untitled Listing' ),
            'post_content' => wp_kses_post( $listing_data['description'] ?? '' ),
            'post_excerpt' => wp_kses_post( $listing_data['publicDescription']['summary'] ?? '' ),
            'post_status'  => 'publish',
        );

        // Update existing post or create new one
        if ( ! empty( $existing_post ) ) {
            $post_data['ID'] = $existing_post[0]->ID;
            $post_id = wp_update_post( $post_data );
        } else {
            $post_id = wp_insert_post( $post_data );
        }

        if ( is_wp_error( $post_id ) ) {
            return $post_id;
        }

        // Save all listing metadata
        update_post_meta( $post_id, '_hostaway_id', $listing_data['id'] );
        update_post_meta( $post_id, '_hostaway_data', $listing_data );

        // Save specific fields for easy access
        if ( isset( $listing_data['bedrooms'] ) ) {
            update_post_meta( $post_id, '_bedrooms', intval( $listing_data['bedrooms'] ) );
        }
        if ( isset( $listing_data['bathrooms'] ) ) {
            update_post_meta( $post_id, '_bathrooms', floatval( $listing_data['bathrooms'] ) );
        }
        if ( isset( $listing_data['accommodates'] ) ) {
            update_post_meta( $post_id, '_accommodates', intval( $listing_data['accommodates'] ) );
        }
        if ( isset( $listing_data['address'] ) ) {
            update_post_meta( $post_id, '_address', sanitize_text_field( $listing_data['address'] ) );
        }
        if ( isset( $listing_data['city'] ) ) {
            update_post_meta( $post_id, '_city', sanitize_text_field( $listing_data['city'] ) );
        }
        if ( isset( $listing_data['country'] ) ) {
            update_post_meta( $post_id, '_country', sanitize_text_field( $listing_data['country'] ) );
        }
        if ( isset( $listing_data['zipcode'] ) ) {
            update_post_meta( $post_id, '_zipcode', sanitize_text_field( $listing_data['zipcode'] ) );
        }
        if ( isset( $listing_data['latitude'] ) ) {
            update_post_meta( $post_id, '_latitude', floatval( $listing_data['latitude'] ) );
        }
        if ( isset( $listing_data['longitude'] ) ) {
            update_post_meta( $post_id, '_longitude', floatval( $listing_data['longitude'] ) );
        }
        if ( isset( $listing_data['baseDailyRate'] ) ) {
            update_post_meta( $post_id, '_price', floatval( $listing_data['baseDailyRate'] ) );
        }
        if ( isset( $listing_data['currencyCode'] ) ) {
            update_post_meta( $post_id, '_currency', sanitize_text_field( $listing_data['currencyCode'] ) );
        }

        // Set property type
        if ( isset( $listing_data['propertyTypeName'] ) ) {
            wp_set_object_terms( $post_id, $listing_data['propertyTypeName'], 'property_type' );
        }

        // Set location
        if ( isset( $listing_data['city'] ) ) {
            wp_set_object_terms( $post_id, $listing_data['city'], 'location' );
        }

        // Handle thumbnail/featured image
        if ( isset( $listing_data['thumbnailUrl'] ) && ! empty( $listing_data['thumbnailUrl'] ) ) {
            self::set_featured_image_from_url( $post_id, $listing_data['thumbnailUrl'] );
        }

        // Save image gallery
        if ( isset( $listing_data['images'] ) && is_array( $listing_data['images'] ) ) {
            $image_urls = array();
            foreach ( $listing_data['images'] as $image ) {
                if ( isset( $image['url'] ) ) {
                    $image_urls[] = $image['url'];
                }
            }
            update_post_meta( $post_id, '_image_gallery', $image_urls );
        }

        return $post_id;
    }

    /**
     * Set featured image from URL
     */
    private static function set_featured_image_from_url( $post_id, $image_url ) {
        // Check if thumbnail already exists
        if ( has_post_thumbnail( $post_id ) ) {
            return;
        }

        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        // Download image to temp location
        $temp_file = download_url( $image_url );

        if ( is_wp_error( $temp_file ) ) {
            return false;
        }

        // Prepare file array
        $file_array = array(
            'name'     => basename( $image_url ),
            'tmp_name' => $temp_file,
        );

        // Upload to media library
        $attachment_id = media_handle_sideload( $file_array, $post_id );

        if ( is_wp_error( $attachment_id ) ) {
            @unlink( $temp_file );
            return false;
        }

        // Set as featured image
        set_post_thumbnail( $post_id, $attachment_id );

        return $attachment_id;
    }
}
