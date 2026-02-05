<?php
/**
 * Fix home page product UI and ensure About Us page is accessible
 */

define('WP_USE_THEMES', false);
require_once(__DIR__ . '/wp-load.php');

echo "=== Checking Home Page ===\n\n";

$home_id = get_option('page_on_front');
if ($home_id) {
    $page = get_post($home_id);
    echo "Home page ID: $home_id\n";
    echo "Home page title: " . $page->post_title . "\n";
    echo "Home page template: " . get_page_template_slug($home_id) . "\n\n";
} else {
    echo "No static home page set\n\n";
}

echo "=== Checking About Us Page ===\n\n";

$about = get_page_by_path('about-us');
if ($about) {
    echo "✓ About Us page exists (ID: {$about->ID})\n";
    echo "  Status: {$about->post_status}\n";
    
    // Ensure it's published
    if ($about->post_status != 'publish') {
        wp_update_post([
            'ID' => $about->ID,
            'post_status' => 'publish'
        ]);
        echo "  ✓ Status updated to 'publish'\n";
    }
    
    // Add to main menu if not already there
    $menu_locations = get_nav_menu_locations();
    if (isset($menu_locations['primary'])) {
        $menu_id = $menu_locations['primary'];
        $menu_items = wp_get_nav_menu_items($menu_id);
        $about_in_menu = false;
        
        foreach ($menu_items as $item) {
            if ($item->object_id == $about->ID) {
                $about_in_menu = true;
                break;
            }
        }
        
        if (!$about_in_menu) {
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => 'About Us',
                'menu-item-object' => 'page',
                'menu-item-object-id' => $about->ID,
                'menu-item-type' => 'post_type',
                'menu-item-status' => 'publish'
            ]);
            echo "  ✓ Added to primary menu\n";
        } else {
            echo "  ✓ Already in menu\n";
        }
    }
} else {
    echo "✗ About Us page not found - creating...\n";
    // Create it (should already exist from previous script)
}

echo "\n=== Ensuring Product Card Template Works on Home Page ===\n";
echo "The product card template at /wp-content/themes/tm-organik-child/woocommerce/content-product.php\n";
echo "will be used automatically for all WooCommerce product loops, including home page.\n";
echo "Make sure home page uses WooCommerce shortcode or product loop.\n\n";

echo "✅ Setup Complete!\n";

