<?php
/**
 * WordPress Spices Shop Setup - Organic Cosmetics Style UI
 * Pages: Cart, Checkout, My account, Shop, Hero (Home)
 * Payment: Cash on Delivery only
 * Downloads dummy images from internet
 */
define("WP_USE_THEMES", false);
require("wp-load.php");

echo "Starting Organic Spices Shop setup...\n\n";

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

// Activate WooCommerce if available
if (file_exists(WP_PLUGIN_DIR . '/woocommerce/woocommerce.php')) {
    activate_plugin('woocommerce/woocommerce.php');
    echo "✓ WooCommerce activated\n";
}

function create_page($title, $slug, $content) {
    $existing = get_page_by_path($slug);
    if ($existing) return $existing->ID;
    $id = wp_insert_post([
        'post_title' => $title,
        'post_name' => $slug,
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_content' => $content
    ]);
    return $id;
}

// Download image from URL and attach to post
function download_and_attach_image($url, $post_id, $filename = null) {
    if (empty($url)) return 0;
    $tmp = download_url($url);
    if (is_wp_error($tmp)) {
        echo "  ⚠ Could not download: $url\n";
        return 0;
    }
    $file_array = [
        'name' => $filename ?: basename(parse_url($url, PHP_URL_PATH)) ?: 'image.jpg',
        'tmp_name' => $tmp
    ];
    $id = media_handle_sideload($file_array, $post_id);
    if (is_wp_error($id)) {
        @unlink($tmp);
        return 0;
    }
    set_post_thumbnail($post_id, $id);
    return $id;
}

// Free spice/organic product images (Unsplash - free to use)
$IMAGE_URLS = [
    'hero' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=1400&q=80', // Organic produce
    'turmeric' => 'https://images.unsplash.com/photo-1596040033229-a0b5c0c5d4e0?w=600&q=80',
    'cinnamon' => 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?w=600&q=80',
    'pepper' => 'https://images.unsplash.com/photo-1506368249639-73a05d6f6488?w=600&q=80',
    'cumin' => 'https://images.unsplash.com/photo-1596040033229-a0b5c0c5d4e0?w=600&q=80',
    'chili' => 'https://images.unsplash.com/photo-1506368249639-73a05d6f6488?w=600&q=80',
    'cardamom' => 'https://images.unsplash.com/photo-1596040033229-a0b5c0c5d4e0?w=600&q=80',
    'cloves' => 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?w=600&q=80',
    'masala' => 'https://images.unsplash.com/photo-1506368249639-73a05d6f6488?w=600&q=80',
];

// ========== STEP 1: Create required pages ==========
echo "\n1. Creating pages...\n";

$shop_page = create_page('Shop', 'shop', '[products limit="12" columns="4"]');
update_option('woocommerce_shop_page_id', $shop_page);
echo "   ✓ Shop (ID: $shop_page)\n";

$cart_page = create_page('Cart', 'cart', '[woocommerce_cart]');
update_option('woocommerce_cart_page_id', $cart_page);
echo "   ✓ Cart (ID: $cart_page)\n";

$checkout_page = create_page('Checkout', 'checkout', '[woocommerce_checkout]');
update_option('woocommerce_checkout_page_id', $checkout_page);
echo "   ✓ Checkout (ID: $checkout_page)\n";

$account_page = create_page('My account', 'my-account', '[woocommerce_my_account]');
update_option('woocommerce_myaccount_page_id', $account_page);
echo "   ✓ My account (ID: $account_page)\n";

// Remove Sample Page if it exists (no longer used)
$sample_page_obj = get_page_by_path('sample-page');
if ($sample_page_obj) {
    wp_delete_post($sample_page_obj->ID, true);
    echo "   ✓ Sample Page removed\n";
}

