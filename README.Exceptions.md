# Exception rules

What exceptions to use for what

NOTE: There will be custom Excpetions creaed and so some rules will change

NOTE: For catching: always catch \Exception at the end to avoid missing some changed exceptions

NOTE: Changed exceptions will have marked as critical API change

## \Exception

if there is nothing else matching, use this one

## \InvalidArgumentException

if argument to a function is not expected type

## \UnexpectedValueException

If the value is not matching to what we expect

## \LengthException

Given value is out of range

## \RuntimeException

Missing php modules or external programs

## \OutOfRangeException

Not in range of given expression (array or other)

## Below are ERRORs

### \ArgumentCountError [ERROR]

If we have dynamic argument methods and we are missing a certain arguemnt count

### \TypeError

Invalid type
