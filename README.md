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

The active branch, which is the namespace branch

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
