<?php
/**
 * Template for displaying a single listing
 *
 * This template can be overridden by copying it to your-theme/hostaway-integration/single-listing.php
 *
 * Available variables:
 * - $post: The listing post object
 * - $post_id: The listing post ID
 * - All listing meta fields
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
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
            <?php foreach ( array_slice( $image_gallery, 0, 6 ) as $image_url ) : ?>
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
            <?php if ( $address ) : ?>
                <p class="address"><?php echo esc_html( $address ); ?></p>
            <?php endif; ?>
            <div class="map-container" data-lat="<?php echo esc_attr( $latitude ); ?>" data-lng="<?php echo esc_attr( $longitude ); ?>">
                <p><em>Map can be integrated with Google Maps or other mapping service.</em></p>
            </div>
        </div>
    <?php endif; ?>
</div>
