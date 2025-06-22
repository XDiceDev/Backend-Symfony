.PHONY: phpcs php-cs-fixer

phpcs:
	docker-compose exec php vendor/bin/phpcs --standard=phpcs.xml

php-cs-fixer:
	docker-compose exec php vendor/bin/php-cs-fixer fix