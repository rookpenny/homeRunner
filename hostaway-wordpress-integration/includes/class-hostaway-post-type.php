<?php
/**
 * Custom Post Type for Hostaway Listings
 * Can work with existing post types or create its own
 */
class Hostaway_Post_Type {

    /**
     * Register the custom post type for listings (if needed)
     */
    public function register_post_type() {
        // Only create our own post type if we're not using an existing one
        if (!Hostaway_Config::should_create_post_type()) {
            return; // Use existing 'listing' or 'property' post type
        }

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
        $post_type = Hostaway_Config::get_post_type();

        // Property Type taxonomy
        register_taxonomy(
            'property_type',
            $post_type,
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
            $post_type,
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
            $post_type,
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
        // Get the post type to use (existing or custom)
        $post_type = Hostaway_Config::get_post_type();

        // Check if listing already exists
        $existing_post = get_posts( array(
            'post_type'  => $post_type,
            'meta_key'   => '_hostaway_id',
            'meta_value' => $listing_data['id'],
            'numberposts' => 1,
            'post_status' => 'any',
        ) );

        $post_data = array(
            'post_type'    => $post_type,
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

        // Save reference ID
        update_post_meta( $post_id, '_hostaway_id', $listing_data['id'] );
        update_post_meta( $post_id, '_hostaway_data', $listing_data );

        // Save fields using theme's field names

        // Basic property details
        if ( isset( $listing_data['bedrooms'] ) ) {
            Hostaway_Config::save_field( $post_id, 'bedrooms', intval( $listing_data['bedrooms'] ) );
        }
        if ( isset( $listing_data['bathrooms'] ) ) {
            Hostaway_Config::save_field( $post_id, 'bathrooms', floatval( $listing_data['bathrooms'] ) );
        }
        if ( isset( $listing_data['accommodates'] ) ) {
            Hostaway_Config::save_field( $post_id, 'accommodates', intval( $listing_data['accommodates'] ) );
        }
        if ( isset( $listing_data['numberOfRooms'] ) ) {
            Hostaway_Config::save_field( $post_id, 'rooms', intval( $listing_data['numberOfRooms'] ) );
        }

        // Location information
        if ( isset( $listing_data['address'] ) ) {
            Hostaway_Config::save_field( $post_id, 'address', sanitize_text_field( $listing_data['address'] ) );
        }
        if ( isset( $listing_data['city'] ) ) {
            Hostaway_Config::save_field( $post_id, 'city', sanitize_text_field( $listing_data['city'] ) );
        }
        if ( isset( $listing_data['country'] ) ) {
            Hostaway_Config::save_field( $post_id, 'country', sanitize_text_field( $listing_data['country'] ) );
        }
        if ( isset( $listing_data['zipcode'] ) ) {
            Hostaway_Config::save_field( $post_id, 'zipcode', sanitize_text_field( $listing_data['zipcode'] ) );
        }
        if ( isset( $listing_data['latitude'] ) ) {
            Hostaway_Config::save_field( $post_id, 'latitude', floatval( $listing_data['latitude'] ) );
        }
        if ( isset( $listing_data['longitude'] ) ) {
            Hostaway_Config::save_field( $post_id, 'longitude', floatval( $listing_data['longitude'] ) );
        }

        // Pricing information
        if ( isset( $listing_data['baseDailyRate'] ) ) {
            Hostaway_Config::save_field( $post_id, 'price', floatval( $listing_data['baseDailyRate'] ) );
        }
        if ( isset( $listing_data['currencyCode'] ) ) {
            Hostaway_Config::save_field( $post_id, 'currency', sanitize_text_field( $listing_data['currencyCode'] ) );
        }
        if ( isset( $listing_data['weeklyDiscount'] ) ) {
            Hostaway_Config::save_field( $post_id, 'weekly_discount', floatval( $listing_data['weeklyDiscount'] ) );
        }
        if ( isset( $listing_data['monthlyDiscount'] ) ) {
            Hostaway_Config::save_field( $post_id, 'monthly_discount', floatval( $listing_data['monthlyDiscount'] ) );
        }
        if ( isset( $listing_data['cleaningFee'] ) ) {
            Hostaway_Config::save_field( $post_id, 'cleaning_fee', floatval( $listing_data['cleaningFee'] ) );
        }
        if ( isset( $listing_data['securityDepositFee'] ) ) {
            Hostaway_Config::save_field( $post_id, 'security_deposit', floatval( $listing_data['securityDepositFee'] ) );
        }
        if ( isset( $listing_data['extraPersonFee'] ) ) {
            Hostaway_Config::save_field( $post_id, 'extra_person_fee', floatval( $listing_data['extraPersonFee'] ) );
        }

        // Property size and features
        if ( isset( $listing_data['propertySize'] ) ) {
            Hostaway_Config::save_field( $post_id, 'property_size', floatval( $listing_data['propertySize'] ) );
        }
        if ( isset( $listing_data['propertySizeUnit'] ) ) {
            Hostaway_Config::save_field( $post_id, 'property_size_unit', sanitize_text_field( $listing_data['propertySizeUnit'] ) );
        }

        // Check-in/Check-out
        if ( isset( $listing_data['checkInTime'] ) ) {
            Hostaway_Config::save_field( $post_id, 'checkin_time', sanitize_text_field( $listing_data['checkInTime'] ) );
        }
        if ( isset( $listing_data['checkOutTime'] ) ) {
            Hostaway_Config::save_field( $post_id, 'checkout_time', sanitize_text_field( $listing_data['checkOutTime'] ) );
        }
        if ( isset( $listing_data['checkInTimeStart'] ) ) {
            Hostaway_Config::save_field( $post_id, 'checkin_time_start', sanitize_text_field( $listing_data['checkInTimeStart'] ) );
        }
        if ( isset( $listing_data['checkInTimeEnd'] ) ) {
            Hostaway_Config::save_field( $post_id, 'checkin_time_end', sanitize_text_field( $listing_data['checkInTimeEnd'] ) );
        }

        // Stay requirements
        if ( isset( $listing_data['minNights'] ) ) {
            Hostaway_Config::save_field( $post_id, 'min_stay', intval( $listing_data['minNights'] ) );
        }
        if ( isset( $listing_data['maxNights'] ) ) {
            Hostaway_Config::save_field( $post_id, 'max_stay', intval( $listing_data['maxNights'] ) );
        }

        // Status and availability
        if ( isset( $listing_data['isActive'] ) ) {
            Hostaway_Config::save_field( $post_id, 'is_active', $listing_data['isActive'] ? 1 : 0 );
        }
        if ( isset( $listing_data['isListed'] ) ) {
            Hostaway_Config::save_field( $post_id, 'is_listed', $listing_data['isListed'] ? 1 : 0 );
        }

        // Policies
        if ( isset( $listing_data['houseRules'] ) ) {
            Hostaway_Config::save_field( $post_id, 'house_rules', wp_kses_post( $listing_data['houseRules'] ) );
        }
        if ( isset( $listing_data['cancellationPolicy'] ) ) {
            Hostaway_Config::save_field( $post_id, 'cancellation_policy', sanitize_text_field( $listing_data['cancellationPolicy'] ) );
        }

        // Additional details
        if ( isset( $listing_data['airbnbPropertyType'] ) ) {
            Hostaway_Config::save_field( $post_id, 'airbnb_property_type', sanitize_text_field( $listing_data['airbnbPropertyType'] ) );
        }
        if ( isset( $listing_data['roomType'] ) ) {
            Hostaway_Config::save_field( $post_id, 'room_type', sanitize_text_field( $listing_data['roomType'] ) );
        }
        if ( isset( $listing_data['timezone'] ) ) {
            Hostaway_Config::save_field( $post_id, 'timezone', sanitize_text_field( $listing_data['timezone'] ) );
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

        // Handle amenities
        if ( isset( $listing_data['amenities'] ) && is_array( $listing_data['amenities'] ) ) {
            $amenity_names = array();
            foreach ( $listing_data['amenities'] as $amenity ) {
                if ( is_string( $amenity ) ) {
                    $amenity_names[] = $amenity;
                } elseif ( isset( $amenity['name'] ) ) {
                    $amenity_names[] = $amenity['name'];
                }
            }

            if ( ! empty( $amenity_names ) ) {
                // Set as taxonomy terms
                wp_set_object_terms( $post_id, $amenity_names, 'amenity' );

                // Also save as meta for easier access
                update_post_meta( $post_id, '_amenities', $amenity_names );
                Hostaway_Config::save_field( $post_id, 'amenities', $amenity_names );
            }
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
