# DB Query Params ? and : to $

dbReturn*
dbExec

keep
->query
->params
for reference

## : named params

in order for each named found replace with order number:

```txt
:name, :foo, :bar, :name =>
$1,    $2,   $3,    $1
```

```php
$query = str_replace(
    [':name', ':foo', ':bar'],
    ['$1', '$2', '$3'],
    $query
);
```

## ? Params

Foreach ? set $1 to $n and store that in new params array
in QUERY for each ? replace with matching $n
