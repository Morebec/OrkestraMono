# Orkestra
[![Monorepo Split](https://github.com/Morebec/OrkestraMono/actions/workflows/split-repo.yaml/badge.svg)](https://github.com/Morebec/OrkestraMono/actions/workflows/split-repo.yaml)

Orkestra is a set of PHP components for building Systems and Applications using concepts from
Domain Driven Design (DDD), Command Query Responsibility Segregation (CQRS) and Event Souring (ES).

These components can be used independently according to the needs of the systems.

The main goal behind this set of components is to simplify the plumbing required 
to support the principles of DDD/CQRS/ES and allow developers to build systems 
by simply focusing on the core domain and business logic. 

These components are Infrastructure and Framework independent, allowing them to be used in any kind of setup.
There is currently a component tailored for easy integration with the Symfony Framework to simplify the process even further.

### The Orkestra components

#### Core Components
Here's a list of the current core components of Orkestra:
- **[DateTime](https://github.com/Morebec/orkestra-datetime)**: Based on `cake-php/chronos` to improve the capabilities of DateTimes in an Immutable fashion as well as providing interfaces to access time through `ClockInterface` some an application can provide
  different means to get the current time in different contexts, such as time travel.
- **[Normalization](https://github.com/Morebec/orkestra-normalization)**: This component allows transforming complex object trees into simple PHP arrays of primitives for easy serialization in any format. As a side effect it greatly reduces the need for
  ORMs while decoupling persistence mechanism from the domain model as it does not require annotations or inheriting interfaces. In short, it provides ways to do: `Entity -> array -> dump to database (MongoDb, PostgreSql, Redis etc.)`
- **[Enum](https://github.com/Morebec/orkestra-enum)**: Provides typed Enum to PHP.
- **[Collections](https://github.com/Morebec/orkestra-collections)**: Provides typed Collections.
- **[Exceptions](https://github.com/Morebec/orkestra-collections)**: Provides common exceptions.
- **[Messaging](https://github.com/Morebec/orkestra-messaging)**: Provides the building block to define messages and handlers such as Commands, Queries, Events, Time based messages called (Timers).
  It also includes the basic mechanisms to integrate intra-process message buses to an application to map messages to their message handlers while providing hooks
  to alter the behaviour of sending messages to their handlers. In short, it's the Mediator pattern with helpers for CQRS.
- **[Modeling](https://github.com/Morebec/orkestra-modeling)**:  Provides the building blocks for Modeling Entities, Aggregates and Aggregate Roots. It also provides additional utilities like Enums
  or common  useful Exception classes.
- **[EventSourcing](https://github.com/Morebec/orkestra-event-sourcing)**: This component contains the building block to add Event Sourcing capabilities to a system. It provides implementations
  for Event Sourced Aggregate Roots, interfaces for an Event Store as well as Event Processors which are services responsible for forwarding events in an event store
  to the various components of the system.
- **[Privacy](https://github.com/Morebec/orkestra-privacy)**: This components provides building blocks to make Personal Data and Privacy an explicit requirement of the system and to simplify 
  the integration of regulations such as GDPR or CCPA.
- **[Retry](https://github.com/Morebec/orkestra-retry)**: Provides utilities to improve the resiliency of some components in a system through the use of Retry Classes with support for delays and exponential backoffs.

#### Infrastructure Specific Components
There are also infrastructure specific implementations of some the Interfaces from the core Components:
- **[PostgreSQLDocumentStore](https://github.com/Morebec/orkestra-postgresql-document-store)**: Allows to treat PostgreSQL like a Document Store.
- **[PostgreSQLEventStore](https://github.com/Morebec/orkestra-postgresql-event-store)**: Implementation of an Event Store in PostgreSQL.
- **[PostgreSQLPersonalInformationStore](https://github.com/Morebec/orkestra-postgresql-personal-information-store)**: To be used with the [Privacy](https://github.com/Morebec/orkestra-privacy) component, allows storing personal data of users in an encrypted form in a PostgreSQL Database.
- **[PostgreSQLTimerStorage](https://github.com/Morebec/orkestra-postgresql-timer-storage)**: Allows to store timers of the [Messaging](https://github.com/Morebec/orkestra-messaging) component, making it easy to trigger tasks in the future in a much more complex way than Cron by using custom logic.

#### Framework Integrations
Here are additional components for easier integration with specific frameworks:
- **[SymfonyBundle](https://github.com/Morebec/orkestra-symfony-bundle)**: Integrates various Orkestra Components with Symfony, while also providing a Module system in order to structure projects in a Bounded Context oriented approach.


## Monorepo
The current repository is maintained as a Monorepo, all issues and pull requests for any of the components should be done in this repository.
Internally whenever a new release is made it is propagated to the standalone repositories of the different components.

## Getting Started
To get started, please read the following [page]() from the documentation.

## Help & Support
If you are using Orkestra Components in your project and are stuck on something or want to deepen your understanding of the code base, do not hesitate to open an issue in this repository,
we will try our best to help you.

## Contributing
Orkestra is open for contributions. If you want to contribute to one of the components or the documentation, please read
the [Contribution Guide]().

