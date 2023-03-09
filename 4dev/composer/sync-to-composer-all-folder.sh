#!/bin/env bash

# syncs
# 4dev/tests/
# www/lib/CoreLibs/
#
# to the composer corelibs all repo

GO="${1}";
DRY_RUN="";
if [ "${GO}" != "go" ]; then
	DRY_RUN="-n ";
fi;

BASE="/storage/var/www/html/developers/clemens/core_data/";
SOURCE="${BASE}php_libraries/trunk/"
TARGET="${BASE}composer-packages/CoreLibs-Composer-All/"

rsync ${DRY_RUN}-Plzvrupt --stats --delete ${SOURCE}4dev/tests/ ${TARGET}test/phpunit/
rsync ${DRY_RUN}-Plzvrupt --stats --delete ${SOURCE}www/lib/CoreLibs/ ${TARGET}src/

# __END__
