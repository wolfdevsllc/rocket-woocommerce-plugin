# Rocket WooCommerce Plugin

A WordPress plugin that integrates with Rocket.net to enable reselling WordPress Hosting through WooCommerce.

## Description

This version of the plugin is a fork of the original Rocket WooCommerce Plugin developed by the Rocket.net team. The Rocket WooCommerce Plugin offers seamless integration between WooCommerce and Rocket.net's hosting services, enabling website owners to resell WordPress hosting services directly through their WooCommerce store. For more information about the original plugin, visit the [Rocket.net WooCommerce Plugin page](https://support.rocket.net/hc/en-us/articles/4409254082459-Getting-started-with-Rocket-net-WooCommerce-Plugin).

## Features

### Original Features

- Customer Site Creation on checkout
- Hosting Location Selection
- Site Management Dashboard
- Real-time Site Status Monitoring
- Integrated Billing through WooCommerce

### Enhanced Features

- Site Allocation Management
- Improved UI/UX for site management
- Dynamic site allocation display
- Interactive site creation form
- Enhanced error handling and user feedback
- Responsive design improvements
- Optimized AJAX operations

## Requirements

- WordPress 5.0 or higher
- WooCommerce 6.0 or higher
- PHP 7.4 or higher

## Installation

1. Download the plugin zip file
2. Go to WordPress admin > Plugins > Add New
3. Click "Upload Plugin"
4. Upload the zip file
5. Click "Install Now"
6. Activate the plugin

## Configuration

1. Navigate to Rocket.net from WordPress dashboard
2. Enter your Rocket.net login details
3. Configure your hosting packages with WooCommerce products
4. Set up pricing and allocation limits

## Usage

### For Store Owners

- Create and manage hosting packages
- Monitor customer sites
- Handle hosting allocations
- View usage statistics
- Manage site access permissions

### For Customers

- Purchase hosting packages
- Create new WordPress sites with improved UI
- Manage existing sites through enhanced dashboard
- Monitor site status in real-time
- Access site control panel
- Edit site names with instant feedback

## Support

For support inquiries, please contact:

- Website: [https://rocket.net/](https://rocket.net/)
- Support Portal: [Rocket.net Support](https://rocket.net/support)

## License

This plugin is licensed under the terms of use provided by Rocket.net.

## Credits

- Originally developed by [Rocket.net](https://rocket.net/)

## Available Filters

### Logging Filters

- `wc_rocket_verbose_logging` (boolean)
  - Default: false
  - Controls detailed logging output for debugging
  - Example: `add_filter('wc_rocket_verbose_logging', '__return_true');`

### Site Creation Filters

- `wc_create_site_rocket` (array)
  - Modifies the site creation request data before sending to Rocket.net API
  - Parameters: $request_fields (array of site creation data)
  - Example:
    ```php
    add_filter('wc_create_site_rocket', function($request_fields) {
        $request_fields['label'] = 'Custom-' . $request_fields['name'];
        return $request_fields;
    });
    ```

### Portal Customization Filters

- `wc_rocket_portal_customization_{setting}` (string)
  - Available for: body_background_color, icon_primary_color, icon_secondary_color,
    primary_color, primary_hover_color, primary_active_color,
    primary_menu_hover_color, primary_menu_active_color
  - Modifies portal UI customization settings
  - Example:
    ```php
    add_filter('wc_rocket_portal_customization_primary_color', function($color) {
        return '#FF5733';
    });
    ```

### Email Filters

- `woocommerce_email_footer_text` (string)
  - Customizes the footer text in site creation emails
  - Example:
    ```php
    add_filter('woocommerce_email_footer_text', function($text) {
        return 'Powered by Your Company Name';
    });
    ```

---

For more information about Rocket.net's WordPress hosting services, visit [rocket.net](https://rocket.net/).
