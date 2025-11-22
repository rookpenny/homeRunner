# Hostaway WordPress Integration - Technical Documentation

**Version:** 1.0.0
**Last Updated:** 2025-11-22

This document provides comprehensive technical documentation for the Hostaway WordPress Integration plugin, detailing its architecture, components, and implementation.

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [File Structure](#file-structure)
3. [Core Components](#core-components)
4. [Database Schema](#database-schema)
5. [API Integration](#api-integration)
6. [Hooks & Filters](#hooks--filters)
7. [Shortcodes](#shortcodes)
8. [Template System](#template-system)
9. [Admin Interface](#admin-interface)
10. [Frontend Display](#frontend-display)
11. [Development Guide](#development-guide)
12. [Code Examples](#code-examples)

---

## Architecture Overview

### Plugin Architecture

The plugin follows WordPress plugin development best practices with a modular, object-oriented architecture:

```
┌─────────────────────────────────────────────────────────────┐
│                   WordPress Core                             │
└────────────────────┬────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────┐
│            hostaway-integration.php                          │
│            (Main Plugin File)                                │
└────────────────────┬────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────┐
│       Hostaway_Integration (Core Class)                      │
│       - Orchestrates all components                          │
│       - Loads dependencies                                   │
│       - Registers hooks                                      │
└─────┬──────────────┬──────────────┬─────────────────────────┘
      │              │              │
┌─────▼─────┐  ┌────▼─────┐  ┌────▼──────┐
│ API Client│  │  Admin   │  │  Public   │
│ Component │  │Component │  │ Component │
└───────────┘  └──────────┘  └───────────┘
      │              │              │
      ▼              ▼              ▼
┌─────────────────────────────────────────┐
│         Hostaway API                     │
│    (External Service)                    │
└─────────────────────────────────────────┘
```

### Design Patterns Used

1. **Singleton Pattern**: Core plugin class ensures single instance
2. **Factory Pattern**: Post type creation from API data
3. **Observer Pattern**: WordPress hooks and filters
4. **Template Method Pattern**: Customizable templates
5. **Strategy Pattern**: Different sync strategies (manual/auto)

---

## File Structure

### Complete Directory Tree

```
hostaway-wordpress-integration/
│
├── hostaway-integration.php          # Main plugin file (entry point)
│
├── includes/                         # Core functionality
│   ├── class-hostaway-integration.php    # Main orchestrator class
│   ├── class-hostaway-loader.php         # Hooks/filters loader
│   ├── class-hostaway-api-client.php     # Hostaway API client
│   ├── class-hostaway-post-type.php      # Custom post type & taxonomies
│   ├── class-hostaway-activator.php      # Plugin activation logic
│   └── class-hostaway-deactivator.php    # Plugin deactivation logic
│
├── admin/                            # Admin-specific functionality
│   ├── class-hostaway-admin.php          # Admin interface logic
│   ├── css/
│   │   └── hostaway-admin.css            # Admin styles
│   └── js/
│       └── hostaway-admin.js             # Admin JavaScript (AJAX)
│
├── public/                           # Public-facing functionality
│   ├── class-hostaway-public.php         # Public interface logic
│   ├── css/
│   │   └── hostaway-public.css           # Frontend styles
│   └── js/
│       └── hostaway-public.js            # Frontend JavaScript
│
├── templates/                        # Template files (overridable)
│   ├── listing-card.php                  # Grid item template
│   └── single-listing.php                # Single listing template
│
└── README.md                         # Plugin documentation
```

### File Purposes

| File | Purpose | Key Functions |
|------|---------|---------------|
| `hostaway-integration.php` | Plugin entry point, defines constants | Activation/deactivation hooks, initialization |
| `class-hostaway-integration.php` | Core orchestrator | Load dependencies, register hooks |
| `class-hostaway-loader.php` | Hook management | Register actions/filters systematically |
| `class-hostaway-api-client.php` | API communication | OAuth, fetch listings, handle requests |
| `class-hostaway-post-type.php` | Data model | Register CPT, save listings, handle metadata |
| `class-hostaway-admin.php` | Admin interface | Settings page, sync page, AJAX handlers |
| `class-hostaway-public.php` | Frontend display | Shortcodes, template rendering |

---

## Core Components

### 1. Hostaway_Integration (Main Class)

**Location:** `includes/class-hostaway-integration.php`

**Responsibilities:**
- Load all plugin dependencies
- Define admin and public hooks
- Initialize plugin components
- Manage plugin lifecycle

**Key Methods:**
```php
__construct()                 // Initialize plugin
load_dependencies()           // Require all class files
define_admin_hooks()         // Register admin hooks
define_public_hooks()        // Register public hooks
run()                        // Execute plugin via loader
```

**Initialization Flow:**
```
1. Plugin file loads class
2. Constructor initializes properties
3. load_dependencies() includes all files
4. define_admin_hooks() registers admin functionality
5. define_public_hooks() registers frontend functionality
6. run() executes the loader
```

### 2. Hostaway_Loader (Hook Manager)

**Location:** `includes/class-hostaway-loader.php`

**Responsibilities:**
- Store actions and filters in arrays
- Register all hooks with WordPress in one place
- Decouple hook registration from business logic

**Key Methods:**
```php
add_action($hook, $component, $callback, $priority, $accepted_args)
add_filter($hook, $component, $callback, $priority, $accepted_args)
run()  // Register all stored hooks with WordPress
```

**Usage Pattern:**
```php
$this->loader->add_action('init', $post_type, 'register_post_type');
$this->loader->add_filter('the_content', $plugin_public, 'filter_content');
```

### 3. Hostaway_API_Client (API Integration)

**Location:** `includes/class-hostaway-api-client.php`

**Responsibilities:**
- Handle OAuth 2.0 authentication
- Manage access tokens (with caching)
- Make API requests to Hostaway
- Handle errors and logging

**Authentication Flow:**
```
1. Check for cached access token
2. If none, request new token from /v1/accessTokens
3. Send Account ID and Secret Key
4. Receive access token
5. Cache token for 23 hours (expires in 24)
6. Use token in Authorization header for all requests
```

**Key Methods:**
```php
get_access_token()                           // OAuth token retrieval
make_request($endpoint, $method, $data)      // Generic API request
test_connection()                            // Verify credentials
get_listings($limit, $offset)                // Fetch listings (paginated)
get_listing($listing_id)                     // Get single listing
get_all_listings()                           // Fetch ALL listings (auto-paginated)
clear_token_cache()                          // Invalidate cached token
```

**API Endpoints Used:**
| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/v1/accessTokens` | POST | Get OAuth token |
| `/v1/listings` | GET | Fetch all listings |
| `/v1/listings/{id}` | GET | Get single listing |
| `/v1/listings/{id}/photos` | GET | Get listing photos |
| `/v1/listings/{id}/amenities` | GET | Get amenities |
| `/v1/listings/{id}/calendar` | GET | Get availability |

**Error Handling:**
- Returns `WP_Error` objects on failure
- Logs errors to WordPress error log
- Handles HTTP status codes (401, 403, 404, 500, etc.)

### 4. Hostaway_Post_Type (Data Model)

**Location:** `includes/class-hostaway-post-type.php`

**Responsibilities:**
- Register `hostaway_listing` custom post type
- Register taxonomies (property_type, location, amenity)
- Convert API data to WordPress posts
- Save metadata and images

**Custom Post Type Configuration:**
```php
Post Type: hostaway_listing
Supports: title, editor, thumbnail, excerpt
Public: true
Has Archive: true
Menu Icon: dashicons-admin-home
Rewrite Slug: listing
```

**Taxonomies:**
| Taxonomy | Hierarchical | Purpose |
|----------|-------------|---------|
| `property_type` | Yes | Categorize by type (apartment, house, etc.) |
| `location` | Yes | Organize by location (city, region) |
| `amenity` | No | Tag with amenities (WiFi, pool, etc.) |

**Key Methods:**
```php
register_post_type()                          // Register CPT and taxonomies
save_listing_from_api($listing_data)         // Convert API data to post
set_featured_image_from_url($post_id, $url)  // Download and set image
```

**Metadata Saved:**
```php
_hostaway_id           // Original Hostaway listing ID
_hostaway_data         // Full API response (JSON)
_bedrooms              // Number of bedrooms
_bathrooms             // Number of bathrooms
_accommodates          // Max guests
_address               // Street address
_city                  // City
_country               // Country
_zipcode               // Postal code
_latitude              // GPS latitude
_longitude             // GPS longitude
_price                 // Base daily rate
_currency              // Currency code (USD, EUR, etc.)
_image_gallery         // Array of image URLs
```

### 5. Hostaway_Admin (Admin Interface)

**Location:** `admin/class-hostaway-admin.php`

**Responsibilities:**
- Create admin menu pages
- Handle settings registration
- Process AJAX requests
- Sync listings manually

**Admin Pages:**
1. **Settings** (`hostaway-settings`)
   - API credentials form
   - Auto-sync configuration
   - Connection testing

2. **Sync Listings** (`hostaway-sync`)
   - Manual sync trigger
   - Last sync timestamp
   - Progress feedback

**Settings Registered:**
```php
hostaway_account_id              // API Account ID
hostaway_secret_key              // API Secret Key
hostaway_auto_sync_interval      // Sync frequency (hourly, daily, etc.)
hostaway_sync_enabled            // Enable/disable auto-sync
hostaway_last_sync              // Timestamp of last sync
```

**AJAX Actions:**
```php
wp_ajax_hostaway_test_connection   // Test API credentials
wp_ajax_hostaway_sync_listings     // Manually sync all listings
```

**Key Methods:**
```php
add_admin_menu()              // Add submenu pages
register_settings()           // Register WordPress settings
display_settings_page()       // Render settings page
display_sync_page()          // Render sync page
test_connection()            // AJAX: test API
sync_listings()              // AJAX: sync all listings
```

### 6. Hostaway_Public (Frontend Display)

**Location:** `public/class-hostaway-public.php`

**Responsibilities:**
- Register shortcodes
- Render listing displays
- Load templates
- Handle frontend assets

**Shortcodes Registered:**
1. `[hostaway_listings]` - Display multiple listings
2. `[hostaway_listing]` - Display single listing

**Template Hierarchy:**
```
1. Theme: your-theme/hostaway-integration/listing-card.php
2. Plugin: plugins/hostaway-integration/templates/listing-card.php
```

**Key Methods:**
```php
register_shortcodes()                    // Register all shortcodes
listings_shortcode($atts)               // Handle [hostaway_listings]
single_listing_shortcode($atts)         // Handle [hostaway_listing]
render_listing_card($post_id)           // Render grid item
render_single_listing($post_id)         // Render detail page
locate_template($template_name)         // Find template file
```

---

## Database Schema

### Custom Post Type

**Table:** `wp_posts`

| Field | Type | Purpose |
|-------|------|---------|
| `ID` | bigint(20) | WordPress post ID |
| `post_type` | varchar(20) | Always 'hostaway_listing' |
| `post_title` | text | Listing name |
| `post_content` | longtext | Full description |
| `post_excerpt` | text | Short summary |
| `post_status` | varchar(20) | publish, draft, etc. |

### Post Meta

**Table:** `wp_postmeta`

| meta_key | meta_value Type | Description |
|----------|----------------|-------------|
| `_hostaway_id` | int | Hostaway listing ID |
| `_hostaway_data` | serialized array | Full API response |
| `_bedrooms` | int | Number of bedrooms |
| `_bathrooms` | float | Number of bathrooms |
| `_accommodates` | int | Maximum guests |
| `_address` | string | Street address |
| `_city` | string | City name |
| `_country` | string | Country name |
| `_zipcode` | string | Postal code |
| `_latitude` | float | GPS latitude |
| `_longitude` | float | GPS longitude |
| `_price` | float | Base daily rate |
| `_currency` | string | Currency code |
| `_image_gallery` | serialized array | Image URLs |

### Taxonomies

**Table:** `wp_term_taxonomy`

| Taxonomy | Type | Example Terms |
|----------|------|---------------|
| `property_type` | hierarchical | Apartment, House, Villa, Condo |
| `location` | hierarchical | Miami, Los Angeles, New York |
| `amenity` | non-hierarchical | WiFi, Pool, Parking, Kitchen |

### WordPress Options

**Table:** `wp_options`

| option_name | option_value | Purpose |
|------------|--------------|---------|
| `hostaway_account_id` | string | API Account ID |
| `hostaway_secret_key` | string | API Secret Key |
| `hostaway_auto_sync_interval` | string | Sync frequency |
| `hostaway_sync_enabled` | yes/no | Auto-sync enabled |
| `hostaway_last_sync` | datetime | Last sync timestamp |

### Transients

**Table:** `wp_options` (with timeout)

| transient | value | ttl | Purpose |
|-----------|-------|-----|---------|
| `hostaway_access_token` | string | 23 hours | Cached OAuth token |

---

## API Integration

### OAuth 2.0 Flow

```
┌──────────────┐                           ┌──────────────┐
│   Plugin     │                           │  Hostaway    │
│              │                           │     API      │
└──────┬───────┘                           └──────┬───────┘
       │                                          │
       │  POST /v1/accessTokens                   │
       │  grant_type=client_credentials           │
       │  client_id={account_id}                  │
       │  client_secret={secret_key}              │
       │  scope=general                           │
       ├─────────────────────────────────────────>│
       │                                          │
       │                                          │
       │  200 OK                                  │
       │  {                                       │
       │    "access_token": "eyJ...",             │
       │    "token_type": "Bearer",               │
       │    "expires_in": 86400                   │
       │  }                                       │
       │<─────────────────────────────────────────┤
       │                                          │
       │  Cache token for 23 hours                │
       │                                          │
       │                                          │
       │  GET /v1/listings                        │
       │  Authorization: Bearer eyJ...            │
       ├─────────────────────────────────────────>│
       │                                          │
       │  200 OK                                  │
       │  { "result": [...listings...] }          │
       │<─────────────────────────────────────────┤
       │                                          │
```

### API Response Structure

**Listings Response:**
```json
{
  "status": "success",
  "result": [
    {
      "id": 12345,
      "name": "Beautiful Beach House",
      "description": "Full description...",
      "publicDescription": {
        "summary": "Short summary..."
      },
      "bedrooms": 3,
      "bathrooms": 2.5,
      "accommodates": 6,
      "propertyTypeName": "House",
      "address": "123 Beach Road",
      "city": "Miami",
      "country": "United States",
      "zipcode": "33139",
      "latitude": 25.7617,
      "longitude": -80.1918,
      "baseDailyRate": 250.00,
      "currencyCode": "USD",
      "thumbnailUrl": "https://...",
      "images": [
        {
          "url": "https://...",
          "caption": "Living room"
        }
      ]
    }
  ]
}
```

### Pagination

The API uses limit/offset pagination:

```php
// Page 1: GET /v1/listings?limit=100&offset=0
// Page 2: GET /v1/listings?limit=100&offset=100
// Page 3: GET /v1/listings?limit=100&offset=200
```

The `get_all_listings()` method automatically handles pagination:

```php
public function get_all_listings() {
    $all_listings = array();
    $limit = 100;
    $offset = 0;
    $has_more = true;

    while ($has_more) {
        $response = $this->get_listings($limit, $offset);

        if (count($response['result']) < $limit) {
            $has_more = false;
        } else {
            $offset += $limit;
        }

        $all_listings = array_merge($all_listings, $response['result']);
    }

    return $all_listings;
}
```

### Rate Limiting

Hostaway API rate limits are not explicitly documented. The plugin implements:

- Token caching (reduces auth requests)
- No built-in request throttling (rely on Hostaway's limits)
- Error handling for 429 (Too Many Requests) status codes

**Best Practices:**
- Use auto-sync instead of frequent manual syncs
- Sync daily rather than hourly for most use cases
- Monitor error logs for rate limit errors

---

## Hooks & Filters

### Available Actions

#### Plugin Lifecycle
```php
// After plugin activation
do_action('hostaway_plugin_activated');

// Before plugin deactivation
do_action('hostaway_plugin_deactivating');
```

#### Sync Events
```php
// Before sync starts
do_action('hostaway_sync_start');

// After individual listing saved
do_action('hostaway_listing_saved', $post_id, $listing_data);

// After sync completes
do_action('hostaway_sync_complete', $synced_count, $errors);

// On sync error
do_action('hostaway_sync_error', $error);
```

#### Data Events
```php
// Before listing data is saved
do_action('hostaway_before_save_listing', $listing_data);

// After listing data is saved
do_action('hostaway_after_save_listing', $post_id, $listing_data);

// When listing is deleted
do_action('hostaway_listing_deleted', $post_id);
```

### Available Filters

#### Data Filtering
```php
// Modify listing data before saving
apply_filters('hostaway_listing_data', $listing_data, $post_id);

// Modify post data before insert/update
apply_filters('hostaway_post_data', $post_data, $listing_data);

// Modify meta data before saving
apply_filters('hostaway_meta_data', $meta_data, $listing_data);
```

#### Template Filtering
```php
// Modify template path
apply_filters('hostaway_template_path', $path, $template_name);

// Modify template variables
apply_filters('hostaway_template_vars', $vars, $template_name, $post_id);

// Modify shortcode output
apply_filters('hostaway_listings_output', $html, $atts, $listings);

// Modify single listing output
apply_filters('hostaway_listing_output', $html, $post_id);
```

#### Query Filtering
```php
// Modify WP_Query args for listings
apply_filters('hostaway_listings_query_args', $args, $atts);

// Modify API request parameters
apply_filters('hostaway_api_request_args', $args, $endpoint);
```

#### Display Filtering
```php
// Modify grid columns class
apply_filters('hostaway_grid_columns_class', $class, $columns);

// Modify listing card HTML
apply_filters('hostaway_listing_card_html', $html, $post_id);

// Modify price display
apply_filters('hostaway_price_display', $price_html, $price, $currency);
```

### Hook Usage Examples

**Example 1: Add custom field to listings**
```php
add_action('hostaway_after_save_listing', function($post_id, $listing_data) {
    if (isset($listing_data['customField'])) {
        update_post_meta($post_id, '_custom_field', $listing_data['customField']);
    }
}, 10, 2);
```

**Example 2: Modify listing data before save**
```php
add_filter('hostaway_listing_data', function($listing_data, $post_id) {
    // Add custom processing
    $listing_data['processed'] = true;
    return $listing_data;
}, 10, 2);
```

**Example 3: Send notification after sync**
```php
add_action('hostaway_sync_complete', function($synced_count, $errors) {
    wp_mail(
        get_option('admin_email'),
        'Hostaway Sync Complete',
        "Synced {$synced_count} listings."
    );
}, 10, 2);
```

**Example 4: Custom price display**
```php
add_filter('hostaway_price_display', function($price_html, $price, $currency) {
    return '<span class="custom-price">' . $currency . ' ' . number_format($price) . '</span>';
}, 10, 3);
```

---

## Shortcodes

### [hostaway_listings]

Display multiple listings in a grid layout.

**Syntax:**
```
[hostaway_listings attribute="value" ...]
```

**Attributes:**

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `limit` | int | -1 | Number of listings to display (-1 = all) |
| `columns` | int | 3 | Grid columns (2, 3, or 4) |
| `property_type` | string | '' | Filter by property type slug |
| `location` | string | '' | Filter by location slug |
| `bedrooms` | int | '' | Filter by number of bedrooms |
| `orderby` | string | 'date' | Sort by: date, title, price |
| `order` | string | 'DESC' | Sort order: ASC or DESC |

**Examples:**

```php
// Basic grid
[hostaway_listings]

// 4-column grid, limit 8
[hostaway_listings columns="4" limit="8"]

// Only apartments
[hostaway_listings property_type="apartment"]

// 2+ bedroom properties in Miami
[hostaway_listings location="miami" bedrooms="2"]

// Sort by price (low to high)
[hostaway_listings orderby="price" order="ASC"]

// Combining multiple filters
[hostaway_listings property_type="villa" bedrooms="3" orderby="price" limit="6"]
```

**Implementation:**
```php
public function listings_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit'         => -1,
        'columns'       => 3,
        'property_type' => '',
        'location'      => '',
        'bedrooms'      => '',
        'orderby'       => 'date',
        'order'         => 'DESC',
    ), $atts, 'hostaway_listings');

    $args = array(
        'post_type'      => 'hostaway_listing',
        'posts_per_page' => intval($atts['limit']),
        'orderby'        => $atts['orderby'],
        'order'          => $atts['order'],
    );

    // Add taxonomy and meta queries...
    $query = new WP_Query($args);

    // Render output...
}
```

### [hostaway_listing]

Display a single listing.

**Syntax:**
```
[hostaway_listing id="123"]
```

**Attributes:**

| Attribute | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | int | Yes | WordPress post ID of the listing |

**Examples:**

```php
// Display listing with ID 123
[hostaway_listing id="123"]

// In PHP template
<?php echo do_shortcode('[hostaway_listing id="' . $post_id . '"]'); ?>
```

**Implementation:**
```php
public function single_listing_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => '',
    ), $atts, 'hostaway_listing');

    if (empty($atts['id'])) {
        return '<p>Please provide a listing ID.</p>';
    }

    ob_start();
    $this->render_single_listing(intval($atts['id']));
    return ob_get_clean();
}
```

---

## Template System

### Template Hierarchy

When rendering templates, the plugin searches in this order:

1. **Theme Override:** `your-theme/hostaway-integration/{template-name}.php`
2. **Plugin Default:** `hostaway-wordpress-integration/templates/{template-name}.php`

### Available Templates

#### 1. listing-card.php

**Purpose:** Render individual listing in grid view

**Available Variables:**
```php
$post_id         // int: Post ID
$bedrooms        // int: Number of bedrooms
$bathrooms       // float: Number of bathrooms
$accommodates    // int: Max guests
$price           // float: Base daily rate
$currency        // string: Currency code
$city            // string: City name
```

**Default Template:**
```php
<div class="hostaway-listing-card">
    <?php if (has_post_thumbnail($post_id)): ?>
        <div class="listing-thumbnail">
            <a href="<?php the_permalink($post_id); ?>">
                <?php echo get_the_post_thumbnail($post_id, 'large'); ?>
            </a>
        </div>
    <?php endif; ?>

    <div class="listing-content">
        <h3 class="listing-title">
            <a href="<?php the_permalink($post_id); ?>">
                <?php echo get_the_title($post_id); ?>
            </a>
        </h3>
        <!-- More content... -->
    </div>
</div>
```

#### 2. single-listing.php

**Purpose:** Render full listing detail page

**Available Variables:**
```php
$post              // WP_Post object
$post_id           // int: Post ID
$bedrooms          // int: Number of bedrooms
$bathrooms         // float: Number of bathrooms
$accommodates      // int: Max guests
$price             // float: Base daily rate
$currency          // string: Currency code
$address           // string: Street address
$city              // string: City
$country           // string: Country
$latitude          // float: GPS latitude
$longitude         // float: GPS longitude
$image_gallery     // array: Image URLs
```

**Default Template:**
```php
<div class="hostaway-single-listing">
    <h1 class="listing-title"><?php echo $post->post_title; ?></h1>

    <!-- Image Gallery -->
    <?php if (!empty($image_gallery)): ?>
        <div class="listing-gallery">
            <?php foreach ($image_gallery as $image_url): ?>
                <img src="<?php echo esc_url($image_url); ?>" />
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- More content... -->
</div>
```

### Custom Template Example

**Create:** `your-theme/hostaway-integration/listing-card.php`

```php
<?php
/**
 * Custom Listing Card Template
 */
?>
<div class="my-custom-listing-card">
    <div class="listing-image-wrapper">
        <?php if (has_post_thumbnail($post_id)): ?>
            <?php echo get_the_post_thumbnail($post_id, 'full'); ?>
        <?php endif; ?>

        <?php if ($price): ?>
            <div class="price-overlay">
                <?php echo $currency . $price; ?>/night
            </div>
        <?php endif; ?>
    </div>

    <div class="listing-info">
        <h2><?php echo get_the_title($post_id); ?></h2>

        <div class="listing-specs">
            <?php if ($bedrooms): ?>
                <span><?php echo $bedrooms; ?> Beds</span>
            <?php endif; ?>

            <?php if ($bathrooms): ?>
                <span><?php echo $bathrooms; ?> Baths</span>
            <?php endif; ?>
        </div>

        <a href="<?php echo get_permalink($post_id); ?>" class="btn-view">
            View Details
        </a>
    </div>
</div>
```

### Template Functions

**Get Listing Meta:**
```php
$bedrooms = get_post_meta($post_id, '_bedrooms', true);
$price = get_post_meta($post_id, '_price', true);
$gallery = get_post_meta($post_id, '_image_gallery', true);
```

**Get Taxonomies:**
```php
$property_types = get_the_terms($post_id, 'property_type');
$locations = get_the_terms($post_id, 'location');
$amenities = get_the_terms($post_id, 'amenity');
```

**Check if Listing:**
```php
if (get_post_type($post_id) === 'hostaway_listing') {
    // This is a Hostaway listing
}
```

---

## Admin Interface

### Settings Page

**URL:** `wp-admin/edit.php?post_type=hostaway_listing&page=hostaway-settings`

**Fields:**
1. Account ID (text input)
2. Secret Key (password input)
3. Auto Sync Interval (dropdown: hourly, twicedaily, daily, weekly)
4. Enable Auto Sync (checkbox)

**Validation:**
- Account ID: Required, alphanumeric
- Secret Key: Required, min 20 characters
- Settings saved to `wp_options` table

**JavaScript Functionality:**
```javascript
// Test Connection button
$('#test-connection').on('click', function() {
    $.ajax({
        url: hostawayAdmin.ajax_url,
        type: 'POST',
        data: {
            action: 'hostaway_test_connection',
            nonce: hostawayAdmin.nonce
        },
        success: function(response) {
            // Show success/error message
        }
    });
});
```

### Sync Page

**URL:** `wp-admin/edit.php?post_type=hostaway_listing&page=hostaway-sync`

**Features:**
- Display last sync timestamp
- Manual sync trigger button
- Progress indicator during sync
- Success/error reporting
- Error details (if any)

**Sync Process:**
```
1. User clicks "Sync All Listings"
2. JavaScript sends AJAX request
3. PHP increases execution time limit
4. API client fetches all listings (paginated)
5. Each listing processed and saved
6. Success/error counts tracked
7. Last sync timestamp updated
8. Results returned to JavaScript
9. UI updated with results
```

**JavaScript Functionality:**
```javascript
// Sync Listings button
$('#sync-listings').on('click', function() {
    $('#sync-progress').show();

    $.ajax({
        url: hostawayAdmin.ajax_url,
        type: 'POST',
        data: {
            action: 'hostaway_sync_listings',
            nonce: hostawayAdmin.nonce
        },
        success: function(response) {
            // Display sync results
        },
        complete: function() {
            $('#sync-progress').hide();
        }
    });
});
```

### Auto-Sync (Cron)

**Cron Hook:** `hostaway_auto_sync`

**Registration:**
```php
// On plugin activation
if (!wp_next_scheduled('hostaway_auto_sync')) {
    wp_schedule_event(time(), 'daily', 'hostaway_auto_sync');
}
```

**Implementation:**
```php
// Would need to add this action in future version
add_action('hostaway_auto_sync', function() {
    $api_client = new Hostaway_API_Client();
    $listings = $api_client->get_all_listings();

    foreach ($listings as $listing) {
        Hostaway_Post_Type::save_listing_from_api($listing);
    }

    update_option('hostaway_last_sync', current_time('mysql'));
});
```

**Available Intervals:**
- `hourly`: Every hour
- `twicedaily`: Every 12 hours
- `daily`: Once per day (recommended)
- `weekly`: Once per week

---

## Frontend Display

### CSS Architecture

**Grid System:**
```css
.hostaway-listings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
}

.hostaway-listings-grid.columns-2 {
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
}

.hostaway-listings-grid.columns-3 {
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
}

.hostaway-listings-grid.columns-4 {
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
}
```

**Responsive Breakpoints:**
```css
@media (max-width: 768px) {
    .hostaway-listings-grid {
        grid-template-columns: 1fr;  /* Single column on mobile */
    }
}
```

**Component Structure:**
```
.hostaway-listing-card
├── .listing-thumbnail
│   └── img (with hover scale effect)
├── .listing-content
    ├── .listing-title
    ├── .listing-location
    ├── .listing-details (flex row)
    │   └── .detail-item (icon + text)
    ├── .listing-price
    ├── .listing-excerpt
    └── .listing-button
```

### JavaScript Features

**Current Implementation:**
- Image click handlers (placeholder for lightbox)
- Map initialization (placeholder for mapping library)
- Smooth scrolling for anchor links

**Future Enhancements:**
```javascript
// Lightbox integration
$('.listing-gallery img').magnificPopup({
    type: 'image',
    gallery: { enabled: true }
});

// Map integration (Google Maps)
var map = new google.maps.Map(element, {
    center: { lat: latitude, lng: longitude },
    zoom: 15
});

// Map integration (Leaflet)
var map = L.map('map').setView([latitude, longitude], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
```

### Performance Optimization

**Image Loading:**
```html
<!-- Add lazy loading to images -->
<img loading="lazy" src="..." alt="..." />
```

**CSS Critical Path:**
```php
// Inline critical CSS in head
add_action('wp_head', function() {
    if (has_shortcode(get_the_content(), 'hostaway_listings')) {
        echo '<style>' . file_get_contents(PLUGIN_DIR . 'public/css/critical.css') . '</style>';
    }
});
```

**Caching:**
```php
// Cache listing query results
$cache_key = 'hostaway_listings_' . md5(serialize($atts));
$listings = get_transient($cache_key);

if (false === $listings) {
    $query = new WP_Query($args);
    set_transient($cache_key, $query, HOUR_IN_SECONDS);
}
```

---

## Development Guide

### Local Development Setup

**Requirements:**
- Local WordPress installation (Local, MAMP, or similar)
- PHP 7.2+
- Composer (optional, for future dependencies)
- Node.js (optional, for asset building)

**Setup Steps:**

1. Clone repository
```bash
cd /path/to/wordpress/wp-content/plugins/
git clone <repo-url> hostaway-wordpress-integration
```

2. Activate plugin in WordPress admin

3. Add test credentials in Settings

4. Enable WordPress debugging in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### Coding Standards

**Follow WordPress Coding Standards:**
- File naming: `class-plugin-name.php` (lowercase, hyphens)
- Class naming: `Plugin_Name` (capitalized, underscores)
- Function naming: `plugin_name_function()` (lowercase, underscores)
- Variable naming: `$variable_name` (lowercase, underscores)
- Indentation: Tabs (not spaces)
- Line length: Max 100 characters

**Security Best Practices:**
1. Sanitize input: `sanitize_text_field()`, `sanitize_email()`, etc.
2. Escape output: `esc_html()`, `esc_attr()`, `esc_url()`
3. Validate data: Check types, ranges, formats
4. Use nonces: `wp_create_nonce()`, `wp_verify_nonce()`
5. Check capabilities: `current_user_can('manage_options')`

**Example:**
```php
// GOOD
$account_id = sanitize_text_field($_POST['account_id']);
echo '<input value="' . esc_attr($account_id) . '" />';

// BAD
$account_id = $_POST['account_id'];
echo '<input value="' . $account_id . '" />';
```

### Adding New Features

**Example: Add "Featured" Listing Support**

1. **Add meta field in sync:**
```php
// In class-hostaway-post-type.php, save_listing_from_api()
if (isset($listing_data['featured'])) {
    update_post_meta($post_id, '_is_featured', (bool) $listing_data['featured']);
}
```

2. **Add shortcode attribute:**
```php
// In class-hostaway-public.php, listings_shortcode()
$atts = shortcode_atts(array(
    // ... existing attributes
    'featured_only' => false,
), $atts);

if ($atts['featured_only']) {
    $args['meta_query'][] = array(
        'key'   => '_is_featured',
        'value' => '1',
    );
}
```

3. **Add admin column:**
```php
// In class-hostaway-admin.php
add_filter('manage_hostaway_listing_posts_columns', function($columns) {
    $columns['featured'] = 'Featured';
    return $columns;
});

add_action('manage_hostaway_listing_posts_custom_column', function($column, $post_id) {
    if ($column === 'featured') {
        $is_featured = get_post_meta($post_id, '_is_featured', true);
        echo $is_featured ? '⭐ Yes' : 'No';
    }
}, 10, 2);
```

4. **Update templates:**
```php
// In templates/listing-card.php
<?php
$is_featured = get_post_meta($post_id, '_is_featured', true);
if ($is_featured):
?>
    <span class="featured-badge">Featured</span>
<?php endif; ?>
```

### Debugging Tips

**Enable Query Monitor Plugin:**
- Shows database queries
- Tracks hooks and filters
- Displays PHP errors
- Monitors API requests

**Check Error Logs:**
```bash
tail -f /path/to/wordpress/wp-content/debug.log
```

**Add Debug Output:**
```php
// Temporary debugging (remove before commit)
error_log('Listing Data: ' . print_r($listing_data, true));
```

**Test API Connection:**
```bash
# Using curl
curl -X POST https://api.hostaway.com/v1/accessTokens \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=client_credentials" \
  -d "client_id=YOUR_ACCOUNT_ID" \
  -d "client_secret=YOUR_SECRET_KEY" \
  -d "scope=general"
```

### Testing

**Manual Testing Checklist:**
- [ ] Plugin activates without errors
- [ ] Settings page displays correctly
- [ ] API connection test works
- [ ] Manual sync imports listings
- [ ] Listings display in admin
- [ ] Shortcode renders on frontend
- [ ] Templates are customizable
- [ ] Auto-sync cron job is scheduled
- [ ] Plugin deactivates cleanly

**Test Data:**
- Create test listings in Hostaway staging environment
- Use varied data (different property types, locations, etc.)
- Test with 1, 10, 100, 1000+ listings
- Test with missing/incomplete data

---

## Code Examples

### Example 1: Custom Query for Luxury Properties

```php
// Get all luxury properties (price > $500/night)
$args = array(
    'post_type'      => 'hostaway_listing',
    'posts_per_page' => -1,
    'meta_query'     => array(
        array(
            'key'     => '_price',
            'value'   => 500,
            'type'    => 'NUMERIC',
            'compare' => '>',
        ),
    ),
    'orderby'        => 'meta_value_num',
    'meta_key'       => '_price',
    'order'          => 'DESC',
);

$luxury_listings = new WP_Query($args);

if ($luxury_listings->have_posts()) {
    while ($luxury_listings->have_posts()) {
        $luxury_listings->the_post();
        // Display listing
    }
}
wp_reset_postdata();
```

### Example 2: Create Custom Listing Filter Widget

```php
<?php
class Hostaway_Filter_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'hostaway_filter_widget',
            'Hostaway Listing Filters',
            array('description' => 'Filter listings by criteria')
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        ?>
        <form method="get" action="">
            <h3>Filter Listings</h3>

            <!-- Property Type Filter -->
            <select name="property_type">
                <option value="">All Types</option>
                <?php
                $types = get_terms(array(
                    'taxonomy'   => 'property_type',
                    'hide_empty' => true,
                ));
                foreach ($types as $type) {
                    echo '<option value="' . $type->slug . '">' . $type->name . '</option>';
                }
                ?>
            </select>

            <!-- Bedrooms Filter -->
            <select name="bedrooms">
                <option value="">Any Bedrooms</option>
                <option value="1">1+</option>
                <option value="2">2+</option>
                <option value="3">3+</option>
                <option value="4">4+</option>
            </select>

            <!-- Price Range Filter -->
            <input type="number" name="min_price" placeholder="Min Price" />
            <input type="number" name="max_price" placeholder="Max Price" />

            <button type="submit">Filter</button>
        </form>
        <?php
        echo $args['after_widget'];
    }
}

// Register widget
add_action('widgets_init', function() {
    register_widget('Hostaway_Filter_Widget');
});
```

### Example 3: Add Custom Listing Endpoint to REST API

```php
// Add REST API endpoint for listings
add_action('rest_api_init', function() {
    register_rest_route('hostaway/v1', '/listings', array(
        'methods'  => 'GET',
        'callback' => 'hostaway_rest_get_listings',
        'permission_callback' => '__return_true',
    ));
});

function hostaway_rest_get_listings($request) {
    $params = $request->get_params();

    $args = array(
        'post_type'      => 'hostaway_listing',
        'posts_per_page' => isset($params['per_page']) ? $params['per_page'] : 10,
        'paged'          => isset($params['page']) ? $params['page'] : 1,
    );

    $query = new WP_Query($args);
    $listings = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            $listings[] = array(
                'id'          => $post_id,
                'title'       => get_the_title(),
                'description' => get_the_content(),
                'bedrooms'    => get_post_meta($post_id, '_bedrooms', true),
                'bathrooms'   => get_post_meta($post_id, '_bathrooms', true),
                'price'       => get_post_meta($post_id, '_price', true),
                'currency'    => get_post_meta($post_id, '_currency', true),
                'city'        => get_post_meta($post_id, '_city', true),
                'image'       => get_the_post_thumbnail_url($post_id, 'large'),
            );
        }
    }
    wp_reset_postdata();

    return new WP_REST_Response($listings, 200);
}

// Access via: /wp-json/hostaway/v1/listings?per_page=20
```

### Example 4: Sync Individual Listing by ID

```php
// Add function to sync single listing
function hostaway_sync_single_listing($hostaway_id) {
    $api_client = new Hostaway_API_Client();
    $listing = $api_client->get_listing($hostaway_id);

    if (is_wp_error($listing)) {
        return $listing;
    }

    $post_id = Hostaway_Post_Type::save_listing_from_api($listing);

    return $post_id;
}

// Usage:
$result = hostaway_sync_single_listing(12345);

if (is_wp_error($result)) {
    echo 'Error: ' . $result->get_error_message();
} else {
    echo 'Synced! WordPress Post ID: ' . $result;
}
```

### Example 5: Custom Email Notification on New Listing

```php
add_action('hostaway_listing_saved', function($post_id, $listing_data) {
    // Check if this is a new listing (not an update)
    $is_new = get_post_meta($post_id, '_first_sync', true) !== 'yes';

    if ($is_new) {
        // Mark as synced
        update_post_meta($post_id, '_first_sync', 'yes');

        // Send email notification
        $to = get_option('admin_email');
        $subject = 'New Listing Added: ' . get_the_title($post_id);
        $message = "A new listing has been added:\n\n";
        $message .= "Title: " . get_the_title($post_id) . "\n";
        $message .= "Bedrooms: " . get_post_meta($post_id, '_bedrooms', true) . "\n";
        $message .= "Price: " . get_post_meta($post_id, '_price', true) . "\n";
        $message .= "View: " . get_permalink($post_id) . "\n";

        wp_mail($to, $subject, $message);
    }
}, 10, 2);
```

### Example 6: Add Map to Single Listing with Google Maps

```php
// In your theme's functions.php
add_action('wp_footer', function() {
    if (is_singular('hostaway_listing')) {
        $post_id = get_the_ID();
        $lat = get_post_meta($post_id, '_latitude', true);
        $lng = get_post_meta($post_id, '_longitude', true);

        if ($lat && $lng):
        ?>
        <script>
        function initMap() {
            var location = {lat: <?php echo $lat; ?>, lng: <?php echo $lng; ?>};
            var map = new google.maps.Map(document.getElementById('listing-map'), {
                zoom: 15,
                center: location
            });
            var marker = new google.maps.Marker({
                position: location,
                map: map
            });
        }
        </script>
        <script async defer
            src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap">
        </script>
        <?php
        endif;
    }
});
```

### Example 7: Create Shortcode for Featured Listings Slider

```php
// Add to theme's functions.php or custom plugin
function hostaway_featured_slider_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => 5,
    ), $atts);

    $args = array(
        'post_type'      => 'hostaway_listing',
        'posts_per_page' => $atts['limit'],
        'meta_query'     => array(
            array(
                'key'   => '_is_featured',
                'value' => '1',
            ),
        ),
    );

    $query = new WP_Query($args);

    ob_start();

    if ($query->have_posts()):
    ?>
    <div class="hostaway-featured-slider owl-carousel">
        <?php while ($query->have_posts()): $query->the_post(); ?>
            <div class="slide">
                <?php the_post_thumbnail('large'); ?>
                <h3><?php the_title(); ?></h3>
                <a href="<?php the_permalink(); ?>">View Details</a>
            </div>
        <?php endwhile; ?>
    </div>
    <script>
    jQuery('.hostaway-featured-slider').owlCarousel({
        items: 1,
        loop: true,
        autoplay: true
    });
    </script>
    <?php
    endif;

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('hostaway_featured_slider', 'hostaway_featured_slider_shortcode');

