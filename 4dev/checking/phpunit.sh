base="/storage/var/www/html/developers/clemens/core_data/php_libraries/trunk/";
# -c phpunit.xml
# --testdox
# prefix with PHP bin to test different version
# /usr/bin/php7.3
# /usr/bin/php7.4
# /usr/bin/php8.0
# /usr/bin/php8.1
${base}www/vendor/bin/phpunit -c ${base}phpunit.xml ${base}4dev/tests/
