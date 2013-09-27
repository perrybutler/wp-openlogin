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
	}
	
	function scripts_init() {
		wp_enqueue_script('rp-login', plugin_dir_url( __FILE__ ) . 'login.js', array());
		wp_enqueue_style('rp-login-css', plugin_dir_url( __FILE__ ) . 'login.css', array());
	}

	// TODO: UNSAFE!!! use prepare() before get_var()...
	function get_user_by_openid($id) {
		global $wpdb;
		$usermeta_table = $wpdb->prefix . "usermeta";
		$query_string = "SELECT $usermeta_table.user_id FROM $usermeta_table WHERE $usermeta_table.meta_key = 'openid' AND $usermeta_table.meta_value LIKE '%" . $id . "%'";
		$query_result = $wpdb->get_var($query_string);
		return get_user_by("id", $query_result);
	}
	
	// TODO: UNSAFE!!! use prepare() before get_var()...
	function get_user_by_openid_email($email) {
		global $wpdb;
		$usermeta_table = $wpdb->prefix . "usermeta";
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
		$usermeta_table = $wpdb->prefix . "usermeta";
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
			echo $openid_provider . " - " . $openid_email . " <a class='delete-linked-account' data-provider='" . $openid_provider . "' href='#'>Unlink</a>" . "</br>";
		}
		echo "</div>";
		
		//echo "<p><a class='add-linked-account' href='#'>Add a linked account</a></p>";
		
		// TODO: stuff this in an accordion using the link above as the click target/title
		global $rapid_platform;
		echo "<p>Link another account:</p>";
		//UNDONE: echo $rapid_platform->ui->rp_login("");
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
		$usermeta_table = $wpdb->prefix . "usermeta";		
		$query_string = "DELETE FROM $usermeta_table WHERE $usermeta_table.user_id = $user_id AND $usermeta_table.meta_key = 'openid' AND $usermeta_table.meta_value LIKE '%" . $openid_email . "%'";
		$query_result = $wpdb->query($query_string);

		// TODO: there is no indication to the user of success or failure...create a json return message
		//	and check it at the client (login.js) before removing the linked account element from the page
		echo json_encode( array("result" => 1) );
		
		// wp_ajax_* pattern requires death
		die();
	}

	function user_add_linked_account($user_id) {
		if ($_SESSION["OPENID_IDENTITY"] != "") {
			add_user_meta( $user_id, "openid", $_SESSION["OPENID_PROVIDER"] . "|" . $_SESSION["OPENID_EMAIL"] . "|" . $_SESSION["OPENID_IDENTITY"]);
		}
	}
	
} // RapidLogin()


?>