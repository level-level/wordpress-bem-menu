# FDMG API Client

The FDMG API is used to:
- Get a list of IP addresses that should be redirected to the IDP.


The OpenAPI spec is found in the [`spec.json`](spec.json) file in this folder.
You can use the [Swagger editor](https://editor.swagger.io/) to manage the specification file and test the service.

We have not automatically generated an API client, as this would error out due to an invalid spec.

## Information about the API

The API endpoints are made available by FDMG.
The latest version of the spec can be downloaded from [https://api.fdmg.org/private/fdmg/account/v3/api-docs](https://api.fdmg.org/private/fdmg/account/v3/api-docs).
You can also view the latest version of the specification at [https://api.fdmg.org/private/fdmg/account/swagger-ui/index.html](https://api.fdmg.org/private/fdmg/account/swagger-ui/index.html).

## Endpoints

- PROD: https://api.fdmg.org/private/fdmg/account/
- ACC: https://acc-api.fdmg.org/private/fdmg/account/
- TEST: https://test-api.fdmg.org/private/fdmg/account/

### Authentication

Authenticate using OAuth 2.0, with the ClientCredentials flow.
You can then authenticate your request, using a Bearer token.

Endpoints for authentication:

- PROD: https://login.fdmg.nl/auth/realms/services/protocol/openid-connect/token
- ACC: https://login-acc.fdmg.nl/auth/realms/services/protocol/openid-connect/token
- TEST: https://login-dev.fdmg.nl/auth/realms/services/protocol/openid-connect/token
