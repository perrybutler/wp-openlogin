<?php

// TODO: we are calling wp-load...bad news!

// load the Wordpress core so this page can access WP functions during an ajax call
require_once( "../../../wp-load.php" );
		
// load the lightopenid library
//require '../../libs/lightopenid/lightopenid.php';

// initiate the user session
session_start();

// TODO: maybe put this in a separate function
// get the openid provider url from the query string
$get_oid_provider = $_GET["oid_provider"];
$provider_name = "";
$provider_url = "";
if ($get_oid_provider == 'google') {
	$provider_name = "Google";
	$provider_url = 'https://www.google.com/accounts/o8/id';
}
else if ($get_oid_provider == 'yahoo') {
	$provider_name = "Yahoo";
	$provider_url = 'https://me.yahoo.com';
}
else if ($get_oid_provider == 'myopenid') {
	$provider_name = "MyOpenID";
	$provider_url = 'https://www.myopenid.com';
}
$_SESSION['OPENID_PROVIDER'] = $provider_name;

// initiate the openid object
$openid = new LightOpenID(get_bloginfo("url"));

// set a session variable we can use to monitor the user's authentication status throughout the lifetime of his connection
$_SESSION['OPENID_IDENTITY'] = "";
$_SESSION['OPENID_EMAIL'] = "";

// check openid's mode so we know if we're at pre-authentication or post-authentication
if(!$openid->mode) {
	// openid mode is nothing, we are at pre-authentication so we simply start authentication
	$openid->identity = $provider_url;
	$openid->required = array('namePerson/first', 'namePerson/last', 'contact/email', 'pref/language');
	
	// UNDONE: no need to set returnUrl?
	//$openid->returnUrl = plugins_url('', __FILE__) . "/login-openid.php";
	
	// remember the user's last visited page so we can return there after this process
	$_SESSION['LAST_URL'] = $_SERVER['HTTP_REFERER'];
	
	// redirect to the openid authurl to begin the third party authentication process
	header('Location: ' . $openid->authUrl()); exit;
} 
elseif($openid->mode == 'cancel') {
	// openid mode is cancel, we are at post-authentication cancellation
	$_SESSION['OPENID_IDENTITY'] = "";
	$_SESSION['OPENID_EMAIL'] = "";
	// TODO: make a special alert case for when the openid auth is cancelled by the user externally
	header("Location: " . get_bloginfo("url") . "?alert=login_fail&msg=mode 'cancel' was unexpected"); exit;
} 
else {
	// the third-party responded to our request, check if we're authenticated
	if($openid->validate()) {
		// third-party authentication SUCCESS - get the profile info we requested from the third-party
		$atts = $openid->getAttributes();
		// store the user's third-party profile info in the user session so we can persist it between
		//  http requests e.g. during third-party authentication
		$_SESSION['OPENID_IDENTITY'] = $openid->identity;
		$_SESSION['OPENID_EMAIL'] = $atts["contact/email"];
	}
	else {
		// third-party authentication FAILED - clear the session and exit
		$_SESSION['OPENID_IDENTITY'] = "";
		$_SESSION['OPENID_EMAIL'] = "";
		exit();
	}
	
	// TODO: this does not prompt for registration and instead automatically registers the user account
	//	but we should follow the StackExchange pattern...
	// check if a WP user account has already been linked to this now-authenticated openid account 
	//  and if so, login that user now...if not, prompt for registration
	$matched_user = $rapid_login->get_user_by_openid($_SESSION["OPENID_IDENTITY"]);
	
	// handle matched user
	if ( $matched_user ) {
		// login - a WP user account is already associated with this third-party account, login that user now
		$user_id = $matched_user->ID;
		$user_login = $matched_user->user_login;
		wp_set_current_user( $user_id, $user_login );
		wp_set_auth_cookie( $user_id );
		do_action( 'wp_login', $user_login );
		// after login, redirect to the user's last location
		header("Location: " . $_SESSION["LAST_URL"]); exit;
	}
	
	// handle logged in user
	if ( is_user_logged_in() ) {
		// link accounts & login - no WP user account is associated with this third-party account, but a WP user account is
		//	currently logged in so we link the two accounts
		global $current_user;
		get_currentuserinfo();
		$user_id = $current_user->ID;
		global $rapid_login;
		$rapid_login->user_add_linked_account($user_id);
		// after linking the account, redirect user to their last url
		header("Location: " . $_SESSION["LAST_URL"]); exit;
	}

	// handle logged out user or no matching user
	if ( !is_user_logged_in() && !$matched_user ) {
		// register & login - no WP user account is logged in and no WP user account is associated with this third-party
		//  account so proceed to registration
		header("Location: " . plugins_url('', __FILE__) . "/process-registration.php"); exit;
	}

}

?>