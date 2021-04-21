# UPGRADE

# 2.x

## 2.0

### General
The Messaging Component has been completely redesigned so that the concept of `MessageBus` is first class citizen rather
than being `DomainMessageBus`. This means that instead of using the `DomainMessageBusInterface` one can use directly the `MessageBusInterface`.
This change has the benefit of allowing other types of messages bus for various needs in an application.
The `DomainMessageBusInterface` now extends the `MessageBusInterface` that takes `MessageInterface` and `MessageHeaders`.
In essence all `Domain*` classes and interfaces have been made to extend the generic classes and interfaces.

The `DomainContext` concept no longer exists and was replaced by the generic `MessageBusContext`. If you have
services that depend on the `DomainContext`, they should replace their reference to the new `MessageBusContext`.
The same goes for the `DomainContextProvider` that is now the `MessageBusContextProvider`.

The `MultiDomainMessageHandlerResponse` has been replaced by the `MultiMessageHandlerResponse`. This type of response also had
its status code attribution logic changed.

Now it will compute a status code based on the most common status code from its different responses, as well as giving a veto
for the `FAILED` status code, followed by weighting other failure codes as more important than any number of success codes.

### Authorization
The Authorization was improved to allow doing authorization before a response has been returned and after.

The `supports` method has been split into two methods: `supportsPreAuthorization` and `supportsPostAuthorization`.
The `authorize` method has been split into two methods: `preAuthorize` and `postAuthorize`.

