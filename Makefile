install:
	composer install

lint:
	composer run-script phpcs -- --standard=PSR12 src bin tests

test:
	composer test

test-coverage:
	composer test -- --coverage-clover reports/phpunit/coverage/clover.xml --coverage-html reports/phpunit/coverage/coverage-html