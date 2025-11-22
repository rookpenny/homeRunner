# Hostaway WordPress Plugin - Quick Reference

**Version:** 1.0.0

This is a quick reference guide for common tasks and key information about the Hostaway WordPress Integration plugin.

---

## Plugin Architecture at a Glance

```
Main Entry Point: hostaway-integration.php
    ↓
Core Class: Hostaway_Integration
    ↓
    ├── API Client (Hostaway_API_Client) - OAuth & API requests
    ├── Post Type (Hostaway_Post_Type) - CPT & data storage
    ├── Admin (Hostaway_Admin) - Settings & sync UI
    └── Public (Hostaway_Public) - Shortcodes & frontend display
```

---

## Key Files Reference

| File | Purpose | Key Functions |
|------|---------|---------------|
| `includes/class-hostaway-api-client.php` | API communication | `get_listings()`, `get_access_token()` |
| `includes/class-hostaway-post-type.php` | Data model | `save_listing_from_api()`, `register_post_type()` |
| `admin/class-hostaway-admin.php` | Admin interface | `sync_listings()`, `test_connection()` |
| `public/class-hostaway-public.php` | Frontend | `listings_shortcode()`, `render_listing_card()` |
| `templates/listing-card.php` | Grid item template | Override in theme |
| `templates/single-listing.php` | Detail page template | Override in theme |

---

## Database Schema Quick Reference

### Post Type
- **Name:** `hostaway_listing`
- **Supports:** title, editor, thumbnail, excerpt
- **Public:** Yes
- **Archive:** Yes

### Meta Fields
```php
_hostaway_id       // int: Hostaway listing ID
_bedrooms          // int: Number of bedrooms
_bathrooms         // float: Number of bathrooms
_accommodates      // int: Max guests
_price             // float: Base daily rate
_currency          // string: Currency code (USD, EUR, etc.)
_city              // string: City
_latitude          // float: GPS latitude
_longitude         // float: GPS longitude
_image_gallery     // array: Image URLs
```

### Taxonomies
```php
property_type      // hierarchical: Apartment, House, Villa
location           // hierarchical: Cities, regions
amenity            // non-hierarchical: WiFi, Pool, Kitchen
```

### Options
```php
hostaway_account_id              // API Account ID
hostaway_secret_key              // API Secret Key
hostaway_auto_sync_interval      // hourly|twicedaily|daily|weekly
hostaway_sync_enabled            // yes|no
hostaway_last_sync              // datetime
```

---

## Shortcode Quick Reference

### Display Multiple Listings

```
[hostaway_listings]
[hostaway_listings columns="3"]
[hostaway_listings limit="9" columns="3"]
[hostaway_listings property_type="apartment"]
[hostaway_listings location="miami" bedrooms="2"]
[hostaway_listings orderby="price" order="ASC"]
```

**Attributes:**
- `limit` (int, default: -1) - Number of listings
- `columns` (int, default: 3) - Grid columns (2, 3, 4)
- `property_type` (string) - Filter by type slug
- `location` (string) - Filter by location slug
- `bedrooms` (int) - Minimum bedrooms
- `orderby` (string, default: 'date') - Sort by: date, title, price
- `order` (string, default: 'DESC') - ASC or DESC

### Display Single Listing

```
[hostaway_listing id="123"]
```

---

## API Endpoints Reference

### Authentication
```
POST https://api.hostaway.com/v1/accessTokens
Body:
  grant_type: client_credentials
  client_id: {account_id}
  client_secret: {secret_key}
  scope: general
```

### Listings
```
GET https://api.hostaway.com/v1/listings
GET https://api.hostaway.com/v1/listings?limit=100&offset=0
GET https://api.hostaway.com/v1/listings/{id}
GET https://api.hostaway.com/v1/listings/{id}/photos
GET https://api.hostaway.com/v1/listings/{id}/amenities
GET https://api.hostaway.com/v1/listings/{id}/calendar?startDate=2025-01-01&endDate=2025-12-31
```

---

## Common Code Snippets

### Get Listing Meta Data
```php
$post_id = get_the_ID();
$bedrooms = get_post_meta($post_id, '_bedrooms', true);
$bathrooms = get_post_meta($post_id, '_bathrooms', true);
$price = get_post_meta($post_id, '_price', true);
$currency = get_post_meta($post_id, '_currency', true);
$city = get_post_meta($post_id, '_city', true);
$gallery = get_post_meta($post_id, '_image_gallery', true);
```

