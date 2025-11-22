<?php
/**
 * The public-facing functionality of the plugin.
 */
class Hostaway_Public {

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side.
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            HOSTAWAY_INTEGRATION_PLUGIN_URL . 'public/css/hostaway-public.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the public-facing side.
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            HOSTAWAY_INTEGRATION_PLUGIN_URL . 'public/js/hostaway-public.js',
            array( 'jquery' ),
            $this->version,
            false
        );
    }

    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode( 'hostaway_listings', array( $this, 'listings_shortcode' ) );
        add_shortcode( 'hostaway_listing', array( $this, 'single_listing_shortcode' ) );
    }

    /**
     * Shortcode to display all listings
     *
     * Usage: [hostaway_listings limit="9" columns="3" property_type="apartment"]
     */
    public function listings_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'limit'         => -1,
            'columns'       => 3,
            'property_type' => '',
            'location'      => '',
            'bedrooms'      => '',
            'orderby'       => 'date',
            'order'         => 'DESC',
        ), $atts, 'hostaway_listings' );

        $args = array(
            'post_type'      => 'hostaway_listing',
            'posts_per_page' => intval( $atts['limit'] ),
            'orderby'        => $atts['orderby'],
            'order'          => $atts['order'],
        );

        // Add taxonomy filters
        $tax_query = array();
        if ( ! empty( $atts['property_type'] ) ) {
            $tax_query[] = array(
                'taxonomy' => 'property_type',
                'field'    => 'slug',
                'terms'    => $atts['property_type'],
            );
        }
        if ( ! empty( $atts['location'] ) ) {
            $tax_query[] = array(
                'taxonomy' => 'location',
                'field'    => 'slug',
                'terms'    => $atts['location'],
            );
        }
        if ( count( $tax_query ) > 0 ) {
            $args['tax_query'] = $tax_query;
        }

        // Add meta filters
        if ( ! empty( $atts['bedrooms'] ) ) {
            $args['meta_query'] = array(
                array(
                    'key'     => '_bedrooms',
                    'value'   => intval( $atts['bedrooms'] ),
                    'compare' => '=',
                ),
            );
        }

        $query = new WP_Query( $args );

        ob_start();

        if ( $query->have_posts() ) {
            echo '<div class="hostaway-listings-grid columns-' . esc_attr( $atts['columns'] ) . '">';

            while ( $query->have_posts() ) {
                $query->the_post();
                $this->render_listing_card( get_the_ID() );
            }

            echo '</div>';
        } else {
            echo '<p class="hostaway-no-listings">No listings found.</p>';
        }

        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * Shortcode to display a single listing
     *
     * Usage: [hostaway_listing id="123"]
     */
    public function single_listing_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'id' => '',
        ), $atts, 'hostaway_listing' );

        if ( empty( $atts['id'] ) ) {
            return '<p>Please provide a listing ID.</p>';
        }

        ob_start();
        $this->render_single_listing( intval( $atts['id'] ) );
        return ob_get_clean();
    }

    /**
     * Render listing card (grid item)
     */
    private function render_listing_card( $post_id ) {
        $bedrooms = get_post_meta( $post_id, '_bedrooms', true );
        $bathrooms = get_post_meta( $post_id, '_bathrooms', true );
        $accommodates = get_post_meta( $post_id, '_accommodates', true );
        $price = get_post_meta( $post_id, '_price', true );
        $currency = get_post_meta( $post_id, '_currency', true );
        $city = get_post_meta( $post_id, '_city', true );

        // Check for custom template
        $template = $this->locate_template( 'listing-card.php' );
        if ( $template ) {
            include $template;
        } else {
            // Default template
            ?>
            <div class="hostaway-listing-card">
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="listing-thumbnail">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail( 'large' ); ?>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="listing-content">
                    <h3 class="listing-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h3>

                    <?php if ( $city ) : ?>
                        <p class="listing-location">
                            <span class="dashicons dashicons-location"></span>
                            <?php echo esc_html( $city ); ?>
                        </p>
                    <?php endif; ?>

                    <div class="listing-details">
                        <?php if ( $bedrooms ) : ?>
                            <span class="detail-item">
                                <span class="dashicons dashicons-building"></span>
                                <?php echo esc_html( $bedrooms ); ?> Bedrooms
                            </span>
                        <?php endif; ?>

                        <?php if ( $bathrooms ) : ?>
                            <span class="detail-item">
                                <span class="dashicons dashicons-admin-home"></span>
                                <?php echo esc_html( $bathrooms ); ?> Bathrooms
                            </span>
                        <?php endif; ?>

                        <?php if ( $accommodates ) : ?>
                            <span class="detail-item">
                                <span class="dashicons dashicons-groups"></span>
                                <?php echo esc_html( $accommodates ); ?> Guests
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ( $price ) : ?>
                        <div class="listing-price">
                            <?php echo esc_html( $currency ); ?><?php echo esc_html( number_format( $price, 2 ) ); ?>
                            <span class="price-period">/night</span>
                        </div>
                    <?php endif; ?>

                    <div class="listing-excerpt">
                        <?php the_excerpt(); ?>
                    </div>

                    <a href="<?php the_permalink(); ?>" class="listing-button">View Details</a>
                </div>
            </div>
            <?php
        }
    }

    /**
     * Render single listing page
     */
    private function render_single_listing( $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post || $post->post_type !== 'hostaway_listing' ) {
            echo '<p>Listing not found.</p>';
            return;
        }

        $bedrooms = get_post_meta( $post_id, '_bedrooms', true );
        $bathrooms = get_post_meta( $post_id, '_bathrooms', true );
        $accommodates = get_post_meta( $post_id, '_accommodates', true );
        $price = get_post_meta( $post_id, '_price', true );
        $currency = get_post_meta( $post_id, '_currency', true );
        $address = get_post_meta( $post_id, '_address', true );
        $city = get_post_meta( $post_id, '_city', true );
        $country = get_post_meta( $post_id, '_country', true );
        $latitude = get_post_meta( $post_id, '_latitude', true );
        $longitude = get_post_meta( $post_id, '_longitude', true );
        $image_gallery = get_post_meta( $post_id, '_image_gallery', true );

        // Check for custom template
        $template = $this->locate_template( 'single-listing.php' );
        if ( $template ) {
            include $template;
        } else {
            // Default template
            ?>
            <div class="hostaway-single-listing">
                <h1 class="listing-title"><?php echo esc_html( $post->post_title ); ?></h1>

                <?php if ( $city || $country ) : ?>
                    <p class="listing-location">
                        <span class="dashicons dashicons-location"></span>
                        <?php echo esc_html( implode( ', ', array_filter( array( $city, $country ) ) ) ); ?>
                    </p>
                <?php endif; ?>

                <?php if ( ! empty( $image_gallery ) ) : ?>
                    <div class="listing-gallery">
                        <?php foreach ( $image_gallery as $image_url ) : ?>
                            <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $post->post_title ); ?>" />
                        <?php endforeach; ?>
                    </div>
                <?php elseif ( has_post_thumbnail( $post_id ) ) : ?>
                    <div class="listing-featured-image">
                        <?php echo get_the_post_thumbnail( $post_id, 'full' ); ?>
                    </div>
                <?php endif; ?>

                <div class="listing-meta">
                    <?php if ( $price ) : ?>
                        <div class="listing-price-large">
                            <?php echo esc_html( $currency ); ?><?php echo esc_html( number_format( $price, 2 ) ); ?>
                            <span class="price-period">/night</span>
                        </div>
                    <?php endif; ?>

                    <div class="listing-details-large">
                        <?php if ( $bedrooms ) : ?>
                            <div class="detail-item">
                                <span class="dashicons dashicons-building"></span>
                                <strong><?php echo esc_html( $bedrooms ); ?></strong> Bedrooms
                            </div>
                        <?php endif; ?>

                        <?php if ( $bathrooms ) : ?>
                            <div class="detail-item">
                                <span class="dashicons dashicons-admin-home"></span>
                                <strong><?php echo esc_html( $bathrooms ); ?></strong> Bathrooms
                            </div>
                        <?php endif; ?>

                        <?php if ( $accommodates ) : ?>
                            <div class="detail-item">
                                <span class="dashicons dashicons-groups"></span>
                                <strong><?php echo esc_html( $accommodates ); ?></strong> Guests
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="listing-description">
                    <h2>Description</h2>
                    <?php echo wp_kses_post( $post->post_content ); ?>
                </div>

                <?php
                $amenities = get_the_terms( $post_id, 'amenity' );
                if ( $amenities && ! is_wp_error( $amenities ) ) :
                ?>
                    <div class="listing-amenities">
                        <h2>Amenities</h2>
                        <ul>
                            <?php foreach ( $amenities as $amenity ) : ?>
                                <li><?php echo esc_html( $amenity->name ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ( $latitude && $longitude ) : ?>
                    <div class="listing-map">
                        <h2>Location</h2>
                        <div class="map-container" data-lat="<?php echo esc_attr( $latitude ); ?>" data-lng="<?php echo esc_attr( $longitude ); ?>">
                            <p>Map integration available with custom implementation.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php
        }
    }

    /**
     * Locate template file
     */
    private function locate_template( $template_name ) {
        // Check theme directory first
        $theme_template = locate_template( array(
            'hostaway-integration/' . $template_name,
            $template_name,
        ) );

        if ( $theme_template ) {
            return $theme_template;
        }

        // Check plugin templates directory
        $plugin_template = HOSTAWAY_INTEGRATION_PLUGIN_DIR . 'templates/' . $template_name;
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }

        return false;
    }
}
