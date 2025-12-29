# Installation Guide

This guide will walk you through the installation process of the Digiplanet Digital Products plugin.

## Prerequisites

Before installing the plugin, ensure your WordPress installation meets the following requirements:

- WordPress 5.6 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher / MariaDB 10.1 or higher
- At least 256MB PHP memory limit
- SSL Certificate (recommended for payment processing)

## Installation Methods

### Method 1: Install via WordPress Admin (Recommended)

1. **Login to your WordPress admin panel**
   Navigate to `YourDomain.com/wp-admin` and log in with your administrator credentials.

2. **Go to Plugins → Add New**
   In the left sidebar, click on "Plugins" and then "Add New".

3. **Upload the Plugin**
   Click the "Upload Plugin" button at the top of the page.

4. **Choose the Plugin File**
   Click "Choose File" and select the `digiplanet-digital-products.zip` file from your computer.

5. **Install Now**
   Click the "Install Now" button.

6. **Activate the Plugin**
   After installation, click the "Activate Plugin" button.

### Method 2: Install via FTP

1. **Download the Plugin**
   Download the `digiplanet-digital-products.zip` file to your computer.

2. **Extract the Files**
   Extract the ZIP file. You should get a folder named `digiplanet-digital-products`.

3. **Connect to Your Server via FTP**
   Use an FTP client (like FileZilla) to connect to your WordPress server.

4. **Upload the Plugin Folder**
   Navigate to `/wp-content/plugins/` and upload the `digiplanet-digital-products` folder.

5. **Activate the Plugin**
   Go to your WordPress admin panel → Plugins → Installed Plugins, find "Digiplanet Digital Products" and click "Activate".

### Method 3: Install via cPanel File Manager

1. **Login to cPanel**
   Access your hosting control panel.

2. **Open File Manager**
   Navigate to the File Manager tool.

3. **Go to Plugins Directory**
   Browse to: `public_html/wp-content/plugins/`

4. **Upload the ZIP File**
   Click "Upload" and select the `digiplanet-digital-products.zip` file.

5. **Extract the Files**
   Right-click the uploaded ZIP file and select "Extract".

6. **Activate the Plugin**
   Go to WordPress admin → Plugins and activate "Digiplanet Digital Products".

## Automatic Installation

If you purchased the plugin from the WordPress repository, you can install it directly:

1. Go to Plugins → Add New
2. Search for "Digiplanet Digital Products"
3. Click "Install Now"
4. Click "Activate"

## Post-Installation Checklist

After successful installation, complete these steps:

### 1. Create Required Pages

The plugin will automatically create the following pages if they don't exist:

- **Digital Products** - Product listing page
- **Cart** - Shopping cart page
- **Checkout** - Checkout process page
- **My Account** - Customer portal page

To manually create these pages:

1. Go to Pages → Add New
2. Create pages with these shortcodes:
   - Products: `[digiplanet_products]`
   - Cart: `[digiplanet_cart]`
   - Checkout: `[digiplanet_checkout]`
   - Account: `[digiplanet_account]`

3. Save the pages and note their IDs

### 2. Configure Permalinks

1. Go to Settings → Permalinks
2. Select "Post name" or "Custom Structure"
3. Click "Save Changes"

### 3. Set Up User Roles

The plugin creates two custom user roles:

1. **Digital Customer** - For customers purchasing digital products
2. **Software Client** - For software service clients (created by admin only)

To check user roles:
- Go to Users → All Users
- Each user will have their role displayed

### 4. Database Setup

The plugin automatically creates the required database tables. To verify:

1. Go to Digiplanet → Dashboard
2. Check if all tables are listed as "Active"

If tables are missing:
1. Go to Digiplanet → Tools
2. Click "Create Database Tables"

## Troubleshooting Installation Issues

### Common Issues and Solutions

#### Issue 1: "Plugin failed to activate"
- **Solution**: Check PHP version (minimum 7.4 required)
- **Solution**: Increase PHP memory limit to at least 256MB
- **Solution**: Check if the plugin folder exists in `/wp-content/plugins/`

#### Issue 2: "The package could not be installed"
- **Solution**: Ensure you have proper file permissions (755 for folders, 644 for files)
- **Solution**: Check if the ZIP file is corrupted
- **Solution**: Try manual FTP installation

