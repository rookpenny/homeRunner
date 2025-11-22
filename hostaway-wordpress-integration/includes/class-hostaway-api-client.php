<?php
/**
 * Hostaway API Client
 *
 * Handles all communication with the Hostaway API using OAuth 2.0 authentication.
 */
class Hostaway_API_Client {

    /**
     * API base URL
     */
    private $api_base_url = 'https://api.hostaway.com/v1';

    /**
     * OAuth token URL
     */
    private $token_url = 'https://api.hostaway.com/v1/accessTokens';

    /**
     * Account ID
     */
    private $account_id;

    /**
     * API Secret Key
     */
    private $secret_key;

    /**
     * Access token
     */
    private $access_token;

    /**
     * Constructor
     */
    public function __construct() {
        $this->account_id = get_option( 'hostaway_account_id' );
        $this->secret_key = get_option( 'hostaway_secret_key' );
        $this->access_token = get_transient( 'hostaway_access_token' );
    }

    /**
     * Get OAuth access token
     */
    private function get_access_token() {
        // Return cached token if available
        if ( $this->access_token ) {
            return $this->access_token;
        }

        // Request new token
        $response = wp_remote_post( $this->token_url, array(
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
            ),
            'body' => array(
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->account_id,
                'client_secret' => $this->secret_key,
                'scope'         => 'general',
            ),
            'timeout' => 30,
        ) );

        if ( is_wp_error( $response ) ) {
            error_log( 'Hostaway API Error: ' . $response->get_error_message() );
            return false;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $body['access_token'] ) ) {
            $this->access_token = $body['access_token'];
            // Cache token for 23 hours (expires in 24)
            set_transient( 'hostaway_access_token', $this->access_token, 23 * HOUR_IN_SECONDS );
            return $this->access_token;
        }

        error_log( 'Hostaway API Error: Unable to get access token. Response: ' . print_r( $body, true ) );
        return false;
    }

    /**
     * Make API request
     */
    private function make_request( $endpoint, $method = 'GET', $data = array() ) {
        $token = $this->get_access_token();

        if ( ! $token ) {
            return new WP_Error( 'no_token', 'Unable to get access token' );
        }

        $url = $this->api_base_url . $endpoint;

        $args = array(
            'method'  => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ),
            'timeout' => 30,
        );

        if ( $method === 'POST' || $method === 'PUT' ) {
            $args['body'] = json_encode( $data );
        }

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            error_log( 'Hostaway API Request Error: ' . $response->get_error_message() );
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $status_code >= 400 ) {
            error_log( 'Hostaway API Error: Status ' . $status_code . ', Response: ' . print_r( $body, true ) );
            return new WP_Error( 'api_error', 'API returned error status: ' . $status_code, $body );
        }

        return $body;
    }

    /**
     * Test API connection
     */
    public function test_connection() {
        $result = $this->make_request( '/listings', 'GET' );
        return ! is_wp_error( $result );
    }

    /**
     * Get all listings
     */
    public function get_listings( $limit = 100, $offset = 0 ) {
        $endpoint = '/listings?limit=' . $limit . '&offset=' . $offset;
        return $this->make_request( $endpoint );
    }

    /**
     * Get single listing by ID
     */
    public function get_listing( $listing_id ) {
        $endpoint = '/listings/' . $listing_id;
        return $this->make_request( $endpoint );
    }

    /**
     * Get listing photos
     */
    public function get_listing_photos( $listing_id ) {
        $endpoint = '/listings/' . $listing_id . '/photos';
        return $this->make_request( $endpoint );
    }

    /**
     * Get listing amenities
     */
    public function get_listing_amenities( $listing_id ) {
        $endpoint = '/listings/' . $listing_id . '/amenities';
        return $this->make_request( $endpoint );
    }

    /**
     * Get listing calendar/availability
     */
    public function get_listing_calendar( $listing_id, $start_date = null, $end_date = null ) {
        $start = $start_date ? $start_date : date( 'Y-m-d' );
        $end = $end_date ? $end_date : date( 'Y-m-d', strtotime( '+1 year' ) );

        $endpoint = '/listings/' . $listing_id . '/calendar?startDate=' . $start . '&endDate=' . $end;
        return $this->make_request( $endpoint );
    }

    /**
     * Get all listings with full details (paginated)
     */
    public function get_all_listings() {
        $all_listings = array();
        $limit = 100;
        $offset = 0;
        $has_more = true;

        while ( $has_more ) {
            $response = $this->get_listings( $limit, $offset );

            if ( is_wp_error( $response ) ) {
                return $response;
            }

            if ( isset( $response['result'] ) && is_array( $response['result'] ) ) {
                $all_listings = array_merge( $all_listings, $response['result'] );

                // Check if there are more results
                if ( count( $response['result'] ) < $limit ) {
                    $has_more = false;
                } else {
                    $offset += $limit;
                }
            } else {
                $has_more = false;
            }

            // Safety break after 10 pages (1000 listings)
            if ( $offset >= 1000 ) {
                break;
            }
        }

        return $all_listings;
    }

    /**
     * Clear cached access token
     */
    public function clear_token_cache() {
        delete_transient( 'hostaway_access_token' );
        $this->access_token = null;
    }
}
