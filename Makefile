.PHONY: phpcs
phpcs:
	docker-compose exec php vendor/bin/phpcs --standard=phpcs.xml