// Usage: [hostaway_featured_slider limit="5"]
```

---

## Troubleshooting Guide

### Common Issues

#### 1. "Connection Failed" Error

**Symptoms:** Test connection fails, sync doesn't work

**Causes:**
- Incorrect Account ID or Secret Key
- API not activated in Hostaway dashboard
- Server blocking outbound HTTPS requests
- Firewall/security plugin blocking requests

**Solutions:**
```php
// 1. Verify credentials
$account_id = get_option('hostaway_account_id');
$secret_key = get_option('hostaway_secret_key');
var_dump($account_id, $secret_key); // Check they're saved correctly

// 2. Test direct API call
$response = wp_remote_post('https://api.hostaway.com/v1/accessTokens', array(
    'body' => array(
        'grant_type'    => 'client_credentials',
        'client_id'     => $account_id,
        'client_secret' => $secret_key,
        'scope'         => 'general',
    ),
));

var_dump($response); // Check raw response

// 3. Check if allow_url_fopen is enabled
var_dump(ini_get('allow_url_fopen'));

// 4. Clear token cache
delete_transient('hostaway_access_token');
```

#### 2. Images Not Importing

**Symptoms:** Listings sync but no featured images

**Causes:**
- `allow_url_fopen` disabled
- Insufficient file permissions
- Memory limit too low
- Image URLs invalid/expired

**Solutions:**
```php
// 1. Check PHP settings
var_dump(
    ini_get('allow_url_fopen'),
    ini_get('upload_max_filesize'),
    ini_get('memory_limit')
);

