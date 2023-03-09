# Composer package from CoreLibs

This is just the lib/CoreLibs folder in a composer package.

For local install only

**Note**: for following classes the `egrajp/smarty-extended` has to be installed

- Template\SmartyExtended
- Admin\EditBase

## Setup from central composer

Setup from gitea internal servers

```sh
composer config repositories.git.egplusww.jp.Composer composer https://git.egplusww.jp/api/packages/Composer/composer
```

Alternative setup composer local zip file repot:
`composer config repositories.composer.egplusww.jp composer http://composer.egplusww.jp`

## Install package

`composer require egrajp/corelibs-composer-all:^8.0`
