install:
	docker run --rm -v "$(PWD):/app" -w /app composer:latest install

test:
	docker run --rm -v "$(PWD):/app" -w /app php:7.3-cli php vendor/bin/phpunit
