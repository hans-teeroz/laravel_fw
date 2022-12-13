#!/usr/bin/with-contenv bash

source /root/.bashrc
echo -e "${BLUE}--- Run Document init ---${NC}"

### Run init
php artisan scribe:generate --force
