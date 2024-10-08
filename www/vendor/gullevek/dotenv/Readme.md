# dotenv: readEnvFile()

A simple implementation of <https://github.com/vlucas/phpdotenv>

This is not a functional replacement, but a very simple implementation of the basic functions.

It is recommended to create a `.env.example` example file that is checked into the
repository. The `.env` should *NEVER* be checked into anything

## How to install

`composer require gullevek/dotEnv`

## Run it

Create a `.env` file in the current folder.
Create a file like below

```php
require '../vendor/autoload.php';
gullevek\dotEnv\DotEnv::readEnvFile(__DIR__);
```

All data will be in the `$_ENV` array

## How it works

Put the function where it is needed or put it in a file and load it.

if not parameter is given it will use `__DIR__` as base path.
Second parameter is file name override. Default is `.env`

Data is loaded into _ENV only.

If there is already an entry in _ENV then it will not be overwritten.

## .env file example

A valid entry has to start with an alphanumeric string, underscores are allowed and
then have an equal sign (=). After the equal sign the data block starts. Data can be
quoted with double quotes (") and if this is done can stretch over multiple lines.
The openeing double quote must be on the same lign as the requal sign (=). If double
quoted (") charcters are used it will read each line until another double quote (")
character is found. Everything after that is ignored.

Any spaces before the variable or before and after the equal sign (=) are ignored.

Line is read until `PHP_EOL`. So any trailing spaces are read too.

Any line that is not valid is ignored.

```ini
# this line is ignored
SOMETHING=A
OTHER="A B C"
MULTI_LINE="1 2 3
4 5 6
7 8 9" ; and this is ignored
ESCAPE="String \" inside \" other "
DOUBLE="I will be used"
DOUBLE="This will be ignored"
```

A prefix name can be set with `[PrefixName]`. Tne name rules are like for variables, but spaces
are allowed, but will be converted to "_".
The prefix is valid from the time set until the next prefix block appears or the file ends.

Example

```ini
FOO="bar"
FOOBAR="bar bar"
[SecitonA]
FOO="other bar"
FOOBAR="other bar bar"
```

Will have environmen variables as

```php
$_ENV["FOO"];
$_ENV["FOOBAR"];
$_ENV["SecitonA.FOO"];
$_ENV["SecitonA.FOOBAR"];
```

## Development

### Phan

`vendor/bin/phan --analyze-twice`

### PHPstan

`vendor/bin/phpstan`

### PHPUnit

Unit tests have to be run from base folder with

`vendor/bin/phpunit test/phpUnitTests/`
