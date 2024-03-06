# Composer package from Smarty Extended

This is an updated package for smarty\smarty

Adds:

- translation block
- label and pos for checkboxes and radio buttons

For local install only

## Setup from central composer

Setup from gitea internal servers

```sh
composer config repositories.git.egplusww.jp.Composer composer https://git.egplusww.jp/api/packages/Composer/composer
```

Alternative setup composer local zip file repot:
`composer config repositories.composer.egplusww.jp composer http://composer.egplusww.jp`

## Install package

`composer require egrajp/smarty-extended:^4.3`

## How to update

1) update the original composer for ^4.3
2) copy over the src/sysplugins and all base files in src/
3) check either function.html_checkboxes.php and function.html_options.php have changed
4) copy src/plugins except the above two files, be sure to keep the block.t.php and function_popup*.php
5) Create new release version as official relase number

## Updated files (different from master)

### New

`src/plugins/block.t.php`
`src/plugins/function_popup.php`
`src/plugins/function_popup.init.php`

### Changed

`src/plugins/function.html_checkboxes.php`
`src/plugins/function.html_options.php`
