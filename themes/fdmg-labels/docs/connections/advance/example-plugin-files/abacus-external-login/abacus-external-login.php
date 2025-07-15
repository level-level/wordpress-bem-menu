<?php
/*
Plugin Name: Abacus External Login
Description: Authenticate WordPress users against Abacus external API, handle grace checks, set ADFEAuthCookie, override profile access, sync profile, redirect after login, and force username sync.
Version: 0.9
Author: Your Name

make sure get_client_ip() is able to retrieve the user external ip

*/

require_once WP_PLUGIN_DIR . '/abacus-common/abacus-shared-helpers.php';


// Default fallback config values
$advance_external_login_defaults = [
    //WARNING - Changing these config items after initial implementation would require a bulk update of existing usernames values in WP.
    //CHANGING EXISTING WP USERNAMES MAY AFFECT OTHER INTEGRATIONS - REQUIRES REVIEW IF USED
    'advance_username_format_mode' => 'partyid_with_prefix', //'partyid_with_prefix' = WP usernames are set with $advance_partyid_based_wp_username_prefix . $partyId. 'advance_username' = WP usernames are set as per the ADvance username value.
    'advance_partyid_based_wp_username_prefix' => 'advance_impactinvestor_', //used where 'advance_username_format_mode' = 'partyid_with_prefix'. The value of this config is prefixed to the ADvance user's partyId value before being set in the WP username. Allows namespacing usernames in WP.
    'advance_allow_wp_username_update' => false, //true = WP usernames are always updated to the format specified in 'advance_username_format_mode'. false = WP usernames are only set if a new WP user is created at login.
    //WARNING

    'advance_auth_cookie_days' => 30,
    'advance_auth_cookie_extend_on_autologin' => true, //determines if ADvance auth cookie is extended when it is used to re-authenticate a user via AutoLogin.

    'advance_brand_id' => '1', //defines the ADvance brand id used for the WP site integration. This will be supplied by Abacus.
    'advance_ad_wpid_additionalcode' => 'impact_investor_wpid', //defines the field in ADvance where the WP user is stored. This will be supplied by Abacus.
    
    //if set true wp /login will be overriden to use ADvance frontend login.
    'advance_redirect_login' => false,
    'advance_login_url'      => 'https://adtest-ipe-ipe.abasoftaws.co.uk/login',
    
    'advance_register_url' => 'https://adtest-ipe-ipe.abasoftaws.co.uk/register', //defines the URL to be used for WP register.
    'advance_forgot_password_url' => 'https://adtest-ipe-ipe.abasoftaws.co.uk/forgottenpassword', //defines the URL to be used for WP forgotten password.
    'advance_external_profile_url' => 'https://adtest-ipe-ipe.abasoftaws.co.uk/edituserdetails', //defines the URL to be used for editing the user profile.
    
    'advance_profile_sync_frequency' => 20, //specified in minutes. defines how long since the last profile data retrieval from ADvance a subsequent refresh is to be applied to the WP logged in user. This refresh occurs on next WP page load after the time is exceeded.
    
    'advance_enable_concurrency_check' => false, //determines if ADvance concurrency checks are to be applied at WP login.
    'advance_enable_activation_check' => true, //determines if ADvance email activation checks are to be applied at WP login. If enabled and a user is not activate, the WP login will be rejected with an 'activation required' message, including a link to resend the activation email to the user.
    
    'advance_login_activity_action_name' => 'Login', //determines the activity recorded in ADvance when a login occurs. This will be supplied by Abacus.
    'advance_login_activity_item_type' => 'Website', //determines the activity recorded in ADvance when a auto-login occurs. This will be supplied by Abacus.
    'advance_autologin_activity_action_name' => 'AutoLogin', //determines the activity recorded in ADvance when a auto-login occurs. This will be supplied by Abacus.
    'advance_autologin_activity_item_type' => 'Website', //determines the activity recorded in ADvance when a auto-login occurs. This will be supplied by Abacus.
    
    'advance_product_metadataUpdate_enabled' => true, //controls if products are requested from ADvance and recorded in user metadata ('advance_products') at profile update
    'advance_product_access_map' => [ //controls which products are checked in ADvance via the ADvance product URI. [value stored in WP meta 'advance_products' array] => [ADvance product URI]
        'ImpactInvestorOnline' => '/accesscheck/ImpactInvestorOnline',
    ],

    //match conditions applied at login. The match cases are applied as per the config order, with first match case selected. If all enabled match cases fail a new WP user is created, formatted as per the 'advance_username_format_mode' config.
    'advance_user_match_by_party_id' => true, //match ADvance partyId -> WP usermeta 'advance_partyid'. Recommended to be enabled; this directly links the ADvance user to the WP user by a unique id, linked at first login.
    'advance_user_match_by_username_direct' => false, //match ADvance username -> WP username. Should be set as true where 'advance_username_format_mode' = 'advance_username'.
    'advance_user_match_by_partyid_to_username' => true, //match ADvance namespaced partyId -> WP username (ADvance namespaced partyId = $advance_partyid_based_wp_username_prefix . $partyId). Should be set as true where 'advance_username_format_mode' = 'partyid_with_prefix'.
    'advance_user_match_by_email' => true, //match ADvance username -> WP user email
];



