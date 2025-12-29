# Payment Gateway Setup

This guide covers setting up and configuring payment gateways for the Digiplanet Digital Products plugin.

## Supported Payment Gateways

The plugin supports multiple payment gateways:

1. **Stripe** - Credit/Debit Cards
2. **Paystack** - African Payments (Cards, Bank Transfer, USSD)
3. **Bank Transfer** - Manual payments
4. **Cash on Delivery** - Local pickups

## Stripe Setup

### Prerequisites for Stripe
1. **Stripe Account**: Sign up at [stripe.com](https://stripe.com)
2. **Business Details**: Complete Stripe onboarding
3. **Website URL**: Must be live (not localhost for production)
4. **SSL Certificate**: Required for live payments

### Step-by-Step Stripe Configuration

#### 1. Get Your Stripe API Keys

**For Live Mode:**
1. Log in to your [Stripe Dashboard](https://dashboard.stripe.com)
2. Click on "Developers" in the sidebar
3. Click "API keys"
4. Copy:
   - **Publishable Key** (starts with `pk_live_`)
   - **Secret Key** (starts with `sk_live_`)

**For Test Mode:**
1. In Stripe Dashboard, toggle "View test data"
2. Go to Developers → API keys
3. Copy:
   - **Test Publishable Key** (starts with `pk_test_`)
   - **Test Secret Key** (starts with `sk_test_`)

#### 2. Configure Stripe in WordPress

1. **Go to Digiplanet → Settings → Payments**
2. **Click on the "Stripe" tab**
3. **Enable Stripe**: Toggle to "Enabled"
4. **Enter API Keys**:

Live Publishable Key: pk_live_xxxxxxxxxxxxxxxxxxxx
Live Secret Key: sk_live_xxxxxxxxxxxxxxxxxxxx
Test Publishable Key: pk_test_xxxxxxxxxxxxxxxxxxxx
Test Secret Key: sk_test_xxxxxxxxxxxxxxxxxxxx

5. **Configure Settings**:
- **Title**: "Credit/Debit Card" (customer-facing name)
- **Description**: "Pay securely with your credit or debit card"
- **Test Mode**: Enable for testing, disable for live
- **Statement Descriptor**: Your business name (max 22 characters)
- **Capture Method**: "Automatic" (recommended)
- **Saved Cards**: Allow customers to save cards
- **Payment Methods**: Select which cards to accept

#### 3. Webhook Configuration (Important!)

Webhooks notify your site of payment events. **This is required for proper functionality.**

1. **In Stripe Dashboard**, go to Developers → Webhooks
2. **Click "Add endpoint"**
3. **Enter Endpoint URL**:

https://yourdomain.com/?digiplanet_stripe_webhook=1


Replace `yourdomain.com` with your actual domain
4. **Select events to listen to**:
- `payment_intent.succeeded`
- `payment_intent.payment_failed`
- `charge.refunded`
- `customer.subscription.created`
- `customer.subscription.deleted`
5. **Click "Add endpoint"**
6. **Copy the "Signing secret"** (starts with `whsec_`)
7. **Paste in WordPress**:
- Go to Digiplanet → Settings → Payments → Stripe
- Paste in "Webhook Secret" field
- Save changes

#### 4. Test Stripe Integration

**Test Mode:**
1. Enable "Test Mode" in plugin settings
2. Use Stripe test cards:
- Success: `4242 4242 4242 4242`
- Authentication required: `4000 0027 6000 3184`
- Decline: `4000 0000 0000 0002`
- CVC: Any 3 digits
- Expiry: Any future date
- ZIP: Any 5 digits

**Live Mode Testing:**
1. Disable "Test Mode"
2. Use a real card with small amount ($1)
3. Verify payment appears in Stripe Dashboard

### Stripe Advanced Settings

#### 3D Secure (Required in EU)
1. **Enable 3D Secure**: Required for SCA compliance
2. **Setup Future Payments**: For subscriptions
3. **Custom Payment Methods**: Apple Pay/Google Pay

#### Radar Rules (Fraud Prevention)
1. **Enable Radar**: Stripe's fraud prevention
2. **Block High-Risk Payments**: Auto-block suspicious payments
3. **Review Threshold**: Manual review threshold

#### Receipts & Invoices
1. **Auto-send Receipts**: Send Stripe receipts
2. **Custom Receipt Email**: Add custom message
3. **Invoice Settings**: Configure invoice templates

## Paystack Setup

### Prerequisites for Paystack
1. **Paystack Account**: Sign up at [paystack.com](https://paystack.com)
2. **Verified Business**: Complete business verification
3. **Bank Account**: Connect Nigerian bank account
4. **Live Mode Activation**: Request activation from Paystack

### Step-by-Step Paystack Configuration

#### 1. Get Your Paystack API Keys

**For Live Mode:**
1. Log in to [Paystack Dashboard](https://dashboard.paystack.com)
2. Click on "Settings" → "API Keys & Webhooks"
3. Copy:
- **Public Key** (starts with `pk_live_`)
- **Secret Key** (starts with `sk_live_`)

**For Test Mode:**
1. In Paystack Dashboard, toggle to "Test Mode"
2. Go to Settings → API Keys & Webhooks
3. Copy:
- **Test Public Key** (starts with `pk_test_`)
- **Test Secret Key** (starts with `sk_test_`)

#### 2. Configure Paystack in WordPress

1. **Go to Digiplanet → Settings → Payments**
2. **Click on the "Paystack" tab**
3. **Enable Paystack**: Toggle to "Enabled"
4. **Enter API Keys**:

Live Public Key: pk_live_xxxxxxxxxxxxxxxxxxxx
Live Secret Key: sk_live_xxxxxxxxxxxxxxxxxxxx
Test Public Key: pk_test_xxxxxxxxxxxxxxxxxxxx
Test Secret Key: sk_test_xxxxxxxxxxxxxxxxxxxx


5. **Configure Settings**:
- **Title**: "Pay with Paystack" (customer-facing name)
- **Description**: "Secure payments with cards, bank transfer, or USSD"
- **Test Mode**: Enable for testing
- **Merchant Email**: Your Paystack registered email
- **Payment Channels**: Select which methods to show
- **Auto-verify Transactions**: Auto-confirm successful payments

#### 3. Webhook Configuration

1. **In Paystack Dashboard**, go to Settings → Webhooks
2. **Click "Add Webhook"**
3. **Enter Webhook URL**:


https://yourdomain.com/?digiplanet_paystack_webhook=1


4. **Select Events**:
- `charge.success`
- `transfer.success`
- `refund.processed`
5. **Click "Add Webhook"**
6. **Test Webhook** using test button
7. **Verify in WordPress** that webhooks are received

#### 4. Test Paystack Integration

**Test Mode:**
1. Enable "Test Mode" in plugin settings
2. Use Paystack test cards:
- Success: `5078 5078 5078 5078`
- Authentication: `5061 0666 6666 6666`
- Insufficient funds: `5061 0666 6666 6667`
- Do not honor: `5061 0666 6666 6668`
- Expired card: `5061 0666 6666 6669`

**Bank Transfer Test:**
1. Select "Bank Transfer" at checkout
2. Use test account details provided
3. Verify payment confirmation

### Paystack Advanced Settings

#### Multiple Currency Support
1. **Enable Multi-currency**: Accept NGN, USD, GHS, etc.
2. **Default Currency**: Set store default currency
3. **Conversion Rates**: Automatic or manual rates

#### Transfer Settings
1. **Auto-transfer to Bank**: Automatic settlement
2. **Transfer Schedule**: Daily, weekly, or manual
3. **Transfer Fees**: Configure fee structure

#### Subaccount Support
1. **Enable Subaccounts**: For marketplace splits
2. **Split Payments**: Percentage or fixed amount splits
3. **Subaccount Management**: Add multiple subaccounts

## Bank Transfer Setup

### Manual Payment Method

1. **Go to Digiplanet → Settings → Payments**
2. **Click "Bank Transfer" tab**
3. **Enable Bank Transfer**: Toggle to "Enabled"
4. **Configure Settings**:
- **Title**: "Bank Transfer"
- **Description**: "Make payment directly to our bank account"
- **Instructions**: Add your bank details
- **Account Name**: Your account name
- **Bank Name**: Your bank name
- **Account Number**: Your account number
- **Routing/Sort Code**: If applicable
- **IBAN/SWIFT**: For international transfers
- **Additional Info**: Any special instructions

### Order Processing
- **Pending Status**: Orders marked pending until payment confirmation
- **Manual Verification**: Admin manually verifies payments
- **Notification**: Email admin on new bank transfer orders

## Cash on Delivery Setup

### For Local Pickups

1. **Go to Digiplanet → Settings → Payments**
2. **Click "Cash on Delivery" tab**
3. **Enable COD**: Toggle to "Enabled"
4. **Configure Settings**:
- **Title**: "Cash on Delivery"
- **Description**: "Pay when you receive your digital product"
- **Instructions**: Pickup location/details
- **Minimum Amount**: Minimum order for COD
- **Maximum Amount**: Maximum order for COD
- **Allowed Locations**: Restrict to specific areas

## Payment Gateway Comparison

| Feature | Stripe | Paystack | Bank Transfer | COD |
|---------|--------|----------|---------------|-----|
| **Instant Payment** | ✓ | ✓ | ✗ | ✗ |
| **Card Support** | ✓ | ✓ | ✗ | ✗ |
| **Bank Transfer** | ✗ | ✓ | ✓ | ✗ |
| **USSD** | ✗ | ✓ | ✗ | ✗ |
| **Mobile Money** | ✗ | ✓ | ✗ | ✗ |
| **Fees** | 2.9% + $0.30 | 1.5% + ₦100 | None | None |
| **Settlement** | 2-7 days | 24 hours | Manual | Manual |
| **Currency** | Multi | Multi | Local | Local |
| **Refunds** | ✓ | ✓ | Manual | Manual |

## Payment Flow Configuration

### Checkout Process

1. **Customer selects payment method**
2. **Redirect to gateway** (or stay on site for Stripe)
3. **Payment processing**
4. **Webhook notification**
5. **Order confirmation**
6. **Product delivery**

### Order Status Flow

Pending → Processing → Completed
↘ On Hold → Cancelled


### Failed Payment Handling
1. **Retry Logic**: Allow payment retry
2. **Failed Order Status**: Mark as failed
3. **Customer Notification**: Email customer
4. **Admin Alert**: Notify admin of failures

## Currency & Pricing

### Multi-currency Support
1. **Base Currency**: Set your store currency
2. **Exchange Rates**: Automatic or manual rates
3. **Currency Switcher**: Frontend currency selector
4. **Rounding Rules**: How to round converted prices

### Price Display
1. **Tax Inclusion**: Show prices with/without tax
2. **Currency Symbol**: Position and format
3. **Decimal Places**: 0, 1, or 2 decimals
4. **Thousands Separator**: Comma or period

## Tax Configuration

### Digital Product Taxes
1. **Tax Calculation**: Enable/disable tax
2. **Tax Rates**: Set by country/state
3. **Tax Classes**: Different rates for different products
4. **Tax Display**: Show prices incl/excl tax

### VAT MOSS (EU)
1. **EU VAT Numbers**: Validate VAT numbers
2. **B2B vs B2C**: Different rates for businesses
3. **Location Evidence**: Collect proof of location
4. **Reporting**: Generate VAT reports

## Subscription Payments

### Recurring Payments Setup
1. **Enable Subscriptions**: In payment gateway
2. **Plan Creation**: Create subscription plans
3. **Trial Periods**: Configure free trials
4. **Grace Periods**: Allow failed payment grace period

### Subscription Management
1. **Customer Portal**: Self-service subscription management
2. **Upgrade/Downgrade**: Change subscription plans
3. **Cancellation**: Process cancellations
4. **Expiration**: Handle expired subscriptions

## Security & Compliance

### PCI Compliance
1. **Stripe**: PCI DSS Level 1 compliant
2. **Paystack**: PCI DSS compliant
3. **Never store cards**: Tokens only
4. **SSL Required**: For all payment pages

### Data Protection
1. **GDPR Compliance**: Data processing agreements
2. **Privacy Policy**: Link to privacy policy
3. **Data Retention**: Set retention periods
4. **Right to Erasure**: Delete customer data

### Fraud Prevention
1. **AVS Checks**: Address verification
2. **CVC Verification**: Card verification code
3. **IP Geolocation**: Location checks
4. **Velocity Checks**: Multiple payment attempts

## Testing Payments

### Test Environment Setup
1. **Enable Test Mode**: In plugin settings
2. **Use Test Cards**: Provided by gateways
3. **Test Webhooks**: Use webhook testing tools
4. **Sandbox Accounts**: Create test accounts

### Test Scenarios
1. **Successful Payment**: Complete checkout
2. **Failed Payment**: Decline card
3. **Refund Test**: Process refund
4. **Webhook Test**: Simulate webhook events
5. **Currency Test**: Different currencies
6. **Tax Test**: Tax calculations

### Live Testing Checklist
- [ ] Disable test mode
- [ ] Use real card with small amount
- [ ] Verify payment in gateway dashboard
- [ ] Check order status in WordPress
- [ ] Verify customer email received
- [ ] Test product download
- [ ] Process refund
- [ ] Check all webhook events

## Troubleshooting Payment Issues

### Common Problems & Solutions

#### Payments Not Processing
1. **Check API Keys**: Verify keys are correct
2. **SSL Certificate**: Ensure SSL is installed
3. **Server Time**: Correct server timezone
4. **Firewall Rules**: Allow gateway domains

#### Webhook Issues
1. **Endpoint URL**: Correct webhook URL
2. **Secret Key**: Matching signing secret
3. **Server Access**: Gateway can reach your site
4. **Logs**: Check webhook delivery logs

#### Currency Problems
1. **Gateway Support**: Check currency support
2. **Exchange Rates**: Update rates
3. **Rounding Errors**: Adjust decimal places
4. **Formatting**: Correct currency format

#### Refund Issues
1. **Gateway Balance**: Sufficient funds for refund
2. **Time Limit**: Within refund period
3. **Original Payment**: Refund to same method
4. **Status Check**: Verify refund status

### Debug Mode
Enable debug logging:
1. Go to Digiplanet → Settings → Advanced
2. Enable "Debug Mode"
3. Check logs in Digiplanet → System Status
4. Disable debug after troubleshooting

### Getting Support
When contacting support:
1. **Gateway**: Which payment gateway
2. **Error Message**: Exact error shown
3. **Order ID**: Affected order number
4. **Screenshots**: Of settings and errors
5. **Logs**: Debug logs if enabled

## Best Practices

### Security Best Practices
1. **Regular Updates**: Keep plugin and gateways updated
2. **API Key Rotation**: Rotate keys periodically
3. **Access Control**: Limit admin access
4. **Monitoring**: Monitor for suspicious activity

### Performance Optimization
1. **Caching**: Cache product pages
2. **CDN**: Use CDN for assets
3. **Optimize Images**: Compress product images
4. **Database Optimization**: Regular maintenance

### Customer Experience
1. **Clear Instructions**: Payment instructions
2. **Multiple Options**: Offer several payment methods
3. **Mobile Optimized**: Mobile-friendly checkout
4. **Quick Support**: Provide payment support

## Advanced Configuration

### Custom Payment Gateway
Add custom gateway via code:
```php
add_filter('digiplanet_payment_gateways', function($gateways) {
    $gateways['custom'] = array(
        'title' => 'Custom Gateway',
        'class' => 'Custom_Payment_Gateway',
        'file' => 'custom-gateway.php'
    );
    return $gateways;
});


Hook Reference
Available payment hooks:

digiplanet_before_payment_processing

digiplanet_after_payment_success

digiplanet_after_payment_failure

digiplanet_payment_gateway_settings

digiplanet_payment_validation

API Reference
Payment API endpoints:

/digiplanet-api/payment/process

/digiplanet-api/payment/status/{order_id}

/digiplanet-api/payment/refund

/digiplanet-api/payment/webhook/{gateway}

Migration from Other Plugins
Migrating Payment Data
Export Orders: From old plugin

Import Orders: Using our import tool

Customer Migration: Move customer data

Transaction Mapping: Map old transaction IDs

Testing After Migration
Test Payments: Make test purchases

Verify Data: Check order history

Customer Access: Test customer accounts

Reporting: Verify financial reports

Next Steps
After payment setup:

Test the checkout process

Configure email notifications

Set up analytics tracking

Create your first product


## 7. `documentation/sections/elementor-widgets.md`

```markdown
# Elementor Widgets Guide

The Digiplanet Digital Products plugin includes powerful Elementor widgets to build beautiful product pages and shopping experiences without coding.

## Available Widgets

The plugin provides the following Elementor widgets:

1. **Product Grid** - Display products in grid layout
2. **Product Carousel** - Show products in a slider/carousel
3. **Product Categories** - Display product categories
4. **Product Search** - Product search form
5. **Product Detail** - Single product display
6. **Add to Cart Button** - Customizable buy button
7. **Cart Icon** - Shopping cart with counter
8. **Checkout Form** - Complete checkout form
9. **Account Dashboard** - User account portal
10. **Product Filter** - Advanced filtering options

## Installation & Activation

### Prerequisites
- WordPress 5.6+
- Elementor 3.5+
- Digiplanet Digital Products plugin activated

### Automatic Installation
The widgets are automatically available when:
1. Elementor is installed and activated
2. Digiplanet Digital Products is activated
3. You're using Elementor Pro (recommended) or Free

### Manual Installation (if needed)
1. Go to Elementor → Settings → Advanced
2. Enable "Digiplanet Widgets" if option exists
3. Clear cache and refresh

## Widget Configuration

### Product Grid Widget

#### Basic Settings
- **Layout**: Grid or List view
- **Columns**: Number of columns (1-6)
- **Rows**: Number of rows to show
- **Products Per Page**: Items per page

#### Content Settings
- **Source**: Latest, Featured, On Sale, Category, Tag
- **Category**: Filter by specific category
- **Tag**: Filter by specific tag
- **Order By**: Date, Price, Sales, Rating, Random
- **Order**: Ascending or Descending
- **Exclude Products**: Hide specific products

#### Style Settings
- **Card Style**: Default, Modern, Minimal, Classic
- **Image Size**: Thumbnail, Medium, Large, Full
- **Image Aspect Ratio**: Square, Portrait, Landscape
- **Typography**: Title, Price, Description fonts
- **Colors**: Background, text, button colors
- **Border & Shadow**: Card border and shadow
- **Animation**: Hover animations

#### Advanced Features
- **Pagination**: Load more or numbered pagination
- **Quick View**: Enable quick product view
- **Wishlist**: Add to wishlist button
- **Compare**: Add to compare button
- **Badges**: Sale, New, Featured badges

### Product Carousel Widget

#### Carousel Settings
- **Slides to Show**: Number of visible slides
- **Slides to Scroll**: Items per scroll
- **Autoplay**: Enable/disable auto rotation
- **Autoplay Speed**: Rotation speed in milliseconds
- **Infinite Loop**: Continuous scrolling
- **Pause on Hover**: Pause on mouse hover
- **Show Arrows**: Navigation arrows
- **Show Dots**: Pagination dots
- **Center Mode**: Center active slide

#### Responsive Settings
- **Breakpoints**: Configure for tablet/mobile
- **Tablet Columns**: Columns on tablet
- **Mobile Columns**: Columns on mobile
- **Vertical on Mobile**: Stack on mobile

#### Style Settings
- **Arrow Style**: Custom arrow design
- **Dot Style**: Custom pagination dots
- **Slide Spacing**: Space between slides
- **Slide Animation**: Slide, fade, cube effect

### Product Categories Widget

#### Display Settings
- **Layout**: Grid, List, Dropdown
- **Columns**: Number of columns
- **Show Count**: Product count in category
- **Show Empty**: Show empty categories
- **Hierarchical**: Show subcategories
- **Order By**: Name, Slug, Count, ID
- **Order**: Ascending/Descending

#### Style Settings
- **Category Card**: Customize card design
- **Image Style**: Round, square, custom shape
- **Overlay Effects**: Hover overlay effects
- **Typography**: Category name styling
- **Icon**: Custom category icons

### Product Search Widget

#### Search Settings
- **Placeholder**: Search input placeholder text
- **Button Text**: Search button text
- **Search Type**: Products only or all content
- **Live Search**: Enable instant results
- **Min Characters**: Minimum characters for search
- **Search in**: Title, description, content, SKU

#### Style Settings
- **Form Style**: Modern, minimal, classic
- **Input Style**: Customize input field
- **Button Style**: Customize search button
- **Results Style**: Dropdown results styling
- **Icon**: Search icon customization

### Product Detail Widget

#### Layout Options
- **Template**: Choose from pre-made templates
- **Image Gallery**: Gallery or single image
- **Image Position**: Left, right, or top
- **Tabs Layout**: Horizontal or vertical tabs
- **Sticky Sidebar**: Sticky product info

#### Content Sections
- **Show Title**: Display product title
- **Show Price**: Display price with formatting
- **Show Rating**: Display star ratings
- **Show Description**: Full or excerpt
- **Show Add to Cart**: Customizable button
- **Show Meta**: Categories, tags, SKU
- **Show Reviews**: Customer reviews section
- **Show Related**: Related products

#### Style Customization
- **Gallery Style**: Thumbnail position, zoom
- **Typography**: Custom fonts for all text
- **Color Scheme**: Custom color palette
- **Button Styles**: Add to cart, wishlist buttons
- **Tab Styles**: Customize tab design

### Add to Cart Button Widget

#### Button Settings
- **Button Type**: Button, icon, text link
- **Button Text**: Custom text for button
- **Icon**: Choose icon for button
- **Icon Position**: Left or right of text
- **Quantity Selector**: Show quantity input
- **Variable Options**: For variable products

#### Style Settings
- **Button Style**: Primary, secondary, custom
- **Hover Effects**: Hover animations
- **Size**: Small, medium, large, custom
- **Typography**: Button text styling
- **Border & Shadow**: Custom borders
- **Width**: Full width or custom

### Cart Icon Widget

#### Display Settings
- **Icon Type**: Shopping cart, basket, custom
- **Show Count**: Display item count
- **Count Position**: Top, bottom, inline
- **Show Total**: Display cart total
- **Dropdown**: Show cart dropdown on hover
- **Link To**: Cart page or checkout

#### Style Settings
- **Icon Style**: Choose icon design
- **Badge Style**: Count badge styling
- **Dropdown Style**: Cart dropdown design
- **Typography**: Text styling in dropdown
- **Animation**: Hover and click animations

### Checkout Form Widget

#### Form Layout
- **Template**: Modern, classic, minimal
- **Columns**: One column or two columns
- **Guest Checkout**: Allow guest checkout
- **Login Reminder**: Show login prompt
- **Coupon Field**: Show coupon field
- **Terms Checkbox**: Require terms agreement

#### Section Controls
- **Billing Details**: Show/hide billing fields
- **Shipping Details**: Show shipping fields
- **Order Review**: Show order summary
- **Payment Methods**: Show payment options
- **Additional Notes**: Show notes field

#### Style Settings
- **Form Style**: Custom form styling
- **Field Styles**: Input field customization
- **Button Styles**: Place order button
- **Section Headers**: Style section headers
- **Error Messages**: Custom error styling

### Account Dashboard Widget

#### Dashboard Layout
- **Template**: Customer or client dashboard
- **Welcome Message**: Custom welcome text
- **Show Stats**: Display user statistics
- **Quick Links**: Custom quick links
- **Recent Activity**: Show recent orders
- **Profile Summary**: Show profile info

#### Tab Management
- **Available Tabs**: Choose which tabs to show
- **Tab Order**: Reorder dashboard tabs
- **Custom Tabs**: Add custom dashboard tabs
- **Tab Icons**: Add icons to tabs

#### Style Settings
- **Dashboard Style**: Overall styling
- **Card Style**: Stats card design
- **Tab Style**: Tab navigation styling
- **Table Style**: Order table styling
- **Profile Style**: Profile section design

### Product Filter Widget

#### Filter Types
- **Price Filter**: Price range slider
- **Category Filter**: Category checklist
- **Tag Filter**: Tag cloud or checklist
- **Attribute Filter**: Custom attributes
- **Rating Filter**: Star rating filter
- **Stock Filter**: In stock/out of stock

#### Display Settings
- **Layout**: Sidebar, horizontal, dropdown
- **Collapsible**: Collapse filter sections
- **Show Count**: Show product counts
- **Ajax Filtering**: Live filtering without reload
- **Reset Button**: Show reset filters button

#### Style Settings
- **Filter Style**: Modern, minimal, classic
- **Slider Style**: Price slider customization
- **Checkbox Style**: Custom checkbox design
- **Button Style**: Apply/reset button styling

## Advanced Widget Features

### Dynamic Content
All widgets support Elementor dynamic tags:
- **Product Data**: Title, price, description, image
- **User Data**: Name, email, account info
- **Cart Data**: Items, total, count
- **Site Data**: Site name, logo, URL

### Conditional Display
Show/hide widgets based on conditions:
- **User Role**: Digital customer vs software client
- **Cart Status**: Empty vs has items
- **Product Type**: Specific product types
- **Page Type**: Shop, product, cart, checkout

### Custom CSS & JavaScript
Each widget has advanced tab for:
- **Custom CSS**: Widget-specific styles
- **Custom Attributes**: HTML data attributes
- **Animation**: Entrance animations
- **Responsive**: Custom responsive controls

### Global Widgets
Save widget settings as globals:
1. Configure a widget
2. Click "Save as Global"
3. Reuse across site
4. Update once, apply everywhere

## Templates & Pre-made Designs

### Included Templates
The plugin includes pre-designed templates:

#### Product Grid Templates
1. **Modern Grid**: Clean, card-based layout
2. **Minimal Grid**: Simple, focus on products
3. **Masonry Grid**: Pinterest-style layout
4. **List View**: Detailed product list

#### Single Product Templates
1. **Classic Product**: Traditional product page
2. **Modern Product**: Full-width gallery
3. **Minimal Product**: Focus on essentials
4. **Creative Product**: Unique layout options

#### Cart & Checkout Templates
1. **Modern Cart**: Clean cart design
2. **One-Page Checkout**: Streamlined checkout
3. **Multi-step Checkout**: Guided checkout
4. **Minimal Checkout**: Distraction-free

### Creating Custom Templates

#### Using Theme Builder
1. Go to Elementor → Theme Builder
2. Create new template
3. Choose "Single Product" or "Archive"
4. Design with Digiplanet widgets
5. Set display conditions

#### Saving as Template
1. Design a section with widgets
2. Right-click → Save as Template
3. Name and categorize
4. Reuse in other pages

## Responsive Design

### Mobile Optimization
All widgets are mobile-responsive:
- **Stack Columns**: Auto-stack on mobile
- **Touch-Friendly**: Optimized for touch
- **Readable Text**: Proper font sizes
- **Accessible**: Proper contrast ratios

### Breakpoint Configuration
Configure different settings per device:
1. **Desktop**: Default settings
2. **Tablet**: 768px and below
3. **Mobile**: 480px and below
4. **Custom**: Add custom breakpoints

### Responsive Testing
Test widgets on all devices:
1. Use Elementor responsive mode
2. Test actual mobile devices
3. Check different screen sizes
4. Verify touch interactions

## Performance Optimization

### Widget Performance Tips
1. **Limit Products**: Don't show too many at once
2. **Lazy Load**: Enable lazy loading for images
3. **Cache Results**: Use Elementor cache
4. **Minimize Animations**: Reduce heavy animations
5. **Optimize Images**: Compress product images

### Loading Optimization
- **Defer JavaScript**: Load scripts after page
- **Async Loading**: Load non-critical scripts async
- **Critical CSS**: Inline critical styles
- **Image Optimization**: WebP format, proper sizing

## Customization Examples

### Custom Product Grid
```css
/* Custom card design */
.digiplanet-product-card {
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.3s ease;
}

.digiplanet-product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

/* Custom badge */
.digiplanet-badge-sale {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: bold;
}


Custom Add to Cart Button

/* Gradient button */
.digiplanet-add-to-cart {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    padding: 12px 30px;
    border-radius: 30px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
}

.digiplanet-add-to-cart:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

Integration with Other Elementor Widgets
Combining with Pro Widgets
Forms: Connect to product inquiries

Popup: Show cart confirmation

Slides: Product feature slides

Countdown: Sale countdown timer

Table: Product comparison table

Third-party Widgets
Header/Footer: Integration with header builders

Menu Cart: Compatible with menu cart plugins

Wishlist: Integration with wishlist plugins

Social Proof: Review and testimonial widgets

Troubleshooting
Common Issues
Widgets Not Showing
Clear Cache: Elementor and site cache

Regenerate CSS: Elementor → Tools → Regenerate CSS

Update Plugins: Ensure all are updated

PHP Version: Minimum PHP 7.4 required

Styling Issues
Specificity: Use more specific CSS selectors

!important: Avoid unless necessary

Inheritance: Check parent element styles

Cache: Clear browser and site cache

Performance Issues
Widget Count: Reduce number of widgets

Image Size: Optimize product images

Animations: Reduce complex animations

Server Resources: Upgrade hosting if needed

Debug Mode
Enable Elementor debug:

Go to Elementor → Settings → Advanced

Enable "Debug Mode"

Check browser console for errors

Disable after troubleshooting

Best Practices
Design Guidelines
Consistent Branding: Use brand colors and fonts

Mobile First: Design for mobile first

Clear CTAs: Make buttons obvious

Readable Text: Proper contrast and size

Fast Loading: Optimize for speed

User Experience
Intuitive Navigation: Easy to find products

Quick Checkout: Minimal steps to purchase

Clear Pricing: No hidden costs

Trust Signals: Reviews, badges, guarantees

Support Access: Easy contact options

Maintenance
Regular Updates: Keep widgets updated

Backup Designs: Export widget templates

Test Updates: Test in staging first

Monitor Performance: Regular speed tests

Advanced Customization

Custom Widget Development

Create custom widgets:



class Custom_Product_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'custom_product_widget';
    }
    
    public function get_title() {
        return __('Custom Product Widget', 'digiplanet');
    }
    
    // ... widget implementation
}

add_action('elementor/widgets/register', function($widgets_manager) {
    $widgets_manager->register(new Custom_Product_Widget());
});




Hooks & Filters
Available hooks for customization:

// Modify widget categories
add_filter('digiplanet_elementor_categories', function($categories) {
    $categories[] = [
        'name' => 'digiplanet',
        'title' => __('Digiplanet', 'digiplanet-digital-products')
    ];
    return $categories;
});

// Custom widget settings
add_filter('digiplanet_product_grid_settings', function($settings) {
    $settings['new_option'] = 'value';
    return $settings;
});



API Integration
Use Digiplanet API in widgets:


// AJAX product loading
jQuery.ajax({
    url: digiplanet_ajax.ajax_url,
    method: 'POST',
    data: {
        action: 'digiplanet_get_products',
        category: 'scripts',
        nonce: digiplanet_ajax.nonce
    },
    success: function(response) {
        // Update widget with products
    }
});



Support & Resources
Documentation
Elementor Docs: elementor.com/help

Plugin Docs: In-plugin documentation

Video Tutorials: Available in member area

Community Support
Facebook Group: Digiplanet Users Community

GitHub Issues: Bug reports and feature requests

Forum: Official support forum

Professional Services
Custom Development: Hire for custom widgets

Design Services: Professional template design

Training: One-on-one training sessions

Next Steps
After setting up Elementor widgets:

Create product pages

Design checkout flow

Build account portals

Test user experience

Optimize for conversions



## 8. `documentation/sections/troubleshooting.md`

```markdown
# Troubleshooting Guide

This guide helps you resolve common issues with the Digiplanet Digital Products plugin.

## Quick Troubleshooting Checklist

Before diving deep, try these quick fixes:

1. **Clear all caches** (browser, plugin, server)
2. **Update everything** (WordPress, plugins, theme)
3. **Disable other plugins** to check for conflicts
4. **Switch to default theme** (Twenty Twenty-Four)
5. **Check error logs** in Digiplanet → System Status
6. **Increase PHP limits** (memory_limit, max_execution_time)

## Common Issues & Solutions

### Installation Issues

#### Issue: "Plugin failed to activate"
**Symptoms**: Error message when activating plugin

**Solutions**:
1. **Check PHP Version**
   ```bash
   # Current PHP version
   php -v
   
   
Minimum: PHP 7.4, Recommended: PHP 8.0+

How to update:

Contact your hosting provider

Use hosting control panel (cPanel, Plesk)

Update via command line (if you have access)

Increase PHP Memory Limit
Add to wp-config.php:


define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');   


Check File Permissions


# Correct permissions for WordPress
find /path/to/wordpress -type d -exec chmod 755 {} \;
find /path/to/wordpress -type f -exec chmod 644 {} \;
chmod 755 wp-content


Check for Missing Extensions
Required PHP extensions:

curl

json

mbstring

openssl

mysqli

pdo_mysql

Enable in php.ini:



extension=curl
extension=json
extension=mbstring
extension=openssl


Issue: "Database tables not created"
Symptoms: Tables missing, data not saving

Solutions:

Manual Table Creation
Go to Digiplanet → Tools → Database
Click "Create Missing Tables"

Check Database User Permissions
User needs these permissions:

CREATE

ALTER

INSERT

UPDATE

DELETE

SELECT

Repair Database Tables

-- In phpMyAdmin or MySQL CLI
REPAIR TABLE wp_digiplanet_products;
REPAIR TABLE wp_digiplanet_orders;

Check Table Prefix
Verify tables use correct prefix in wp-config.php:

$table_prefix = 'wp_';  // Change if different

Payment Issues
Issue: "Payment not processing"
Symptoms: Payment fails, no order created

Solutions:

For Stripe:

Verify API Keys

Live vs Test mode keys

No typos or spaces

Keys from correct Stripe account

Check SSL Certificate

# Test SSL
openssl s_client -connect yourdomain.com:443