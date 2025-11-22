<?php
/**
 * Template for displaying a listing card in the grid view
 *
 * This template can be overridden by copying it to your-theme/hostaway-integration/listing-card.php
 *
 * Available variables:
 * - $post_id: The listing post ID
 * - $bedrooms, $bathrooms, $accommodates, $price, $currency, $city
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="hostaway-listing-card">
    <?php if ( has_post_thumbnail( $post_id ) ) : ?>
        <div class="listing-thumbnail">
            <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
                <?php echo get_the_post_thumbnail( $post_id, 'large' ); ?>
            </a>
        </div>
    <?php endif; ?>

    <div class="listing-content">
        <h3 class="listing-title">
            <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
                <?php echo esc_html( get_the_title( $post_id ) ); ?>
            </a>
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
            <?php echo wp_kses_post( get_the_excerpt( $post_id ) ); ?>
        </div>

        <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="listing-button">View Details</a>
    </div>
</div>