// 2. Test image download manually
$url = 'https://example.com/image.jpg';
$temp = download_url($url);
var_dump($temp, is_wp_error($temp));

// 3. Check upload directory permissions
$upload_dir = wp_upload_dir();
var_dump($upload_dir, is_writable($upload_dir['path']));

// 4. Increase memory limit in wp-config.php
define('WP_MEMORY_LIMIT', '256M');
```

#### 3. Sync Timeout

**Symptoms:** Sync starts but never completes

**Causes:**
- Too many listings (1000+)
- Low PHP execution time limit
- Server timeout
- Memory exhaustion

**Solutions:**
```php
// 1. Increase execution time in sync method
set_time_limit(300); // 5 minutes

// 2. Increase memory limit
ini_set('memory_limit', '512M');

// 3. Batch sync instead of all at once
function hostaway_batch_sync($batch_size = 50) {
    $offset = get_option('hostaway_sync_offset', 0);
    $api_client = new Hostaway_API_Client();
    $listings = $api_client->get_listings($batch_size, $offset);

    foreach ($listings['result'] as $listing) {
        Hostaway_Post_Type::save_listing_from_api($listing);
    }

    update_option('hostaway_sync_offset', $offset + $batch_size);

    // If more to sync, schedule next batch
    if (count($listings['result']) === $batch_size) {
        wp_schedule_single_event(time() + 60, 'hostaway_batch_sync');
    } else {
        delete_option('hostaway_sync_offset');
    }
}
```

#### 4. Shortcode Not Displaying

**Symptoms:** Shortcode appears as text or shows nothing

**Causes:**
- Shortcode not registered
- Syntax error in shortcode
- No listings match criteria
- Template error

**Solutions:**
```php
// 1. Verify shortcode is registered
global $shortcode_tags;
var_dump(isset($shortcode_tags['hostaway_listings']));

