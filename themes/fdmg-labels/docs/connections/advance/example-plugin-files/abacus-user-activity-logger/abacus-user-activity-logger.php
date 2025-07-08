<?php
/**
 * Plugin Name: Abacus User Activity Logger
 * Description: Logs post and page views to the Abacus external system using secure delayed server-side logic.
 * Version: 0.9
 */

defined('ABSPATH') || exit;

require_once WP_PLUGIN_DIR . '/abacus-common/abacus-shared-helpers.php';

//DO NOT CHANGE
$advance_user_activity_logger_defaults = [
    'advance_log_post_activity'    => true,
    'advance_log_page_activity'    => true,
    'advance_post_meta_type'       => 'Story',
    'advance_post_action_name'     => 'View Story',
    'advance_page_meta_type'       => 'External page',
    'advance_page_action_name'     => 'View Page',
    'advance_activity_brand_id'    => '1',
];

// === Output client JS to delay execution ===
add_action('wp_footer', function () {
    if (!is_user_logged_in() || is_admin()) return;
    global $post;
    if (!isset($post->ID)) return;

    $post_id = intval($post->ID);
    $nonce   = wp_create_nonce('advance_user_activity_logger');

    echo "<script>
        window.addEventListener('load', function() {
            fetch('" . esc_url(admin_url('admin-ajax.php')) . "', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=advance_log_user_activity_ajax&post_id={$post_id}&nonce={$nonce}'
            });
        });
    </script>";
}, 100);

// === AJAX handler for actual logging ===
add_action('wp_ajax_advance_log_user_activity_ajax', function () {
    if (!is_user_logged_in() || is_admin()) wp_die();

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $nonce   = $_POST['nonce'] ?? '';

    if (!$post_id || !wp_verify_nonce($nonce, 'advance_user_activity_logger')) wp_die();

    $post = get_post($post_id);
    if (!$post) wp_die();

    $user = wp_get_current_user();
    $user_token = get_user_meta($user->ID, 'advance_usertoken', true);
    if (!$user_token) wp_die();

    global $advance_user_activity_logger_defaults;

    $is_post = is_single($post);
    $is_page = is_page($post);

    $log_post = filter_var(Advance_Shared_Helpers::get_config(
        'advance_log_post_activity', 'abacus-access-control', 'advance_log_post_activity', $advance_user_activity_logger_defaults
    ), FILTER_VALIDATE_BOOLEAN);

    $log_page = filter_var(Advance_Shared_Helpers::get_config(
        'advance_log_page_activity', 'abacus-access-control', 'advance_log_page_activity', $advance_user_activity_logger_defaults
    ), FILTER_VALIDATE_BOOLEAN);

    if (($is_post && !$log_post) || ($is_page && !$log_page)) wp_die();

    $meta_type = Advance_Shared_Helpers::get_config(
        $is_post ? 'advance_post_meta_type' : 'advance_page_meta_type',
        'abacus-user-activity-logger',
        $is_post ? 'advance_post_meta_type' : 'advance_page_meta_type',
        $advance_user_activity_logger_defaults
    );

    $action_name = Advance_Shared_Helpers::get_config(
        $is_post ? 'advance_post_action_name' : 'advance_page_action_name',
        'abacus-user-activity-logger',
        $is_post ? 'advance_post_action_name' : 'advance_page_action_name',
        $advance_user_activity_logger_defaults
    );

    $ip = Advance_Shared_Helpers::get_client_ip();
    $meta_id = (string) $post->ID;
    $meta_title = get_permalink($post);

    $payload = [
        'userToken'   => $user_token,
        'MetaId'      => $meta_id,
        'MetaType'    => $meta_type,
        'metaTitle'   => $meta_title,
        'actionName'  => $action_name,
        'ipAddress'   => $ip,
        'itemType'    => 'Website',
    ];

    $response = Advance_Shared_Helpers::call_advance_api('/User/LogWebActivity/1', $payload, 'POST');

    if (is_wp_error($response)) {
        error_log('[Abacus Activity Logger] Error: ' . $response->get_error_message());
    }

    wp_die();
});
