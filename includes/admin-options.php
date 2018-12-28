<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class WPOSSO_Admin
 *
 * @author Justin Greer <justin@justin-greer.com
 * @package WP Single Sign On Client
 */
class WPOSSO_Admin {

	protected $option_name = 'wposso_options';

	public static function init() {
		add_action( 'admin_init', array( new self, 'admin_init' ) );
		add_action( 'admin_menu', array( new self, 'add_page' ) );
	}

	/**
	 * [admin_init description]
	 * @return [type] [description]
	 */
	public function admin_init() {
		register_setting( 'wposso_options', $this->option_name, array( $this, 'validate' ) );
	}

	/**
	 * [add_page description]
	 */
	public function add_page() {
		add_options_page( 'Single Sign On', 'Single Sign On', 'manage_options', 'wposso_settings', array(
			$this,
			'options_do_page'
		) );
	}

	/**
	 * loads the plugin styles and scripts into scope
	 * @return void
	 */
	public function admin_head() {
		wp_enqueue_style( 'wposso_admin' );
		wp_enqueue_script( 'wposso_admin' );
	}

	/**
	 * [options_do_page description]
	 * @return [type] [description]
	 */
	public function options_do_page() {
		$options = get_option( $this->option_name );
		$this->admin_head();
		?>
		<div class="wrap">
			<h2>Single Sign On Configuration</h2>
			<p>This plugin is meant to be used with <a href="https://wp-oauth.com">WordPress OAuth Server</a>.</p>
			<div>
				<strong>Setting up WordPress OAuth Server</strong>
				<ol>
					<li>Enable Authorization Code</li>
					<li>Create a new client and set the Redirect URI to:
						<strong><?php echo site_url( '?auth=sso' ); ?></strong></li>
					<li>Copy the Client ID and Client Secret in the text fields below.</li>
				</ol>
				<strong>How to use</strong>
				<p>
					When activated, this plugin adds a Single Sign On button to the login screen. If you want to add a
					custom link anywhere in your theme simply link to
					<strong><?php echo site_url( '?auth=sso' ); ?></strong>
					if the user is not logged in. Logging out the user is done as normal.
				</p>
			</div>
			<form method="post" action="options.php">
				<?php settings_fields( 'wposso_options' ); ?>
				<hr/>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">Client ID</th>
						<td>
							<input type="text" name="<?php echo $this->option_name ?>[client_id]" min="10"
							       value="<?php echo $options["client_id"]; ?>"/>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">Client Secret</th>
						<td>
							<input type="text" name="<?php echo $this->option_name ?>[client_secret]" min="10"
							       value="<?php echo $options["client_secret"]; ?>"/>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">OAuth Server URL</th>
						<td>
							<input type="text" name="<?php echo $this->option_name ?>[server_url]" min="10"
							       value="<?php echo $options["server_url"]; ?>"/>
							<p class="description">Example: https://your-site.com</p>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">Redirect to the dashboard after signing in</th>
						<td>
							<input type="checkbox" name="<?php echo $this->option_name ?>[redirect_to_dashboard]"
							       value="1" <?php echo $options['redirect_to_dashboard'] == 1 ? 'checked="checked"' : ''; ?> />
						</td>
					</tr>
				</table>

				<!--
				<h3 class="seperator">Advanced Options</h3>
							<table class="form-table">
								<tr valign="top">
	               	<th scope="row">Authorization Endpoint</th>
	                <td>
	                  <input type="text" name="<?php echo $this->option_name ?>[server_auth_endpoint]" min="10" value="<?php echo $options["server_auth_endpoint"]; ?>" />
	              	</td>
	              </tr>

								<tr valign="top">
	               	<th scope="row">Server Token Endpoint</th>
	                <td>
	                  <input type="text" name="<?php echo $this->option_name ?>[server_token_endpont]" min="10" value="<?php echo $options["server_token_endpont"]; ?>" />
	              	</td>
	              </tr>

	              <tr valign="top">
	               	<th scope="row">User Information Endpoint</th>
	                <td>
	                  <input type="text" name="<?php echo $this->option_name ?>[user_info_endpoint]" min="10" value="<?php echo $options["user_info_endpoint"]; ?>" />
	                  <p class="description">Example: https://your-site.com</p>
	              	</td>
	              </tr>
							</table>
							-->
		</div>

		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>"/>
		</p>
		</form>
		</div>
		<?php
	}

	/**
	 * Settings Validation
	 *
	 * @param  [type] $input [description]
	 *
	 * @return [type]        [description]
	 */
	public function validate( $input ) {
		$input['redirect_to_dashboard'] = isset( $input['redirect_to_dashboard'] ) ? $input['redirect_to_dashboard'] : 0;

		return $input;
	}
}

WPOSSO_Admin::init();
