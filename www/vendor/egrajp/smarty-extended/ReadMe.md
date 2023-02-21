# Composer package from Smarty Extended

This is an updated package for smarty\smarty

Adds:

- translation block
- label and pos for checkboxes and radio buttons

For local install only

## Setup from central composer

| Host | Location | Type |
| - | - | - |
| composer.tokyo.tequila.jp | soba-local | Local test |
| composer-local.tokyo.tequila.jp | udon-local | Local Live, no https |
| composer.egplusww.jp | udon | General Live (use this) |

composer.json:

For Local test, note that secure-http has to be turned off:

```json
{
    "repositories": [
        {
            "type": "composer",
            "url": "http://composer.tokyo.tequila.jp"
        }
    ],
    "require": {
        "egrajp/smarty-extended": "@dev"
    },
    "config": {
        "secure-http": false
    }
}
```

For live settings

```json
{
    "repositories": [
        {
            "type": "composer",
            "url": "https://composer.egplusww.jp"
        }
    ],
    "require": {
        "egrajp/smarty-extended": "@dev"
    }
}
```
