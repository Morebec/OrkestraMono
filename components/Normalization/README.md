# Normalization
The normalization component allows to easily normalize complex object graphs to arrays of primitives for persistence purposes.

Essentially, it makes transforming POPOs into human-readable, platform independent serializable arrays
of primitives a breeze.

It can be used to:
- Serialize in any format that supports PHP arrays and primitives.
- Dump objects to an SQL database without the need for an ORM.
- Dump complex object graphs to a document store.
- Load an object in memory from a serialized representation.
- Convert HTTP requests to Typed Objects.


## Installation

## Usage
Let's take an example of an object graph:
```php
class Project 
{
    /** @var string */
    private $id;
    
    /** @var string */
    private $title;
    
    /** @var string */
    private $description;
    
    /** @var Task[] */
    private $tasks;
    
    // ...
}

class Task 
{
    /** @var string */
    private $id;
    
    /** @var string */
    private $name;
    
    /** @var DateTime */
    private $dueDate;
    
    /** @var bool */
    private $completed;
    
    // ...
}
```

Here's how one can normalize an object:

```php
use Morebec\Orkestra\Normalization\ObjectNormalizer;$project = new Project('prj123456789', 'A new Project', 'This is our latest project');
$project->addTask(new Task('tsk123456789', 'Deploy to production', $dueDate));

// Normalize
$normalizer = new ObjectNormalizer();
$data = $normalizer->normalize($project);

print_r($data);
// Would print:
[
    'id' => 'prj123456789',
    'title' => 'A new Project',
    'description' => 'This is our latest project',
    'tasks' => [
        [
            'id' => 'tsk123456789',
            'name' => 'Deploy to production',
            'dueDate' => '2021-01-01T10:25:55+00:00',
            'completed' => false
        ]       
    ]
];
```

This last representation can easily be serialized to json, xml, yaml or any other format.

### Denormalization
Here's how to convert a normalized object back to an instance of its class:

```php

// Would print:
use Morebec\Orkestra\Normalization\ObjectNormalizer;$data = [
    'id' => 'prj123456789',
    'title' => 'A new Project',
    'description' => 'This is our latest project',
    'tasks' => [
        [
            'id' => 'tsk123456789',
            'name' => 'Deploy to production',
            'dueDate' => '2021-01-01T10:25:55+00:00',
            'completed' => false
        ]       
    ]
];
$normalizer = new ObjectNormalizer();
$project = $normalizer->denormalize($data, Project::class);
```

> The process of denormalization works using reflection inspecting an object's structure.
> In order to know what into what type to denormalize a value to, the normalizer checks for `@var`
> annotations on the object for PHP < 7.4, or the declared type for PHP >= 7.4. Therefore, it is important
> to correctly declare the @var annotations if using an older version of PHP.


### Custom normalizers/denormalizer.
Depending on the structure of your objects you might want to personalize the way they are (de)normalized.
A common example is with value objects wrapping primitives:

```php
class Username {

    /** @var string */    
    private $value;

    public function __construct(string $value) {
        $this->value = $value;
    }
    
    public function __toString()
    {
        return $this->value;
    }
}
```

Such object out of the box would be normalized as follows:
```php
[
    'value' => 'the_username'
];
```

For such object, and especially when they are part of an object graph, you might want to normalize them to
the primitive they wrap for example.

To perform this you need to specify a custom normalizer/denormalizer pair and add it to your normalizer:

```php

use Morebec\Orkestra\Normalization\Denormalizer\DenormalizationContextInterface;
use Morebec\Orkestra\Normalization\ObjectNormalizer;
use Morebec\Orkestra\Normalization\Normalizer\ObjectNormalizer\FluentNormalizer;
use Morebec\Orkestra\Normalization\Denormalizer\ObjectDenormalizer\FluentDenormalizer;

$normalizer = new ObjectNormalizer();

$normalizer->addNormalizer(FluentNormalizer::for(Username::class)->asString());
$normalizer->addDenormalizer(FluentDenormalizer::for(Username::class)->as(static function (DenormalizationContextInterface $context) {
    return new Username($context->getValue());
}));
```

The `FluentNormalizer` and `FluentDenormalizer` are the most convenient way to define (De)Normalizers.
If you want to have full control over them you can implement the `NormalizerInterface` and `DenormalizerInterface`.