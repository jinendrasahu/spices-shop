<?php
/**
 * Script to remove all product images and use local spice images from anazah-product-images folder
 * Reuses same image for products with same base name
 */

define('WP_USE_THEMES', false);
require_once(__DIR__ . '/wp-load.php');

// Map product names to image files in anazah-product-images folder
$product_image_map = [
    'Black Pepper' => 'Black-Pepper.jpeg',
    'Cardamom - Small' => 'Cardamom---Small.jpeg',
    'Cardamom - Large 7.5 mm and above' => 'Cardamom---Large-75-mm-and-above.jpeg',
    'Gundu Mundu Chilli' => 'Gundu-Mundu-Chilli.jpeg',
    'Dry Ginger (Whole)' => 'Dry-Ginger-Whole.jpeg',
    'Turmeric Fingers (Whole)' => 'Turmeric-Fingers-Whole.jpeg',
    'Ajwain' => null, // Will use default
    'Coriander Seeds' => 'Coriander-Seeds.png',
    'Cumin Seeds' => 'Cumin-Seeds.jpeg',
    'Fennel Seeds' => 'Fennel-Seeds.jpeg',
    'Fenugreek Seeds (Methi)' => 'Fenugreek-Seeds-Methi.jpeg',
    'Tamarind' => 'Tamarind.jpeg',
    'Cloves' => 'Cloves.jpeg',
    'Nutmeg' => 'Nutmeg.jpeg',
    'Cinnamon Sticks' => 'Cinnamon-Sticks.jpeg',
    'Black Cardamom' => 'Black-Cardamom.jpeg',
    'Mustard Seeds' => 'Mustard-Seeds.jpeg',
    'Tejpatta (Bay Leaf)' => 'Tejpatta-Bay-Leaf.png',
    'Star Anise (Chakra Phool)' => 'Star-Anise-Chakra-Phool.png',
    'White Pepper' => 'White-Pepper.jpeg',
    'Cinnamon Roll' => 'Cinnamon-Roll.png',
    'Byadagi Chilli' => 'Byadagi-Chilli.jpeg',
];

$source_images_dir = ABSPATH . 'anazah-product-images/';
$default_image = ABSPATH . 'anazah-product-images/Black-Pepper.jpeg'; // Fallback default

echo "=== Removing All Product Images ===\n\n";

// Step 1: Remove all product images
$all_products = get_posts([
    'post_type' => 'product',
    'posts_per_page' => -1,
    'post_status' => 'any'
]);

$removed_count = 0;
foreach ($all_products as $product) {
    $product_id = $product->ID;
    
    // Remove featured image
    $thumbnail_id = get_post_thumbnail_id($product_id);
    if ($thumbnail_id) {
        wp_delete_attachment($thumbnail_id, true);
        $removed_count++;
    }
    
    // Remove gallery images
    $gallery_ids = get_post_meta($product_id, '_product_image_gallery', true);
    if ($gallery_ids) {
        $ids = explode(',', $gallery_ids);
        foreach ($ids as $id) {
            if ($id) {
                wp_delete_attachment($id, true);
                $removed_count++;
            }
        }
        delete_post_meta($product_id, '_product_image_gallery');
    }
    
    // Remove all attachments for this product
    $attachments = get_attached_media('image', $product_id);
    foreach ($attachments as $attachment) {
        wp_delete_attachment($attachment->ID, true);
        $removed_count++;
    }
}

echo "Removed $removed_count product images\n\n";

echo "=== Attaching Spice Images to Products ===\n\n";

// Step 2: Attach relevant spice images
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');

$processed_images = []; // Cache for image attachment IDs by product base name