// 2. Test with minimal attributes
[hostaway_listings]

// 3. Check if listings exist
$count = wp_count_posts('hostaway_listing');
var_dump($count);

// 4. Enable WP_DEBUG to see template errors
define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', true);
```

---

## Performance Considerations

### Database Optimization

**Indexes:** WordPress automatically creates indexes for:
- `post_type` in `wp_posts`
- `meta_key` in `wp_postmeta`
- `term_taxonomy_id` in `wp_term_relationships`

**Query Optimization:**
```php
// GOOD: Use meta_query only when needed
$args = array(
    'post_type' => 'hostaway_listing',
    'posts_per_page' => 10,
);

// AVOID: Multiple meta queries slow down queries
$args = array(
    'post_type' => 'hostaway_listing',
    'meta_query' => array(
        'relation' => 'AND',
        array('key' => '_bedrooms', 'value' => 2, 'compare' => '>='),
        array('key' => '_bathrooms', 'value' => 1, 'compare' => '>='),
        array('key' => '_price', 'value' => 500, 'compare' => '<='),
    ),
);
```

### Caching Strategy

**Object Caching:**
```php
// Cache listing queries
$cache_key = 'hostaway_listings_' . md5(serialize($args));
$results = wp_cache_get($cache_key, 'hostaway');

