# Short init endpoints

## IP to IDP Redirect

This endpoint can be used to retrieve the IDP login url for a visitor. This is done by determining it's IP-address.

GET: `/wp-content/endpoints/ip-to-idp-redirect.php`

### Success response:

- Status code: 200
- Content Type: `applicartion/json`

Response structure: `object`
- success (`boolean`): Determines if the request was successful
- data (`object`): Extra data
  - redirect_url (`string`): The url to redirect the user back to after logging in.
