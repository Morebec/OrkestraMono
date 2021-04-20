# CHANGELOG

## 2.x

### 2.0
- Removed the `MessageSchedulerWorker` and `MessageSchedulerWorkerOptions` classes.
- Allowed MessageAuthorizers to preAuthorize` and `postAuthorize`.
- Renamed all references to `domainMessage` to simply `message`. 
- Moved all domain related classes and interfaces to a sub namespace `Morebec/Orkestra/Messaging/Domain`. 

## 1.x

### 1.1

- Deprecated `AbstractDomainResponse` since version 1.1, use `AbstractMessageBusResponse` instead.
- Deprecated `DomainMessageHandlerResponse` since version 1.1, use `MessageHandlerResponse` instead.
- Deprecated `DomainMessageHandlerInterface` since version 1.1, use `MessageHandlerInterface` instead.
- Deprecated `MultiDomainMessageHandlerResponse` since version 1.1, use `MultiMessageHandlerResponse` instead.
- Deprecated `DomainMessageBusMiddlewareInterface` since version 1.1, use `MessageBusMiddlewareInterface` instead.
- Deprecated `VersionedDomainMessageInterface` since version 1.1, use `VersionedMessageInterface` instead.
  
- Add `MessageHeaders` as second parameter to `NoResponseFromMiddlewareException`.
- Make `NoResponseFromMiddlewareException` accept `MessageInterface` instead of `DomainMessageInterface`.
- Added `MessageBus`.
- Added `MessageBusInterface`.
- Added `MessageHandlerResponse`.
- Added `MessageHandlerResponse`.
- Added `MultiMessageHandlerResponse`.
- Added `AbstractMessageBusResponse`.
- Made `LoggerMiddleware` implement MessageBusMiddlewareInterface` instead of `DomainMessageBusMiddlewareInterface`.
- Added `MessageBusMiddlewareInterface`.
- Added `MessageBusResponseStatusCode`.
- Added `MessageBusResponseInterface`.
- Added `MessageHeaders`.
- Added `VersionedMessageInterface`.
- Added `MessageInterface`.
- Introduction of a more generic set of interfaces for the Message Bus. The Domain Message Bus related interfaces have been made extending these generic interfaces when possible.
  However, the `DomainMessageBusInterface` cannot extend the `MessageBusInterface` for PHP7.3 compatibility. This new set of interfaces will allow different types of message buses 
  not necessarily related to the domain layer.