all: run

start: run

run:
	docker-compose -f docker-compose.yml -p laravel8 up -d web

stop:
	docker-compose -f docker-compose.yml -p laravel8 kill

destroy:
	docker-compose -f docker-compose.yml -p laravel8 down

logs:
	docker-compose -f docker-compose.yml -p laravel8 logs -f web

shell:
	docker-compose -f docker-compose.yml -p laravel8 exec --user nginx web bash

root:
	docker-compose -f docker-compose.yml -p laravel8 exec web bash

ip:
	docker inspect laravel8-web | grep \"IPAddress\"