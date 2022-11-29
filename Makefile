all: build run

build:
	docker-compose -f docker-compose.yml build --no-cache --build-arg hostUID=1000 --build-arg hostGID=1000 web

start: run

run:
	docker-compose -f docker-compose.yml -p laravel81 up -d web

stop:
	docker-compose -f docker-compose.yml -p laravel81 kill

destroy:
	docker-compose -f docker-compose.yml -p laravel81 down

logs:
	docker-compose -f docker-compose.yml -p laravel81 logs -f web

shell:
	docker-compose -f docker-compose.yml -p laravel81 exec --user nginx web bash

root:
	docker-compose -f docker-compose.yml -p laravel81 exec web bash

ip:
	docker inspect laravel8-web1 | grep \"IPAddress\"

mysql:
	docker exec -it laravel8-db1 bash
	
db:
	docker exec -it laravel8-db1 mysql -uuser -psecret
	
redis:
	docker exec -it laravel8-redis redis-cli