class Advance_External_Login {

    public function __construct() {
        global $advance_external_login_defaults;
        
        $this->advance_auth_cookie_days = Advance_Shared_Helpers::get_config('advance_auth_cookie_days', 'abacus-access-control', 'advance_auth_cookie_days', $advance_external_login_defaults);
        $this->advance_auth_cookie_extend_on_autologin = filter_var(Advance_Shared_Helpers::get_config('advance_auth_cookie_extend_on_autologin', 'abacus-external-login', 'advance_auth_cookie_extend_on_autologin', $advance_external_login_defaults), FILTER_VALIDATE_BOOLEAN);
        
        $this->advance_brand_id = Advance_Shared_Helpers::get_config('advance_brand_id', 'abacus-access-control', 'advance_brand_id', $advance_external_login_defaults);
        $this->api_version = '1';
        
        $this->advance_product_access_map = Advance_Shared_Helpers::get_config('advance_product_access_map', 'abacus-external-login', 'advance_product_access_map', $advance_external_login_defaults);

        $this->advance_allow_wp_username_update = filter_var(Advance_Shared_Helpers::get_config('advance_allow_wp_username_update', 'abacus-external-login', 'advance_allow_wp_username_update', $advance_external_login_defaults), FILTER_VALIDATE_BOOLEAN);
        $this->advance_username_format_mode = Advance_Shared_Helpers::get_config('advance_username_format_mode', 'abacus-external-login', 'advance_username_format_mode', $advance_external_login_defaults);

        $this->advance_partyid_based_wp_username_prefix = Advance_Shared_Helpers::get_config('advance_partyid_based_wp_username_prefix', 'abacus-external-login', 'advance_partyid_based_wp_username_prefix', $advance_external_login_defaults);
        
        $this->advance_ad_wpid_additionalcode = Advance_Shared_Helpers::get_config('advance_ad_wpid_additionalcode', 'abacus-external-login', 'advance_ad_wpid_additionalcode', $advance_external_login_defaults);

        $this->advance_auth_cookie_extend_on_autologin = filter_var(Advance_Shared_Helpers::get_config('advance_auth_cookie_extend_on_autologin', 'abacus-external-login', 'advance_auth_cookie_extend_on_autologin', $advance_external_login_defaults),FILTER_VALIDATE_BOOLEAN);
        $this->advance_register_url = Advance_Shared_Helpers::get_config('advance_register_url', 'abacus-external-login', 'advance_register_url', $advance_external_login_defaults);
        $this->advance_forgot_password_url = Advance_Shared_Helpers::get_config('advance_forgot_password_url', 'abacus-external-login', 'advance_forgot_password_url', $advance_external_login_defaults);
        $this->advance_external_profile_url = Advance_Shared_Helpers::get_config('advance_external_profile_url', 'abacus-external-login', 'advance_external_profile_url', $advance_external_login_defaults);
        $this->advance_profile_sync_frequency = intval(Advance_Shared_Helpers::get_config('advance_profile_sync_frequency', 'abacus-external-login', 'advance_profile_sync_frequency', $advance_external_login_defaults));

        $this->advance_enable_activation_check = filter_var(Advance_Shared_Helpers::get_config('advance_enable_activation_check', 'abacus-external-login', 'advance_enable_activation_check', $advance_external_login_defaults), FILTER_VALIDATE_BOOLEAN);

        $this->advance_redirect_login = filter_var(Advance_Shared_Helpers::get_config('advance_redirect_login', 'abacus-external-login', 'advance_redirect_login', $advance_external_login_defaults),FILTER_VALIDATE_BOOLEAN);
        $this->advance_login_url = Advance_Shared_Helpers::get_config('advance_login_url', 'abacus-external-login', 'advance_login_url', $advance_external_login_defaults);

        $this->advance_login_activity_action_name = Advance_Shared_Helpers::get_config('advance_login_activity_action_name', 'abacus-external-login', 'advance_login_activity_action_name', $advance_external_login_defaults);
        $this->advance_login_activity_item_type = Advance_Shared_Helpers::get_config('advance_login_activity_item_type', 'abacus-external-login', 'advance_login_activity_item_type', $advance_external_login_defaults);
        $this->advance_autologin_activity_action_name = Advance_Shared_Helpers::get_config('advance_autologin_activity_action_name', 'abacus-external-login', 'advance_autologin_activity_action_name', $advance_external_login_defaults);
        $this->advance_autologin_activity_item_type = Advance_Shared_Helpers::get_config('advance_autologin_activity_item_type', 'abacus-external-login', 'advance_autologin_activity_item_type', $advance_external_login_defaults);

        $this->advance_product_metadataUpdate_enabled = filter_var(Advance_Shared_Helpers::get_config('advance_product_metadataUpdate_enabled', 'abacus-external-login', 'advance_product_metadataUpdate_enabled', $advance_external_login_defaults), FILTER_VALIDATE_BOOLEAN);

        $this->advance_product_access_map = Advance_Shared_Helpers::get_config('advance_product_access_map', 'abacus-external-login', 'advance_product_access_map', $advance_external_login_defaults);

        $this->advance_user_match_by_party_id = filter_var(Advance_Shared_Helpers::get_config('advance_user_match_by_party_id', 'abacus-external-login', 'advance_user_match_by_party_id', $advance_external_login_defaults), FILTER_VALIDATE_BOOLEAN);
        $this->advance_user_match_by_username_direct = filter_var(Advance_Shared_Helpers::get_config('advance_user_match_by_username_direct', 'abacus-external-login', 'advance_user_match_by_username_direct', $advance_external_login_defaults), FILTER_VALIDATE_BOOLEAN);
        $this->advance_user_match_by_partyid_to_username = filter_var(Advance_Shared_Helpers::get_config('advance_user_match_by_partyid_to_username', 'abacus-external-login', 'advance_user_match_by_partyid_to_username', $advance_external_login_defaults), FILTER_VALIDATE_BOOLEAN);
        $this->advance_user_match_by_email = filter_var(Advance_Shared_Helpers::get_config('advance_user_match_by_email', 'abacus-external-login', 'advance_user_match_by_email', $advance_external_login_defaults), FILTER_VALIDATE_BOOLEAN);

        $this->advance_login_activity_item_title = get_bloginfo('name') . ' Frontend Login';
        $this->advance_autologin_activity_item_title = get_bloginfo('name') . ' Frontend AutoLogin';

        add_filter('authenticate', [$this, 'authenticate_user'], 20, 3);
        add_action('wp_login', [$this, 'set_auth_cookie'], 10, 2);
        add_action('wp_logout', [$this, 'clear_auth_cookie']);
        add_filter('register_url', [$this, 'custom_advance_register_url']);
        add_filter('lostpassword_url', [$this, 'custom_advance_forgot_password_url']);
        add_filter('login_redirect', [$this, 'redirect_after_login'], 10, 3);
        add_filter('logout_redirect', [$this, 'redirect_after_logout'], 10, 3);
        add_action('admin_init', [$this, 'redirect_non_admin_profile_page']);
        add_action('init', [$this, 'maybe_auto_login_and_logout'], 0);
        add_action('init', [$this, 'set_adconctrl_cookie']);
        add_action('template_redirect', [$this, 'cleanup_profile_sync_url']);
        add_action('init', [$this, 'maybe_resend_activation']);
        add_action('login_message', [$this, 'show_activation_resent_message']);
        add_action('init', [$this, 'maybe_run_advance_sync'], 1);
        add_filter('login_url', [$this, 'maybe_override_login_url'], 10, 2);
        add_action('template_redirect', [$this, 'maybe_handle_advance_login']);
        
        //!!!!!!!! DEBUG
        add_action('wp_footer', [$this, 'output_user_advance_products_debug']);
        
    }
    
