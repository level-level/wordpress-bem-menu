# Short init endpoints

## IP to IDP Redirect

This endpoint can be used to retrieve the IDP login url for a visitor. This is done by determining it's IP-address.

GET: `/wp-content/endpoints/ip-to-idp-redirect.php`

### Success response:

- Status code: 200
- Content Type: `applicartion/json`

Response structure: `object`
- redirect_url: `string|null`
