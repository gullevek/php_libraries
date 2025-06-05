base=$(pwd)"/";
# must be run in ${base}
cd $base || exit;
#PHAN_DISABLE_XDEBUG_WARN=1;${base}tools/phan --progress-bar -C --analyze-twice
PHAN_DISABLE_XDEBUG_WARN=1;${base}vendor/bin/phan --progress-bar -C --analyze-twice
cd ~ || exit;
