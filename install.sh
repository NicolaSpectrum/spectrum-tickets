#Install dependencies
sudo apt update && sudo apt upgrade -y
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3 php8.3-cli php8.3-common php8.3-fpm \
php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip php8.3-bcmath \
git unzip

sudo apt update
sudo apt install imagemagick
sudo apt install php8.3-imagick

# create laravel proyect and permitions
sudo mkdir -p /var/www/laravel
sudo chown -R ubuntu:ubuntu /var/www/laravel
cd /var/www
sudo git clone https://github.com/NicolaSpectrum/spectrum-tickets.git laravel

sudo chown -R ubuntu:www-data /var/www/laravel
/logs/laravel.log
sudo chmod -R 775 /var/www/laravel/bootstrap/cache
sudo chown -R www-data:www-data /var/www/laravel/storage
sudo chown -R www-data:www-data /var/www/laravel/bootstrap/cache
sudo chmod -R 777 /var/www/laravel/storage/logs
sudo chmod -R 775 /var/www/laravel/bootstrap/cache
# install laravel dependencies

sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

sudo apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-common php8.3-mysql php8.3-xml \
php8.3-curl php8.3-mbstring php8.3-zip php8.3-bcmath php8.3-intl php8.3-gd php8.3-opcache

sudo update-alternatives --set php /usr/bin/php8.3

sudo systemctl restart php8.3-fpm

# install nginx
sudo apt install nginx -y
sudo nano /etc/nginx/sites-available/laravel

server {
    listen 80;
    server_name tudominio.com www.tudominio.com;

    root /var/www/laravel/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}

sudo nano /etc/nginx/sites-available/laravel
 #Remplazar 
 fastcgi_pass unix:/run/php/php8.2-fpm.sock;
 # por
 fastcgi_pass unix:/run/php/php8.3-fpm.sock;



sudo ln -s /etc/nginx/sites-available/laravel /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx

# install https free 
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d spectrumtickets.com -d www.spectrumtickets.com





#generar el QR secretKey
php -r "echo base64_encode(random_bytes(32));"
S1+tN2iCYlc5/a6tSsWldGlN+Aw8lN9i06GEdKEOWak=

cd /var/www/laravel




php artisan migrate
php artisan make:filament-user
php artisan tinker 

use Spatie\Permission\Models\Role;

Role::create(['name' => 'admin']);
Role::create(['name' => 'organizer']);
Role::create(['name' => 'verifier']);

use App\Models\User;
$user = User::find(1);
$user->assignRole('admin');




php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
sudo service php8.3-fpm restart
sudo service nginx restart



server {    server_name spectrumtickets.com www.spectrumtickets.com;    root /var/www/laravel/public;    index index.php;    location / {        try_files $uri $uri/ /index.php?$query_string;    }    location ~ \.php$ {        include snippets/fastcgi-php.conf;        fastcgi_pass unix:/run/php/php8.3-fpm.sock;    }    location ~ /\.ht {        deny all;    }    listen 443 ssl; # managed by Certbot    ssl_certificate /etc/letsencrypt/live/spectrumtickets.com/fullchain.pem; # managed by Certbot    ssl_certificate_key /etc/letsencrypt/live/spectrumtickets.com/privkey.pem; # managed by Certbot    include /etc/letsencrypt/options-ssl-nginx.conf; # managed by Certbot    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem; # managed by Certbot    large_client_header_buffers 8 32k;}server {  if ($host = www.spectrumtickets.com) {        return 301 https://$host$request_uri;    } # managed by Certbot    if ($host = spectrumtickets.com) {        return 301 https://$host$request_uri;    } # managed by Certbot    listen 80;    server_name spectrumtickets.com www.spectrumtickets.com;    return 404; # managed by Certbot}


sudo ln -s /etc/nginx/sites-available/laravel /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
sudo service php8.3-fpm restart
