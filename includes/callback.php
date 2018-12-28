<?php
/**
 * File callback.php
 *
 * @author Justin Greer <justin@justin-greer.com
 * @package WP Single Sign On Client
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Redirect the user back to the home page if logged in.
if ( is_user_logged_in() ) {
	wp_redirect( home_url() );
	exit;
}

// Grab a copy of the options and set the redirect location.
$options       = get_option( 'wposso_options' );
$user_redirect = wpssoc_get_user_redirect_url();

// Authenticate Check and Redirect
if ( ! isset( $_GET['code'] ) ) {
	$params = array(
		'oauth'         => 'authorize',
		'response_type' => 'code',
		'client_id'     => $options['client_id'],
		'client_secret' => $options['client_secret'],
		'redirect_uri'  => site_url( '?auth=sso' )
	);
	$params = http_build_query( $params );
	wp_redirect( $options['server_url'] . '?' . $params );
	exit;
}

// Handle the callback from the server is there is one.
if ( isset( $_GET['code'] ) && ! empty( $_GET['code'] ) ) {

	$code       = sanitize_text_field( $_GET['code'] );
	$server_url = $options['server_url'] . '?oauth=token';
	$response   = wp_remote_post( $server_url, array(
		'method'      => 'POST',
		'timeout'     => 45,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => array(),
		'body'        => array(
			'grant_type'    => 'authorization_code',
			'code'          => $code,
			'client_id'     => $options['client_id'],
			'client_secret' => $options['client_secret'],
			'redirect_uri'  => site_url( '?auth=sso' )
		),
		'cookies'     => array(),
		'sslverify'   => false
	) );

	$tokens = json_decode( $response['body'] );

	if ( isset( $tokens->error ) ) {
		wp_die( $tokens->error_description );
	}

	$server_url = $options['server_url'] . '?oauth=me&access_token=' . $tokens->access_token;
	$response   = wp_remote_get( $server_url, array(
		'timeout'     => 45,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => array(),
		'sslverify'   => false
	) );

	$user_info = json_decode( $response['body'] );
	$user_id   = username_exists( $user_info->user_login );
	if ( ! $user_id && email_exists( $user_info->user_email ) == false ) {

		// Does not have an account... Register and then log the user in
		$random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
		$user_id         = wp_create_user( $user_info->user_login, $random_password, $user_info->user_email );

		// Trigger new user created action so that there can be modifications to what happens after the user is created.
		// This can be used to collect other information about the user.
		do_action( 'wpoc_user_created', $user_info, 1);

		wp_clear_auth_cookie();
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id );

		if ( is_user_logged_in() ) {
			wp_redirect( $user_redirect );
			exit;
		}

	} else {

		// Already Registered... Log the User In
		$random_password = __( 'User already exists.  Password inherited.' );
		$user            = get_user_by( 'login', $user_info->user_login );


		// Trigger action when a user is logged in. This will help allow extensions to be used without modifying the
		// core plugin.
		do_action( 'wpoc_user_login', $user_info, 1);

		// User ID 1 is not allowed
		//if ( '1' === $user->ID ) {
		//	wp_die( 'For security reasons, this user can not use Single Sign On' );
		//}

		wp_clear_auth_cookie();
		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID );

		if ( is_user_logged_in() ) {
			wp_redirect( $user_redirect );
			exit;
		}

	}

	exit( 'Single Sign On Failed.' );
}