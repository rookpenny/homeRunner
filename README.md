# Hostaway WordPress Integration Plugin

A powerful WordPress plugin that seamlessly connects your Hostaway property management listings to your WordPress website, giving you complete control over design and functionality.

## 🚀 Features

- **Full Hostaway API Integration** with OAuth 2.0
- **Automatic Listing Sync** on customizable schedules
- **Custom Post Types** for easy management
- **Flexible Shortcodes** for displaying listings anywhere
- **Customizable Templates** that can be overridden in your theme
- **Responsive Design** that works on all devices
- **Rich Metadata** including pricing, location, amenities
- **Image Galleries** automatically imported from Hostaway
- **Taxonomies** for property types, locations, and amenities

## 📦 Installation

See [INSTALLATION.md](INSTALLATION.md) for detailed setup instructions.

**Quick Start:**
1. Upload plugin to `/wp-content/plugins/hostaway-wordpress-integration/`
2. Activate in WordPress admin
3. Configure API credentials in Hostaway > Settings
4. Sync your listings
5. Add `[hostaway_listings]` shortcode to any page

## 📖 Documentation

Complete documentation is available in:
- [Plugin README](hostaway-wordpress-integration/README.md) - Full feature documentation
- [INSTALLATION.md](INSTALLATION.md) - Step-by-step setup guide

## 🎯 Usage Examples

Display all listings in a 3-column grid:
```
[hostaway_listings columns="3"]
```

Show only apartments in Miami:
```
[hostaway_listings property_type="apartment" location="miami"]
```

Display properties with 2+ bedrooms:
```
[hostaway_listings bedrooms="2"]
```

## 🛠️ Requirements

- WordPress 5.0+
- PHP 7.2+
- Active Hostaway account with API access
- cURL or allow_url_fopen enabled

## 📝 License

GPL-2.0+

## 🤝 Contributing

Issues and pull requests are welcome! Please visit the [issues page](https://github.com/rookpenny/homeRunner/issues).

## 📚 Resources

- [Hostaway API Documentation](https://api.hostaway.com/documentation)
- [Hostaway Support - API Setup](https://support.hostaway.com/hc/en-us/articles/360002576293-Hostaway-Public-API-Account-Secret-Key)