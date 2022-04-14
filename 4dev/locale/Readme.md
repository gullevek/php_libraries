# Translation files

## Source file name convetion

Locale Name . Domain . Encoding .po

## Name parte explenations

### Locale Name Examples

If underscore in name the long version is checked first, then the short version:
en_US@latin -> en_US -> en

* en
* en_US
* en_US.UTF-8
* en_US@latin

### Domain

For current case auto set CONTENT_PATH is used

* admin
* frontend

### Encoding

if not set UTF-8 is assumed. Any other utf8 encoding is changed to UTF-8

* UTF-8
* SJIS
* EUC

## File name example source

`ja-admin.po`

First part is LOCALE, second part is domain. Separator is `-`

## Folder layout

`includes/locale/ja/LC_MESSAGES/frontend.mo`

ALTERNATE LOCALE NAMES:
* ja
* ja_JP
* ja.UTF-8
* ja_JP.UTF-8

ja_JP.UTF-8: Locale Name
frontend: dmain (CONTENT_PATH)

## command

`msgfmt -o www/includes/locale/ja/LC_MESSAGES/frontend.mo 4dev/lang/ja.admin.po`
