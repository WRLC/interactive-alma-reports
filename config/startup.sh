cp /home/site/wwwroot/config/default /etc/nginx/sites-available/default
service nginx reload

cd /home/site/wwwroot
composer install