#!/usr/bin/env bash

BASE_FOLDER=$(dirname "$(readlink -f "$0")")"/";
PACKAGE_DOWNLOAD="${BASE_FOLDER}package-download/";
if [ ! -d "${PACKAGE_DOWNLOAD}" ]; then
     mkdir "${PACKAGE_DOWNLOAD}";
fi;
VERSION=$(git tag --list | sort -V | tail -n1 | sed -e "s/^v//");
file_last_published="${BASE_FOLDER}last.published";
go_flag="$1";

function gitea_publish
{
     _GITEA_PUBLISH="${1}"
     _GITEA_UPLOAD_FILENAME="${2}"
     _GITEA_URL_DL="${3}"
     _GITEA_URL_PUSH="${4}"
     _GITEA_USER="${5}"
     _GITEA_TOKEN="${6}"
     _PACKAGE_DOWNLOAD="${7}"
     _VERSION="${8}"
     _file_last_published="${9}"

     if [ -z "${_GITEA_PUBLISH}" ]; then
          return
     fi;
     if [ -n "${_GITEA_UPLOAD_FILENAME}" ] &&
          [ -n "${_GITEA_URL_DL}" ] && [ -n "${_GITEA_URL_PUSH}" ] &&
          [ -n "${_GITEA_USER}" ] && [ -n "${_GITEA_TOKEN}" ]; then
          echo "> Publish ${_GITEA_UPLOAD_FILENAME} with ${_VERSION} to: ${_GITEA_URL_PUSH}";
          if [ ! -f "${_PACKAGE_DOWNLOAD}${_GITEA_UPLOAD_FILENAME}-v${_VERSION}.zip" ]; then
               echo "> Download: ${_GITEA_UPLOAD_FILENAME}-v${_VERSION}.zip";
               curl -LJO \
                    --output-dir "${_PACKAGE_DOWNLOAD}" \
                    "${_GITEA_URL_DL}"/v"${_VERSION}".zip;
          fi;
          if [ ! -f "${_PACKAGE_DOWNLOAD}${_GITEA_UPLOAD_FILENAME}-v${_VERSION}.zip" ]; then
               echo "[!] Package file does not exist for version: ${_VERSION}";
          else
               response=$(curl --user "${_GITEA_USER}":"${_GITEA_TOKEN}" \
                    --upload-file "${_PACKAGE_DOWNLOAD}${_GITEA_UPLOAD_FILENAME}-v${_VERSION}.zip" \
                    "${_GITEA_URL_PUSH}"?version="${_VERSION}");
               status=$(echo "${response}" | jq .errors[].status);
               message=$(echo "${response}" | jq .errors[].message);
               if [ -n "${status}" ]; then
                    echo "[!] Error ${status}: ${message}";
               else
                    echo "> Publish completed";
               fi;
               echo "${_VERSION}" > "${_file_last_published}";
          fi;
     else
          echo "[!] Missing either GITEA_UPLOAD_FILENAME, GITEA_URL_DL, GITEA_URL_PUSH, GITEA_USER or GITEA_TOKEN environment variable";
     fi;
}

function gitlab_publish
{
     _GITLAB_PUBLISH="${1}";
     _GITLAB_URL="${2}";
     _GITLAB_DEPLOY_TOKEN="${3}";
     _PACKAGE_DOWNLOAD="${4}"
     _VERSION="${5}"
     _file_last_published="${6}"
     if [ -z "${GITLAB_PUBLISH}" ]; then
          return;
     fi;
     if  [ -n "${_GITLAB_URL}" ] && [ -n  "${_GITLAB_DEPLOY_TOKEN}" ]; then
          curl --data tag=v"${_VERSION}" \
               --header "Deploy-Token: ${_GITLAB_DEPLOY_TOKEN}" \
               "${_GITLAB_URL}";
          curl --data branch=master \
               --header "Deploy-Token: ${_GITLAB_DEPLOY_TOKEN}" \
               "${_GITLAB_URL}";
          echo "${_VERSION}" > "${_file_last_published}";
     else
          echo "[!] Missing GITLAB_URL or GITLAB_DEPLOY_TOKEN environment variable";
     fi;
}


if [ -z "${VERSION}" ]; then
     echo "[!] Version must be set in the form x.y.z without any leading characters";
     exit;
fi;
# compare version, if different or newer, deploy
if [ -f "${file_last_published}" ]; then
     LAST_PUBLISHED_VERSION=$(cat "${file_last_published}");
     if dpkg --compare-versions "${VERSION}" le "${LAST_PUBLISHED_VERSION}"; then
          echo "[!] git tag version ${VERSION} is not newer than previous published version ${LAST_PUBLISHED_VERSION}";
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
     echo "[!] Deploy enviroment file .env.deploy is missing";
     exit;
fi;
set -o allexport;
cd "${BASE_FOLDER}" || exit
# shellcheck source=.env.deploy
source .env.deploy;
cd - >/dev/null 2>&1 || exit;
set +o allexport;

if [ "${go_flag}" != "go" ]; then
     echo "[!] No go flag given";
     echo "> Would publish ${VERSION}";
     echo "[END]";
     exit;
fi;

echo "[START]";
# gitea
gitea_publish "${GITEA_PUBLISH}" "${GITEA_UPLOAD_FILENAME}" "${GITEA_URL_DL}" "${GITEA_URL_PUSH}" "${GITEA_USER}" "${GITEA_TOKEN}" "${PACKAGE_DOWNLOAD}" "${VERSION}" "${file_last_published}";
gitea_publish "${PR_GITEA_PUBLISH}" "${PR_GITEA_UPLOAD_FILENAME}" "${PR_GITEA_URL_DL}" "${PR_GITEA_URL_PUSH}" "${PR_GITEA_USER}" "${PR_GITEA_TOKEN}" "${PACKAGE_DOWNLOAD}" "${VERSION}" "${file_last_published}";

# gitlab
# gitlab_publish "${GITLAB_PUBLISH}" "${GITLAB_URL}" "${GITLAB_DEPLOY_TOKEN}" "${PACKAGE_DOWNLOAD}" "${VERSION}" "${file_last_published}";
echo "";
echo "[DONE]";

# __END__
