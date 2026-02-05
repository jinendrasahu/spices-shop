#!/bin/bash
# Export WordPress for deployment (Option B / C).
# Usage:
#   ./deploy-export.sh                    # export with current site URL in DB
#   ./deploy-export.sh https://yourdomain.com   # export and replace URLs in DB dump

set -e
PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$PROJECT_DIR"
OUT_DIR="${PROJECT_DIR}/deploy-export"
mkdir -p "$OUT_DIR"
NEW_URL="${1:-}"

echo "=== Anazah Global – deployment export ==="
echo "Output directory: $OUT_DIR"
echo ""

# 1. Export database
echo "1. Exporting database..."
docker compose exec -T db mysqldump -u wordpress_user -pwordpress_password wordpress > "$OUT_DIR/backup.sql" 2>/dev/null || \
  docker-compose exec -T db mysqldump -u wordpress_user -pwordpress_password wordpress > "$OUT_DIR/backup.sql"
echo "   → $OUT_DIR/backup.sql"

# 2. Optional: search-replace URL in dump
if [ -n "$NEW_URL" ]; then
  echo "2. Replacing site URL with: $NEW_URL"
  SITE_URL=$(docker compose exec -T wordpress php -r "define('WP_USE_THEMES', false); require '/var/www/html/wp-load.php'; echo get_option('siteurl');" 2>/dev/null || echo "http://localhost:8080")
  sed -i.bak "s|${SITE_URL}|${NEW_URL}|g" "$OUT_DIR/backup.sql" 2>/dev/null || sed -i "" "s|${SITE_URL}|${NEW_URL}|g" "$OUT_DIR/backup.sql"
  rm -f "$OUT_DIR/backup.sql.bak"
  echo "   → URLs replaced in backup.sql"
else
  echo "2. Skipping URL replace (pass new URL as argument to replace, e.g. ./deploy-export.sh https://yourdomain.com)"
fi

# 3. Export WordPress files (from container)
echo "3. Exporting WordPress files..."
docker compose exec -T wordpress tar czf - -C /var/www html > "$OUT_DIR/wp-files.tar.gz" 2>/dev/null || \
  docker-compose exec -T wordpress tar czf - -C /var/www html > "$OUT_DIR/wp-files.tar.gz"
echo "   → $OUT_DIR/wp-files.tar.gz"

echo ""
echo "Done. For traditional hosting:"
echo "  - Create DB and user on server, import $OUT_DIR/backup.sql"
echo "  - Upload and extract wp-files.tar.gz to public HTML folder"
echo "  - Configure wp-config.php with new DB credentials"
echo "  - Upload logo.png, hero-organic.jpg, big_title_bg_1.png, anazah-product-images/, anazah-products.json, configure-site.php and run configure-site.php once"
echo ""