// Hero page - Organic Cosmetics style (inline styles only; no <style> block to avoid raw CSS showing)
$hero_html = '<section class="organic-hero" style="position:relative;background-size:cover;background-position:center;min-height:70vh;display:flex;align-items:center;color:#fff;padding:80px 24px;background-image:url(\'PLACEHOLDER_HERO\');">';
$hero_html .= '<div style="position:absolute;inset:0;background:linear-gradient(135deg,rgba(76,115,76,.75),rgba(56,85,56,.6))"></div>';
$hero_html .= '<div style="position:relative;max-width:1200px;margin:0 auto;width:100%">';
$hero_html .= '<h1 style="font-size:clamp(36px,5vw,56px);line-height:1.1;margin:0 0 16px;font-weight:700;text-shadow:0 2px 4px rgba(0,0,0,.2)">Anazah Global</h1>';
$hero_html .= '<p style="font-size:18px;margin:0 0 28px;opacity:.95;max-width:500px">Premium whole spices by Anazah Essora — sustainably sourced, freshly packed.</p>';
$hero_html .= '<a href="' . get_permalink($shop_page) . '" style="display:inline-block;background:#8B7355;color:#fff;padding:14px 32px;border-radius:4px;text-decoration:none;font-weight:600">Shop Now</a></div></section>';
$hero_html .= '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;padding:60px 24px;max-width:1200px;margin:0 auto">';
$hero_html .= '<div style="text-align:center;padding:30px 20px;background:#f8f6f3;border-radius:8px"><h3 style="margin:0 0 8px;color:#4c734c;font-size:18px">Indian Spices</h3><p style="margin:0;color:#666;font-size:14px">Traditional flavors</p></div>';
$hero_html .= '<div style="text-align:center;padding:30px 20px;background:#f8f6f3;border-radius:8px"><h3 style="margin:0 0 8px;color:#4c734c;font-size:18px">Organic Collection</h3><p style="margin:0;color:#666;font-size:14px">Certified organic</p></div>';
$hero_html .= '<div style="text-align:center;padding:30px 20px;background:#f8f6f3;border-radius:8px"><h3 style="margin:0 0 8px;color:#4c734c;font-size:18px">Spice Blends</h3><p style="margin:0;color:#666;font-size:14px">Ready-to-use mixes</p></div>';
$hero_html .= '</div>';
$hero_html .= '<div style="padding:40px 24px;max-width:1200px;margin:0 auto">[products limit="8" columns="4"]</div>';

$home_page = create_page('Home', 'home', $hero_html);
// Always overwrite with clean hero (inline styles only) so no raw CSS ever shows
wp_update_post(['ID' => $home_page, 'post_content' => $hero_html]);
update_option('page_on_front', $home_page);
update_option('show_on_front', 'page');
echo "   ✓ Home / Hero (ID: $home_page)\n";

// Upload hero image and update page
echo "\n2. Downloading hero image...\n";
$hero_attach_id = 0;
$tmp = @download_url($IMAGE_URLS['hero']);
if (!is_wp_error($tmp)) {
    $file_array = ['name' => 'hero-organic.jpg', 'tmp_name' => $tmp];
    $hero_attach_id = media_handle_sideload($file_array, 0);
    if (!is_wp_error($hero_attach_id)) {
        $hero_src = wp_get_attachment_image_url($hero_attach_id, 'full');
        if ($hero_src) {
            $content = get_post_field('post_content', $home_page);
            $content = str_replace('PLACEHOLDER_HERO', esc_url($hero_src), $content);
            wp_update_post(['ID' => $home_page, 'post_content' => $content]);
            echo "   ✓ Hero image set\n";
        }
    }
}
if (!$hero_attach_id || is_wp_error($hero_attach_id)) {
    $content = get_post_field('post_content', $home_page);
    $content = str_replace('PLACEHOLDER_HERO', esc_url($IMAGE_URLS['hero']), $content);
    wp_update_post(['ID' => $home_page, 'post_content' => $content]);
    echo "   ✓ Hero using direct URL\n";
}

// ========== STEP 3: Create menu (only requested items) ==========
echo "\n3. Creating navigation menu...\n";
$menu_id = wp_get_nav_menu_object('Main Menu');
if (!$menu_id) $menu_id = wp_create_nav_menu('Main Menu');
$menu_id = is_object($menu_id) ? $menu_id->term_id : $menu_id;

// Clear existing items and add only: Home, Shop, Cart, Checkout, My account (no Sample Page)
$items = wp_get_nav_menu_items($menu_id);
if ($items) foreach ($items as $item) wp_delete_post($item->ID, true);

$menu_pages = [
    ['Home', $home_page],
    ['Shop', $shop_page],
    ['Cart', $cart_page],
    ['Checkout', $checkout_page],
    ['My account', $account_page],
];
foreach ($menu_pages as $p) {
    wp_update_nav_menu_item($menu_id, 0, [
        'menu-item-title' => $p[0],
        'menu-item-object-id' => $p[1],
        'menu-item-object' => 'page',
        'menu-item-type' => 'post_type',
        'menu-item-status' => 'publish'
    ]);
}
$locations = get_theme_mod('nav_menu_locations', []);
$locations['primary'] = $menu_id;
$locations['main'] = $menu_id;
set_theme_mod('nav_menu_locations', $locations);
echo "   ✓ Menu: Home, Shop, Cart, Checkout, My account\n";

