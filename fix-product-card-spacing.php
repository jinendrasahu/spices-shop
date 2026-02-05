<?php
/**
 * Fix product card spacing and remove duplicate add to cart
 */

define('WP_USE_THEMES', false);
require_once(__DIR__ . '/wp-load.php');

$functions_file = ABSPATH . 'wp-content/themes/tm-organik-child/functions.php';
$content = file_get_contents($functions_file);

// Find and update the spacing in the existing styles
$pattern = '/(\.woocommerce ul\.products \{)[^}]*(\})/s';
$replacement = '$1
            margin: 0 -15px !important;
        $2
        .woocommerce ul.products li.product {
            padding: 0 15px !important;
            margin-bottom: 40px !important;
        }
        .product-card-enhanced {
            margin-bottom: 0 !important;
        }';

if (preg_match($pattern, $content)) {
    $content = preg_replace($pattern, $replacement, $content);
} else {
    // Add spacing styles if not found
    $spacing_styles = '
        /* Product Card Spacing */
        .woocommerce ul.products {
            margin: 0 -15px !important;
        }
        .woocommerce ul.products li.product {
            padding: 0 15px !important;
            margin-bottom: 40px !important;
        }
        .product-card-enhanced {
            margin-bottom: 0 !important;
        }
        ';
    
    // Insert before the closing of the inline style
    $content = str_replace('    " );', $spacing_styles . '    " );', $content);
}

file_put_contents($functions_file, $content);
echo "Product card spacing updated successfully!\n";