#### Issue 3: "Missing required PHP extensions"
- **Solution**: Enable these PHP extensions:
  - curl
  - json
  - mbstring
  - openssl

#### Issue 4: "Database tables not created"
- **Solution**: Check database user permissions
- **Solution**: Run the database creation manually from Tools
- **Solution**: Check for plugin conflicts

### Server Configuration

#### Recommended PHP Settings
```ini
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 64M
post_max_size = 64M
max_input_vars = 3000

Required MySQL Settings
innodb_file_per_table = ON
innodb_file_format = Barracuda
innodb_large_prefix = ON

Plugin Conflict Detection
If you experience issues after installation:

Disable all other plugins

Activate Digiplanet Digital Products

Test the plugin functionality

Re-enable other plugins one by one

Test after each activation

Getting Help
If you encounter issues during installation:

Check the error log in Digiplanet → System Status

Contact support with the error details

Include system information from Digiplanet → System Info

Next Steps
After successful installation:

Configure the plugin settings

Set up payment gateways

Add your first product

Configure Elementor widgets

Video Tutorial
For visual learners, watch our installation tutorial:
[Insert video link here]

Frequently Asked Questions
Q: Can I install the plugin on a local development environment?
A: Yes, the plugin works on local environments like XAMPP, MAMP, and Local by Flywheel.

Q: Is the plugin compatible with multisite?
A: Yes, but each site needs separate configuration.

Q: Can I migrate from another digital products plugin?
A: Yes, we provide migration tools in the Pro version.

Q: What happens if I deactivate the plugin?
A: Your data is preserved. Reactivating will restore all settings and data.

Q: How do I update the plugin?
A: Go to Plugins → Installed Plugins, find Digiplanet Digital Products, and click "Update Now".


## 5. `documentation/sections/configuration.md`

```markdown
# Configuration Guide

This guide covers all configuration options available in the Digiplanet Digital Products plugin.

## Initial Setup

After installation, access the plugin configuration:

1. **Navigate to WordPress Admin Dashboard**
2. **Go to Digiplanet → Settings**
3. **You'll see the main settings tabs**

## General Settings

### Site Configuration
- **Store Name**: Your business/store name
- **Store Logo**: Upload your store logo (displayed in emails)
- **Store Email**: Email address for store communications
- **Store Phone**: Contact phone number (optional)
- **Store Address**: Physical/store address (optional)

### Currency Settings
- **Currency**: Select your primary currency (USD, EUR, GBP, etc.)
- **Currency Position**: Where to display currency symbol
  - Left: $100
  - Right: 100$
  - Left with space: $ 100
  - Right with space: 100 $
- **Thousand Separator**: Comma (,) or Period (.)
- **Decimal Separator**: Period (.) or Comma (,)
- **Number of Decimals**: 0, 1, or 2 decimal places

### Pages Configuration
Configure the main pages for your store:

1. **Products Page**
   - Select or create a page with `[digiplanet_products]` shortcode
   - This page displays all your digital products

2. **Cart Page**
   - Select or create a page with `[digiplanet_cart]` shortcode
   - Shopping cart page

3. **Checkout Page**
   - Select or create a page with `[digiplanet_checkout]` shortcode
   - Checkout process page

4. **Account Page**
   - Select or create a page with `[digiplanet_account]` shortcode
   - Customer portal page

5. **Terms & Conditions Page** (optional)
   - Link to your terms page
   - Required for checkout process

### Product Settings

#### Display Settings
- **Products per Page**: Number of products to show per page (default: 12)
- **Default Product View**: Grid or List layout
- **Show Product Ratings**: Enable/disable star ratings
- **Show Sale Badge**: Display sale badges on discounted products
- **Show Stock Status**: Show product availability
- **Product Image Size**: Thumbnail size for product listings

#### Catalog Settings
- **Shop Page Display**: What to show on main shop page
- **Default Sort Order**: Default product sorting
- **Hide Out of Stock Items**: Remove out-of-stock products from catalog

## Payment Settings

### General Payment Configuration
- **Enable Test Mode**: Use sandbox for testing payments
- **Currency**: Must match your currency settings
- **Tax Calculation**: Enable/disable tax calculations
- **Tax Rate**: Default tax percentage
- **Tax Display**: How to display prices (incl/excl tax)

