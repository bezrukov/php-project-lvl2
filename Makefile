install:
	composer install

lint:
	composer run-script phpcs -- --standard=PSR12 src bin

.PHONY: test-unit-coverage
test-unit-coverage:
	vendor/bin/phpunit --configuration phpunit.xml --do-not-cache-result --order-by=random

.PHONY: test-unit
test-unit:
	vendor/bin/phpunit --configuration phpunit.xml --no-coverage --do-not-cache-result --order-by=random