// ========== STEP 4: Cash on Delivery only ==========
echo "\n4. Configuring Cash on Delivery only...\n";
update_option('woocommerce_cod_settings', [
    'enabled' => 'yes',
    'title' => 'Cash on Delivery',
    'description' => 'Pay with cash upon delivery.',
    'instructions' => 'Pay with cash when your order is delivered.',
    'enable_for_methods' => [],
    'enable_for_virtual' => 'yes',
]);
// Create mu-plugin to force COD only (survives admin changes)
$mu_dir = WP_CONTENT_DIR . '/mu-plugins';
if (!is_dir($mu_dir)) wp_mkdir_p($mu_dir);
$mu_plugin = '<?php
/**
 * Force Cash on Delivery only - Organic Spices Shop
 */
add_filter("woocommerce_available_payment_gateways", function($gateways) {
    $cod = isset($gateways["cod"]) ? $gateways["cod"] : null;
    return $cod ? ["cod" => $cod] : $gateways;
});
';
file_put_contents($mu_dir . '/cod-only.php', $mu_plugin);
echo "   ✓ Cash on Delivery only (mu-plugin created)\n";

// ========== STEP 5: Anazah Global branding & WooCommerce (INR) ==========
echo "\n5. Configuring Anazah Global store...\n";
update_option('blogname', 'Anazah Global');
update_option('blogdescription', 'Premium Whole Spices by Anazah Essora');
update_option('woocommerce_currency', 'INR');
update_option('woocommerce_currency_pos', 'left');
update_option('woocommerce_price_decimal_sep', '.');
update_option('woocommerce_price_thousand_sep', ',');
update_option('woocommerce_price_num_decimals', '0');
// Set theme custom logo if Anazah logo file exists (PNG/JPG)
$logo_paths = [ABSPATH . 'anazah-logo.png', ABSPATH . 'anazah-logo.jpg'];
foreach ($logo_paths as $logo_path) {
    if (file_exists($logo_path)) {
        $tmp = sys_get_temp_dir() . '/' . basename($logo_path);
        @copy($logo_path, $tmp);
        $file_array = ['name' => basename($logo_path), 'tmp_name' => $tmp];
        $logo_id = media_handle_sideload($file_array, 0);
        @unlink($tmp);
        if (!is_wp_error($logo_id)) {
            set_theme_mod('custom_logo', $logo_id);
            echo "   ✓ Custom logo set (Anazah Global)\n";
            break;
        }
    }
}
echo "   ✓ Brand: Anazah Global | Currency: INR\n";

// ========== STEP 6: Remove previous products, create from Product List (Excel) ==========
echo "\n6. Syncing products from Product List...\n";

