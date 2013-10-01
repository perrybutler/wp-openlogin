<?php

/**
 * Plugin Name: WP-OpenLogin
 * Plugin URI: http://github.com/perrybutler/wp-openlogin
 * Description: OAuth 2.0/OpenID login system that integrates with the standard WordPress User Accounts.
 * Version: 1.0
 * Author: Perry Butler
 * Author URI: http://glassocean.net
 * License: GPL2
 */

include_once 'lightopenid.php';

global $rapid_login;
$rapid_login = new RapidLogin();

Class RapidLogin {

	function __construct() {
		add_action ( 'init', array($this, 'init') );
	}

	function init() {
		add_action( 'wp_enqueue_scripts', array($this, 'scripts_init') );
		add_action( 'admin_enqueue_scripts', array($this, 'scripts_init') );
		//add_action( 'wp_logout', array($this, 'redirect_last') );
		add_action( 'show_user_profile', array($this, 'user_get_linked_accounts') );
		add_action( 'wp_ajax_rp_user_delete_linked_account', array($this, 'user_delete_linked_account') );
		add_action( 'wp_ajax_nopriv_rp_user_delete_linked_account', array($this, 'user_delete_linked_account') );
		add_shortcode( 'rp_login_form', array($this, 'rp_login_form') );
		add_action( 'admin_menu', 'rp_login_settings' );
		add_action( 'admin_init', 'rp_login_settings_register' );
	}
	
	function scripts_init() {
		// localize specific WordPress variables for use at the client (PHP -> JS)
		$wpjs = array(
			"ajaxurl" => admin_url('admin-ajax.php'),
			"template_directory" => get_bloginfo("template_directory"),
			"stylesheet_directory" => get_bloginfo("stylesheet_directory"),
			"plugins_url" => plugins_url(),
			"plugin_dir_url" => plugin_dir_url(__FILE__),
			"url" => get_bloginfo("url")
		);
		wp_enqueue_script('wpjs', plugins_url('/wpjs.js', __FILE__));
		wp_localize_script('wpjs', 'wpjs', $wpjs);
		// load some libs
		wp_enqueue_script('jquery');
		// load the rp_login scripts/styles
		wp_enqueue_script('rp-login', plugin_dir_url( __FILE__ ) . 'login.js', array());
		wp_enqueue_style('rp-login-css', plugin_dir_url( __FILE__ ) . 'login.css', array());
	}

	// TODO: UNSAFE!!! use prepare() before get_var()...
	function get_user_by_openid($id) {
		global $wpdb;
		$usermeta_table = $wpdb->usermeta;
		$query_string = "SELECT $usermeta_table.user_id FROM $usermeta_table WHERE $usermeta_table.meta_key = 'openid' AND $usermeta_table.meta_value LIKE '%" . $id . "%'";
		$query_result = $wpdb->get_var($query_string);
		return get_user_by("id", $query_result);
	}
	
	// TODO: UNSAFE!!! use prepare() before get_var()...
	function get_user_by_openid_email($email) {
		global $wpdb;
		$usermeta_table = $wpdb->usermeta;
		$query_string = "SELECT $usermeta_table.user_id FROM $usermeta_table WHERE $usermeta_table.meta_key = 'openid' AND $usermeta_table.meta_value LIKE '%" . $email . "%'";
		$query_result = $wpdb->get_var($query_string);
		return get_user_by("id", $query_result);
	}
	
	function redirect_last(){
		wp_redirect( $_SERVER['HTTP_REFERER'] );
		exit();
	}

	// TODO: UNSAFE!!! use prepare() before get_results()...
	function user_get_linked_accounts() {
	
		global $current_user;
		get_currentuserinfo();
		$user_id = $current_user->ID;
		
		global $wpdb;
		$usermeta_table = $wpdb->usermeta;
		$query_string = "SELECT * FROM $usermeta_table WHERE $user_id = $usermeta_table.user_id AND $usermeta_table.meta_key = 'openid'";
		$query_result = $wpdb->get_results($query_string);
		
		echo "<h3>Linked Accounts</h3>";
		echo "<p>Manage the linked accounts which you have previously authorized to be used for logging into this website.</p>";
		
		// TODO: if there are no linked accounts, indicate that with a message, otherwise list them
		if ( count($query_result) == 0) {
			"<p>You currently don't have any accounts linked.</p>";
		}
		echo "<div class='linked-accounts'>";
		foreach ($query_result as $openid) {
			$openid_data = explode("|", $openid->meta_value);
			$openid_provider = $openid_data[0];
			$openid_email = $openid_data[1];
			$openid_identity = $openid_data[2];
			echo "<div>" . $openid_provider . " - " . $openid_email . " <a class='delete-linked-account' data-provider='" . $openid_provider . "' data-openid-email='" . $openid_email . "' href='#'>Unlink</a></div>";
		}
		echo "</div>";
		
		//echo "<p><a class='add-linked-account' href='#'>Add a linked account</a></p>";
		
		// TODO: stuff this in an accordion using the link above as the click target/title
		echo "<p>Link another account:</p>";
		echo $this->rp_login_form("");
		//echo do_shortcode("[rp_accordion title='Add a linked account' width='200px']" . $rapid_platform->ui->rp_login("") . "[/rp_accordion]");
		//echo $rapid_platform->ui->rp_login("");
		
	}
	
	// TODO: UNSAFE!!! use prepare() before get_results()...
	function user_delete_linked_account() {
	
		// TODO: sanitize POST data from client
		// acquire the posted user's openid_email
		$post_openid_email = $_POST['openid_email'];
		$openid_email = $post_openid_email;
		
		global $current_user;
		get_currentuserinfo();
		$user_id = $current_user->ID;
		
		global $wpdb;
		$usermeta_table = $wpdb->usermeta;		
		$query_string = "DELETE FROM $usermeta_table WHERE $usermeta_table.user_id = $user_id AND $usermeta_table.meta_key = 'openid' AND $usermeta_table.meta_value LIKE '%" . $openid_email . "%'";
		$query_result = $wpdb->query($query_string);

		// TODO: there is no indication to the user of success or failure...create a json return message
		//	and check it at the client (login.js) before removing the linked account element from the page
		if ($query_result) {
			echo json_encode( array("result" => 1) );
		}
		else {
			echo json_encode( array("result" => 0) );
		}
		
		// wp_ajax_* pattern requires death
		die();
	}

	function user_add_linked_account($user_id) {
		if ($_SESSION["OPENID_IDENTITY"] != "") {
			add_user_meta( $user_id, "openid", $_SESSION["OPENID_PROVIDER"] . "|" . $_SESSION["OPENID_EMAIL"] . "|" . $_SESSION["OPENID_IDENTITY"]);
		}
	}
	
	function rp_login_form( $atts, $content = null ){
		$atts = shortcode_atts( array(
			'title' => 'Title',
			'design' => 'basic',
		), $atts );
		$html = "";
		$html .= "<div class='rp_login_form'>";
		$html .= "<a href='#' onclick='loginOpenID(\"google\"); return false;'>Google</a><br/>";
		$html .= "<a href='#' onclick='loginFacebook(); return false;'>Facebook</a><br/>";
		$html .= "<a href='#' onclick='loginOpenID(\"yahoo\"); return false;'>Yahoo</a><br/>";
		$html .= "<a href='#' onclick='loginOpenID(\"myopenid\"); return false;'>MyOpenID</a><br/>";
		$html .= "</div>";
		return $html;
	}
	
} // RapidLogin()

