.PHONY: phpcs php-cs-fixer psalm

phpcs:
	docker-compose exec php vendor/bin/phpcs --standard=phpcs.xml

php-cs-fixer:
	docker-compose exec php vendor/bin/php-cs-fixer fix

psalm:
	docker-compose exec php vendor/bin/psalm