if (false === $results) {
    $query = new WP_Query($args);
    wp_cache_set($cache_key, $query, 'hostaway', HOUR_IN_SECONDS);
}
```

**Transient Caching:**
```php
// Cache expensive operations
$cache_key = 'hostaway_property_types';
$types = get_transient($cache_key);

if (false === $types) {
    $types = get_terms(array('taxonomy' => 'property_type'));
    set_transient($cache_key, $types, DAY_IN_SECONDS);
}
```

**Page Caching:**
- Use caching plugin (WP Rocket, W3 Total Cache)
- Cache pages with shortcodes
- Exclude admin pages from cache

### Image Optimization

**Lazy Loading:**
```php
// Add to templates
<img loading="lazy" src="<?php echo $image_url; ?>" />
```

**Responsive Images:**
```php
// WordPress handles this automatically with featured images
the_post_thumbnail('large'); // Generates srcset attribute
```

**CDN Integration:**
```php
// Filter image URLs to use CDN
add_filter('wp_get_attachment_url', function($url) {
    $cdn_url = 'https://cdn.example.com';
    return str_replace(home_url(), $cdn_url, $url);
});
```

---

## Security Considerations

### Input Validation

**Sanitization Functions:**
```php
sanitize_text_field()      // General text
sanitize_email()           // Email addresses
sanitize_url()             // URLs
sanitize_key()             // Keys/slugs
wp_kses_post()             // HTML content
intval()                   // Integers
floatval()                 // Floats
absint()                   // Positive integers
```

**Example:**
```php
// SECURE
$account_id = sanitize_text_field($_POST['account_id']);
update_option('hostaway_account_id', $account_id);

