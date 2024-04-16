# Login redirect endpoints

## IP to IDP

This endpoint can be used to retrieve the IDP login url for a visitor. This is done by determining it's IP-address.

GET: `/wp-json/fdmg-labels/v1/login-redirect/ip-to-idp`

### Success response:

- Status code: 200
- Content Type: `applicartion/json`

Response structure: `object`
- redirect_url: `string|null`
