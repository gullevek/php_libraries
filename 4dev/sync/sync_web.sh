#!/bin/bash
# $Id: sync_web.sh 3158 2010-09-02 02:49:00Z gullevek $

exit 0;

# create path
path=`pwd`"/"$0;

LOCAL_DIR="/home/developer/html/adidas/20060912_shoplocator/";
REMOTE_DIR="/var/www/adidas/shoplocator/";

echo "Syncing from '$LOCAL_DIR' to '$REMOTE_DIR'";
echo "You hav 5 seconds to abort (<ctrl> + c)";
for ((i=5;i>=1;i--));
do
	echo -n $i" ";
	sleep 1;
done;

# see man rsync for flag explenation
rsync -Plzvrpt --stats --include ".htaccess" --exclude ".*.swp" --exclude "._*" --exclude ".DS_Store" --exclude ".svn" --exclude ".svnignore"  --exclude "tmp/*" --exclude "cache/*" --exclude "templates_c/*" --exclude "media/*" --delete -e ssh $LOCAL_DIR/ developer@somen.tokyo.tequila.jp:/$REMOTE_DIR/
