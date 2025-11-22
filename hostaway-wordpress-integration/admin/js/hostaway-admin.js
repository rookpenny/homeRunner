/**
 * Hostaway Integration - Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        /**
         * Test API Connection
         */
        $('#test-connection').on('click', function() {
            var button = $(this);
            var resultDiv = $('#connection-result');

            button.prop('disabled', true).text('Testing...');
            resultDiv.html('');

            $.ajax({
                url: hostawayAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hostaway_test_connection',
                    nonce: hostawayAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        resultDiv
                            .removeClass('error')
                            .addClass('success')
                            .html('<strong>Success:</strong> ' + response.data.message);
                    } else {
                        resultDiv
                            .removeClass('success')
                            .addClass('error')
                            .html('<strong>Error:</strong> ' + response.data.message);
                    }
                },
                error: function() {
                    resultDiv
                        .removeClass('success')
                        .addClass('error')
                        .html('<strong>Error:</strong> Connection failed. Please try again.');
                },
                complete: function() {
                    button.prop('disabled', false).text('Test Connection');
                }
            });
        });

        /**
         * Sync Listings
         */
        $('#sync-listings').on('click', function() {
            var button = $(this);
            var progressDiv = $('#sync-progress');
            var resultDiv = $('#sync-result');

            button.prop('disabled', true);
            progressDiv.show();
            resultDiv.html('');

            $.ajax({
                url: hostawayAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'hostaway_sync_listings',
                    nonce: hostawayAdmin.nonce
                },
                success: function(response) {
                    progressDiv.hide();

                    if (response.success) {
                        var html = '<div class="sync-stats">';
                        html += '<h3>Sync Completed Successfully!</h3>';
                        html += '<p><strong>Listings synced:</strong> ' + response.data.synced_count + '</p>';
                        html += '</div>';

                        if (response.data.errors && response.data.errors.length > 0) {
                            html += '<div class="sync-errors">';
                            html += '<h4>Errors encountered:</h4>';
                            html += '<ul>';
                            response.data.errors.forEach(function(error) {
                                html += '<li>' + error + '</li>';
                            });
                            html += '</ul>';
                            html += '</div>';
                        }

                        resultDiv
                            .removeClass('error')
                            .addClass('success')
                            .html(html);
                    } else {
                        resultDiv
                            .removeClass('success')
                            .addClass('error')
                            .html('<strong>Error:</strong> ' + response.data.message);
                    }
                },
                error: function() {
                    progressDiv.hide();
                    resultDiv
                        .removeClass('success')
                        .addClass('error')
                        .html('<strong>Error:</strong> Sync failed. Please try again.');
                },
                complete: function() {
                    button.prop('disabled', false);
                }
            });
        });

    });

})(jQuery);
