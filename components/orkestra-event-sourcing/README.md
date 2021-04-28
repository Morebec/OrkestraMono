# Event Sourcing

The Event Sourcing Orkestra component provides utilities and classes 
for implementing Event Sourcing in a system Using PHP. 

It can be seen as persistence agnostic library that serves as an abstraction over the concept of an Event Store.
It also provides utilities for modeling Event Sourced Aggregate Roots in a DDD sense,
as well as schema validation and migration tools through an upcasting pipeline.
It also supports snapshotting and projections.

> Note: Being an abstraction library, this component does not provide any concrete implementation of an Event Store (except for an in memory one used for testing), 
> it simply provides the required interface to support the concepts.
> For an actual implementation this component can be used in tandem with the official companion component
> `morebec/orkestra-postgresql-eventstore`.
> 
> You are however, free to implement your own concrete storage implementation according to your
> system's technology stack. In that case you can refer yourself to the [PostgreSQL implementation](https://github.com/Morebec/orkestra-postgrsql-eventstore).


## Features:
- Event schema migrations through Upcasting
- Modeling classes for Event Sourced Aggregate Roots
- Projections support
- Snapshotting


## Need help? Have Question?
Create an issue or a discussion on the [Orkestra Repository](https://github.com/Morebec/orkestra), we will be glad to help you as best as we can!

## Getting Started
Install the component in your project through composer.
```shell
composer require morebec/orkestra-event-sourcing
```

## Usage
For usage read the [documentation](docs/EventStore.md)

### Reading from a Stream
Reading from a stream is also very simple:

