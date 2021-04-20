# Enum

This Orkestra component provides typed enumerations to PHP.

## Installation
```shell
composer require morebec/orkestra-enum
```

## Usage

### Creating an Enum 
To create a new Enum, one needs to extend the `Enum` class. 
As an example, lets pretend we want to create a `CardinalPoint` Class. 
Since there are strictly 4 cardinal points, this is a good candidate for an Enum:
```php
class CardinalPoint extends Enum
{
    const NORTH = 'NORTH';    
    const EAST = 'EAST';    
    const WEST = 'WEST';  
    const SOUTH = 'SOUTH';
}
```

Simply doing this, will allow us to use our class in the following way:
```php
// Instantiate a new CardinalPoint instance
$direction = new CardinalPoint(CardinalPoint::NORTH);

// Since Enums have builtin validation,
// the following line would throw an InvalidArgumentException:
$direction = new CardinalPoint('North');

// However the following would work:
$direction = new CardinalPoint('NORTH');

// Using in functions or class methods 
public function changeDirection(CardinalPoint $direction)
{
    // Testing equlity with string
    if(!$direction->isEqualTo(new CardinalPoint(CardinalPoint::EAST))) {
        echo 'Not going East!';
    }

    // Since the constants are strings, it is also possible to compare
    // using loose comparison
    if($direction == CardinalPoint::NORTH) {
        echo 'Definitely going North!';
    }    
}
```

> For easier IDE integration we can even go further adding `@method` annotations to the Enum class:
> ```php
> /**
 > * @method static self NORTH() 
 > * @method static self EAST() 
 > * @method static self WEST() 
 > * @method static self SOUTH() 
 > */
> class CardinalPoint extends Enum
> {
>    const NORTH = 'NORTH';    
>    const EAST = 'EAST';    
>    const WEST = 'WEST';  
>    const SOUTH = 'SOUTH';
> }
> ```
> This will allow us to do the following in code:
> ```php
> $direction = CardinalPoint::NORTH();
> ```

### Getting all possible values
In order to get all the possible values as an array you can use the static method `getValues`:
```php
CardinalPoint::getValues(); 
// Returns an array as: 
// [ 'NORTH', 'EAST', 'WEST', 'SOUTH' ]
```