<?php
/**
 * Configuration for Hostaway Integration
 *
 * This allows the plugin to work with existing post types and custom fields
 */
class Hostaway_Config {

    /**
     * Get the post type to use for listings
     *
     * Set to 'auto' to use existing 'listing' post type
     * Set to 'hostaway_listing' to create separate post type
     */
    public static function get_post_type() {
        $use_existing = get_option('hostaway_use_existing_post_type', 'yes');

        if ($use_existing === 'yes') {
            // Try common post type names
            $possible_types = array('listing', 'listings', 'property', 'properties', 'estate_property');

            foreach ($possible_types as $type) {
                if (post_type_exists($type)) {
                    return $type;
                }
            }
        }

        // Fallback to our custom post type
        return 'hostaway_listing';
    }

    /**
     * Check if we should create our own post type
     */
    public static function should_create_post_type() {
        $post_type = self::get_post_type();
        return $post_type === 'hostaway_listing';
    }

    /**
     * Get custom field mappings
     * Maps Hostaway fields to theme's custom fields
     */
    public static function get_field_mapping() {
        $theme = wp_get_theme();
        $theme_name = strtolower($theme->get('Name'));

        // Auto-detect theme and return appropriate mappings
        if (strpos($theme_name, 'houzez') !== false) {
            return self::get_houzez_mapping();
        } elseif (strpos($theme_name, 'realhomes') !== false) {
            return self::get_realhomes_mapping();
        } elseif (strpos($theme_name, 'residence') !== false) {
            return self::get_wpresidence_mapping();
        }

        // Check for custom mapping in settings
        $custom_mapping = get_option('hostaway_custom_field_mapping', array());
        if (!empty($custom_mapping)) {
            return $custom_mapping;
        }

        // Default mapping (works with many themes)
        return array(
            'bedrooms'     => 'bedrooms',
            'bathrooms'    => 'bathrooms',
            'accommodates' => 'guests',
            'price'        => 'price',
            'currency'     => 'currency',
            'address'      => 'address',
            'city'         => 'city',
            'country'      => 'country',
            'zipcode'      => 'zipcode',
            'latitude'     => 'latitude',
            'longitude'    => 'longitude',
            'property_type' => 'property_type',
        );
    }

    /**
     * Houzez theme field mapping
     */
    private static function get_houzez_mapping() {
        return array(
            'bedrooms'     => 'fave_property_bedrooms',
            'bathrooms'    => 'fave_property_bathrooms',
            'accommodates' => 'fave_property_guests',
            'price'        => 'fave_property_price',
            'currency'     => 'fave_currency',
            'address'      => 'fave_property_address',
            'city'         => 'fave_property_city',
            'country'      => 'fave_property_country',
            'zipcode'      => 'fave_property_zip',
            'latitude'     => 'houzez_geolocation_lat',
            'longitude'    => 'houzez_geolocation_long',
            'property_type' => 'property_type',
        );
    }

    /**
     * RealHomes theme field mapping
     */
    private static function get_realhomes_mapping() {
        return array(
            'bedrooms'     => 'REAL_HOMES_property_bedrooms',
            'bathrooms'    => 'REAL_HOMES_property_bathrooms',
            'accommodates' => 'REAL_HOMES_property_guests',
            'price'        => 'REAL_HOMES_property_price',
            'currency'     => 'REAL_HOMES_currency',
            'address'      => 'REAL_HOMES_property_address',
            'city'         => 'REAL_HOMES_property_city',
            'country'      => 'REAL_HOMES_property_country',
            'zipcode'      => 'REAL_HOMES_property_zip',
            'latitude'     => 'REAL_HOMES_property_lat',
            'longitude'    => 'REAL_HOMES_property_lng',
            'property_type' => 'property-type',
        );
    }

    /**
     * WPResidence theme field mapping
     */
    private static function get_wpresidence_mapping() {
        return array(
            'bedrooms'     => 'room_number',
            'bathrooms'    => 'bathroom_number',
            'accommodates' => 'max_guests',
            'price'        => 'price',
            'currency'     => 'currency',
            'address'      => 'property_address',
            'city'         => 'property_city',
            'country'      => 'property_country',
            'zipcode'      => 'property_zip',
            'latitude'     => 'property_latitude',
            'longitude'    => 'property_longitude',
            'property_type' => 'property_category',
        );
    }

    /**
     * Get meta field prefix (if theme uses one)
     */
    public static function get_meta_prefix() {
        $theme = wp_get_theme();
        $theme_name = strtolower($theme->get('Name'));

        if (strpos($theme_name, 'houzez') !== false) {
            return 'fave_';
        }

        return '';
    }

    /**
     * Save a field value using the correct mapping
     */
    public static function save_field($post_id, $hostaway_field, $value) {
        $mapping = self::get_field_mapping();

        if (isset($mapping[$hostaway_field])) {
            $field_name = $mapping[$hostaway_field];

            // Update post meta
            update_post_meta($post_id, $field_name, $value);

            // Also save with our prefix for reference
            update_post_meta($post_id, '_hostaway_' . $hostaway_field, $value);
        }
    }
}
