# PHP Core Library

## Code Standard

 * Uses PSR-12
 * tab indent instead of 4 spaces indent
 * Warning at 120 character length, error at 240 character length

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
Currently compatible with PHP 7.4 and 8.0

### legacy

The old non namepsace format layout.
This is fully deprecated and will no longer be maintaned.
last tested PHP 5.6 and PHP 7.0

### development

Any current development is done here

## Static checks

With phpstan (`4dev/checking/phpstan.sh`)
`phpstan`

With phan (`4dev/checking/phan.sh`)
`phan --progress-bar -C --analyze-twice`

pslam is setup but not configured

## Unit tests

With phpunit (`4dev/checking/phpunit.sh`)
`phpunit -c $phpunit.xml 4dev/tests/`


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

* \CoreLibs\ACL\Login uses \CoreLibs\Debug\Logger, \CoreLibs\Language\L10n
* \CoreLibs\DB\IO uses \CoreLibs\Debug\Logger, \CoreLibs\DB\SQL\PgSQL
* \CoreLibs\Admin\Backend uses \CoreLibs\Debug\Logger, \CoreLibs\Language\L10n
* \CoreLibs\Output\Form\Generate uses \CoreLibs\Debug\Logger, \CoreLibs\Language\L10n
* \CoreLibs\Template\SmartyExtend uses \CoreLibs\Language\L10n
* \CoreLibs\Language\L10n uses FileReader, GetTextReader
