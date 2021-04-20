# DateTime
The Orkestra DateTime Component builds on top of [cakephp/chronos](https://github.com/cakephp/chronos) which itself builds on top of
[nesbot/carbon](https://github.com/nesbot/carbon) in order to bring immutability to dates and times as the default.
It provides the concept of a `ClockInterface` to be used in projects to get the time as an infrastructure concern
instead of directly accessing time as well as providing Date(Time)Range implementations.

## Installation

```shell
composer require morebec/orkestra-datetime
```

Then ensure the composer autoloader is imported:
```php
<?php
require 'vendor/autoload.php';

use Morebec\Orkestra\DateTime\ClockInterface;
use Morebec\Orkestra\DateTime\SystemClock;

/** @var ClockInterface $clock */
$clock = new SystemClock();
printf("Now: %s", $clock->now());
```

## Usage
### `ClockInterface`
The clock interface is used in order to centralize the location where the current date/time is fetched.
In essence, doing things like `DateTime::now()` in arbitrary locations of the code makes it harder to test, and manipulate.
Being an infrastructure related concept, it makes more sense to obtain the time from a Clock than being able to access 
it from anywhere.

This can allow to test the system in the past or future.

Here are the implementations of the `ClockInterface`:
- `SystemClock` Implementation of the system clock based on the time of the system.
- `FixedClock` Implementation of clock always returning the same fixed date time, to be used in unit tests for better control of time.
- `OffsetClock`  Clock that returns the date time from a specific defined offset at creation as if it were the current time. This is useful when we have to reverse in time or fast-forward in the future but still keep the clock running as  the actual time passes.


For more information on the available functions of `Date` and `DateTime`, please refer to the documentation of [cakephp/chronos](https://github.com/cakephp/chronos).