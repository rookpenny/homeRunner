# Hostaway WordPress Plugin - Installation Guide

This guide will walk you through installing and setting up the Hostaway WordPress Integration plugin.

## Quick Start

### Step 1: Install the Plugin

**Option A: Upload via WordPress Admin**
1. Download or zip the `hostaway-wordpress-integration` folder
2. In WordPress, go to **Plugins > Add New > Upload Plugin**
3. Upload the zip file and click **Install Now**
4. Click **Activate Plugin**

**Option B: Manual Installation**
1. Upload `hostaway-wordpress-integration` folder to `/wp-content/plugins/`
2. Go to **Plugins** in WordPress admin
3. Find "Hostaway WordPress Integration" and click **Activate**

### Step 2: Get Your API Credentials

1. Log in to your Hostaway account at https://dashboard.hostaway.com
2. Navigate to **Settings** (gear icon in bottom left)
3. Click on **Hostaway API** in the menu
4. Click **Generate API Key** button
5. **IMPORTANT**: Copy both the **Account ID** and **Secret Key** immediately
   - These credentials are shown only once
   - Store them securely

### Step 3: Configure the Plugin

1. In WordPress admin, go to **Hostaway > Settings**
2. Paste your **Account ID** in the first field
3. Paste your **Secret Key** in the second field
4. Configure auto-sync settings (optional):
   - Check **Enable Auto Sync** for automatic updates
   - Select sync interval (Daily recommended)
5. Click **Save Changes**

### Step 4: Test Your Connection

1. On the Settings page, scroll down to **Test Connection**
2. Click the **Test Connection** button
3. You should see a success message
4. If you see an error:
   - Double-check your credentials
   - Ensure your Hostaway API is activated
   - Verify your server can make HTTPS requests

### Step 5: Sync Your Listings

1. Go to **Hostaway > Sync Listings**
2. Click **Sync All Listings** button
3. Wait for the sync to complete
   - This may take a few minutes for many listings
   - You'll see a progress indicator
4. Check **Hostaway > All Listings** to see imported properties

### Step 6: Display Listings on Your Site

**Using Shortcodes:**

1. Create or edit a page where you want listings to appear
2. Add the shortcode: `[hostaway_listings]`
3. Customize with attributes if needed:
   ```
   [hostaway_listings limit="9" columns="3"]
   ```
4. Save and view your page

**Using Gutenberg Blocks:**

1. Add a **Shortcode Block** to your page
2. Enter your shortcode: `[hostaway_listings]`
3. Preview and publish

## Customization

### Customize Look and Feel

**Method 1: Custom CSS (Easiest)**

Go to **Appearance > Customize > Additional CSS** and add:

```css
.hostaway-listing-card {
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.listing-price {
    color: #your-brand-color;
    font-size: 28px;
}
```

**Method 2: Override Templates (Advanced)**

1. Create folder in your theme: `your-theme/hostaway-integration/`
2. Copy template files from plugin's `templates/` folder
3. Edit the copied templates to match your design
4. Templates will automatically be used instead of defaults

### Common Customizations

**Change grid layout:**
```
[hostaway_listings columns="4"]
```

**Show only apartments:**
```
[hostaway_listings property_type="apartment"]
```

**Filter by location:**
```
[hostaway_listings location="miami"]
```

**Show 2+ bedroom properties:**
```
[hostaway_listings bedrooms="2"]
```

## Troubleshooting

### "Connection Failed" Error

**Solutions:**
- Verify Account ID and Secret Key are correct
- Check if you've activated the API in Hostaway dashboard
- Contact your hosting provider to ensure outbound HTTPS is allowed
- Try clearing the access token: Deactivate and reactivate plugin

### No Listings Appear After Sync

**Check:**
1. Go to **Hostaway > All Listings** - are they there?
2. If yes, check your shortcode syntax
3. If no, check error logs in **Tools > Site Health**
4. Verify you have active listings in Hostaway

### Images Not Loading

**Solutions:**
- Check if `allow_url_fopen` is enabled in PHP
- Increase `max_execution_time` in PHP settings
- Try syncing again with fewer listings
- Contact hosting support if issues persist

### Styling Looks Broken

**Check:**
- Is your theme compatible with WordPress standards?
- Clear all caches (browser, plugin, CDN)
- Check browser console for CSS/JS errors
- Try a default WordPress theme to isolate the issue

## Advanced Setup

### Schedule Custom Sync Times

Add to your theme's `functions.php`:

```php
// Custom sync schedule - every 6 hours
add_filter('cron_schedules', function($schedules) {
    $schedules['six_hours'] = array(
        'interval' => 6 * 60 * 60,
        'display'  => 'Every 6 Hours'
    );
    return $schedules;
});
```

### Add Custom Listing Data

```php
add_action('hostaway_listing_saved', function($post_id, $listing_data) {
    // Add custom processing
    if (isset($listing_data['customField'])) {
        update_post_meta($post_id, '_custom_field', $listing_data['customField']);
    }
}, 10, 2);
```

### Integration with Booking Systems

The plugin stores all Hostaway data. You can integrate with booking systems by:

1. Using the listing ID: `get_post_meta($post_id, '_hostaway_id', true)`
2. Passing data to booking widgets
3. Creating custom checkout pages

## Performance Optimization

### For Large Listing Counts (100+)

1. Use **Daily** auto-sync instead of more frequent
2. Increase PHP memory limit to 256M or higher
3. Consider using a caching plugin
4. Optimize images with an image optimization plugin

### Recommended Plugins

- **Caching**: WP Rocket, W3 Total Cache, or WP Super Cache
- **Images**: Smush or ShortPixel
- **Performance**: Autoptimize for CSS/JS minification

## Getting Help

### Before Requesting Support

1. Check this installation guide
2. Review the main README.md
3. Test with a default WordPress theme
4. Disable other plugins to check for conflicts
5. Check WordPress and PHP versions meet requirements

### Support Channels

- GitHub Issues: https://github.com/rookpenny/homeRunner/issues
- Include:
  - WordPress version
  - PHP version
  - Error messages (if any)
  - Steps to reproduce the issue

## Next Steps

- ✅ Plugin installed and activated
- ✅ API credentials configured
- ✅ Listings synced successfully
- ✅ Shortcode added to page

**Recommended:**
- Customize templates to match your brand
- Set up auto-sync for automatic updates
- Add property type and location filters
- Integrate with your booking system
- Optimize images for faster loading

## Resources

- Main Documentation: README.md in plugin folder
- Hostaway API Docs: https://api.hostaway.com/documentation
- WordPress Codex: https://codex.wordpress.org/
- Shortcode Reference: See README.md

---

**Questions?** Open an issue on GitHub or check the FAQ in the main README.
