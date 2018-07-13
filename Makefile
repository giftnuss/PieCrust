
.PHONY: cover

cover:
	php -d zend_extension=xdebug ./libs/bin/phpunit --coverage-html cover --whitelist src tests/src/