### Stripe Configuration
1. **Enable Stripe**: Toggle Stripe payment gateway
2. **API Keys**:
   - **Live Publishable Key**: From Stripe Dashboard
   - **Live Secret Key**: From Stripe Dashboard
   - **Test Publishable Key**: For testing
   - **Test Secret Key**: For testing

3. **Stripe Settings**:
   - **Statement Descriptor**: Appears on customer statements
   - **Capture Method**: Automatic or manual capture
   - **Payment Methods**: Cards supported (Visa, Mastercard, etc.)

### Paystack Configuration
1. **Enable Paystack**: Toggle Paystack payment gateway
2. **API Keys**:
   - **Live Public Key**: From Paystack Dashboard
   - **Live Secret Key**: From Paystack Dashboard
   - **Test Public Key**: For testing
   - **Test Secret Key**: For testing

3. **Paystack Settings**:
   - **Merchant Email**: Your Paystack registered email
   - **Payment Channels**: Cards, Bank Transfer, USSD

### Other Payment Methods
- **Bank Transfer**: Manual payment instructions
- **Cash on Delivery**: For local pickups
- **Custom Payment Gateway**: Add custom gateway via hooks

## Email Settings

### Email Templates
Configure automated email notifications:

#### Customer Emails
- **Order Confirmation**: Sent after successful purchase
- **License Details**: Sent with license keys
- **Download Instructions**: Sent with download links
- **Password Reset**: Account recovery emails
- **Welcome Email**: New account welcome message

#### Admin Emails
- **New Order Notification**: Alert for new orders
- **New Customer Registration**: New account alerts
- **Low Stock Notification**: Inventory alerts

#### Email Configuration
- **From Name**: Sender name for all emails
- **From Email**: Sender email address
- **Email Type**: HTML or Plain Text
- **Header Image**: Email header logo
- **Footer Text**: Email footer content

### Customizing Email Templates
Each email template can be customized:
1. Go to Digiplanet → Settings → Emails
2. Select the email template
3. Edit subject and content
4. Use available template tags:
   - `{site_name}`: Website name
   - `{customer_name}`: Customer's name
   - `{order_number}`: Order number
   - `{order_date}`: Order date
   - `{product_list}`: List of purchased products
   - `{license_keys}`: License keys
   - `{download_links}`: Download links

## User & Account Settings

### Registration Settings
- **Allow Registration**: Enable/disable customer registration
- **Registration Default Role**: Digital Customer (default)
- **Require Email Verification**: Verify email before login
- **Auto-login after Registration**: Automatically log users in

### Account Pages
Configure what customers see in their accounts:

#### Digital Customer Features
- **Dashboard**: Overview of purchases and licenses
- **My Products**: List of purchased products
- **Orders**: Order history and details
- **Licenses**: Active license keys
- **Downloads**: Download history
- **Reviews**: Product reviews written
- **Settings**: Profile and password settings

#### Software Client Features
- **Dashboard**: Project overview
- **Projects**: Active software projects
- **Support**: Support tickets
- **Documents**: Project documentation
- **Billing**: Invoices and payments
- **Settings**: Profile settings

### Login/Registration Pages
Customize the login/registration experience:
- **Custom Login Page**: Use plugin's styled login page
- **Redirect after Login**: Where users go after login
- **Redirect after Logout**: Where users go after logout
- **Remember Me**: Enable/disable remember me option

## Product Management Settings

### Digital Product Settings
- **Download Method**: Force download or redirect
- **Download Expiry**: Days until download link expires
- **Download Limit**: Maximum number of downloads per purchase
- **File Protection**: Protect files from direct access

### License Settings
- **License Generation**: Automatic or manual license keys
- **License Format**: Custom format for license keys
- **Default Activations**: Default number of activations per license
- **License Expiry**: Days until license expires (0 = never)

### Inventory Settings
- **Manage Stock**: Enable stock management
- **Hold Stock**: Minutes to hold stock during checkout
- **Low Stock Threshold**: Alert when stock reaches this level
- **Out of Stock Threshold**: Mark as out of stock at this level

## Shipping & Tax Settings

### Tax Configuration
Even for digital products, tax may be required:

1. **Enable Taxes**: Turn tax calculations on/off
2. **Prices Entered With Tax**: Are product prices entered with tax included?
3. **Calculate Tax Based On**: Customer billing address or store location
4. **Shipping Tax Class**: Which tax class applies to shipping
5. **Rounding**: Round tax at subtotal level or per line

