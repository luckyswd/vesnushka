up: down
	docker compose up -d
down:
	docker compose down
start:
	docker compose start
stop:
	docker compose stop
build:
	docker compose build --no-cache