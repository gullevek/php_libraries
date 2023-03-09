# Create new package in system gitea/gitlab/composer.egplusww.jp

## Prepare

The following things must have been done:

- full phpstan check/phan check where possible
- a valid version tag `vX.Y.Z` must have been created and pushed to all services

## Publish

To do the final publish

### GITEA and GITLAB

Run `publish/publish.sh` script to create composer packages.

This will automatically run all commands to create the packages

### composer.egplusww.jp web host

For the local composer package host.

update `/storage/var/www/html/composer/www/pacakges.json` file with new version and commit
The entry is a copy of the `composer.json` with the following new entries:

```json
{
  ...,
  "version": "X.Y.Z",
  ...
  "dist": {
      "url": "https://git.egplusww.jp/Composer/CoreLibs-Composer-All/archive/vX.Y.Z.zip",
      "type": "zip"
  },
  ...
}
```

run `git pull egra-gitea master` on udon-core in `/var/www/html/composer/www`