### Query Listings
```php
$args = array(
    'post_type'      => 'hostaway_listing',
    'posts_per_page' => 10,
    'meta_query'     => array(
        array(
            'key'     => '_bedrooms',
            'value'   => 2,
            'compare' => '>=',
        ),
    ),
);
$query = new WP_Query($args);
```

### Get Taxonomies
```php
$property_types = get_the_terms($post_id, 'property_type');
$locations = get_the_terms($post_id, 'location');
$amenities = get_the_terms($post_id, 'amenity');
```

### Programmatic Sync
```php
$api_client = new Hostaway_API_Client();
$listings = $api_client->get_all_listings();

foreach ($listings as $listing) {
    Hostaway_Post_Type::save_listing_from_api($listing);
}

update_option('hostaway_last_sync', current_time('mysql'));
```

### Custom Hook Examples
```php
// After listing saved
add_action('hostaway_listing_saved', function($post_id, $listing_data) {
    // Your code here
}, 10, 2);

// Modify listing data before save
add_filter('hostaway_listing_data', function($listing_data) {
    // Modify $listing_data
    return $listing_data;
});

// After sync complete
add_action('hostaway_sync_complete', function($synced_count) {
    error_log("Synced {$synced_count} listings");
});
```

---

## Template Override Quick Guide

### Override Listing Card Template

1. Create folder: `your-theme/hostaway-integration/`
2. Copy: `plugins/hostaway-wordpress-integration/templates/listing-card.php`
3. Paste to: `your-theme/hostaway-integration/listing-card.php`
4. Customize as needed

**Available Variables in listing-card.php:**
```php
$post_id, $bedrooms, $bathrooms, $accommodates,
$price, $currency, $city
```

### Override Single Listing Template

1. Copy: `plugins/hostaway-wordpress-integration/templates/single-listing.php`
2. Paste to: `your-theme/hostaway-integration/single-listing.php`
3. Customize

**Available Variables in single-listing.php:**
```php
$post, $post_id, $bedrooms, $bathrooms, $accommodates,
$price, $currency, $address, $city, $country,
$latitude, $longitude, $image_gallery
```

---

## Admin Pages Quick Access

### Settings Page
**URL:** `wp-admin/edit.php?post_type=hostaway_listing&page=hostaway-settings`

**Fields:**
- Account ID (required)
- Secret Key (required)
- Auto Sync Interval (hourly, twicedaily, daily, weekly)
- Enable Auto Sync (checkbox)

### Sync Page
**URL:** `wp-admin/edit.php?post_type=hostaway_listing&page=hostaway-sync`

**Actions:**
- View last sync time
- Trigger manual sync
- View sync progress
- See errors (if any)

### All Listings
**URL:** `wp-admin/edit.php?post_type=hostaway_listing`

---

## CSS Selectors Reference

### Grid Layout
```css
.hostaway-listings-grid              /* Grid container */
.hostaway-listings-grid.columns-2    /* 2-column grid */
.hostaway-listings-grid.columns-3    /* 3-column grid */
.hostaway-listings-grid.columns-4    /* 4-column grid */
```

### Listing Card
```css
.hostaway-listing-card               /* Card container */
.listing-thumbnail                   /* Image wrapper */
.listing-content                     /* Content wrapper */
.listing-title                       /* Listing title */
.listing-location                    /* Location text */
.listing-details                     /* Details container */
.detail-item                         /* Individual detail */
.listing-price                       /* Price display */
.listing-excerpt                     /* Description */
.listing-button                      /* View button */
```

### Single Listing
```css
.hostaway-single-listing             /* Main container */
.listing-gallery                     /* Image gallery */
.listing-featured-image              /* Featured image */
.listing-meta                        /* Meta info container */
.listing-price-large                 /* Large price display */
.listing-details-large               /* Details list */
.listing-description                 /* Description section */
.listing-amenities                   /* Amenities section */
.listing-map                         /* Map section */
```

---

## Troubleshooting Cheatsheet

### Connection Issues
```php
// Check credentials
var_dump(
    get_option('hostaway_account_id'),
    get_option('hostaway_secret_key')
);

// Clear token cache
delete_transient('hostaway_access_token');

// Test API directly
$api_client = new Hostaway_API_Client();
var_dump($api_client->test_connection());
```

### Image Import Issues
```php
// Check PHP settings
var_dump(
    ini_get('allow_url_fopen'),
    ini_get('upload_max_filesize'),
    ini_get('memory_limit')
);

// Check upload directory
$upload = wp_upload_dir();
var_dump($upload, is_writable($upload['path']));
```

