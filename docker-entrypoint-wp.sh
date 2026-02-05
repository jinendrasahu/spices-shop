#!/bin/bash
# Ensure .htaccess exists so Apache does not return 403 "unable to read htaccess"
HTACCESS="/var/www/html/.htaccess"
if [ ! -f "$HTACCESS" ] || [ ! -r "$HTACCESS" ]; then
  cat > "$HTACCESS" << 'HTEOF'
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
HTEOF
  chmod 644 "$HTACCESS"
fi
exec /usr/local/bin/docker-entrypoint.sh "$@"
