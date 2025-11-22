/**
 * Hostaway Integration - Public JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        /**
         * Initialize image gallery
         */
        $('.listing-gallery img').on('click', function() {
            // Placeholder for lightbox functionality
            // Can integrate with popular lightbox libraries like:
            // - Lightbox2
            // - PhotoSwipe
            // - GLightbox
            console.log('Image clicked - integrate lightbox here');
        });

        /**
         * Initialize map
         */
        $('.map-container').each(function() {
            var lat = $(this).data('lat');
            var lng = $(this).data('lng');

            // Placeholder for map integration
            // Can integrate with:
            // - Google Maps
            // - Leaflet
            // - Mapbox
            if (lat && lng) {
                console.log('Map coordinates:', lat, lng);
                // Initialize map here
            }
        });

        /**
         * Smooth scroll to sections
         */
        $('a[href^="#"]').on('click', function(e) {
            var target = $(this.getAttribute('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 100
                }, 1000);
            }
        });

    });

})(jQuery);
