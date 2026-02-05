#!/usr/bin/env python3
"""
Script to download product images from internet based on product names
"""
import json
import requests
import os
import time
import sys
from urllib.parse import quote

def get_unique_products(json_file):
    """Extract unique product names from JSON"""
    with open(json_file, 'r', encoding='utf-8') as f:
        products = json.load(f)
    
    unique = []
    for p in products:
        name = p['name']
        if name not in unique:
            unique.append(name)
    
    return unique

def download_image_from_unsplash(product_name, output_path):
    """Download image from Unsplash Source API"""
    search_term = quote(product_name + " spice")
    url = f"https://source.unsplash.com/800x600/?{search_term}"
    
    try:
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        }
        response = requests.get(url, headers=headers, timeout=30, allow_redirects=True)
        
        if response.status_code == 200 and len(response.content) > 1000:
            with open(output_path, 'wb') as f:
                f.write(response.content)
            return True
    except Exception as e:
        print(f"  Error: {e}")
    
    return False

def download_image_from_pixabay(product_name, output_path):
    """Try to get image from Pixabay (free images)"""
    # Note: This would require API key, but we can try direct search
    # For now, use a placeholder that redirects to a free image
    search_term = quote(product_name)
    # Using a free image service
    url = f"https://api.pexels.com/v1/search?query={search_term}&per_page=1"
    # Actually, let's use a simpler approach with Lorem Picsum or similar
    
    return False

def download_image_simple(product_name, output_path):
    """Simple image download using multiple sources"""
    search_term = quote(product_name + " spice food")
    
    # Try multiple free image sources
    sources = [
        f"https://source.unsplash.com/800x600/?{search_term}",
        f"https://picsum.photos/800/600?random={int(time.time())}",
    ]
    
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
    }
    
    for url in sources:
        try:
            response = requests.get(url, headers=headers, timeout=30, allow_redirects=True, stream=True)
            if response.status_code == 200:
                content = response.content
                if len(content) > 1000:  # Valid image should be > 1KB
                    with open(output_path, 'wb') as f:
                        f.write(content)
                    return True
        except Exception as e:
            continue
    
    return False

def main():
    json_file = 'anazah-products.json'
    if not os.path.exists(json_file):
        print(f"Error: {json_file} not found")
        sys.exit(1)
    
    unique_products = get_unique_products(json_file)
    print(f"Found {len(unique_products)} unique products\n")
    
    # Create output directory
    output_dir = 'product-images-downloaded'
    os.makedirs(output_dir, exist_ok=True)
    
    print("Downloading images...\n")
    downloaded = 0
    failed = 0
    
    for product_name in unique_products:
        print(f"Processing: {product_name}... ", end='', flush=True)
        
        # Sanitize filename
        safe_name = "".join(c for c in product_name if c.isalnum() or c in (' ', '-', '_')).strip()
        safe_name = safe_name.replace(' ', '-')
        output_path = os.path.join(output_dir, f"{safe_name}.jpg")
        
        if download_image_simple(product_name, output_path):
            print(f"✓ Downloaded ({os.path.getsize(output_path)} bytes)")
            downloaded += 1
        else:
            print("✗ Failed")
            failed += 1
        
        # Rate limiting
        time.sleep(2)
    
    print(f"\n{'='*50}")
    print(f"Summary:")
    print(f"  Downloaded: {downloaded} images")
    print(f"  Failed: {failed} images")
    print(f"  Output directory: {output_dir}/")
    print(f"{'='*50}")

if __name__ == '__main__':
    main()

