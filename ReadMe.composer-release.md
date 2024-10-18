# CoreLibs Composer release flow

- run local phan/phptan/phunit tests
- commit and sync to master branch
- create a version tag in master branch
- checkout development on CoreLibs-composer-all branch
- sync `php_libraries/trunk/www/lib/CoreLibs/*` to c`omposer-packages/CoreLibs-Composer-All/src/`
- if phpunit files have been changed/updated sync them to `composer-packages/CoreLibs-Composer-All/test/phpunit/`
- run phan/phpstan/phpunit tests in composer branch
- commit and sync to master
- create the same version tag as before in the trunk/master
- GITEA and GITLAB:
  - Run `publish/publish.sh` script to create composer packages
- Composer Packagest local
  - update pacakges.json file with new version and commit
  - run `git pull egra-gitea master` on udon-core in `/var/www/html/composer/www`