foreach ($product_image_map as $product_base_name => $image_filename) {
    echo "Processing: $product_base_name... ";
    
    // Determine which image file to use
    $image_file = null;
    if ($image_filename && file_exists($source_images_dir . $image_filename)) {
        $image_file = $source_images_dir . $image_filename;
    } elseif (file_exists($default_image)) {
        $image_file = $default_image;
        echo "[Using default] ";
    } else {
        echo "✗ No image file found\n";
        continue;
    }
    
    // Find all products that start with this base name
    global $wpdb;
    $product_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_title LIKE %s AND post_status != 'trash'",
        $wpdb->esc_like($product_base_name) . '%'
    ));
    
    if (empty($product_ids)) {
        echo "✗ No products found\n";
        continue;
    }
    
    // Check if we already processed this image (reuse attachment)
    $attach_id = null;
    if (isset($processed_images[$product_base_name])) {
        $attach_id = $processed_images[$product_base_name];
        echo "[Reusing image] ";
    } else {
        // Create new attachment from file
        $upload_dir = wp_upload_dir();
        $filename = basename($image_file);
        $new_filename = sanitize_file_name($product_base_name . '-' . time() . '.' . pathinfo($filename, PATHINFO_EXTENSION));
        $upload_path = $upload_dir['path'] . '/' . $new_filename;
        
        // Copy file to uploads directory
        if (copy($image_file, $upload_path)) {
            $wp_filetype = wp_check_filetype($new_filename, null);
            
            $attachment = [
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => sanitize_file_name($product_base_name),
                'post_content' => '',
                'post_status' => 'inherit'
            ];
            
            // Attach to first product temporarily to get ID
            $attach_id = wp_insert_attachment($attachment, $upload_path, $product_ids[0]);
            
            if (!is_wp_error($attach_id)) {
                $attach_data = wp_generate_attachment_metadata($attach_id, $upload_path);
                wp_update_attachment_metadata($attach_id, $attach_data);
                $processed_images[$product_base_name] = $attach_id;
            } else {
                echo "✗ Failed to create attachment\n";
                @unlink($upload_path);
                continue;
            }
        } else {
            echo "✗ Failed to copy image file\n";
            continue;
        }
    }
    
    // Attach image to all products with this base name (reuse same attachment)
    $attached_count = 0;
    foreach ($product_ids as $product_id) {
        // Set the same attachment as featured image for all products with same base name
        $result = set_post_thumbnail($product_id, $attach_id);
        if ($result) {
            $attached_count++;
        }
    }
    
    echo "✓ Attached to $attached_count product(s)\n";
}

// Handle products that don't match any in the map (use default)
echo "\n=== Processing unmatched products with default image ===\n";
if (file_exists($default_image)) {
    global $wpdb;
    $all_product_ids = $wpdb->get_col(
        "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status != 'trash'"
    );
    
    $unmatched_count = 0;
    foreach ($all_product_ids as $product_id) {
        $thumbnail_id = get_post_thumbnail_id($product_id);
        if (!$thumbnail_id) {
            // No image, use default
            $upload_dir = wp_upload_dir();
            $filename = basename($default_image);
            $new_filename = sanitize_file_name('default-spice-' . $product_id . '-' . time() . '.' . pathinfo($filename, PATHINFO_EXTENSION));
            $upload_path = $upload_dir['path'] . '/' . $new_filename;
            
            if (copy($default_image, $upload_path)) {
                $wp_filetype = wp_check_filetype($new_filename, null);
                $attachment = [
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title' => 'Default Spice Image',
                    'post_content' => '',
                    'post_status' => 'inherit'
                ];
                
                $attach_id = wp_insert_attachment($attachment, $upload_path, $product_id);
                if (!is_wp_error($attach_id)) {
                    $attach_data = wp_generate_attachment_metadata($attach_id, $upload_path);
                    wp_update_attachment_metadata($attach_id, $attach_data);
                    set_post_thumbnail($product_id, $attach_id);
                    $unmatched_count++;
                }
            }
        }
    }
    
    if ($unmatched_count > 0) {
        echo "Attached default image to $unmatched_count unmatched products\n";
    }
}

echo "\n========================================\n";
echo "✅ All product images updated!\n";
echo "========================================\n";

