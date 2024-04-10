# Tracking user data

For this project, customisations have been made to the [tracking of user data](https://github.com/level-level/fdmg-server-side-tracking/blob/main/docs/tracking-user-data.md).

## Extra data that is tracked

Some extra data is tracked inside the data property of the json.

- `fdmg_user_id` (int): The FDMG user ID of the currently logged in user. This is not the WordPress user ID.
- `fdmg_organisation_id` (int): The FDMG organisation ID of the currently logged in user.
- `has_subscription` (int): Determines if the currrently logged in user has a subscription. `1` == yes and `0` = no.
