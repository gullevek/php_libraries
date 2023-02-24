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
- GITEA
  - download ZIP file from TAG
  - `curl --user clemens.schwaighofer:KEY \
     --upload-file ~/Documents/Composer/CoreLibs-Composer-All-vX.Y.Z.zip \
     https://git.egplusww.jp/api/packages/Composer/composer?version=X.Y.Z`
- GitLab
  - `curl --data tag=vX-Y-Z --header "Deploy-Token: TOKENr" "https://gitlab-na.factory.tools/api/v4/projects/950/packages/composer"`
- Composer Packagest local
  - update pacakges.json file with new version and commit
  - `git pull egra-gitea master`