#### Tax Rates
Add tax rates for different regions:
1. **Country Code**: ISO country code
2. **State Code**: State/region code
3. **Postcode**: ZIP/postal code range
4. **City**: City name
5. **Rate %**: Tax percentage
6. **Tax Name**: Name for this tax rate
7. **Priority**: Which rate applies if multiple match
8. **Compound**: Compound tax calculation

## Performance & Security

### Cache Settings
- **Enable Cache**: Cache product pages
- **Cache Duration**: How long to cache pages
- **Clear Cache**: Manual cache clearing button

### Security Settings
- **Download Security**: Secure download URLs
- **Anti-leech Protection**: Prevent file sharing
- **IP Restriction**: Limit downloads by IP
- **User Agent Logging**: Log download user agents

### API Settings
- **Enable REST API**: Enable plugin REST API
- **API Key**: Generate API keys for external access
- **API Permissions**: Set API endpoint permissions
- **Rate Limiting**: Limit API requests per minute

## Advanced Settings

### Custom CSS/JS
- **Custom CSS**: Add custom styles without editing theme
- **Custom JavaScript**: Add custom scripts
- **Head Scripts**: Scripts in `<head>` section
- **Footer Scripts**: Scripts before `</body>`

### Integration Settings
- **Google Analytics**: Add tracking code
- **Facebook Pixel**: Add Facebook tracking
- **Custom Tracking**: Add custom tracking scripts

### Maintenance Settings
- **Maintenance Mode**: Put store in maintenance mode
- **Coming Soon Mode**: Show coming soon page
- **Store Notice**: Display notice at top of site

## Import/Export Settings

### Export Configuration
- **Export Products**: Export all products to CSV
- **Export Orders**: Export order history
- **Export Customers**: Export customer list
- **Export Settings**: Export plugin settings

### Import Configuration
- **Import Products**: Import products from CSV
- **Import Orders**: Import order history
- **Import Settings**: Import plugin settings

## Backup & Restore

### Automatic Backups
- **Enable Auto-backup**: Schedule automatic backups
- **Backup Frequency**: Daily, weekly, or monthly
- **Backup Retention**: How many backups to keep
- **Backup Location**: Where to store backups

### Manual Backup
- **Create Backup**: Create immediate backup
- **Download Backup**: Download backup file
- **Restore Backup**: Restore from backup file

## Saving Settings

### Saving Configuration
1. **Click "Save Changes"** at the bottom of any settings page
2. **Wait for confirmation** message
3. **Clear cache** if using caching plugins

### Resetting Settings
- **Reset Section**: Reset only current tab's settings
- **Reset All Settings**: Reset all plugin settings to defaults
- **Export Before Reset**: Always export settings before resetting

## Best Practices

### Configuration Checklist
- [ ] Set currency and location
- [ ] Configure payment gateways
- [ ] Set up email templates
- [ ] Configure user roles
- [ ] Set up product pages
- [ ] Enable security features
- [ ] Configure tax settings
- [ ] Set up backup schedule

### Performance Tips
1. **Enable caching** for better performance
2. **Optimize images** before uploading
3. **Use CDN** for product files
4. **Limit plugins** to essential ones
5. **Regular backups** to prevent data loss

### Security Tips
1. **Use SSL** for all pages
2. **Regular updates** of plugin and WordPress
3. **Strong passwords** for admin accounts
4. **Limit login attempts** with security plugin
5. **Regular security scans**

## Troubleshooting Configuration Issues

### Common Issues

#### Settings Not Saving
- Clear browser cache
- Check file permissions
- Disable conflicting plugins
- Increase PHP memory limit

#### Payment Gateway Not Working
- Verify API keys
- Check SSL certificate
- Test in sandbox mode first
- Check server timezone

#### Emails Not Sending
- Check spam folder
- Configure SMTP plugin
- Verify email settings
- Check server mail configuration

### Getting Help
If configuration issues persist:

1. **Check error logs** in Digiplanet → System Status
2. **Contact support** with:
   - Screenshots of settings
   - Error messages
   - System information
3. **Check documentation** for specific features

## Next Steps

After configuration:
1. [Add your first product](/documentation/#adding-products)
2. [Test the checkout process](/documentation/#testing)
3. [Set up Elementor widgets](/documentation/#elementor)
4. [Configure analytics](/documentation/#analytics)