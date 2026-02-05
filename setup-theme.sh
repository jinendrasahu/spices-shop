#!/bin/bash

# WordPress theme and plugin installation script

WORDPRESS_CONTAINER="wordpress_app"
THEME_DIR="/tmp/theme"
PLUGINS_DIR="/tmp/plugins"

echo "Starting WordPress theme setup..."

# Copy theme files into container
echo "Extracting theme files..."
mkdir -p "$THEME_DIR"
mkdir -p "$PLUGINS_DIR"

# Extract theme
unzip -q /Users/dsk-jinendra-tech/Downloads/tata-wp/thememove_organik/tm-organik-3.4.0.zip -d "$THEME_DIR"
unzip -q /Users/dsk-jinendra-tech/Downloads/tata-wp/thememove_organik/tm-organik-child.zip -d "$THEME_DIR"

# Extract plugins
unzip -q /Users/dsk-jinendra-tech/Downloads/tata-wp/thememove_organik/plugins/insight-core-2.7.4.zip -d "$PLUGINS_DIR"
unzip -q /Users/dsk-jinendra-tech/Downloads/tata-wp/thememove_organik/plugins/js_composer-8.4.1.1.zip -d "$PLUGINS_DIR"
unzip -q /Users/dsk-jinendra-tech/Downloads/tata-wp/thememove_organik/plugins/revslider-6.7.34.zip -d "$PLUGINS_DIR"
unzip -q /Users/dsk-jinendra-tech/Downloads/tata-wp/thememove_organik/plugins/vc_clipboard-5.0.6.zip -d "$PLUGINS_DIR"

echo "Copying files to WordPress container..."

# Copy themes to WordPress (parent zip extracts as tm-organik/, not tm-organik-3.4.0/)
docker cp "$THEME_DIR/tm-organik/" "$WORDPRESS_CONTAINER:/var/www/html/wp-content/themes/tm-organik/" 2>/dev/null || true
docker cp "$THEME_DIR/tm-organik-child/" "$WORDPRESS_CONTAINER:/var/www/html/wp-content/themes/tm-organik-child/" 2>/dev/null || true

# Copy plugins to WordPress
docker cp "$PLUGINS_DIR/insight-core/" "$WORDPRESS_CONTAINER:/var/www/html/wp-content/plugins/insight-core/" 2>/dev/null || true
docker cp "$PLUGINS_DIR/js_composer/" "$WORDPRESS_CONTAINER:/var/www/html/wp-content/plugins/js_composer/" 2>/dev/null || true
docker cp "$PLUGINS_DIR/revslider/" "$WORDPRESS_CONTAINER:/var/www/html/wp-content/plugins/revslider/" 2>/dev/null || true
docker cp "$PLUGINS_DIR/vc_clipboard/" "$WORDPRESS_CONTAINER:/var/www/html/wp-content/plugins/vc_clipboard/" 2>/dev/null || true

echo "Setup complete! Theme and plugins have been installed."
echo ""
echo "Next steps:"
echo "1. Go to http://localhost:8080"
echo "2. Complete WordPress setup if not done"
echo "3. Go to Admin > Appearance > Themes"
echo "4. Activate 'Organik Child' theme"
echo "5. Go to Plugins and activate all plugins:"
echo "   - Insight Core"
echo "   - JS Composer (Visual Composer)"
echo "   - Revolution Slider"
echo "   - VC Clipboard"
echo "6. Install WooCommerce from Plugins > Add New"
echo "7. Configure WooCommerce in Admin > WooCommerce"

# Clean up
rm -rf "$THEME_DIR" "$PLUGINS_DIR"
