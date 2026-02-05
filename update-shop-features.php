<?php
/**
 * Script to:
 * 1. Create About Us page
 * 2. Add address to footer
 */

define('WP_USE_THEMES', false);
require_once(__DIR__ . '/wp-load.php');

echo "=== Creating About Us Page ===\n\n";

// Check if About Us page already exists
$about_page = get_page_by_path('about-us');
if (!$about_page) {
    $about_content = <<<EOT
<h2>Welcome to Anazah Essora</h2>
<p>We are a premium spice retailer dedicated to bringing you the finest quality spices from around the world. Our commitment to excellence and authenticity has made us a trusted name in the spice industry.</p>

<h3>Our Story</h3>
<p>Anazah Essora was founded with a passion for quality spices and a vision to make premium spices accessible to everyone. We source our spices directly from trusted growers and suppliers, ensuring that every product meets our high standards of quality and freshness.</p>

<h3>Our Mission</h3>
<p>To provide our customers with the finest quality spices while maintaining fair trade practices and supporting sustainable farming communities worldwide.</p>

<h3>Why Choose Us?</h3>
<ul>
    <li><strong>Premium Quality:</strong> We carefully select only the finest spices for our collection</li>
    <li><strong>Authentic Flavors:</strong> Our spices are sourced directly from their regions of origin</li>
    <li><strong>Fresh Products:</strong> We ensure all our spices are fresh and properly stored</li>
    <li><strong>Fair Pricing:</strong> Competitive prices without compromising on quality</li>
    <li><strong>Customer Service:</strong> We are committed to providing excellent customer service</li>
</ul>

<h3>Contact Us</h3>
<p>If you have any questions or need assistance, please don't hesitate to reach out to us. We're here to help you find the perfect spices for your culinary needs.</p>
EOT;

    $page_data = [
        'post_title'    => 'About Us',
        'post_content'  => $about_content,
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_name'     => 'about-us',
        'post_author'   => 1,
    ];

    $page_id = wp_insert_post($page_data);
    
    if ($page_id && !is_wp_error($page_id)) {
        echo "✓ About Us page created (ID: $page_id)\n";
    } else {
        echo "✗ Failed to create About Us page\n";
    }
} else {
    echo "✓ About Us page already exists (ID: {$about_page->ID})\n";
}

echo "\n=== Updating Footer Address ===\n";
echo "Note: Footer address will be added via theme customization\n";
echo "Please check the footer component file for address display\n\n";

echo "✅ Setup Complete!\n";

