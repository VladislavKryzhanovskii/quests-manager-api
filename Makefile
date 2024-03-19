#################
# Variables
##################

DOCKER_COMPOSE = docker-compose -f ./docker/docker-compose.yml
DOCKER_COMPOSE_PHP_FPM_EXEC = ${DOCKER_COMPOSE} exec -u www-data php-fpm

##################
# Docker compose
##################

build:
	${DOCKER_COMPOSE} build
start:
	${DOCKER_COMPOSE} start
stop:
	${DOCKER_COMPOSE} stop
up:
	${DOCKER_COMPOSE} up -d --remove-orphans
ps:
	${DOCKER_COMPOSE} ps
logs:
	${DOCKER_COMPOSE} logs -f
down:
	${DOCKER_COMPOSE} down -v --rmi=all --remove-orphans
restart:
	make stop start
rebuild:
	make down build up

##################
# App
##################

app_bash:
	${DOCKER_COMPOSE} exec -u www-data php-fpm bash
fixture:
	${DOCKER_COMPOSE} exec -u www-data php-fpm php bin/console doctrine:fixtures:load
test:
	${DOCKER_COMPOSE} exec -u www-data php-fpm php bin/phpunit
cache:
	docker-compose -f ./docker/docker-compose.yml exec -u www-data php-fpm php bin/console cache:clear
	docker-compose -f ./docker/docker-compose.yml exec -u www-data php-fpm php bin/console cache:clear --env=test

##################
# Database
##################

migrate:
	${DOCKER_COMPOSE} exec -u www-data php-fpm php bin/console doctrine:migrations:migrate --no-interaction
diff:
	${DOCKER_COMPOSE} exec -u www-data php-fpm php bin/console doctrine:migrations:diff --no-interaction
drop:
	docker-compose -f ./docker/docker-compose.yml exec -u www-data php-fpm php bin/console doctrine:schema:drop --force
