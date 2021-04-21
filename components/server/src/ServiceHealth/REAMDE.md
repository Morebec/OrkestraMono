# Service Health Module
This module contains all the code and logic regarding checking the health of registered services.

## Concepts
- **Service**: Represents an application, system, service that was registered and is now considered by Orkestra Server. In this module
  it allows it to be able to benefit from Service Health.
- **Service Check**: In order to determine the health of a service some checks must be performed. They are known as service checks. These checks are HTTP calls
  to the service's API in order to test multiple things such as availability, database connections, third party service connections, software versions etc.
- **Service Check Definition**: Represents the configuration of a service check, to allow Services do define freely how to they want to be checked. They are essentially a description
  of how a given Service Check should be performed and under what conditions.
- **Health Check**: A Health check is an actual execution of a Check. therefore `Service Check !== Health Check`. One of the reasons for this distinction is that a service check in its definition
can require multiple consecutive Health Checks to have a "healthy" status in order for the Service Check to be considered healthy. For example, "mark this service check as healhty only when three health checks API Calls have been successful."
  