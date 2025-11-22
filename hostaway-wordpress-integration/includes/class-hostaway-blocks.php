<?php
/**
 * Hostaway Gutenberg Blocks
 *
 * Registers custom blocks for the WordPress block editor
 */
class Hostaway_Blocks {

    /**
     * Initialize blocks
     */
    public function __construct() {
        add_action('init', array($this, 'register_blocks'));
    }

    /**
     * Register Gutenberg blocks
     */
    public function register_blocks() {
        // Check if Gutenberg is available
        if (!function_exists('register_block_type')) {
            return;
        }

        // Register Listings Grid Block
        register_block_type('hostaway/listings-grid', array(
            'attributes' => array(
                'limit' => array(
                    'type' => 'number',
                    'default' => -1,
                ),
                'columns' => array(
                    'type' => 'number',
                    'default' => 3,
                ),
                'propertyType' => array(
                    'type' => 'string',
                    'default' => '',
                ),
                'location' => array(
                    'type' => 'string',
                    'default' => '',
                ),
                'bedrooms' => array(
                    'type' => 'number',
                    'default' => 0,
                ),
                'orderby' => array(
                    'type' => 'string',
                    'default' => 'date',
                ),
                'order' => array(
                    'type' => 'string',
                    'default' => 'DESC',
                ),
            ),
            'render_callback' => array($this, 'render_listings_block'),
        ));

        // Register Single Listing Block
        register_block_type('hostaway/single-listing', array(
            'attributes' => array(
                'listingId' => array(
                    'type' => 'number',
                    'default' => 0,
                ),
            ),
            'render_callback' => array($this, 'render_single_listing_block'),
        ));
    }

    /**
     * Render listings grid block
     */
    public function render_listings_block($attributes) {
        $atts = array(
            'limit' => $attributes['limit'],
            'columns' => $attributes['columns'],
            'property_type' => $attributes['propertyType'],
            'location' => $attributes['location'],
            'bedrooms' => $attributes['bedrooms'],
            'orderby' => $attributes['orderby'],
            'order' => $attributes['order'],
        );

        // Use the existing shortcode functionality
        $public = new Hostaway_Public('hostaway-integration', HOSTAWAY_INTEGRATION_VERSION);
        return $public->listings_shortcode($atts);
    }

    /**
     * Render single listing block
     */
    public function render_single_listing_block($attributes) {
        if (empty($attributes['listingId'])) {
            return '<p>Please select a listing.</p>';
        }

        $atts = array(
            'id' => $attributes['listingId'],
        );

        $public = new Hostaway_Public('hostaway-integration', HOSTAWAY_INTEGRATION_VERSION);
        return $public->single_listing_shortcode($atts);
    }
}

// Initialize blocks
new Hostaway_Blocks();
