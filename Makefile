default:

all: format-check lint test

format-check:
	composer run-script format-check

format:
	composer run-script format

lint:
	composer run-script phan
	composer run-script phpstan

lint-phan:
	composer run-script phan

lint-phpstan:
	composer run-script phpstan

# Tests
test:
	composer test

# copy to composer build folder
copy: all
	4dev/composer/sync-to-composer-all-folder.sh go

.PHONY: all format-check format lint lint-phan lint-phpstan test copy