    private function is_user_activated($details): bool {
        if (!$this->advance_enable_activation_check) return true;
    
        if (strtolower($details['Activated'] ?? '') === 'true') return true;
    
        $grace = filter_var($details['ActivationGraceActive'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $grace_end = isset($details['ActivationGraceEnd']) ? strtotime($details['ActivationGraceEnd']) : 0;
        
        return $grace && time() <= $grace_end;
    }


    public function authenticate_user($user, $username, $password) {
        if (empty($username) || empty($password)) {
            if (!isset($_POST['log']) && !isset($_POST['pwd'])) {
                return $user;
            }
            return new WP_Error('empty_credentials', __('Please enter username and password.'));
        }

        if ($user instanceof WP_User) return $user;

        // Check if user exists AND is admin (allow WP login only for admin users with exact match)
        $existing_user = get_user_by('login', $username);
        if (
            $existing_user &&
            user_can($existing_user, 'manage_options') &&
            wp_check_password($password, $existing_user->user_pass, $existing_user->ID)
        ) {
            return $existing_user;
        }

        //Set the ADvance concurrency cookie to be used in ADvance Login/AutoLogin/PasswordlessLogin method.
        $cookie = $_COOKIE['ADConCtrlCookie'] ?? null;
        if (!$cookie) {
            return new WP_Error('missing_cookie', __('Login failed due to missing concurrency token.'));
        }

        $body = [
            'Username' => $username,
            'Password' => $password,
            'concurrencyCookie' => $cookie,
            'activityActionName' => $this->advance_login_activity_action_name,
            'activityItemType' => $this->advance_login_activity_item_type,
            'activityItemTitle' => $this->advance_login_activity_item_title,
        ];
        $response = Advance_Shared_Helpers::call_advance_api('/Login/Login/' . $this->api_version, $body);
        if (is_wp_error($response)) return $response;


        if (!empty($response['response']['status']['code']) && $response['response']['status']['code'] === 'ERROR') {
            foreach ($response['response']['status']['messages'] as $msg) {
                if (
                    stripos($msg['msg'] ?? '', 'Maximum number of cookies exceeded') !== false &&
                    $this->advance_enable_concurrency_check
                ) {
                    return new WP_Error('concurrent_logins_exceeded', __('You have exceeded the maximum number of concurrent logins.'));
                }
            }
        }

        $details = $response['response']['data']['LoginDetails'] ?? null;
        if (!$details || empty($details['UserToken'])) {
            return new WP_Error('invalid_login', __('Invalid username or password.'));
        }

        //Apply check for email activation (verification via link click in email), including 'grace' period logic if represented in the Abacus response, messaging and resend activation email functionality)
        if ($this->advance_enable_activation_check && strtolower($details['Activated']) !== 'true') {
            $grace = filter_var($details['ActivationGraceActive'], FILTER_VALIDATE_BOOLEAN);
            $graceEnd = isset($details['ActivationGraceEnd']) ? strtotime($details['ActivationGraceEnd']) : 0;
            if (!$grace || time() > $graceEnd) {
                $resend_url = wp_nonce_url(add_query_arg('resend_activation', rawurlencode($details['UserToken']), wp_login_url()), 'resend_activation');
                return new WP_Error('account_inactive', __('Your account is not activated.') . ' <a href="' . esc_url($resend_url) . '">Resend activation email</a>');
            }
        }

        return $this->create_or_update_user($details);
        
        
    }

    public function maybe_resend_activation() {
        if (!isset($_GET['resend_activation']) || !wp_verify_nonce($_GET['_wpnonce'], 'resend_activation')) return;
        $token = sanitize_text_field($_GET['resend_activation']);
        if (!$token) return;

        $body = ['UserToken' => $token];
        $response = Advance_Shared_Helpers::call_advance_api('/User/SendRegistrationActivationEmail/' . $this->api_version, $body);
        $status = (!is_wp_error($response) && $response['response']['status']['code'] === 'OK') ? '1' : '0';
        wp_redirect(add_query_arg('activation_resent', $status, wp_login_url()));
        exit;
    }

    public function show_activation_resent_message($message) {
        if (isset($_GET['activation_resent'])) {
            $msg = $_GET['activation_resent'] === '1'
                ? __('Activation email resent successfully.')
                : __('Failed to resend activation email.');
            $message .= '<p class="message">' . esc_html($msg) . '</p>';
        }
        
        if (isset($_GET['activation_required'])) {
            $message .= '<p class="message">' . __('Your email address is no longer validated due to being updated. Please check your email for a link to validate this update.') . '</p>';
        }
        return $message;
    }

       public function maybe_override_login_url($login_url, $redirect) {
           if (!$this->advance_redirect_login || empty($this->advance_login_url)) {
               return $login_url;
           }
    
           // 1) Use the WP-standard redirect_to param if set
           if (!empty($_REQUEST['redirect_to'])) {
               $returl = $_REQUEST['redirect_to'];
           }
           // 2) Fall back to wp_get_referer()
           elseif ($referer = wp_get_referer()) {
               $returl = $referer;
           }
           // 3) Last resort, home page
           else {
               $returl = home_url('/');
           }
    
           // Add our advance_login flag
            $returl = add_query_arg('advance_login', '1', $returl);
            $returl = rawurlencode( $returl );
            $external = add_query_arg( 'returl', $returl, $this->advance_login_url );
            wp_redirect($external);
           
       }
    
    //used to handle the return path after ADvance login if wp login override is redirecting to ADvance login ($advance_redirect_login=true)
    public function maybe_handle_advance_login() {
        if (!isset($_GET['advance_login']) || $_GET['advance_login'] !== '1') return;
    
        $user = $this->maybe_auto_login_and_logout();
    
        if ($user instanceof WP_User && $this->advance_auth_cookie_extend_on_autologin) {
            $this->set_auth_cookie($user->user_login, $user);
        }
    
            // Strip advance_login=1 from URL
            $clean_url = remove_query_arg('advance_login');
            wp_safe_redirect($clean_url);
            exit;
    }

    public function maybe_auto_login_and_logout() {
        //Try to fetch the ADvance authentication cookie
        $auth_cookie = $_COOKIE['ADFEAuthCookie'] ?? null;
        
        //For logged in (non-wp admin) users, if the user is logged in at wp but has no ADvance auth cookie, then logout of wp
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $has_token = get_user_meta($user->ID, 'advance_usertoken', true);
            $is_admin = user_can($user, 'manage_options');
            if ($has_token && !$is_admin && !$auth_cookie) wp_logout();
            return;
        }

        if (!$auth_cookie) return;

        //set the ADvance concurrency cookie if not already present
        $cookie = $_COOKIE['ADConCtrlCookie'] ?? null;
        if (!$cookie) {
            $this->set_adconctrl_cookie();
            $cookie = $_COOKIE['ADConCtrlCookie'] ?? null;
        }

        //Call the ADvance AutoLogin method to autenticate and return a UserToken for use in subsequent API requests
        $body = [
            'frontendCookie' => $auth_cookie,
            'concurrencyCookie' => $cookie,
            'ipAddress' => Advance_Shared_Helpers::get_client_ip(),
            'activityActionName' => $this->advance_autologin_activity_action_name,
            'activityItemType' => $this->advance_autologin_activity_item_type,
            'activityItemTitle' => $this->advance_autologin_activity_item_title,
        ];
        $response = Advance_Shared_Helpers::call_advance_api('/Login/AutoLogin/' . $this->api_version, $body);
        if (is_wp_error($response)) return;
        $details = $response['response']['data']['LoginDetails'] ?? null;
        if (!$details || empty($details['UserToken'])) return;

        //call the create_or_update_user method if a user authentication response was successfully retrieved from ADvance.
        $user = $this->create_or_update_user($details);
        
        // Ensure new token is stored after autologin (if not already handled)
        if ($user instanceof WP_User && !empty($details['UserToken'])) {
            update_user_meta($user->ID, 'advance_usertoken', $details['UserToken']);
        }

        if ($user instanceof WP_User) {
            // Ensures WP sets login cookies like wordpress_logged_in_
            wp_set_current_user($user->ID); // Optional: ensure current user context
            wp_set_auth_cookie($user->ID, true); // Enables WP session
        
            //need to extend session ADvance auth cookie if set by ADvance login frontend (as it is set session only)
            $justLoggedInToADvanceViaRedirect = $_GET['advance_login'];
        
            if ($this->advance_auth_cookie_extend_on_autologin || $justLoggedInToADvanceViaRedirect ) {
                $this->set_auth_cookie($user->user_login, $user); // Extend ADvance auth cookie
            }
        }
    }

    public function set_auth_cookie($user_login, $user) {
        $auth_cookie = get_user_meta($user->ID, 'advance_ad_auth_cookie', true);
        if (!$auth_cookie) return;
        $domain = Advance_Shared_Helpers::get_wp_root_domain($_SERVER['HTTP_HOST']);
        //setcookie('ADFEAuthCookie', $auth_cookie, 0, '/', $domain, is_ssl(), true);
        setcookie('ADFEAuthCookie', $auth_cookie, time() + ($this->advance_auth_cookie_days * DAY_IN_SECONDS), '/', $domain, is_ssl(), true);
    }

    public function clear_auth_cookie() {
        $domain = Advance_Shared_Helpers::get_wp_root_domain($_SERVER['HTTP_HOST']);
        setcookie('ADFEAuthCookie', '', time() - 3600, '/', $domain, is_ssl(), true);
    }

    public function set_adconctrl_cookie() {
        if (empty($_COOKIE['ADConCtrlCookie'])) {
            $uuid = wp_generate_uuid4();
            $domain = Advance_Shared_Helpers::get_wp_root_domain($_SERVER['HTTP_HOST']);
            setcookie('ADConCtrlCookie', $uuid, time() + YEAR_IN_SECONDS, '/', $domain, is_ssl(), true);
            $_COOKIE['ADConCtrlCookie'] = $uuid;
        }
    }

    public function redirect_after_login($redirect_to, $request, $user) {
        return !is_wp_error($user) ? home_url('/') : $redirect_to;
    }

    public function redirect_after_logout($redirect_to, $requested_redirect_to, $user) {
        return home_url();
    }

    public function redirect_non_admin_profile_page() {
        global $pagenow;
        if ($pagenow === 'profile.php' && !current_user_can('manage_options')) {
            // Try to get referring page, fallback to home
            $referer = wp_get_referer() ?: home_url();
            $returl = add_query_arg('advance_sync', '1', $referer);
            $returl = rawurlencode( $returl );
            $external = add_query_arg( 'returl', $returl, $this->advance_external_profile_url );
            wp_redirect($external);
            exit;
        }
    }

    private function fetch_activation_status($party_id) {
        if (!$party_id) return null;
        
        //This method is used due to the absence of a standalone 'requestUserActivationStatus' endpoint at Abacus, and so is retrieved instead via '/Login/PasswordlessLogin/1' where it is available.
        $response = Advance_Shared_Helpers::call_advance_api('/Login/PasswordlessLogin/' . $this->api_version, [
            'partyId' => (string) $party_id
        ]);
    
        if (is_wp_error($response)) {
            error_log('[Abacus] Activation check error: ' . $response->get_error_message());
            return null;
        }
    
        return $response['response']['data']['LoginDetails'] ?? [];
    }


    
    private function fetch_advance_profile($token, $codes = []) {
        $body = ['UserToken' => $token];
        if (!empty($codes)) $body['AdditionalCodeList'] = implode(',', $codes);
        $result = Advance_Shared_Helpers::call_advance_api('/User/SimpleUserDetails/' . $this->api_version, $body);
        return $result['response']['data']['PartyDetails'] ?? null;
    }

    //used to retrieve product entitlements from ADvance for a user, storing these in wp user_metadata: 'advance_products'
    private function sync_user_product_access($user_id, $token) {
        if (!$this->advance_product_metadataUpdate_enabled || empty($this->advance_product_access_map)) return;

        $products = [];
        foreach ($this->advance_product_access_map as $label => $url) {
            
            error_log("[Abacus AccessAllowed] Product: $label | IP: " . (Advance_Shared_Helpers::get_client_ip() ?? '0.0.0.0'));
            //error_log("[Abacus AccessAllowed] response: " . ($response['response']['data']['GKResult']['AccessDetails']['AccessAllowed'] ?? 'none'));
            
            //error_log('Client IP: ' . $_SERVER['REMOTE_ADDR']);
            
            $response = Advance_Shared_Helpers::call_advance_api('/gatekeeper/AccessAllowed/' . $this->api_version, [
                'UserToken' => $token,
                'BrandId' => $this->advance_brand_id,
                'Url' => $url,
                'ipAddress' => Advance_Shared_Helpers::get_client_ip(),
            ]);

            if (
                !is_wp_error($response) &&
                isset($response['response']['data']['GKResult']['AccessDetails']['AccessAllowed']) &&
                strtolower($response['response']['data']['GKResult']['AccessDetails']['AccessAllowed']) === 'true'
            ) {
                $products[] = $label;
            }
        }

        update_user_meta($user_id, 'advance_products', $products);
    }

    private function update_wp_user_profile_from_abacus($user_id, $data, $update_token = false) {
        //fix for party id inconsistency in casing between ADvance API methods
        if (isset($data['PartyId']) && !isset($data['PartyID'])) {
            $data['PartyID'] = $data['PartyId'];
        }
        
        wp_update_user([
            'ID' => $user_id,
            'user_email' => $data['Email'] ?? $data['Username'],
            'first_name' => $data['FirstName'],
            'last_name' => $data['Surname'],
            'display_name' => trim($data['FirstName'] . ' ' . $data['Surname']),
            'user_nicename' => sanitize_title($data['Username']),
        ]);

        update_user_meta($user_id, 'advance_first_name', $data['FirstName']);
        update_user_meta($user_id, 'advance_surname', $data['Surname']);
        update_user_meta($user_id, 'advance_username', $data['Username']);

        //update the WP username if enabled in config
        if ($this->advance_allow_wp_username_update) {
            $this->force_update_user_login($user_id, $data['PartyID']);
        }

        if ($update_token && !empty($data['UserToken'])) {
            update_user_meta($user_id, 'advance_usertoken', $data['UserToken']);
        }

        if (!empty($data['FrontendCookieValue'])) {
            update_user_meta($user_id, 'advance_ad_auth_cookie', $data['FrontendCookieValue']);
        }
        
        if (!empty($data['PartyID'])) {
            update_user_meta($user_id, 'advance_partyid', $data['PartyID']);
        }

        update_user_meta($user_id, 'advance_last_profile_sync', time());
        $this->refresh_current_user($user_id);
    }

    //if config 'advance_allow_wp_username_update' = true, perform a DIRECT SQL DB UPDATE TO WP USERNAME
    private function force_update_user_login($user_id, $advance_party_id) {
        global $wpdb;
        if (!$advance_party_id || !$user_id) return;

        $options = get_option('advance_integration')['abacus-external-login'] ?? [];
        
        if ($this->advance_username_format_mode === 'partyid_with_prefix' && $advance_party_id) {
            $new_login = $this->advance_partyid_based_wp_username_prefix . strtolower(sanitize_user($advance_party_id));
        } else {
            $advance_username = get_user_meta($user_id, 'advance_username', true);
            $new_login = strtolower(sanitize_user($advance_username));
        }

        $current = get_userdata($user_id);
        if ($current && $current->user_login === $new_login) return;

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM $wpdb->users WHERE user_login = %s AND ID != %d",
            $new_login,
            $user_id
        ));
        if ($exists) return;

