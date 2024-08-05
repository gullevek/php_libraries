# Composer package from CoreLibs

This is just the lib/CoreLibs folder in a composer package.

For local install only

**Note**: for following classes the `egrajp/smarty-extended` has to be installed

- Template\SmartyExtended
- Admin\EditBase

## Publish to gitea or gitlab server

Currently there are only gitea and gitlab supported, github does not have support for composer packages

`publish\publish.sh go` will run the publish script

All the configuration is done in the `publish\.env.deploy` file

```ini
# downlaod file name is "Repository name" "-" "version" where
# version is "vN.N.N"
GITEA_PUBLISH=1
GITEA_UPLOAD_FILENAME="Upload-File-Name";
GITEA_USER=gitea-user
GITEA_TOKEN=gitea-tokek
GITEA_URL_DL=https://[gitea.hostname]/[to/package/folder]/archive
GITEA_URL_PUSH=https://[gitea.hostname]/api/packages/[organization]/composer

GITLAB_PUBLISH=1
GITLAB_URL=gitlab URl to repository
GITLAB_DEPLOY_TOKEN=gitlab-token
```

At the moment there is only one gitea or gitlab target setable

## Setup from central composer

Setup from gitea servers

[hostname] is the hostname for your gitea server (or wherever this is published)
[OrgName] is the organization name where the composer packages are hosted

```sh
composer config repositories.[hostname].Composer composer https://[hostname]/api/packages/[OrgName]/composer
```

## Install package

`composer require egrajp/corelibs-composer-all:^9.0`

## Tests

All tests must be run from the base folder

### phan

`phan --progress-bar -C --analyze-twic`

### phpstan

`phpstan`

### phpunit

PHP unit is installed via "phiev"

`tools/phpunit test/phpunit`
