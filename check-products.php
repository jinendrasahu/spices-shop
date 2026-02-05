<?php
define('WP_USE_THEMES', false);
require_once(__DIR__ . '/wp-load.php');

$products = get_posts([
    'post_type' => 'product',
    'posts_per_page' => 10,
    'post_status' => 'any'
]);

echo "Found " . count($products) . " products (showing first 10):\n\n";
foreach ($products as $p) {
    echo "ID: {$p->ID}, Title: {$p->post_title}\n";
}