        //!!!!!!!!!!!!!!!!! DIRECT DB UPDATE OF WP USERNAME !!!!!!!!!!!!!!!!!!!!!!
        //This only happens within force_update_user_login(), with this function being called only where the config 'advance_allow_wp_username_update' = true
        $wpdb->update(
            $wpdb->users,
            ['user_login' => $new_login],
            ['ID' => $user_id],
            ['%s'],
            ['%d']
        );
        
        //function to force clear the wp user cache so that the username is reflected on the wp frontend after update
        clean_user_cache($user_id);
    }

    private function refresh_current_user($user_id) {
        wp_set_current_user(0);
        wp_set_current_user($user_id);
        $GLOBALS['current_user'] = get_user_by('ID', $user_id);
    }

    public function cleanup_profile_sync_url() {
        if (isset($_GET['advance_sync'])) {
            wp_safe_redirect(remove_query_arg('advance_sync'));
            exit;
        }
    }

   //called after ADvance authentication has been achieved to create/update the wp user
   private function create_or_update_user($details) {
       
       if (isset($details['PartyId']) && !isset($details['PartyID'])) {
            $details['PartyID'] = $details['PartyId'];
        }
       
        $advance_username = $details['Username'];
        $advance_party_id = $details['PartyID'] ?? '';
        
        if ($this->advance_username_format_mode === 'partyid_with_prefix' && !empty($advance_party_id)) {
            $namespaced_login = $this->advance_partyid_based_wp_username_prefix . strtolower(sanitize_user($advance_party_id));
        } else {
            $namespaced_login = strtolower(sanitize_user($advance_username));
        }

        $user = null;

        // 1. Try match by advance_party_id (Party ID)
        if ($this->advance_user_match_by_party_id && !empty($advance_party_id)) {
            $users = get_users([
                'meta_key' => 'advance_partyid',
                'meta_value' => $advance_party_id,
                'number' => 1,
                'count_total' => false,
            ]);
            if (!empty($users)) {
                $user = $users[0];
                error_log("[Abacus Login] Matched by meta advance_partyid: {$advance_party_id}");
            }
        }

        // 2a. Match by Abacus Username to WP Username
        if (!$user && $this->advance_user_match_by_username_direct) {
            $user = get_user_by('login', strtolower(sanitize_user($advance_username)));
            
            if ($user) {
                error_log("[Abacus Login] Matched by username_direct: {$candidate}");
            }
        }
        
        // 2b. Match by PartyID + Prefix to WP Username
        if (!$user && $this->advance_user_match_by_partyid_to_username && !empty($advance_party_id)) {
            $login_candidate = $this->advance_partyid_based_wp_username_prefix . strtolower(sanitize_user($advance_party_id));
            $user = get_user_by('login', $login_candidate);
            
            if ($user) {
                error_log("[Abacus Login] Matched by partyid_with_prefix: {$login_candidate}");
            }
        }


        // 3. Match Abacus Username to WP email
        if (!$user && $this->advance_user_match_by_email && is_email($advance_username)) {
            $user = get_user_by('email', $advance_username);
            
            if ($user) {
                error_log("[Abacus Login] Matched by email: {$advance_username}");
            }
        }

        //if no user match is achieved, create a new wp user
        if (!$user) {
            $email = is_email($advance_username) ? $advance_username : $advance_username . '@example.com';
            $user_id = wp_create_user($namespaced_login, wp_generate_password(), $email);
            if (is_wp_error($user_id)) {
                error_log('[Abacus] WP user creation failed: ' . $user_id->get_error_message());
                return null;
            }
            $user = get_user_by('ID', $user_id);
            update_user_meta($user->ID, 'advance_username', $advance_username);
            error_log("[Abacus Login] Created new user: login={$namespaced_login}, email={$email}");
        }
    
        //record the wp user id on the ADvance party user
        $form_data = [
            'userToken' => $details['UserToken'],
            'AdditionalCodeTypeName' => $this->advance_ad_wpid_additionalcode,
            'Code' => (string) $user->ID,
            'ForbidAddingDuplicateValue' => 'true', //new value will replace and old wp id stored on the ADvance party
            'RecordStatus' => 'Live',
        ];
        $update_response = Advance_Shared_Helpers::call_advance_api(
            '/User/AddAdditonalCodeForParty/' . $this->api_version,
            $form_data,
            'POST'
        );
        
        if (is_wp_error($update_response)) {
            error_log('[Abacus] AddAdditonalCodeForParty WP_Error: ' . $update_response->get_error_message());
        } else {
            //error_log('[Abacus] AddAdditonalCodeForParty response: ' . print_r($update_response, true));
        }
    
        if (is_wp_error($update_response)) {
            error_log('[Abacus] UpdateAdditionalCodes WP_Error: ' . $update_response->get_error_message());
        } else {
            //error_log('[Abacus] UpdateAdditionalCodes response: ' . print_r($update_response, true));
        }
    
        //apply the user profile retrieved from ADvance to the WP user
        $this->update_wp_user_profile_from_abacus($user->ID, $details, true);
        
        //fetch ADvance product entitlements if 'advance_product_metadataUpdate_enabled' = true
        if ($this->advance_product_metadataUpdate_enabled) {
            $this->sync_user_product_access($user->ID, $details['UserToken']);
        }
        
        return $user;
    }

    //called on init. Checks to see if the last sync has exceeded the 'advance_profile_sync_frequency' config OR user has returned from an external profile edit page with GET parameter 'advance_sync=1'. If so, calls run_advance_sync() to update the user and profile.
    public function maybe_run_advance_sync() {
        if (!is_user_logged_in()) return;

        $user = wp_get_current_user();
        $force = isset($_GET['advance_sync']);

        $last = (int) get_user_meta($user->ID, 'advance_last_profile_sync', true);

        if (!$force && time() - $last < $this->advance_profile_sync_frequency * MINUTE_IN_SECONDS) return;
        $this->run_advance_sync();
    }

    private function run_advance_sync() {
        $user = wp_get_current_user();
    
        $token = get_user_meta($user->ID, 'advance_usertoken', true);
        if (!$token) return;
    
        //fetch the ADvance party data by ADvance 'UserToken', requesting the addition of the wp user id held in an ADvance 'additional code' item. //note there is no current usage of this wp id value once received
        $profile = $this->fetch_advance_profile($token, [$this->advance_ad_wpid_additionalcode]);
        if ($profile) {
            // Normalize PartyID
            if (isset($profile['PartyId']) && !isset($profile['PartyID'])) {
                $profile['PartyID'] = $profile['PartyId'];
            }
    
            // Profile update
            $this->update_wp_user_profile_from_abacus($user->ID, $profile, false);
    
            // Product access
            if ($this->advance_product_metadataUpdate_enabled) {
                $this->sync_user_product_access($user->ID, $token);
            }
    
            update_user_meta($user->ID, 'advance_last_profile_sync', time());
            
            // Activation check - After updates check if the ADvance user activation state has reverted to false, and so requires "you need to activate messaging and logout"
            if ($this->advance_enable_activation_check && !empty($profile['PartyID'])) {
                $activation_details = $this->fetch_activation_status($profile['PartyID']);
                
                //error_log('[Abacus Sync] Activation Details: ' . print_r($activation_details, true));
                
                if (!$this->is_user_activated($activation_details)) {
                    wp_logout();
                    $resend_url = wp_nonce_url(add_query_arg('resend_activation', rawurlencode($token), wp_login_url()), 'resend_activation');
                    wp_redirect(wp_login_url() . '?activation_required=1&redirect_to=' . urlencode($resend_url));
                    exit;
                }
            } else {
                error_log('[Abacus Sync] Skipped activation check: PartyID missing or activation check disabled.');
            }
        }
    }
    
    public function custom_advance_register_url() {
        // 1) Use WP’s redirect_to if provided
        if ( ! empty( $_REQUEST['redirect_to'] ) ) {
            $returl = $_REQUEST['redirect_to'];
        }
        // 2) Fallback to referer
        elseif ( $referer = wp_get_referer() ) {
            $returl = $referer;
        }
        // 3) Last resort, homepage
        else {
            $returl = home_url( '/' );
        }
    
        // 4) URL-encode it
        $returl = rawurlencode( $returl );
    
        // 5) Append returl=...
        return add_query_arg( 'returl', $returl, $this->advance_register_url );
    }
    
    public function custom_advance_forgot_password_url() {
        // 1) Use WP’s redirect_to if provided
        if ( ! empty( $_REQUEST['redirect_to'] ) ) {
            $returl = $_REQUEST['redirect_to'];
        }
        // 2) Fallback to referer
        elseif ( $referer = wp_get_referer() ) {
            $returl = $referer;
        }
        // 3) Last resort, homepage
        else {
            $returl = home_url( '/' );
        }
    
        // 4) URL-encode it
        $returl = rawurlencode( $returl );
    
        // 5) Append returl=...
        return add_query_arg( 'returl', $returl, $this->advance_forgot_password_url );
    }
    
    
    //!!!!!!!!! DEBUG FUNCTION - CAN BE REMOVED ALONG WITH THE init in __construct())
    public function output_user_advance_products_debug() {
        if (!is_user_logged_in()) return;
    
        $user = wp_get_current_user();
        $products = get_user_meta($user->ID, 'advance_products', true);
    
        echo '<pre style="background:#f9f9f9; padding:10px; border:1px solid #ccc; margin:20px; font-size:14px;">';
        echo '<strong>[Abacus Debug] advance_products:</strong> ';
        echo esc_html(print_r($products, true));
        echo '</pre>';
    }
    
}

new Advance_External_Login();
