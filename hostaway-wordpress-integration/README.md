# Hostaway WordPress Integration

A comprehensive WordPress plugin that connects your Hostaway property management listings to your WordPress website with full control over design and functionality.

## Features

- **Complete API Integration**: Seamlessly connects to Hostaway using OAuth 2.0 authentication
- **Custom Post Type**: Listings stored as WordPress posts for easy management
- **Automatic Sync**: Schedule automatic syncing of listings from Hostaway
- **Customizable Templates**: Override default templates in your theme
- **Shortcodes**: Easy-to-use shortcodes for displaying listings
- **Rich Metadata**: Stores bedrooms, bathrooms, price, location, and more
- **Image Galleries**: Automatically imports listing photos
- **Taxonomies**: Organize by property type, location, and amenities
- **Responsive Design**: Mobile-friendly grid and detail views

## Installation

1. Upload the `hostaway-wordpress-integration` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **Hostaway > Settings** to configure your API credentials

## Configuration

### Getting Your Hostaway API Credentials

1. Log in to your Hostaway account
2. Go to **Settings > Hostaway API**
3. Click **Generate API Key**
4. Copy your **Account ID** and **Secret Key**
5. Paste them into the plugin settings page

### Initial Setup

1. Go to **Hostaway > Settings**
2. Enter your Account ID and Secret Key
3. Click **Save Changes**
4. Click **Test Connection** to verify credentials
5. Go to **Hostaway > Sync Listings**
6. Click **Sync All Listings** to import your properties

## Usage

### Displaying Listings

#### Display All Listings

```
[hostaway_listings]
```

#### Display Listings with Options

```
[hostaway_listings limit="9" columns="3"]
```

**Available attributes:**
- `limit` - Number of listings to display (default: -1 for all)
- `columns` - Grid columns: 2, 3, or 4 (default: 3)
- `property_type` - Filter by property type slug
- `location` - Filter by location slug
- `bedrooms` - Filter by number of bedrooms
- `orderby` - Sort by: date, title, price (default: date)
- `order` - Sort order: ASC or DESC (default: DESC)

#### Examples

```
[hostaway_listings limit="6" columns="2" property_type="apartment"]
```

```
[hostaway_listings location="miami" bedrooms="2" orderby="price"]
```

### Display Single Listing

```
[hostaway_listing id="123"]
```

Replace `123` with the WordPress post ID of the listing.

## Customization

### Template Override

You can override the default templates by copying them to your theme:

1. Copy template files from `plugins/hostaway-wordpress-integration/templates/`
2. Paste into your theme: `your-theme/hostaway-integration/`
3. Customize as needed

**Available templates:**
- `listing-card.php` - Grid item template
- `single-listing.php` - Single listing detail page

### Custom Styling

Add custom CSS in your theme or use the Customizer:

```css
/* Customize listing cards */
.hostaway-listing-card {
    border: 2px solid #your-color;
}

/* Customize pricing */
.listing-price {
    color: #your-brand-color;
}
```

### Available Data in Templates

When customizing templates, you have access to:

**Listing Meta Fields:**
- `$bedrooms` - Number of bedrooms
- `$bathrooms` - Number of bathrooms
- `$accommodates` - Maximum guests
- `$price` - Base daily rate
- `$currency` - Currency code (USD, EUR, etc.)
- `$city` - City
- `$address` - Full address
- `$country` - Country
- `$latitude` - GPS latitude
- `$longitude` - GPS longitude
- `$image_gallery` - Array of image URLs

**Taxonomies:**
- Property Type: `get_the_terms($post_id, 'property_type')`
- Location: `get_the_terms($post_id, 'location')`
- Amenities: `get_the_terms($post_id, 'amenity')`

## Auto-Sync Settings

Configure automatic synchronization in **Hostaway > Settings**:

1. Enable **Auto Sync**
2. Choose sync interval:
   - Hourly
   - Twice Daily
   - Daily (recommended)
   - Weekly

Listings will automatically update based on your schedule.

## Advanced Customization

### Adding Custom Fields

You can add custom processing in the sync by hooking into the save action:

```php
add_action('hostaway_listing_saved', function($post_id, $listing_data) {
    // Your custom processing here
}, 10, 2);
```

### Custom Queries

Query listings programmatically:

```php
$args = array(
    'post_type' => 'hostaway_listing',
    'meta_query' => array(
        array(
            'key' => '_bedrooms',
            'value' => 2,
            'compare' => '>=',
        ),
    ),
);
$listings = new WP_Query($args);
```

### Integrating Maps

To add interactive maps, integrate with your preferred mapping service:

**Google Maps:**
```javascript
// In your theme's JavaScript
var map = new google.maps.Map(element, {
    center: {lat: latitude, lng: longitude},
    zoom: 15
});
```

**Leaflet:**
```javascript
var map = L.map('map').setView([latitude, longitude], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
```

## Troubleshooting

### Connection Fails

- Verify your Account ID and Secret Key are correct
- Check that your Hostaway API is activated
- Ensure your server can make outbound HTTPS requests

### Images Not Displaying

- Check that `allow_url_fopen` is enabled in PHP
- Verify image URLs are accessible
- Check WordPress media upload permissions

### Sync Takes Too Long

- Use the automatic sync instead of manual
- Increase PHP `max_execution_time` if needed
- Contact support if you have many listings (500+)

### Listings Not Updating

- Check the last sync time in **Hostaway > Sync Listings**
- Verify auto-sync is enabled
- Manually trigger a sync to force update

## Filters and Hooks

### Available Filters

```php
// Modify listing data before saving
apply_filters('hostaway_listing_data', $listing_data, $post_id);

// Customize template paths
apply_filters('hostaway_template_path', $path, $template_name);

// Modify shortcode output
apply_filters('hostaway_listings_output', $html, $atts);
```

### Available Actions

```php
// After listing is saved/updated
do_action('hostaway_listing_saved', $post_id, $listing_data);

// Before sync starts
do_action('hostaway_sync_start');

// After sync completes
do_action('hostaway_sync_complete', $synced_count);
```

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- Active Hostaway account with API access
- cURL or allow_url_fopen enabled

## Support

For issues and feature requests, please visit:
https://github.com/rookpenny/homeRunner/issues

## API Documentation

Hostaway API Documentation: https://api.hostaway.com/documentation

## License

GPL-2.0+

## Changelog

### 1.0.0
- Initial release
- OAuth 2.0 authentication
- Full listing sync
- Custom post type and taxonomies
- Shortcodes for display
- Template system
- Auto-sync functionality
- Responsive design

## Credits

Built with ❤️ for Hostaway property managers who want full control over their listing presentation.

**Sources:**
- [Hostaway Public API Reference](https://api.hostaway.com/documentation)
- [Hostaway Public API - Account & Secret Key](https://support.hostaway.com/hc/en-us/articles/360002576293-Hostaway-Public-API-Account-Secret-Key)
