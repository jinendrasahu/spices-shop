<?php
/**
 * WordPress Setup Script for Spices Shop
 * This script automatically configures WordPress with WooCommerce and sample spice products
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
    require_once ABSPATH . 'wp-load.php';
}

// Check if user is logged in or is admin
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to run this setup.');
}

echo '<div style="padding: 20px; font-family: Arial; background: #f5f5f5; margin: 20px;">';
echo '<h1>🌶️ Spices Shop Setup</h1>';

// Step 1: Install and activate WooCommerce
echo '<h2>Step 1: Installing WooCommerce...</h2>';
if (!is_plugin_active('woocommerce/woocommerce.php')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/misc.php';
    
    // Check if WooCommerce exists
    if (!file_exists(WP_PLUGIN_DIR . '/woocommerce/woocommerce.php')) {
        echo '<p style="color: orange;">WooCommerce not found. Please install manually from Plugins > Add New and search for WooCommerce.</p>';
    } else {
        activate_plugin('woocommerce/woocommerce.php');
        echo '<p style="color: green;">✓ WooCommerce activated</p>';
    }
}

// Step 2: Activate theme
echo '<h2>Step 2: Activating Theme...</h2>';
if (wp_get_theme('tm-organik-child')->exists()) {
    switch_theme('tm-organik-child');
    echo '<p style="color: green;">✓ Organik Child theme activated</p>';
} else {
    echo '<p style="color: orange;">⚠ Organik Child theme not found. Trying parent theme...</p>';
    if (wp_get_theme('tm-organik')->exists()) {
        switch_theme('tm-organik');
        echo '<p style="color: green;">✓ Organik theme activated</p>';
    }
}

// Step 3: Activate required plugins
echo '<h2>Step 3: Activating Plugins...</h2>';
$plugins_to_activate = [
    'insight-core/insight-core.php',
    'js_composer/js_composer.php',
    'revslider/revslider.php',
];

foreach ($plugins_to_activate as $plugin) {
    if (file_exists(WP_PLUGIN_DIR . '/' . $plugin)) {
        activate_plugin($plugin);
        echo '<p style="color: green;">✓ ' . basename(dirname($plugin)) . ' activated</p>';
    } else {
        echo '<p style="color: orange;">⚠ ' . basename(dirname($plugin)) . ' not found</p>';
    }
}

// Step 4: Create product categories
echo '<h2>Step 4: Creating Product Categories...</h2>';
$categories = [
    'Indian Spices' => 'Traditional spices from India',
    'Imported Spices' => 'Premium spices from around the world',
    'Organic Spices' => 'Certified organic spice collection',
    'Spice Blends' => 'Ready-to-use spice mixtures',
];

$category_ids = [];
foreach ($categories as $name => $desc) {
    $existing = get_term_by('name', $name, 'product_cat');
    if (!$existing) {
        $result = wp_insert_term($name, 'product_cat', ['description' => $desc]);
        if (!is_wp_error($result)) {
            $category_ids[$name] = $result['term_id'];
            echo '<p style="color: green;">✓ Created category: ' . $name . '</p>';
        }
    } else {
        $category_ids[$name] = $existing->term_id;
        echo '<p style="color: green;">✓ Category exists: ' . $name . '</p>';
    }
}

// Step 5: Create sample products
echo '<h2>Step 5: Creating Sample Spice Products...</h2>';

$spices = [
    [
        'name' => 'Premium Turmeric Powder',
        'category' => 'Indian Spices',
        'price' => 12.99,
        'description' => 'High-quality turmeric powder known for its vibrant color and earthy flavor. Rich in curcumin and antioxidants.',
        'sku' => 'SPICE-001'
    ],
    [
        'name' => 'Pure Cinnamon Sticks',
        'category' => 'Imported Spices',
        'price' => 18.50,
        'description' => 'Premium Ceylon cinnamon sticks with a sweet, warm aroma. Perfect for cooking and beverages.',
        'sku' => 'SPICE-002'
    ],
    [
        'name' => 'Organic Black Pepper',
        'category' => 'Organic Spices',
        'price' => 14.99,
        'description' => 'Freshly ground organic black pepper. Sharp and peppery flavor, adds zing to any dish.',
        'sku' => 'SPICE-003'
    ],
    [
        'name' => 'Cumin Seeds',
        'category' => 'Indian Spices',
        'price' => 9.99,
        'description' => 'Authentic Indian cumin seeds with a warm, nutty flavor. Essential for Indian cooking.',
        'sku' => 'SPICE-004'
    ],
    [
        'name' => 'Chili Powder Blend',
        'category' => 'Spice Blends',
        'price' => 8.50,
        'description' => 'Carefully blended chili powder mix. Adds heat and depth to curries and other dishes.',
        'sku' => 'SPICE-005'
    ],
    [
        'name' => 'Garam Masala Mix',
        'category' => 'Spice Blends',
        'price' => 11.99,
        'description' => 'Traditional garam masala blend with warming spices. Perfect for Indian cuisine.',
        'sku' => 'SPICE-006'
    ],
    [
        'name' => 'Cardamom Pods',
        'category' => 'Imported Spices',
        'price' => 22.00,
        'description' => 'Green cardamom pods with aromatic seeds. Premium quality for authentic flavor.',
        'sku' => 'SPICE-007'
    ],
    [
        'name' => 'Organic Cloves',
        'category' => 'Organic Spices',
        'price' => 15.99,
        'description' => 'Whole organic cloves with strong, sweet flavor. Great for spice rubs and beverages.',
        'sku' => 'SPICE-008'
    ],
];

$created_count = 0;
foreach ($spices as $spice) {
    // Check if product already exists
    $existing = get_page_by_title($spice['name'], OBJECT, 'product');
    
    if (!$existing) {
        $post_data = [
            'post_title' => $spice['name'],
            'post_content' => $spice['description'],
            'post_type' => 'product',
            'post_status' => 'publish',
        ];
        
        $post_id = wp_insert_post($post_data);
        
        if ($post_id) {
            // Set product type
            wp_set_object_terms($post_id, 'simple', 'product_type');
            
            // Set category
            if (isset($category_ids[$spice['category']])) {
                wp_set_object_terms($post_id, $category_ids[$spice['category']], 'product_cat');
            }
            
            // Set product meta
            update_post_meta($post_id, '_sku', $spice['sku']);
            update_post_meta($post_id, '_price', $spice['price']);
            update_post_meta($post_id, '_regular_price', $spice['price']);
            update_post_meta($post_id, '_sale_price', '');
            update_post_meta($post_id, '_visibility', 'visible');
            update_post_meta($post_id, '_stock_status', 'instock');
            update_post_meta($post_id, '_stock', 100);
            
            echo '<p style="color: green;">✓ Created: ' . $spice['name'] . ' (' . $spice['sku'] . ')</p>';
            $created_count++;
        }
    } else {
        echo '<p style="color: orange;">⚠ Product already exists: ' . $spice['name'] . '</p>';
    }
}

// Step 6: Configure WooCommerce settings
echo '<h2>Step 6: Configuring WooCommerce...</h2>';

update_option('blogname', 'Spices Shop');
update_option('blogdescription', 'Premium Spices from Around the World');
update_option('woocommerce_currency', 'USD');
update_option('woocommerce_default_country', 'US');
update_option('woocommerce_tax_classes', '');
update_option('woocommerce_calc_taxes', 'no');

echo '<p style="color: green;">✓ Store name set to: Spices Shop</p>';
echo '<p style="color: green;">✓ Currency set to: USD</p>';

// Step 7: Create sample pages
echo '<h2>Step 7: Creating Pages...</h2>';

$pages = [
    'About Us' => 'Welcome to Spices Shop! We offer premium spices sourced from the finest producers around the world. Our mission is to bring authentic flavors to your kitchen.',
    'Contact Us' => 'Contact us for any inquiries. We are here to help! Email: info@spicesshop.com | Phone: 1-800-SPICES',
];

foreach ($pages as $title => $content) {
    $existing = get_page_by_title($title);
    if (!$existing) {
        wp_insert_post([
            'post_title' => $title,
            'post_content' => $content,
            'post_type' => 'page',
            'post_status' => 'publish',
        ]);
        echo '<p style="color: green;">✓ Created page: ' . $title . '</p>';
    } else {
        echo '<p style="color: orange;">⚠ Page already exists: ' . $title . '</p>';
    }
}

echo '<h2 style="color: green; border-top: 2px solid green; padding-top: 20px;">✓ Setup Complete!</h2>';
echo '<p>Your Spices Shop has been configured with:</p>';
echo '<ul>';
echo '<li>✓ WooCommerce activated</li>';
echo '<li>✓ Organik theme activated</li>';
echo '<li>✓ ' . count($category_ids) . ' product categories created</li>';
echo '<li>✓ ' . $created_count . ' spice products created</li>';
echo '<li>✓ Store configured</li>';
echo '</ul>';
echo '<p><strong>Next Steps:</strong></p>';
echo '<ul>';
echo '<li>Go to <a href="/wp-admin/">WordPress Admin</a></li>';
echo '<li>Visit <a href="/">Your Store</a> to view the shop</li>';
echo '<li>Customize products with real images: Products > Edit</li>';
echo '<li>Set up payment methods: WooCommerce > Settings > Payments</li>';
echo '<li>Configure shipping: WooCommerce > Settings > Shipping</li>';
echo '</ul>';
echo '</div>';
?>
