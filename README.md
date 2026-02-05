# WordPress Docker Setup

This setup uses Docker and Docker Compose to run WordPress with MySQL database.

## Prerequisites
- Docker installed on your system
- Docker Compose installed

## Quick Start

### 1. Build and Run with Docker Compose (Recommended)
```bash
cd /Users/dsk-jinendra-tech/Downloads/tata-wp
docker-compose up -d
```

This will:
- Create a MySQL database container
- Build and run the WordPress container
- Set up persistent volumes for both database and WordPress files
- Make WordPress available at http://localhost

### 2. Manual Docker Build and Run

If you prefer to build and run manually:

```bash
# Build the Docker image
docker build -t wordpress-app .

# Run MySQL container first
docker run -d \
  --name wordpress_db \
  -e MYSQL_ROOT_PASSWORD=root_password \
  -e MYSQL_DATABASE=wordpress \
  -e MYSQL_USER=wordpress_user \
  -e MYSQL_PASSWORD=wordpress_password \
  mysql:8.0

# Run WordPress container
docker run -d \
  --name wordpress_app \
  --link wordpress_db:db \
  -p 80:80 \
  -e WORDPRESS_DB_HOST=db \
  -e WORDPRESS_DB_NAME=wordpress \
  -e WORDPRESS_DB_USER=wordpress_user \
  -e WORDPRESS_DB_PASSWORD=wordpress_password \
  wordpress-app
```

## Access WordPress
Once the containers are running:
- Open browser: http://localhost:8080 (or http://localhost if using port 80)
- Complete the WordPress installation wizard
- Default admin account will be created during setup

## Copy this project to another computer (Docker on both)

You can move the project to another machine and run it there with Docker. The **project folder** contains everything needed to recreate the site; Docker **volumes** (database + WordPress files) stay on the first computer, so on the new computer you start fresh and re-run setup.

### What to copy

Copy the **entire project folder** (e.g. `tata-wp`) to the other computer. Include at least:

- `docker-compose.yml`, `Dockerfile`, `.dockerignore`
- `configure-site.php`, `anazah-products.json`, `htaccess.txt`
- `logo.png`, `hero-organic.jpg`, `big_title_bg_1.png`
- `anazah-product-images/`
- `thememove_organik/` (theme + plugins zips)
- `setup-theme.sh`, `deploy-export.sh`
- `README.md` (this file)

You can omit (to save space): `deploy-export/` (export backups), `_xlsx/`, `_docx/` if you don’t need to regenerate the product list from Excel.

### On the new computer

1. **Install Docker and Docker Compose** (if not already installed).

2. **Copy the project folder** (USB drive, cloud, zip, or Git).

3. **Start containers:**
   ```bash
   cd /path/to/tata-wp
   docker compose up -d --build
   ```

4. **Install theme and plugins:**
   ```bash
   chmod +x setup-theme.sh
   ./setup-theme.sh
   ```

5. **Complete WordPress install** (one-time): open http://localhost:8080 in a browser, run the WordPress installer (set admin user and password).

6. **Run site configuration** (recreates pages, menu, products, logo, address, etc.):
   ```bash
   docker compose exec wordpress php /var/www/html/configure-site.php
   ```

After that, the site on the new computer will match this project (Anazah Global, products, logo, COD, etc.).

### Optional: exact clone (copy database + uploads)

If you need an **exact copy** (same admin user, all media, same DB state):

