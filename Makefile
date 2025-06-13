up: down
	docker compose up -d
down:
	docker compose down
start:
	docker compose start
stop:
	docker compose stop
build:
	docker compose build
code:
	docker exec -it vesnushka-php sh -c 'PHP_CS_FIXER_IGNORE_ENV=1 vendor/bin/php-cs-fixer fix src'
	docker exec -it vesnushka-php vendor/bin/phpstan analyse
seed:
	docker exec -it vesnushka-php php bin/console doctrine:fixtures:load
db:
	docker exec -it vesnushka-php php bin/console doctrine:migrations:migrate
test: code
	docker exec -it vesnushka-php sh -c 'php bin/console c:c && php bin/phpunit'
