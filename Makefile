PHPARGS=-dmemory_limit=64M
#PHPARGS=-dmemory_limit=64M -dzend_extension=xdebug.so -dxdebug.remote_enable=1 -dxdebug.remote_host=127.0.0.1 -dxdebug.remote_autostart=1
#PHPARGS=-dmemory_limit=64M -dxdebug.remote_enable=1

all:

check-style:
	vendor/bin/php-cs-fixer fix src/ --diff --dry-run

fix-style:
	vendor/bin/php-cs-fixer fix src/ --diff

install:
	composer install --prefer-dist --no-interaction

test:
	php $(PHPARGS) vendor/bin/codecept run

clean_all:
	docker-compose down
	sudo rm -rf tests/tmp/*

up:
	docker-compose up -d
	chmod +rw -R tests/tmp
	mkdir -p tests/testapp/runtime && chmod +rw -R tests/testapp/runtime

cli:
	docker-compose exec php bash

installdocker:
	docker-compose run --rm php composer install && chmod +x tests/testapp/yii

testdocker:
	docker-compose run --rm php sh -c 'vendor/bin/codecept run --env docker'

.PHONY: all check-style fix-style install test clean clean_all up cli installdocker migrate testdocker