- **On the first computer:** export the database and WordPress files (see [Deploying the App](#deploying-the-app-production), Option B, or run `./deploy-export.sh`). Copy the project folder **and** the `deploy-export/` folder (or `backup.sql` + `wp-files.tar.gz`) to the new computer.
- **On the new computer:** start with `docker compose up -d`, then **import** the database and replace the `html` content with the exported files (e.g. create DB, import `backup.sql`, extract `wp-files.tar.gz` over the volume or into the container). This is the same as a “restore from backup” and gives an exact clone.

## Anazah Global – Spices Shop (Thememove Organik Theme)

This project is configured for **Anazah Global** with products from:
- **Product List for Website (with price).xlsx** → exported to `anazah-products.json`
- **Anazah_Essora_Full_Product_Catalog.docx** → descriptions used for product pages

- **Brand:** Anazah Global
- **Currency:** INR
- **Pages:** Cart, Checkout, My account, Sample Page, Shop, Hero (Home)
- **Payment:** Cash on Delivery only
- **Products:** Exact names, SKUs, pack sizes (gm), and prices from the Excel; previous products are removed on each run

### Setup Steps

1. **Start containers:**
   ```bash
   docker-compose up -d
   ```

2. **Install theme & plugins** (run from project directory):
   ```bash
   ./setup-theme.sh
   ```

3. **Complete WordPress install** at http://localhost:8080 (create admin user, etc.)

4. **Run full site configuration:**
   ```bash
   ./auto-setup.sh
   ```
   Or manually:
   ```bash
   docker exec wordpress_app php /var/www/html/configure-site.php
   ```

The `configure-site.php` script (mounted into the container) will:
- Create all required pages and set Hero/Home to **Anazah Global** branding
- Set up navigation menu (Home, Shop, Cart, Checkout, My account, Sample Page)
- Enable Cash on Delivery only
- Set site title to **Anazah Global** and currency to **INR**
- **Remove all existing products**, then create products from `anazah-products.json` (exact names, SKUs, pack sizes, prices from the Excel)
- Use catalog descriptions from the Word catalog where available

### Product list (Excel → JSON)

The file `anazah-products.json` is generated from **Product List for Website (with price).xlsx** (sheet 2). To regenerate it after editing the Excel, run:
```bash
# Extract xlsx and run parser (requires Python 3)
unzip -o -q "Product List for Website (with price).xlsx" -d _xlsx
python3 -c "
import xml.etree.ElementTree as ET, json
ns = {'main': 'http://schemas.openxmlformats.org/spreadsheetml/2006/main'}
sst = ET.parse('_xlsx/xl/sharedStrings.xml').getroot()
strings = [''.join(si.itertext()).strip() for si in sst.findall('.//main:si', ns)]
sheet = ET.parse('_xlsx/xl/worksheets/sheet2.xml').getroot()
products = []
for row in sheet.findall('.//main:row', ns):
    r = int(row.get('r', 0))
    if r == 1: continue
    cells = {}
    for c in row.findall('main:c', ns):
        ref, col = c.get('r', ''), c.get('r', '')[0] if c.get('r') else ''
        v = c.find('main:v', ns)
        val = v.text if v is not None and v.text else ''
        if c.get('t') == 's' and val:
            idx = int(val)
            val = strings[idx] if idx < len(strings) else val
        elif val and col in ('E','F'): val = float(val) if '.' in str(val) else int(val)
        cells[col] = val
    if 'C' in cells and isinstance(cells.get('C'), str) and cells['C'] not in ('Product Name',):
        name, sku, pack, price = cells.get('C'), cells.get('D'), cells.get('E'), cells.get('F')
        if name and str(price).replace('.','').isdigit() and not name.startswith('ANZ-'):
            cat = cells.get('B', 'Whole Spices')
            if isinstance(cat, int): cat = strings[cat] if cat < len(strings) else 'Whole Spices'
            products.append({'name': name, 'sku': str(sku), 'pack_gm': pack, 'price_inr': price, 'category': cat})
with open('anazah-products.json', 'w') as f: json.dump(products, f, indent=2)
print(len(products), 'products written')
"
```

### Logo

Site title is set to **Anazah Global**. To use a custom logo: add `anazah-logo.png` (or `.jpg`) to the project root, add a volume mount in `docker-compose.yml` for it, and re-run the configure script; it will set the theme custom logo. An `anazah-logo.svg` is included for reference (convert to PNG if needed; WordPress often blocks SVG uploads by default).

## Database Credentials (from docker-compose.yml)
- Database: `wordpress`
- User: `wordpress_user`
- Password: `wordpress_password`
- Root Password: `root_password`

## Useful Commands

### Stop containers
```bash
docker-compose down
```

### Stop and remove volumes (clean slate)
```bash
docker-compose down -v
```

### View logs
```bash
docker-compose logs -f wordpress
docker-compose logs -f db
```

### Access WordPress container shell
```bash
docker exec -it wordpress_app bash
```

### Access MySQL container shell
```bash
docker exec -it wordpress_db mysql -u root -p
```

## Customization

### Change Database Credentials
Edit `docker-compose.yml` and update the environment variables:
- `MYSQL_ROOT_PASSWORD`
- `MYSQL_PASSWORD`
- `WORDPRESS_DB_PASSWORD`

### Add WordPress Plugins or Themes
Place your plugins/themes in the volume and they'll be available in WordPress.

### SSL/HTTPS
For production, consider:
- Using Nginx reverse proxy
- Installing SSL certificates with Let's Encrypt
- Using a managed service like AWS, Heroku, or similar

## Deploying the App (Production)

### Option A: Deploy with Docker on a VPS/Cloud (e.g. DigitalOcean, AWS EC2, Linode)

1. **Prepare the server**
   - Ubuntu/Debian: `sudo apt update && sudo apt install -y docker.io docker-compose-plugin git`
   - Enable Docker: `sudo systemctl enable docker && sudo systemctl start docker`

2. **Upload the project**
   - Clone or upload this folder to the server (e.g. `/var/www/anazah`):
     ```bash
     scp -r tata-wp user@your-server-ip:/var/www/anazah
     ```
   - Or use Git: `git clone <your-repo-url> /var/www/anazah && cd /var/www/anazah`

3. **Set production URL**
   - Before first run, set WordPress URL. After starting containers, run:
     ```bash
     docker compose exec wordpress php -r "
     define('WP_USE_THEMES', false);
     require '/var/www/html/wp-load.php';
     update_option('siteurl', 'https://yourdomain.com');
     update_option('home', 'https://yourdomain.com');
     "
     ```
   - Or use WP-CLI inside the container: `docker compose exec wordpress wp option update siteurl https://yourdomain.com --allow-root` and same for `home`.

4. **Run with Docker Compose**
   ```bash
   cd /var/www/anazah
   docker compose up -d --build
   ```
   - Run setup once: `docker compose exec wordpress php /var/www/html/configure-site.php`
   - Use a reverse proxy (Nginx/Caddy) in front with SSL (e.g. Let's Encrypt).

5. **Nginx reverse proxy example** (on the same server or separate)
   ```nginx
   server {
       listen 443 ssl;
       server_name yourdomain.com;
       ssl_certificate /path/to/fullchain.pem;
       ssl_certificate_key /path/to/privkey.pem;
       location / {
           proxy_pass http://127.0.0.1:8080;
           proxy_set_header Host $host;
           proxy_set_header X-Real-IP $remote_addr;
           proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
           proxy_set_header X-Forwarded-Proto $scheme;
       }
   }
   ```
   - Then set `siteurl` and `home` to `https://yourdomain.com` as in step 3.

### Option B: Traditional hosting (cPanel, shared hosting)

1. **Export site and DB from Docker**
   - Run the deploy export script (creates `deploy-export/backup.sql` and `deploy-export/wp-files.tar.gz`):
     ```bash
     chmod +x deploy-export.sh
     ./deploy-export.sh                           # export as-is
     ./deploy-export.sh https://yourdomain.com   # export and replace URLs in DB dump
     ```
   - Or manually:
     ```bash
     docker compose exec db mysqldump -u wordpress_user -pwordpress_password wordpress > backup.sql
     docker compose exec wordpress tar czf - -C /var/www html > wp-files.tar.gz
     ```
   - If not using the script, edit `backup.sql` and replace `http://localhost:8080` with `https://yourdomain.com`.

2. **On the hosting server**
   - Create a MySQL database and user; import `backup.sql`.
   - Upload and extract `wp-files.tar.gz` into the public HTML folder (e.g. `public_html`).
   - Copy `wp-config.php` (or recreate it) with the new DB name, user, password, and host.
   - Upload `logo.png`, `hero-organic.jpg`, `big_title_bg_1.png`, `anazah-product-images/`, `anazah-products.json`, and `configure-site.php` to WordPress root, then run `configure-site.php` once via browser or PHP CLI if available.

### Option C: One-click / managed WordPress (e.g. WP Engine, Kinsta, Cloudways)

1. Create a new WordPress site and get SSH/SFTP and DB access.
2. Install WooCommerce and upload the tm-organik theme.
3. Import the database (after search-replace from local URL to production URL).
4. Upload theme, plugins, and assets; run `configure-site.php` once to set products, logo, address, and options.

## Troubleshooting

**Port 80 already in use?**
```bash
docker-compose down
# Then modify docker-compose.yml ports section:
# "8080:80" instead of "80:80"
```

**Database connection issues?**
- Ensure db service is running: `docker-compose ps`
- Check logs: `docker-compose logs db`
- Verify credentials match in both services

**Persistent data?**
- Database data is stored in `db_data` volume
- WordPress files are stored in `wp_data` volume
- These volumes persist even if containers stop

### Run full setup + export for deployment (one-time)

From the project directory, run in order:

```bash
docker compose up -d --build
./setup-theme.sh
docker compose exec wordpress php /var/www/html/configure-site.php
chmod +x deploy-export.sh && ./deploy-export.sh https://yourdomain.com
```

Then use Option A, B, or C above on your server/hosting.