As previously communicated, the Impact Investor brand has been added on ADvance test, although current there is no frontend configured for this to allow you to utilise the pages such as registration, edit user details and login. To simplify the set up required for the integration testing, it will be easier if you just use an existing brand where a frontend is already available (i.e. the 'IPE' brand). Using this should provide what is needed to build and test the integration scripts. The urls listed below assume use of this existing 'IPE' test brand.

Register:
https://adtest-ipe-ipe.abasoftaws.co.uk/register

Login:
https://adtest-ipe-ipe.abasoftaws.co.uk/login

Edit user details:
https://adtest-ipe-ipe.abasoftaws.co.uk/edituserdetails

Subscribe:
https://adtest-ipe-ipe.abasoftaws.co.uk/subscribe

All of the above links support specifying the return path via a 'returl' parameter. e.g.
https://adtest-ipe-ipe.abasoftaws.co.uk/login?returl=https%3A%2F%2Fwww.yoursite.com

Although you mentioned that you will not directly use the plugins that I showed during the call, as requested, I will supply the relevant source files so that you can review how the methods have been implemented. These will be sent via our secure email application and so will arrive via another email. I anticipate that the plugins should make it easier to understand the handling required and supplement the existing documentation which you already have reviewed. If you have any specific queries on the methods used please let me know and I'll be happy to answer these, either via email or a call where this works better.

Note: The API access tokens for ADvance test are included in the plugin file /abacus-common/abacus-shared-helpers.php

Please store these tokens securely on receipt and ensure they are not exposed in any client-side code.

In production these tokens will differ, as will the $base_api_url value.

Although I have tested the plugins as I have been building them, they have yet to go through our formal QA process. As such, if you do use any of the included methods, please ensure these pass QA when reviewing your final solution's code.

You mentioned on the call that you intend to use the ADvance frontend login page for user login. In the plugin the following two configs enable this behaviour:

    'advance_redirect_login' => true,
    'advance_login_url'      => 'https://adtest-ipe-ipe.abasoftaws.co.uk/login',

Be aware that the ADvance frontend login sets the ADFEAuthCookie with a session expiry. See the use of "$this->set_auth_cookie($user->user_login, $user);" in 'maybe_handle_advance_login()' where this cookie's expiry is extended. The expiry of ADFEAuthCookie should match the expiry of the wp login session so that both the wp and ADvance frontends share the same expiry time. ADFEAuthCookie must be removed at wp logout to ensure states remain consistent.

For the test site implementation you will need to manually create the ADFEAuthCookie on the wp site's domain after a return from ADvance frontend login. This is necessary since the cookie set on the ADvance test domain will not be available on the wp test site's domain (as these will differ). In production this will not a problem due to sharing the same root domain (ADvance will be on account.impactinvestor.com - or whatever the intended site domain will be - please confirm the intended wp url so that we can ensure we match in our production frontend set up). In the plugin example there are checks for the presence of the ADFEAuthCookie, firing an autologin where present, with cookie's value validated via the ADvance API /Login/AutoLogin/1 endpoint.
