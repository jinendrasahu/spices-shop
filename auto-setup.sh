#!/bin/bash

# Setup script to configure WordPress via container
CONTAINER="wordpress_app"

echo "🌶️ Setting up Spices Shop..."
echo ""

# Create a setup script inside the container
docker exec $CONTAINER bash -c 'cat > /tmp/setup.php << "EOF"
<?php
// Bootstrap WordPress
define("WP_USE_THEMES", false);
require("/var/www/html/wp-load.php");

// Allow unauthenticated access for this script
if (!function_exists("is_admin")) {
    function is_admin() {
        return true;
    }
}

if (!function_exists("current_user_can")) {
    function current_user_can($cap) {
        return true;
    }
}

// 1. Activate WooCommerce
echo "1. Installing WooCommerce...\n";
if (!is_plugin_active("woocommerce/woocommerce.php")) {
    if (file_exists(WP_PLUGIN_DIR . "/woocommerce/woocommerce.php")) {
        require_once(ABSPATH . "wp-admin/includes/plugin.php");
        activate_plugin("woocommerce/woocommerce.php");
        echo "   ✓ WooCommerce activated\n";
    } else {
        echo "   ⚠ WooCommerce plugin not found\n";
    }
} else {
    echo "   ✓ WooCommerce already active\n";
}

// 2. Activate theme
echo "\n2. Activating Organik theme...\n";
if (wp_get_theme("tm-organik-child")->exists()) {
    switch_theme("tm-organik-child");
    echo "   ✓ Organik Child theme activated\n";
} else {
    echo "   ⚠ Organik Child theme not found\n";
}

// 3. Activate plugins
echo "\n3. Activating plugins...\n";
require_once(ABSPATH . "wp-admin/includes/plugin.php");

$plugins = [
    "insight-core/insight-core.php",
    "js_composer/js_composer.php",
    "revslider/revslider.php"
];

foreach ($plugins as $plugin) {
    if (file_exists(WP_PLUGIN_DIR . "/" . $plugin)) {
        activate_plugin($plugin);
        echo "   ✓ " . basename(dirname($plugin)) . " activated\n";
    }
}

// 4. Update store settings
echo "\n4. Configuring store...\n";
update_option("blogname", "Spices Shop");
update_option("blogdescription", "Premium Spices from Around the World");
update_option("woocommerce_currency", "USD");
update_option("woocommerce_default_country", "US");
echo "   ✓ Store configured\n";

// 5. Create categories
echo "\n5. Creating product categories...\n";
$categories = [
    "Indian Spices" => "Traditional spices from India",
    "Imported Spices" => "Premium spices from around the world",
    "Organic Spices" => "Certified organic spice collection",
    "Spice Blends" => "Ready-to-use spice mixtures"
];

$category_ids = [];
foreach ($categories as $name => $desc) {
    $existing = get_term_by("name", $name, "product_cat");
    if (!$existing) {
        $result = wp_insert_term($name, "product_cat", ["description" => $desc]);
        if (!is_wp_error($result)) {
            $category_ids[$name] = $result["term_id"];
            echo "   ✓ Created: $name\n";
        }
    } else {
        $category_ids[$name] = $existing->term_id;
        echo "   ✓ Exists: $name\n";
    }
}

// 6. Create sample products
echo "\n6. Creating sample spice products...\n";
$spices = [
    ["name" => "Premium Turmeric Powder", "cat" => "Indian Spices", "price" => 12.99, "sku" => "SPICE-001"],
    ["name" => "Pure Cinnamon Sticks", "cat" => "Imported Spices", "price" => 18.50, "sku" => "SPICE-002"],
    ["name" => "Organic Black Pepper", "cat" => "Organic Spices", "price" => 14.99, "sku" => "SPICE-003"],
    ["name" => "Cumin Seeds", "cat" => "Indian Spices", "price" => 9.99, "sku" => "SPICE-004"],
    ["name" => "Chili Powder Blend", "cat" => "Spice Blends", "price" => 8.50, "sku" => "SPICE-005"],
    ["name" => "Garam Masala Mix", "cat" => "Spice Blends", "price" => 11.99, "sku" => "SPICE-006"],
    ["name" => "Cardamom Pods", "cat" => "Imported Spices", "price" => 22.00, "sku" => "SPICE-007"],
    ["name" => "Organic Cloves", "cat" => "Organic Spices", "price" => 15.99, "sku" => "SPICE-008"]
];

$created = 0;
foreach ($spices as $spice) {
    $existing = get_page_by_title($spice["name"], OBJECT, "product");
    if (!$existing) {
        $post_id = wp_insert_post([
            "post_title" => $spice["name"],
            "post_type" => "product",
            "post_status" => "publish",
            "post_content" => "High-quality " . strtolower($spice["name"])
        ]);
        
        if ($post_id) {
            wp_set_object_terms($post_id, "simple", "product_type");
            if (isset($category_ids[$spice["cat"]])) {
                wp_set_object_terms($post_id, $category_ids[$spice["cat"]], "product_cat");
            }
            
            update_post_meta($post_id, "_sku", $spice["sku"]);
            update_post_meta($post_id, "_price", $spice["price"]);
            update_post_meta($post_id, "_regular_price", $spice["price"]);
            update_post_meta($post_id, "_visibility", "visible");
            update_post_meta($post_id, "_stock_status", "instock");
            update_post_meta($post_id, "_stock", 100);
            
            echo "   ✓ " . $spice["name"] . " (" . $spice["sku"] . ")\n";
            $created++;
        }
    }
}

echo "\n✅ Setup Complete!\n";
echo "   - 4 Product Categories created\n";
echo "   - $created Products created\n";
echo "   - Store configured\n";
echo "\n🌐 Visit: http://localhost:8080\n";
echo "📊 Admin: http://localhost:8080/wp-admin\n";
EOF
'

# Run the setup script
echo ""
echo "Running setup inside container..."
docker exec $CONTAINER php /tmp/setup.php

# Run configure-site.php for pages, menu, Cash on Delivery, and dummy images
echo ""
echo "Running full site configuration (pages, menu, Cash on Delivery, product images)..."
if docker exec $CONTAINER test -f /var/www/html/configure-site.php 2>/dev/null; then
    docker exec $CONTAINER php /var/www/html/configure-site.php
else
    echo "⚠ configure-site.php not found. Copy it to container:"
    echo "  docker cp configure-site.php $CONTAINER:/var/www/html/"
    echo "  docker exec $CONTAINER php /var/www/html/configure-site.php"
fi

echo ""
echo "✅ Organic Spices Shop Setup Complete!"
echo ""
echo "Pages: Cart, Checkout, My account, Sample Page, Shop, Hero (Home)"
echo "Payment: Cash on Delivery only"
echo ""
echo "Next steps:"
echo "1. Visit http://localhost:8080 to see your shop"
echo "2. Admin panel: http://localhost:8080/wp-admin"
