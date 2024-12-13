#!/usr/bin/env bash

BASE_FOLDER=$(dirname "$(readlink -f "$0")")"/";
PACKAGE_DOWNLOAD="${BASE_FOLDER}package-download/";
if [ ! -d "${PACKAGE_DOWNLOAD}" ]; then
     mkdir "${PACKAGE_DOWNLOAD}";
fi;
VERSION=$(git tag --list | sort -V | tail -n1 | sed -e "s/^v//");
file_last_published="${BASE_FOLDER}last.published";
go_flag="$1";

if [ -z "${VERSION}" ]; then
     echo "Version must be set in the form x.y.z without any leading characters";
     exit;
fi;
# compare version, if different or newer, deploy
if [ -f "${file_last_published}" ]; then
     LAST_PUBLISHED_VERSION=$(cat "${file_last_published}");
     if dpkg --compare-versions "${VERSION}" le "${LAST_PUBLISHED_VERSION}"; then
          echo "git tag version ${VERSION} is not newer than previous published version ${LAST_PUBLISHED_VERSION}";
          exit;
     fi;
fi;

# read in the .env.deploy file and we must have
# for gitea
# GITEA_PUBLISH: must be set with a value to trigger publish run
# GITEA_UPLOAD_FILENAME
# GITEA_USER
# GITEA_DEPLOY_TOKEN
# GITEA_URL_DL
# GITEA_URL_PUSH
# for gitlab
# GITLAB_PUBLISH: must be set with a value to trigger publish run
# GITLAB_USER
# GITLAB_TOKEN
# GITLAB_URL
if [ ! -f "${BASE_FOLDER}.env.deploy" ]; then
     echo "Deploy enviroment file .env.deploy is missing";
     exit;
fi;
set -o allexport;
cd "${BASE_FOLDER}" || exit;
# shellcheck source=.env.deploy
source .env.deploy;
cd - || exit;
set +o allexport;

if [ "${go_flag}" != "go" ]; then
     echo "No go flag given";
     echo "Would publish ${VERSION}";
     echo "[END]";
     exit;
fi;

echo "[START]";
# gitea
# skip iof
if [ -n "${GITEA_PUBLISH}" ]; then
     if [ -n "${GITEA_UPLOAD_FILENAME}" ] &&
          [ -n "${GITEA_URL_DL}" ] && [ -n "${GITEA_URL_PUSH}" ] &&
          [ -n "${GITEA_USER}" ] && [ -n "${GITEA_TOKEN}" ]; then
          if [ ! -f "${PACKAGE_DOWNLOAD}${GITEA_UPLOAD_FILENAME}-v${VERSION}.zip" ]; then
               curl -LJO \
                    --output-dir "${PACKAGE_DOWNLOAD}" \
                    "${GITEA_URL_DL}"/v"${VERSION}".zip;
          fi;
          if [ ! -f "${PACKAGE_DOWNLOAD}${GITEA_UPLOAD_FILENAME}-v${VERSION}.zip" ]; then
               echo "Version file does not exist for ${VERSION}";
          else
               curl --user "${GITEA_USER}":"${GITEA_TOKEN}" \
                    --upload-file "${PACKAGE_DOWNLOAD}${GITEA_UPLOAD_FILENAME}-v${VERSION}.zip" \
                    "${GITEA_URL_PUSH}"?version="${VERSION}";
               echo "${VERSION}" > "${file_last_published}";
          fi;
     else
          echo "Missing either GITEA_UPLOAD_FILENAME, GITEA_URL_DL, GITEA_URL_PUSH, GITEA_USER or GITEA_TOKEN environment variable";
     fi;
fi;

# gitlab
if [ -n "${GITLAB_PUBLISH}" ]; then
     if  [ -n "${GITLAB_URL}" ] && [ -n  "${GITLAB_DEPLOY_TOKEN}" ]; then
          curl --data tag=v"${VERSION}" \
               --header "Deploy-Token: ${GITLAB_DEPLOY_TOKEN}" \
               "${GITLAB_URL}";
          curl --data branch=master \
               --header "Deploy-Token: ${GITLAB_DEPLOY_TOKEN}" \
               "${GITLAB_URL}";
          echo "${VERSION}" > "${file_last_published}";
     else
          echo "Missing GITLAB_URL or GITLAB_DEPLOY_TOKEN environment variable";
     fi;
fi;
echo "";
echo "[DONE]";

# __END__