$products_json_path = ABSPATH . 'anazah-products.json';
if (!file_exists($products_json_path)) {
    echo "   ⚠ anazah-products.json not found. Mount it or copy to WordPress root.\n";
    echo "   Skipping product sync. Run after adding anazah-products.json.\n";
} else {
    $anazah_products = json_decode(file_get_contents($products_json_path), true);
    if (!is_array($anazah_products)) {
        echo "   ⚠ Invalid JSON in anazah-products.json\n";
    } else {
        // Delete all existing products
        $existing = get_posts(['post_type' => 'product', 'numberposts' => -1, 'post_status' => 'any', 'fields' => 'ids']);
        foreach ($existing as $pid) {
            wp_delete_post($pid, true);
        }
        echo "   ✓ Removed " . count($existing) . " previous products\n";

        // Catalog descriptions from Anazah_Essora_Full_Product_Catalog.docx
        $catalog_desc = [
            'Black Pepper' => 'Sourced from premium pepper-growing regions of India, Whole Black Pepper is valued for its bold aroma, sharp heat, and high essential oil content. Widely used across Indian, continental, and Asian cuisines.',
            'Cardamom - Small' => 'Small green cardamom pods from high-altitude regions. Balanced aroma and sweetness. Ideal for desserts, tea, sweets, kheer, pulao, and festive dishes.',
            'Cardamom - Large 7.5 mm and above' => 'Premium large green cardamom pods hand-graded for size and oil content. Intense aroma and deeper flavour. Best for biryanis, premium desserts, and specialty teas.',
            'Black Cardamom' => 'Sourced from Himalayan regions with bold, smoky aroma. Adds depth to biryanis, meat curries, and gravies.',
            'Cumin Seeds' => 'Carefully sourced for strong aroma and uniform size. Warm, earthy notes essential to Indian and global cuisines. Used in tempering, rice dishes, curries, and spice blends.',
            'Coriander Seeds' => 'Whole coriander seeds with mild citrusy aroma. Balanced flavour base for many spice blends. Used in curries, gravies, pickles, and masalas.',
            'Fennel Seeds' => 'Naturally sweet and aromatic fennel seeds. Used in cooking, spice blends, mouth fresheners, and digestive preparations.',
            'Fenugreek Seeds (Methi)' => 'Distinctive bitter-sweet flavour, widely used in Indian kitchens. Added to spice blends, pickles, curries, and vegetable preparations.',
            'Mustard Seeds' => 'Pungent and sharp, sourced for consistent size and freshness. Used for tempering, pickling, and regional Indian cooking.',
            'Turmeric Fingers (Whole)' => 'Whole turmeric fingers from trusted regions. Carefully dried to retain natural potency. Used daily in Indian cooking for colour, balance, and flavour.',
            'Dry Ginger (Whole)' => 'Naturally dried ginger with strong aroma and warm, spicy profile. Used in spice blends, herbal beverages, and savoury dishes.',
            'Gundu Mundu Chilli' => 'Traditionally grown in South India, prized for deep red colour and moderate heat. Ideal for gravies, chutneys, and masalas.',
            'Byadagi Chilli' => 'From Karnataka, known for vibrant red colour with mild heat. Perfect for colour-rich gravies and curry bases.',
            'Cloves' => 'Aromatic flower buds with warm, spicy notes and natural sweetness. Used in biryanis, garam masala, desserts, and festive cooking.',
            'Cinnamon Sticks' => 'Naturally fragrant cinnamon bark with warm, sweet undertones. Used in curries, desserts, beverages, and spice blends.',
            'Cinnamon Roll' => 'Cassia cinnamon rolls, thicker and more intense in flavour. Commonly used in Indian masalas and slow-cooked dishes.',
            'Nutmeg' => 'Whole nutmeg seeds with rich aroma and slightly sweet warmth. Best freshly grated for desserts, sauces, and savoury dishes.',
            'Star Anise (Chakra Phool)' => 'Distinct star shape and sweet licorice-like aroma. Used in biryanis, spice blends, and slow-cooked dishes.',
            'Tejpatta (Bay Leaf)' => 'Indian bay leaves with warm, clove-like aroma. Used whole to infuse flavour into rice dishes, curries, and stews.',
            'White Pepper' => 'Clean heat with milder aroma than black pepper. Ideal for soups, sauces, and light-coloured preparations.',
            'Tamarind' => 'Naturally sour tamarind with rich tanginess. Used in curries, chutneys, rasam, and beverages.',
            'Ajwain' => 'Ajwain seeds with strong, thyme-like aroma. Used in Indian breads, snacks, and digestive preparations.',
        ];

        // Ensure product categories exist
        $category_ids = [];
        $categories = array_unique(array_column($anazah_products, 'category'));
        foreach ($categories as $cat_name) {
            if (empty($cat_name)) $cat_name = 'Whole Spices';
            $term = get_term_by('name', $cat_name, 'product_cat');
            if (!$term) {
                $t = wp_insert_term($cat_name, 'product_cat');
                if (!is_wp_error($t)) $category_ids[$cat_name] = $t['term_id'];
            } else {
                $category_ids[$cat_name] = $term->term_id;
            }
        }

        $created = 0;
        foreach ($anazah_products as $p) {
            $name = $p['name'];
            $pack = isset($p['pack_gm']) ? $p['pack_gm'] : '';
            $title = $pack ? $name . ' – ' . $pack . ' gm' : $name;
            $sku = isset($p['sku']) ? $p['sku'] : '';
            $price = isset($p['price_inr']) ? $p['price_inr'] : 0;
            $cat_name = isset($p['category']) ? $p['category'] : 'Whole Spices';
            $short_desc = 'Pack size: ' . $pack . ' gm. SKU: ' . $sku . '.';
            $long_desc = isset($catalog_desc[$name]) ? $catalog_desc[$name] : 'Premium whole spice from Anazah Essora.';
            $long_desc .= "\n\n" . $short_desc;

            $post_id = wp_insert_post([
                'post_title' => $title,
                'post_content' => $long_desc,
                'post_excerpt' => $short_desc,
                'post_status' => 'publish',
                'post_type' => 'product',
            ]);
            if ($post_id) {
                wp_set_object_terms($post_id, 'simple', 'product_type');
                if (!empty($category_ids[$cat_name])) {
                    wp_set_object_terms($post_id, (int) $category_ids[$cat_name], 'product_cat');
                }
                update_post_meta($post_id, '_sku', $sku);
                update_post_meta($post_id, '_regular_price', $price);
                update_post_meta($post_id, '_price', $price);
                update_post_meta($post_id, '_stock_status', 'instock');
                update_post_meta($post_id, '_visibility', 'visible');
                $created++;
            }
        }
        echo "   ✓ Created $created Anazah products (exact names, SKUs, prices from Product List)\n";
    }
}

echo "\n✅ Anazah Global setup complete!\n";
echo "   Brand: Anazah Global | Currency: INR\n";
echo "   Pages: Cart, Checkout, My account, Shop, Hero (Home)\n";
echo "   Payment: Cash on Delivery only\n";
echo "   Visit: " . site_url() . "\n";
?>
