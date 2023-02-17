# Composer package from CoreLibs

This is just the lib/CoreLibs folder in a composer package.

For local install only

## Setup

In the composer.json file add the following

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://git.egplusww.jp/Composer/CoreLibs-Composer-All"
        }
    ],
    "require": {
        "egrajp/corelibs-composer-all": "@dev"
    }
}
```
