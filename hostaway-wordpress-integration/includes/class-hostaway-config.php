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
        // Check for forced post type (set via debug helper)
        $forced = get_option('hostaway_force_post_type');
        if ($forced && post_type_exists($forced)) {
            return $forced;
        }

        $use_existing = get_option('hostaway_use_existing_post_type', 'yes');

        if ($use_existing === 'yes') {
            // Try common post type names
            $possible_types = array('listing', 'listings', 'property', 'properties', 'estate_property', 'real_estate', 'realestate');

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
            // Basic property details
            'bedrooms'              => 'bedrooms',
            'bathrooms'             => 'bathrooms',
            'accommodates'          => 'guests',
            'rooms'                 => 'rooms',

            // Location
            'address'               => 'address',
            'city'                  => 'city',
            'country'               => 'country',
            'zipcode'               => 'zipcode',
            'latitude'              => 'latitude',
            'longitude'             => 'longitude',

            // Pricing
            'price'                 => 'price',
            'currency'              => 'currency',
            'weekly_discount'       => 'weekly_discount',
            'monthly_discount'      => 'monthly_discount',
            'cleaning_fee'          => 'cleaning_fee',
            'security_deposit'      => 'security_deposit',
            'extra_person_fee'      => 'extra_person_fee',

            // Property size
            'property_size'         => 'property_size',
            'property_size_unit'    => 'property_size_unit',

            // Check-in/Check-out
            'checkin_time'          => 'checkin_time',
            'checkout_time'         => 'checkout_time',
            'checkin_time_start'    => 'checkin_time_start',
            'checkin_time_end'      => 'checkin_time_end',

            // Stay requirements
            'min_stay'              => 'min_stay',
            'max_stay'              => 'max_stay',

            // Status
            'is_active'             => 'is_active',
            'is_listed'             => 'is_listed',

            // Policies
            'house_rules'           => 'house_rules',
            'cancellation_policy'   => 'cancellation_policy',

            // Additional
            'airbnb_property_type'  => 'airbnb_property_type',
            'room_type'             => 'room_type',
            'timezone'              => 'timezone',
            'amenities'             => 'amenities',
            'property_type'         => 'property_type',
        );
    }

    /**
     * Houzez theme field mapping
     */
    private static function get_houzez_mapping() {
        return array(
            // Basic property details
            'bedrooms'              => 'fave_property_bedrooms',
            'bathrooms'             => 'fave_property_bathrooms',
            'accommodates'          => 'fave_property_guests',
            'rooms'                 => 'fave_property_rooms',

            // Location
            'address'               => 'fave_property_address',
            'city'                  => 'fave_property_city',
            'country'               => 'fave_property_country',
            'zipcode'               => 'fave_property_zip',
            'latitude'              => 'houzez_geolocation_lat',
            'longitude'             => 'houzez_geolocation_long',

            // Pricing
            'price'                 => 'fave_property_price',
            'currency'              => 'fave_currency',
            'weekly_discount'       => 'fave_weekly_discount',
            'monthly_discount'      => 'fave_monthly_discount',
            'cleaning_fee'          => 'fave_cleaning_fee',
            'security_deposit'      => 'fave_property_sec_deposit',
            'extra_person_fee'      => 'fave_extra_person_fee',

            // Property size
            'property_size'         => 'fave_property_size',
            'property_size_unit'    => 'fave_property_size_prefix',

            // Check-in/Check-out
            'checkin_time'          => 'fave_checkin_time',
            'checkout_time'         => 'fave_checkout_time',
            'checkin_time_start'    => 'fave_checkin_time_start',
            'checkin_time_end'      => 'fave_checkin_time_end',

            // Stay requirements
            'min_stay'              => 'fave_min_stay',
            'max_stay'              => 'fave_max_stay',

            // Status
            'is_active'             => 'fave_is_active',
            'is_listed'             => 'fave_is_listed',

            // Policies
            'house_rules'           => 'fave_house_rules',
            'cancellation_policy'   => 'fave_cancellation_policy',

            // Additional
            'airbnb_property_type'  => 'fave_property_type',
            'room_type'             => 'fave_room_type',
            'timezone'              => 'fave_timezone',
            'amenities'             => 'fave_property_features',
            'property_type'         => 'property_type',
        );
    }

    /**
     * RealHomes theme field mapping
     */
    private static function get_realhomes_mapping() {
        return array(
            // Basic property details
            'bedrooms'              => 'REAL_HOMES_property_bedrooms',
            'bathrooms'             => 'REAL_HOMES_property_bathrooms',
            'accommodates'          => 'REAL_HOMES_property_guests',
            'rooms'                 => 'REAL_HOMES_property_rooms',

            // Location
            'address'               => 'REAL_HOMES_property_address',
            'city'                  => 'REAL_HOMES_property_city',
            'country'               => 'REAL_HOMES_property_country',
            'zipcode'               => 'REAL_HOMES_property_zip',
            'latitude'              => 'REAL_HOMES_property_lat',
            'longitude'             => 'REAL_HOMES_property_lng',

            // Pricing
            'price'                 => 'REAL_HOMES_property_price',
            'currency'              => 'REAL_HOMES_currency',
            'weekly_discount'       => 'REAL_HOMES_weekly_discount',
            'monthly_discount'      => 'REAL_HOMES_monthly_discount',
            'cleaning_fee'          => 'REAL_HOMES_cleaning_fee',
            'security_deposit'      => 'REAL_HOMES_security_deposit',
            'extra_person_fee'      => 'REAL_HOMES_extra_person_fee',

            // Property size
            'property_size'         => 'REAL_HOMES_property_size',
            'property_size_unit'    => 'REAL_HOMES_property_size_unit',

            // Check-in/Check-out
            'checkin_time'          => 'REAL_HOMES_checkin_time',
            'checkout_time'         => 'REAL_HOMES_checkout_time',
            'checkin_time_start'    => 'REAL_HOMES_checkin_start',
            'checkin_time_end'      => 'REAL_HOMES_checkin_end',

            // Stay requirements
            'min_stay'              => 'REAL_HOMES_min_stay',
            'max_stay'              => 'REAL_HOMES_max_stay',

            // Status
            'is_active'             => 'REAL_HOMES_is_active',
            'is_listed'             => 'REAL_HOMES_is_listed',

            // Policies
            'house_rules'           => 'REAL_HOMES_house_rules',
            'cancellation_policy'   => 'REAL_HOMES_cancellation_policy',

            // Additional
            'airbnb_property_type'  => 'REAL_HOMES_property_type',
            'room_type'             => 'REAL_HOMES_room_type',
            'timezone'              => 'REAL_HOMES_timezone',
            'amenities'             => 'REAL_HOMES_property_features',
            'property_type'         => 'property-type',
        );
    }

    /**
     * WPResidence theme field mapping
     */
    private static function get_wpresidence_mapping() {
        return array(
            // Basic property details
            'bedrooms'              => 'room_number',
            'bathrooms'             => 'bathroom_number',
            'accommodates'          => 'max_guests',
            'rooms'                 => 'total_rooms',

            // Location
            'address'               => 'property_address',
            'city'                  => 'property_city',
            'country'               => 'property_country',
            'zipcode'               => 'property_zip',
            'latitude'              => 'property_latitude',
            'longitude'             => 'property_longitude',

            // Pricing
            'price'                 => 'price',
            'currency'              => 'currency',
            'weekly_discount'       => 'weekly_discount',
            'monthly_discount'      => 'monthly_discount',
            'cleaning_fee'          => 'cleaning_fee',
            'security_deposit'      => 'security_deposit',
            'extra_person_fee'      => 'extra_person_fee',

            // Property size
            'property_size'         => 'property_size',
            'property_size_unit'    => 'property_size_unit',

            // Check-in/Check-out
            'checkin_time'          => 'checkin_time',
            'checkout_time'         => 'checkout_time',
            'checkin_time_start'    => 'checkin_start',
            'checkin_time_end'      => 'checkin_end',

            // Stay requirements
            'min_stay'              => 'min_days_booking',
            'max_stay'              => 'max_days_booking',

            // Status
            'is_active'             => 'property_active',
            'is_listed'             => 'property_listed',

            // Policies
            'house_rules'           => 'house_rules',
            'cancellation_policy'   => 'cancellation_policy',

            // Additional
            'airbnb_property_type'  => 'property_type_text',
            'room_type'             => 'room_type',
            'timezone'              => 'property_timezone',
            'amenities'             => 'property_features',
            'property_type'         => 'property_category',
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
