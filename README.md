# PHP Core Library

## Code Standard

* Uses PSR-12
* tab indent instead of 4 spaces indent

## General information

Base PHP class files to setup any project

* login
* database wrapper
* basic helper class for debugging and other features
* admin/frontend split
* domain controlled database/settings split
* dynamic layout groups

## NOTE

There are three branches:

### master

The active branch, which is the namespace branch.
Compatible with PHP 8.1 or higher

### legacy (deprecated)

The old non namepsace format layout.
This is fully deprecated and will no longer be maintaned.
last tested PHP 5.6 and PHP 7.0

### development

Any current development is done here

## Static checks

With phpstan (`4dev/checking/phpstan.sh`)
`vendor/bin/phpstan`

With phan (`4dev/checking/phan.sh`)
`vendor/bin/phan --progress-bar -C --analyze-twice`

pslam is setup but not configured

## Unit tests

With phpunit (`4dev/checking/phpunit.sh`)
`www/vendor/bin/phpunit -c $phpunit.xml 4dev/tests/`

## Other Notes

### Session used

The following classes use _SESSION
The main one is ACL\Login, this class will fail without a session started

* \CoreLibs\ACL\Login
* \CoreLibs\Admin\Backend
* \CoreLibs\Output\Form\Generate
* \CoreLibs\Output\Form\Token
* \CoreLibs\Template\SmartyExtend

### Class extends

The following classes extend these classes

* \CoreLibs\ACL\Login extends \CoreLibs\DB\IO
* \CoreLibs\Admin\Backend extends \CoreLibs\DB\IO
* \CoreLibs\DB\Extended\ArrayIO extends \CoreLibs\DB\IO
* \CoreLibs\Output\Form\Generate extends \CoreLibs\DB\Extended\ArrayIO
* \CoreLibs\Template\SmartyExtend extends SmartyBC

### Class used

The following classes use the following classes

* \CoreLibs\ACL\Login uses \CoreLibs\Debug\Logging, \CoreLibs\Language\L10n
* \CoreLibs\DB\IO uses \CoreLibs\Debug\Logging, \CoreLibs\DB\SQL\PgSQL
* \CoreLibs\Admin\Backend uses \CoreLibs\Debug\Logging, \CoreLibs\Language\L10n
* \CoreLibs\Output\Form\Generate uses \CoreLibs\Debug\Logging, \CoreLibs\Language\L10n
* \CoreLibs\Template\SmartyExtend uses \CoreLibs\Language\L10n
* \CoreLibs\Language\L10n uses FileReader, GetTextReader
* \CoreLibs\Admin\EditBase uses \CoreLibs\Debug\Logging, \CoreLibs\Language\L10n

### Class internal load

Loads classes internal (not passed in, not extend)

* \CoreLibs\Admin\EditBase loads \CoreLibs\Template\SmartyExtend, \CoreLibs\Output\Form\Generate
* \CoreLibs\Output\From\Generate loads \CoreLibs\Debug\Logging, \CoreLibs\Language\L10n if not passed on
* \CoreLibs\Output\From\Generate loads \CoreLibs\Output\From\TableArrays

## PHP unit testing and Intelephense

Intelephense can not directly read phar files so we do the following

In the workspace root we have `.libs/`, be in the workspace folder not the `.libs/` folder

`php -r "(new Phar('/path/to/.phive/phars/phpunit-9.6.13.phar'))->extractTo('.libs/phpunit/');"`

andd add in vscode Intelephense > Enviroment: Include Paths (intelephense.environment.includePaths)

```json
"intelephense.environment.includePaths": [
  "/.libs/phpunit/"
]
```

Add `.libs` to the master .gitingore

### Update phpunit

On a version update the old phpunit folder in .libs has to be removed and the new version extracted again

## Javascript

The original edit.js javascript functions are now in utils.js or utils.min.js.

The development for thos files is located in a different repository

https://[service]/CodeBlocks/javascript-utils