// INSECURE
$account_id = $_POST['account_id'];
update_option('hostaway_account_id', $account_id);
```

### Output Escaping

**Escaping Functions:**
```php
esc_html()       // HTML content
esc_attr()       // HTML attributes
esc_url()        // URLs
esc_js()         // JavaScript
esc_textarea()   // Textarea content
```

**Example:**
```php
// SECURE
echo '<a href="' . esc_url($url) . '">' . esc_html($title) . '</a>';

// INSECURE
echo '<a href="' . $url . '">' . $title . '</a>';
```

### Nonce Verification

**Creating Nonces:**
```php
// In form
<input type="hidden" name="hostaway_nonce" value="<?php echo wp_create_nonce('hostaway_action'); ?>" />

// In AJAX
wp_localize_script('admin-js', 'hostawayAdmin', array(
    'nonce' => wp_create_nonce('hostaway_admin_nonce'),
));
```

**Verifying Nonces:**
```php
// In form handler
if (!isset($_POST['hostaway_nonce']) || !wp_verify_nonce($_POST['hostaway_nonce'], 'hostaway_action')) {
    wp_die('Security check failed');
}

// In AJAX handler
check_ajax_referer('hostaway_admin_nonce', 'nonce');
```

### Capability Checks

**Always check user capabilities:**
```php
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}
```

**Role-based access:**
```php
// Only admins can sync
if (!current_user_can('administrator')) {
    return new WP_Error('unauthorized', 'Only administrators can sync listings');
}
```

### API Key Protection

**Never expose API keys:**
```php
// GOOD: Store in database
update_option('hostaway_secret_key', $secret_key);