### Shortcode Not Working
```php
// Verify shortcode registered
global $shortcode_tags;
var_dump(isset($shortcode_tags['hostaway_listings']));

// Check if listings exist
var_dump(wp_count_posts('hostaway_listing'));

// Enable debugging
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### Sync Issues
```php
// Increase execution time
set_time_limit(300);

// Increase memory
ini_set('memory_limit', '512M');

// Check last sync
var_dump(get_option('hostaway_last_sync'));
```

---

## Security Checklist

### Always Do
- [ ] Sanitize input: `sanitize_text_field()`, `intval()`, etc.
- [ ] Escape output: `esc_html()`, `esc_attr()`, `esc_url()`
- [ ] Verify nonces: `wp_verify_nonce()`, `check_ajax_referer()`
- [ ] Check capabilities: `current_user_can('manage_options')`
- [ ] Use prepared statements for custom queries
- [ ] Validate data types and ranges
- [ ] Log errors, not sensitive data

### Never Do
- [ ] Trust $_POST, $_GET, $_REQUEST directly
- [ ] Echo variables without escaping
- [ ] Hardcode API keys in files
- [ ] Skip capability checks
- [ ] Expose debug info in production
- [ ] Use user input in file paths
- [ ] Store passwords in plain text

---

## Development Commands

### Enable Debug Mode
```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### View Error Log
```bash
tail -f /path/to/wordpress/wp-content/debug.log
```

### Clear All Plugin Data
```php
// WARNING: Deletes everything
function hostaway_clear_all_data() {
    // Delete all listings
    $listings = get_posts(array(
        'post_type'      => 'hostaway_listing',
        'numberposts'    => -1,
        'post_status'    => 'any',
    ));
    foreach ($listings as $listing) {
        wp_delete_post($listing->ID, true);
    }

    // Delete options
    delete_option('hostaway_account_id');
    delete_option('hostaway_secret_key');
    delete_option('hostaway_last_sync');

    // Clear transients
    delete_transient('hostaway_access_token');
}
```

### Manual Sync via WP-CLI
```bash
wp eval "
\$api = new Hostaway_API_Client();
\$listings = \$api->get_all_listings();
foreach (\$listings as \$listing) {
    Hostaway_Post_Type::save_listing_from_api(\$listing);
}
echo 'Synced ' . count(\$listings) . ' listings';
"
```

---

## Performance Tips

### Query Optimization
```php
// GOOD: Simple query
$args = array('post_type' => 'hostaway_listing', 'posts_per_page' => 10);

// AVOID: Multiple meta queries
$args = array(
    'post_type' => 'hostaway_listing',
    'meta_query' => array(
        array('key' => '_bedrooms', 'value' => 2),
        array('key' => '_bathrooms', 'value' => 1),
        array('key' => '_price', 'value' => 500, 'compare' => '<='),
    ),
);
```

### Caching
```php
// Cache expensive queries
$cache_key = 'hostaway_featured_listings';
$listings = get_transient($cache_key);
if (false === $listings) {
    // Run query
    set_transient($cache_key, $listings, HOUR_IN_SECONDS);
}
```

### Image Optimization
```html
<!-- Lazy loading -->
<img loading="lazy" src="..." />

<!-- Responsive images (WordPress handles automatically) -->
<?php the_post_thumbnail('large'); ?>
```

---

## Version Control

### Git Workflow
```bash
# Make changes
git add .
git commit -m "Descriptive commit message"
git push origin claude/hostaway-wordpress-plugin-01QSDU9trRsZB3qWyqgjBvkS

# Create feature branch
git checkout -b feature/my-new-feature
```

### Commit Message Format
```
Short summary (50 chars or less)

Detailed explanation of changes:
- What was changed
- Why it was changed
- Any breaking changes
```

---

## Quick Links

### Admin URLs
- All Listings: `/wp-admin/edit.php?post_type=hostaway_listing`
- Settings: `/wp-admin/edit.php?post_type=hostaway_listing&page=hostaway-settings`
- Sync: `/wp-admin/edit.php?post_type=hostaway_listing&page=hostaway-sync`

### Documentation
- [Full Technical Docs](TECHNICAL_DOCUMENTATION.md)
- [Installation Guide](INSTALLATION.md)
- [Plugin README](hostaway-wordpress-integration/README.md)
- [Hostaway API Docs](https://api.hostaway.com/documentation)

### External Resources
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [WP_Query Reference](https://developer.wordpress.org/reference/classes/wp_query/)

---

**Quick Reference Version:** 1.0.0
**Last Updated:** 2025-11-22
**For detailed information, see:** [TECHNICAL_DOCUMENTATION.md](TECHNICAL_DOCUMENTATION.md)
