# Caching strategy

The FDMG Labels project is unique, because regular visitors will use the WordPress login to login to their account.
With a normal caching structure, this would result in all those users bypassing the cache.
For this project, there are special caching rules in place.

## Bypassing cache based on a different authentication cookie

For this project, the normal WordPress authentication cookie should not be used.
Instead, the `fdmg_labels_editor` cookie should be used.
This cookie is only set for editors and allows regular visitors to still get served cached pages.

For more information see [`app/FDMGLabels/Auth/UserCookies.php`](../app/FDMGLabels/Auth/UserCookies.php).
