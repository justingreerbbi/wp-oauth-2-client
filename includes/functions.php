<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Main Functions
 *
 * @author Justin Greer <justin@justin-greer.com>
 * @package WP Single Sign On Client
 */

/**
 * Function wp_sso_login_form_button
 *
 * Add login button for SSO on the login form.
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/login_form
 */
function wp_sso_login_form_button() {
	?>
    <a style="color:#FFF; width:100%; text-align:center; margin-bottom:1em;" class="button button-primary button-large"
       href="<?php echo site_url( '?auth=sso' ); ?>">Single
        Sign On</a>
    <div style="clear:both;"></div>
	<?php
}

add_action( 'login_form', 'wp_sso_login_form_button' );

/**
 * Login Button Shortcode
 *
 * @param  [type] $atts [description]
 *
 * @return [type]       [description]
 */
function single_sign_on_login_button_shortcode( $atts ) {
	$a = shortcode_atts( array(
		'type'   => 'primary',
		'title'  => 'Login using Single Sign On',
		'class'  => 'sso-button',
		'target' => '_blank',
		'text'   => 'Single Sign On'
	), $atts );

	return '<a class="' . $a['class'] . '" href="' . site_url( '?auth=sso' ) . '" title="' . $a['title'] . '" target="' . $a['target'] . '">' . $a['text'] . '</a>';
}

add_shortcode( 'sso_button', 'single_sign_on_login_button_shortcode' );

/**
 * Get user login redirect. Just in case the user wants to redirect the user to a new url.
 */
function wpssoc_get_user_redirect_url() {
	$options           = get_option( 'wposso_options' );
	$user_redirect_set = $options['redirect_to_dashboard'] == '1' ? get_dashboard_url() : site_url();
	$user_redirect = apply_filters( 'wpssoc_user_redirect_url', $user_redirect_set );

	return $user_redirect;
}