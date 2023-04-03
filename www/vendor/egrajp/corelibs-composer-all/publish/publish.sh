#!/usr/bin/env bash

BASE_FOLDER=$(dirname $(readlink -f $0))"/";
VERSION=$(git tag --list | sort -V | tail -n1 | sed -e "s/^v//");
file_last_published="${BASE_FOLDER}last.published";

if [ -z "${VERSION}" ]; then
     echo "Version must be set in the form x.y.z without any leading characters";
     exit;
fi;
# compare version, if different or newer, deploy
if [ -f "${file_last_published}" ]; then
     LAST_PUBLISHED_VERSION=$(cat ${file_last_published});
     if $(dpkg --compare-versions "${VERSION}" le "${LAST_PUBLISHED_VERSION}"); then
          echo "git tag version ${VERSION} is not newer than previous published version ${LAST_PUBLISHED_VERSION}";
          exit;
     fi;
fi;

# read in the .env.deploy file and we must have
# GITLAB_USER
# GITLAB_TOKEN
# GITEA_DEPLOY_TOKEN
if [ ! -f "${BASE_FOLDER}.env.deploy" ]; then
     echo "Deploy enviroment file .env.deploy is missing";
     exit;
fi;
set -o allexport;
cd ${BASE_FOLDER};
source .env.deploy;
cd -;
set +o allexport;

echo "[START]";
# gitea
if [ ! -z "${GITEA_USER}" ] && [ ! -z "${GITEA_TOKEN}" ]; then
     curl -LJO \
          --output-dir "${BASE_FOLDER}" \
          https://git.egplusww.jp/Composer/CoreLibs-Composer-All/archive/v${VERSION}.zip;
     curl --user ${GITEA_USER}:${GITEA_TOKEN} \
          --upload-file "${BASE_FOLDER}/CoreLibs-Composer-All-v${VERSION}.zip" \
          https://git.egplusww.jp/api/packages/Composer/composer?version=${VERSION};
     echo "${VERSION}" > "${file_last_published}";
else
     echo "Missing either GITEA_USER or GITEA_TOKEN environment variable";
fi;

# gitlab
if [ ! -z  "${GITLAB_DEPLOY_TOKEN}" ]; then
     curl --data tag=v${VERSION} \
          --header "Deploy-Token: ${GITLAB_DEPLOY_TOKEN}" \
          "https://gitlab-na.factory.tools/api/v4/projects/950/packages/composer";
     curl --data branch=master \
          --header "Deploy-Token: ${GITLAB_DEPLOY_TOKEN}" \
          "https://gitlab-na.factory.tools/api/v4/projects/950/packages/composer";
     echo "${VERSION}" > "${file_last_published}";
else
     echo "Missing GITLAB_DEPLOY_TOKEN environment variable";
fi;
echo "";
echo "[DONE]";

# __END__
