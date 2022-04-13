#!/usr/bin/env bash

# if we don't have one base file we are in the wrong folder
if [ ! -f "locale/en_US/LC_MESSAGES/admin.mo" ]; then
	echo "Locale file is missing, wrong base folder?"
	echo "Should be: 4dev/tests/includes/"
	exit;
fi;

msgfmt -o locale/en_US/LC_MESSAGES/admin.mo locale/en_US.admin.UTF-8.po
msgfmt -o locale/en_US/LC_MESSAGES/frontend.mo locale/en_US.frontend.UTF-8.po
msgfmt -o locale/ja_JP/LC_MESSAGES/admin.mo locale/ja_JP.admin.UTF-8.po
msgfmt -o locale/ja_JP/LC_MESSAGES/frontend.mo locale/ja_JP.frontend.UTF-8.po
