base=$(pwd)"/";
# must be run in ${base}
cd $base || exit;
${base}tools/phan --progress-bar -C --analyze-twice;
cd ~ || exit;
