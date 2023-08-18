# Server side tracking
For each request, FDMG would like to track their users.

## Server reads cookie data
On a webserver level, the access log is analyzed, and information from it used for server side tracking. From the access log, at least the following information is read:

- Request URL
- Value of cookie: `fdmg_user_id`
- Value of cookie: `fdmg_organisation_id`

