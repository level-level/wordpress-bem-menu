<?php
/**
 * Shared Abacus Helper Functions
 * Intended to be included by other Abacus-related plugins
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Advance_Shared_Helpers {

    private static $base_api_url = constant( 'ADVANCE_BASE_URL' );
    private static $access_token = constant( 'ADVANCE_ACCESS_TOKEN' );
    private static $access_hash  = constant( 'ADVANCE_ACCESS_HASH' );
    private static $user_agent   = 'Mozilla/5.0 (compatible; WP/1.0)';

    public static function get_config($key, $plugin_slug = 'abacus-conditional-wrapper', $constant_name = null, $fallback_defaults = []) {
        $all_options = get_option('advance_integration');
        if (!is_array($all_options)) {
            $all_options = [];
        }

        // Ensure $plugin_options is always an array
        $plugin_options = [];
        if (isset($all_options[$plugin_slug]) && is_array($all_options[$plugin_slug])) {
            $plugin_options = $all_options[$plugin_slug];
        }

        $constant_name = $constant_name ?? strtoupper('ABACUS_' . $key);


        if (defined($constant_name)) {
            $val = constant($constant_name);
        } elseif (array_key_exists($key, $plugin_options)) {
            $val = $plugin_options[$key];
        } elseif (is_array($fallback_defaults) && array_key_exists($key, $fallback_defaults)) {
            $val = $fallback_defaults[$key];
        } else {
            error_log('[Abacus Shared Helper] Error: No config found for ' . $constant_name);
            return null;
        }

        if (is_string($val)) {
            $decoded = json_decode($val, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $val;
    }

    public static function call_advance_api($endpoint, $body = [], $method = 'POST') {
        $url = self::$base_api_url . $endpoint;
        $args = [
            'headers' => [
                'ADAPI_RESPONSE_FORMAT' => 'json',
                'ADAPI_ACCESS_TOKEN' => self::$access_token,
                'ADAPI_ACCESS_HASH' =>  self::$access_hash,
                'User-Agent' => self::$user_agent,
            ],
            'body' => $body,
            'timeout' => 15
        ];

        if ( strtoupper( $method ) === 'POST' ) {
            $args['body'] = $body;
            $response = wp_remote_post( $url, $args );
        } else {
            $url = add_query_arg( $body, $url );
            $response = wp_remote_get( $url, $args );
        }


        if (is_wp_error($response)) return $response;

        $clean = preg_replace( '/^\xEF\xBB\xBF/', '', wp_remote_retrieve_body( $response ) );
        return json_decode($clean, true);
    }

    public static function get_wp_root_domain($host) {
        $parts = explode('.', $host);
        $count = count($parts);
        if ($count >= 2) {
            $tld = $parts[$count - 1];
            $sld = $parts[$count - 2];
            $last_two = $sld . '.' . $tld;
            $two_level = ['co.uk', 'org.uk', 'gov.uk', 'ac.uk', 'net.uk'];
            if (in_array($last_two, $two_level) && $count >= 3) {
                return '.' . $parts[$count - 3] . '.' . $last_two;
            }
            return '.' . $last_two;
        }
        return $host;
    }

    /**
     * Get client IP address with fallback
     */
    public static function get_client_ip() {
        foreach ([
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'X-Real-IP',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ] as $key) {
            if (!empty($_SERVER[$key])) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }
        }
        return '0.0.0.0';
    }
}

// Autoload or include once
if ( ! class_exists( 'Advance_Shared_Helpers' ) ) {
    new Advance_Shared_Helpers();
}
