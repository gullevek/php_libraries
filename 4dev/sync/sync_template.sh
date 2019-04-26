#!/bin/bash

# create path
path=$(pwd)"/"$0;

LOCAL_BASE_DIR="<local folder>";
LOCAL_DIR=$LOCAL_BASE_DIR"";
REMOTE_WEB="<remote folder>";
TARGET_HOST_WEB="<user>@<host>";
TMP_DIR=$LOCAL_BASE_DIR"/4dev/tmp/";
tmpf_web=$TMP_DIR"sync.exclude.tmp";

# for web (ika)
rm -f $tmpf_web;
echo ".*.swp" >> $tmpf_web;
echo "._*" >> $tmpf_web;
echo ".DS_Store" >> $tmpf_web;
echo ".svn" >> $tmpf_web;
echo ".svnignore" >> $tmpf_web;
echo ".git" >> $tmpf_web;
echo ".gitignore" >> $tmpf_web;
echo ".htaccess" >> $tmpf_web;
echo "tmp/*" >> $tmpf_web;
echo "templates_c/*" >> $tmpf_web;
echo "cache/*" >> $tmpf_web;
echo "statistics/*" >> $tmpf_web;
echo "media/uploads/*" >> $tmpf_web;
echo "media/csv/*" >> $tmpf_web;
echo "4dev/*" >> $tmpf_web;
echo "log/*" >> $tmpf_web;

echo "Exclude List:"
echo "WEB:";
cat $tmpf_web;

echo "($1) Syncing from $LOCAL_DIR/* to $TARGET_HOST_WEB:$REMOTE_WEB";
echo "You hav 5 seconds to abort (<ctrl> + c)";
#c=0;until [ $c -eq 10 ];do echo -n "#"; sleep 1; c=`expr $c + 1`;done;
for ((i=5;i>=1;i--));
do
        echo -n $i" ";
        sleep 1;
done;

if [ "$1" = "live" ];
then
		# ika sync
		rsync -Plzvrupt --stats --include ".htaccess" --exclude-from=$tmpf_web --delete -e ssh $LOCAL_DIR/* $TARGET_HOST_WEB:$REMOTE_WEB
else
		# ika sync
		rsync -n -Plzvrupt --stats --include ".htaccess" --exclude-from=$tmpf_web --delete -e ssh $LOCAL_DIR/* $TARGET_HOST_WEB:$REMOTE_WEB
fi;

# END