function rp_login_settings() {
	add_options_page( 'WP-OpenLogin Options', 'WP-OpenLogin', 'manage_options', 'wp-openlogin', 'rp_login_settings_page' );
}

function rp_login_settings_page() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
	<div class="wrap">
		<h2>WP-OpenLogin Settings</h2>
		<p>Manage settings for WP-OpenLogin here. Some login providers require the use of an API key/secret. See below for details.</p>
		<h3>Login with Facebook</h3>
		<?php session_start(); ?>
		<?php echo "Last URL: " . $_SESSION["LAST_URL"]; ?>
		<form method='post' action='options.php'>
			<?php settings_fields( 'rp-login-settings' ); ?>
			<?php do_settings_sections( 'rp-login-settings' ); ?>
			<table class='form-table'>
				<tr valign='top'>
				<th scope='row'>App ID</th>
				<td><input type='text' name='facebook_key' value='<?php echo get_option('facebook_key'); ?>' /></td>
				</tr>
				 
				<tr valign='top'>
				<th scope='row'>App Secret</th>
				<td><input type='text' name='facebook_secret' value='<?php echo get_option('facebook_secret'); ?>' /></td>
				</tr>
			</table>
			<p>
				<strong>Instructions:</strong>
				<ol>
					<li>Register as a Facebook Developer at <a href='https://developers.facebook.com/'>developers.facebook.com</a></li>
					<li>At Facebook, create a 'New Facebook App'</li>
					<li>At Facebook, configure the 'New Facebook App' to point to your site's domain (URL). This will allow the Login with Facebook button to work on your site.</li>
					<li>Paste your API key/secret into the fields above, then click Save Changes at the bottom of this page.</li>
				</ol>
			</p>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function rp_login_settings_register() {
	//register our settings
	register_setting( 'rp-login-settings', 'facebook_key' );
	register_setting( 'rp-login-settings', 'facebook_secret' );
}

?>