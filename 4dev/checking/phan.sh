base="/storage/var/www/html/developers/clemens/core_data/php_libraries/trunk/";
# must be run in ${base}
cd $base;
${base}tools/phan --progress-bar -C --analyze-twice;
cd ~;
