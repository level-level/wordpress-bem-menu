# Keycloak

FDMG uses KeyCloak to authenticate all their users.
To connect with KeyCloak, we will use the OpenID Connect OAuth2 API that's available.

## Plugin

To allow authenticating in WordPress, the [OpenID Connect Generic Client plugin](https://wordpress.org/plugins/daggerhart-openid-connect-generic/) is used.
This plugin needs to be configured.

### Configuration

From FDMG we will recieve a list of information, like below:

```
Client ID: client-name-here
Client Secret Key: SomeSuperSecretKey
Issuer URL: https://some.domain.here/auth/realms/realm_name

"authorization_endpoint":"https://some.domain.here/auth/realms/realm_name/protocol/openid-connect/auth"
"token_endpoint":"https://some.domain.here/auth/realms/realm_name/protocol/openid-connect/token"
"introspection_endpoint":"https://some.domain.here/auth/realms/realm_name/protocol/openid-connect/token/introspect"
"userinfo_endpoint":"https://some.domain.here/auth/realms/realm_name/protocol/openid-connect/userinfo"
"end_session_endpoint":"https://some.domain.here/auth/realms/realm_name/protocol/openid-connect/logout"
```

These settings need to be in your wp-config file.

```php
define( 'OIDC_LOGIN_TYPE', 'button' );
// define( 'OIDC_CLIENT_ID', 'CLIENT_ID_HERE' ); // Only add on single sites.
// define( 'OIDC_CLIENT_SECRET', 'CLIENT_SECRET_KEY_HERE' ); // Only add on single sites.
define( 'OIDC_CLIENT_SCOPE', 'openid roles session offline_access' );
define( 'OIDC_ENDPOINT_LOGIN_URL', 'AUTHORIZATION_ENDPOINT_HERE' );
define( 'OIDC_ENDPOINT_USERINFO_URL', 'USERINFO_ENDPOINT_HERE' );
define( 'OIDC_ENDPOINT_TOKEN_URL', 'TOKEN_ENDPOINT_HERE' );
define( 'OIDC_ENDPOINT_LOGOUT_URL', 'LOGOUT_URL_HERE' );
define( 'OIDC_CREATE_IF_DOES_NOT_EXIST', true );
define( 'OIDC_ENFORCE_PRIVACY', false );
define( 'OIDC_LINK_EXISTING_USERS', true );
define( 'OIDC_REDIRECT_ON_LOGOUT', false );
define( 'OIDC_REDIRECT_USER_BACK', false );
define( 'OIDC_ACR_VALUES', '' );
```

These options need to be entered in the OpenID Connect Generic Client plugin as follows (some fields might be readonly, as they are set via the wp-config file):

- Client ID: Same as Client ID
- Client Secret Key: Same as Client Secret Key
- OpenID Scope: Set the value to `openid roles session offline_access`
- Login Endpoint URL: Same as authorization_endpoint
- Userinfo Endpoint URL: Same as userinfo_endpoint
- Token Validation Endpoint URL: Same as token_endpoint
- End Session Endpoint URL: Same as end_session_endpoint
- Identity key: `email`
- Nickname key: `nickname`
- Email Formatting: `{email}`
- Display Name Formatting: `{given_name} {family_name}`
- Identify with User Name: Leave unchecked as we want to match identity by e-mail
- Link Existing Users: Checked as we want to link existing accounts
- Create user if does not exist: Checked, as we want to create new users in WP
- Redirect Back to Origin Page: Leave unchecked, as this logic is handled in own code instead of the plugin.

> For local development, you might need to disable SSL verification by checking the "Disable SSL Verify" checkbox. Do not disable SSL verification on production environments.

> Some of this configuration could seem to be ignored in code, as some values are overwritten [in PHP code](./../../app/Listn/OpenIDConnect.php).

## Known errors

When logging out of the website, it could be that the webserver returns a 502 Bad Request.
And in the nginx logs, you will find errors like "upstream sent too big header while reading response header from upstream".
This can be fixed by changing the size of the fastcgi buffers.

Change the factcgi buffer sizes in the Nginx config to a larger size, for example the following:
```
fastcgi_buffers 16 16k;
fastcgi_buffer_size 32k;
```

If nginx is used as a (reverse) proxy, also change the proxy buffer sizes.
