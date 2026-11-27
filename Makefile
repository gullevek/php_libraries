default:

# always run composer update on self and www
# www uses a classmap on lib/, dump-autoload picks up new classes
update:
	composer update
	cd www && composer update
	cd www && composer dump-autoload
	cd www && composer audit --no-dev --no-scripts --no-plugins --no-interaction

all: update format-check lint test

format-check: update
	composer run-script format-check

format: update
	composer run-script format

lint: update
	composer run-script phan
	composer run-script phpstan

lint-phan: update
	composer run-script phan

lint-phpstan: update
	composer run-script phpstan

# Tests
test: update
	composer test

# copy to composer build folder
copy: all
	4dev/composer/sync-to-composer-all-folder.sh go

.PHONY: update all format-check format lint lint-phan lint-phpstan test copy