// BAD: Hardcode in files
define('HOSTAWAY_SECRET_KEY', 'sk_live_abc123');

// GOOD: Use environment variables
$secret_key = getenv('HOSTAWAY_SECRET_KEY');
```

---

## Future Enhancements

### Planned Features

1. **Booking Integration**
   - Display availability calendar
   - Integration with booking widgets
   - Sync reservations

2. **Advanced Filtering**
   - Search by date availability
   - Price range slider
   - Distance from location

3. **Multi-language Support**
   - WPML compatibility
   - Polylang support
   - Translation-ready strings

4. **Analytics**
   - Track listing views
   - Monitor booking inquiries
   - Revenue reports

5. **Custom Fields**
   - UI for adding custom fields
   - Field mapping from Hostaway
   - Custom field display in templates

6. **Bulk Operations**
   - Bulk edit listings
   - Batch update metadata
   - Mass delete/deactivate

7. **Import/Export**
   - Export listings to CSV
   - Import custom data
   - Backup/restore functionality

### Contribution Guidelines

**To contribute:**
1. Fork the repository
2. Create feature branch: `git checkout -b feature/amazing-feature`
3. Follow WordPress coding standards
4. Test thoroughly
5. Commit: `git commit -m 'Add amazing feature'`
6. Push: `git push origin feature/amazing-feature`
7. Open pull request

**Code Review Checklist:**
- [ ] Follows WordPress coding standards
- [ ] Properly sanitized and escaped
- [ ] Includes inline documentation
- [ ] No security vulnerabilities
- [ ] Backwards compatible
- [ ] Tested on multiple PHP versions
- [ ] No JavaScript errors
- [ ] Responsive design

---

## Changelog

### Version 1.0.0 (2025-11-22)

**Initial Release**

- OAuth 2.0 authentication with Hostaway API
- Custom post type for listings
- Automatic and manual sync
- Admin settings and sync pages
- Shortcodes: `[hostaway_listings]` and `[hostaway_listing]`
- Customizable templates
- Responsive grid layout
- Property metadata support
- Image gallery import
- Custom taxonomies (property type, location, amenity)
- Auto-sync with cron
- AJAX-powered admin interface
- Template override system
- Comprehensive documentation

---

## Support & Resources

### Documentation
- Plugin README: `hostaway-wordpress-integration/README.md`
- Installation Guide: `INSTALLATION.md`
- This Technical Documentation: `TECHNICAL_DOCUMENTATION.md`

### External Resources
- [Hostaway API Documentation](https://api.hostaway.com/documentation)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)

### Getting Help
- GitHub Issues: https://github.com/rookpenny/homeRunner/issues
- WordPress Support Forums
- Stack Overflow: Tag `wordpress` + `hostaway`

---

## License

GPL-2.0+

Copyright (c) 2025

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

---

**Document Version:** 1.0.0
**Last Updated:** 2025-11-22
**Author:** Claude (Anthropic)
