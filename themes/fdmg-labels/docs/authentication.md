# Authentication

In this project, visitors have the the ability to login in the website (if KeyCloak is enabled).
By doing so they get a few benefits.
First of all, if they have a subscription, they can view content behind the paywall (if paywall is enabled).
Second, they can place comments below posts (if this is enabled).

## KeyCloak

FDMG uses the KeyCloak system for authenticating users.
KeyCloak also provides us with information about the users subscription.
Using this information, directly after logging in, we update the user info and their user roles in WordPress.

For more info about how KeyCloak can be set up, visit the [KeyCloak documentation](./connections/keycloak.md).

## Custom login URL

Visitors who visit the website, are able to login by visiting `https://{{SITE_DOMAIN}}/login/`.
This is needed because the login URL from KeyCloak is different for every pagevisit.
And unique urls for every page visit cannot be used, because of caching.

The `https://{{SITE_DOMAIN}}/login/` url, when visited, can generates a unique KeyCloak login url, and redirect the visitor to it.

All URL parameters passed to the `https://{{SITE_DOMAIN}}/login/` page (except some that would conflict with KeyCloak) are passed through to the KeyCloak login url.

## Redirecting back after login

When a user logs in, you want to redirect the user back to the page they came from.
You may do so by adding `?redirect_to={{PAGE_TO_REDIRECT_BACK_TO_AFTER_LOGIN}}` to the login URL.

## IP based login redirect

FDMG has some Business clients.
Employees of these businesses are able to view paid articles in the website, and their company pays for it.
Payments are made based on the amount of articles the employees have read each month.

To make this effective, users working at those companies should be redirected to the login page of KeyCloak when they visit the website, and are not logged in yet.
And not just the login page of KeyCloak, but they should be redirected directly to their Identity Provider (Google, Microsoft, etc.) of choice.

That's why, on first page load, [via JavaScript placed in the head](./../src/scripts/head.ts), we perform a request to a [short-init endpoint](./../../../endpoints/ip-to-idp-redirect.php) to check the visitors IP-address.
We compare that IP address to a list of IP address ranges and their connected Identity provider.
This list is retrieved from the FDMG API. To setup, please visit the [FDMG API documentation](./connections/fdmg-api/readme.md).

If the visitor IP is in the list of IP address ranges, the request should return our custom login URL, with the following URL parameters added:
- `kc_idp_hint={{IDENTITY_PROVIDER}}`
- `method=ipBasedRedirect`

This would result in an URL like so:
`https://{{SITE_DOMAIN}}/login/?redirect_to={{PAGE_TO_REDIRECT_BACK_TO_AFTER_LOGIN}}&kc_idp_hint={{IDENTITY_PROVIDER}}&method=ipBasedRedirect`

JavaScript is then responsible for redirecting the visitor.
