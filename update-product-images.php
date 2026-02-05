<?php
/**
 * Script to remove all product images and download new ones from internet
 */

define('WP_USE_THEMES', false);
require_once(__DIR__ . '/wp-load.php');

// Get unique product names from JSON
$json_file = ABSPATH . 'anazah-products.json';
$products_json = json_decode(file_get_contents($json_file), true);
$unique_products = [];
foreach ($products_json as $p) {
    $name = $p['name'];
    if (!in_array($name, $unique_products)) {
        $unique_products[] = $name;
    }
}

echo "Found " . count($unique_products) . " unique products\n\n";

// Step 1: Remove all product images from uploads
echo "Step 1: Removing existing product images...\n";
$upload_dir = wp_upload_dir();
$upload_path = $upload_dir['basedir'];

// Get all products
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

// Step 2: Download new images for each unique product
echo "Step 2: Downloading new images from internet...\n";

// Function to download image from URL
function download_image_from_url($url, $filename) {
    $upload_dir = wp_upload_dir();
    $upload_path = $upload_dir['path'];
    $file_path = $upload_path . '/' . $filename;
    
    $image_data = @file_get_contents($url);
    if ($image_data === false) {
        return false;
    }
    
    file_put_contents($file_path, $image_data);
    return $file_path;
}

// Function to search and download image from internet
function download_product_image($product_name) {
    // Clean product name for search
    $search_term = urlencode($product_name . ' spice');
    
    $filename = sanitize_file_name($product_name) . '-' . time() . '.jpg';
    $upload_dir = wp_upload_dir();
    $upload_path = $upload_dir['path'];
    $file_path = $upload_path . '/' . $filename;
    
    // Try multiple image sources - using direct image URLs from free image services
    // Using Lorem Picsum with seed based on product name for consistent "random" images
    $seed = crc32($product_name);
    
    $sources = [
        "https://picsum.photos/seed/{$seed}/800/600", // Deterministic random image
        "https://picsum.photos/800/600?random=" . time(),
        "https://source.unsplash.com/800x600/?" . $search_term,
    ];
    
    foreach ($sources as $image_url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $image_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_REFERER, 'https://www.google.com/');
        
        $image_data = @curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            continue; // Try next source
        }
        
        // Check if we got a valid image (Picsum returns images directly)
        if ($http_code == 200 && $image_data && strlen($image_data) > 1000) {
            // Verify it's an image by checking magic bytes
            $is_image = false;
            if (substr($image_data, 0, 2) === "\xFF\xD8") { // JPEG
                $is_image = true;
            } elseif (substr($image_data, 0, 8) === "\x89PNG\r\n\x1a\n") { // PNG
                $is_image = true;
            } elseif (strpos($content_type, 'image/') !== false) {
                $is_image = true;
            }
            
            if ($is_image) {
                file_put_contents($file_path, $image_data);
                return $file_path;
            }
        }
        
        // Small delay between attempts
        usleep(500000); // 0.5 second
    }
    
    return false;
}

// Function to attach image to WordPress
function attach_image_to_wordpress($file_path, $product_id, $set_as_featured = true) {
    if (!file_exists($file_path)) {
        return new WP_Error('file_not_found', 'Image file does not exist');
    }
    
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
    $filename = basename($file_path);
    $wp_filetype = wp_check_filetype($filename, null);
    
    if (!$wp_filetype['type']) {
        return new WP_Error('invalid_filetype', 'Invalid file type');
    }
    
    $attachment = [
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name(pathinfo($filename, PATHINFO_FILENAME)),
        'post_content' => '',
        'post_status' => 'inherit'
    ];
    
    // Use the correct path for wp_insert_attachment
    $attach_id = wp_insert_attachment($attachment, $file_path, $product_id);
    
    if (is_wp_error($attach_id)) {
        return $attach_id;
    }
    
    // Generate attachment metadata and create thumbnails
    $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
    if ($attach_data) {
        wp_update_attachment_metadata($attach_id, $attach_data);
    }
    
    // Set as featured image
    if ($set_as_featured) {
        $result = set_post_thumbnail($product_id, $attach_id);
        if (!$result) {
            return new WP_Error('thumbnail_failed', 'Failed to set featured image');
        }
    }
    
    return $attach_id;
}

$downloaded = 0;
$failed = 0;

foreach ($unique_products as $product_name) {
    echo "Processing: $product_name... ";
    
    // Download image
    $image_path = download_product_image($product_name);
    
    if ($image_path && file_exists($image_path)) {
        // Find all products that start with this name (to handle pack sizes like "Black Pepper • 25 gm")
        global $wpdb;
        $product_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_title LIKE %s AND post_status != 'trash'",
            $wpdb->esc_like($product_name) . '%'
        ));
        
        if (empty($product_ids)) {
            echo "✗ No products found with name starting with: $product_name\n";
            @unlink($image_path);
            $failed++;
            continue;
        }
        
        $attached_count = 0;
        foreach ($product_ids as $product_id) {
            $attach_id = attach_image_to_wordpress($image_path, $product_id, true);
            if ($attach_id && !is_wp_error($attach_id)) {
                $attached_count++;
            } else {
                $error_msg = is_wp_error($attach_id) ? $attach_id->get_error_message() : 'Unknown error';
                echo "  Warning: Failed to attach to product ID $product_id: $error_msg\n";
            }
        }
        
        if ($attached_count > 0) {
            echo "✓ Downloaded and attached to $attached_count product(s)\n";
            $downloaded++;
        } else {
            echo "✗ Downloaded but failed to attach to any products\n";
            @unlink($image_path);
            $failed++;
        }
    } else {
        echo "✗ Failed to download\n";
        $failed++;
    }
    
    // Small delay to avoid rate limiting
    sleep(1);
}

echo "\n";
echo "========================================\n";
echo "Summary:\n";
echo "  Downloaded: $downloaded products\n";
echo "  Failed: $failed products\n";
echo "========================================\n";

