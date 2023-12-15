container=php

up:
	docker compose up -d

down:
	docker compose down

ps:
	docker compose ps

install:
	docker compose exec ${container} composer install

update:
	docker compose exec ${container} composer update

require:
	docker compose exec ${container} composer require

require-dev:
	docker compose exec ${container} composer require --dev

user-shell:
	docker compose exec -u 1000:1000 ${container} sh

root-shell:
	docker compose exec -u root ${container} sh

logs:
	docker compose logs -f ${container}

logs-queries:
	docker compose logs ${container} | grep 'Executing statement'

logs-requests:
	docker compose logs ${container} | grep 'Received request'
