cs-fix-staging:
	git diff --name-only HEAD | xargs -n1 vendor/bin/php-cs-fixer fix

lint:
	vendor/bin/phpstan analyse --level ${level} ${path}

test:
	vendor/bin/phpunit --bootstrap vendor/bin/phpunit tests