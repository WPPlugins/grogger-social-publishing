<?php
/*
	Plugin Name: Grogger Social Publishing
	Plugin URI: http://www.getgrogger.com/
	Description: Grogger Social Publishing is being replaced with the Kapost Social Publishing plugin. Both are made by the same team but future updates and releases will be in the Kapost plugin and we recommend you switch to that. To install the Kapost plugin, go <a href="http://wordpress.org/extend/plugins/kapost-community-publishing/">here</a> .
	Version: 0.9.4
	Author: Grogger
	Author URI: http://www.getgrogger.com
*/
//! WordPress Version
global $wp_version;
define('GROGGER_WP_VERSION', !empty($wp_version) ? $wp_version : 'Unknown');

//! Grogger Version
define('GROGGER_VERSION', '0.9');

//! Grogger Community Username / Nickname
define('GROGGER_COMMUNITY_USER', 'Community');

//! Grogger Register URL
define('GROGGER_START_URL', 'http://start.getgrogger.com');

//! Global Grogger Settings Defaults
$GROGGER_DEFAULT_SETTINGS = array('username' => '', 'password' => '', 'url' => '', 'token' => '', 'category' => '1');

function grogger_active()
{
	$instance = get_option('grogger_settings');
	if(is_array($instance))
	{
		unset($instance['username']);
		unset($instance['password']);
		unset($instance['profile_name']);
		unset($instance['profile_url']);
		unset($instance['url']);

		update_option('grogger_settings', $instance);
	}
}
register_activation_hook( __FILE__, "grogger_active" );

$modules = array
(
	'grogger.inc',
	//'grogger.rp.php',
	//'grogger.pp.php',
	//'grogger.ct.php',
	'grogger.settings.php',
	//'grogger.at.php'
);

//! Include all modules
foreach($modules as $module) require_once(dirname(__FILE__).'/modules/'.$module);
?>
