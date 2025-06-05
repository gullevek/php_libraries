base=$(pwd)"/";
# must be run in ${base}
cd $base || exit;
${base}tools/phpstan;
cd ~ || exit;
