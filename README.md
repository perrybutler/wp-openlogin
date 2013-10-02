WordPress OpenLogin
====================

It's an OAuth 2.0 / OpenID user registration and login plugin for WordPress which integrates with the existing WordPress Users system. Includes and uses the [LightOpenID library](https://github.com/iignatov/LightOpenID) and Facebook-PHP-SDK.

Functions in a similar way to the StackExchange/StackOverflow login system - user's have the ability to link multiple third-party accounts to your WordPress site for logging into their WordPress user account.

Now supports WordPress Multisite.

Instructions
------------

1. Download the plugin files, place them into a "wp-openlogin" folder, then put this folder in your wp-content/plugins directory.
2. Configure the plugin through the WordPress admin dashboard >> Settings >> WP-OpenLogin.
3. Add a login form to your site using the shortcode [rp_login_form].

Who it's for
------------

Implement a single sign-on experience for visitors to your blog/site. Visitors can login with a Google/Yahoo/Facebook button. If the WordPress setting to allow new user registrations is enabled, any guest who authenticates successfully with their OAuth/OpenID provider will have a WordPress user account created for them automatically and linked to their OAuth/OpenID account.

Users can self-manage their linked accounts via the existing Edit Profile page:

![WP Open Login](http://files.glassocean.net/github/wp-openlogin.png)

Roadmap
-------

**Don't use wp-load.php**

Figure out how to eliminate the use of "../../wp-load.php" in some of the files, since this isn't always the path for some WordPress installations.

History
-------

This is a port of the **Unified Login** component from [Rapid Platform](http://github.com/perrybutler/rapidplatform).
