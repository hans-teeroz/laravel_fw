#!/usr/bin/with-contenv bash
#
# Run Laravel queues
#

# Queue:work good for production mode
#php /home/www/app/artisan queue:work --queue=high,default --sleep=2 --tries=3

# Action `queue:listen` good for development mode. But it will take performance slow
# Note: Some Laravel version don't support listen
php /home/www/app/artisan queue:listen --queue=high,default --sleep=2 --tries=3
