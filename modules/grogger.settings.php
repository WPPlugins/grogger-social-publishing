<?php

//! Grogger Settings (in Plugins)
add_action('admin_menu', 'grogger_settings_menu');
function grogger_settings_menu() 
{
	if(function_exists("add_submenu_page"))
	    add_submenu_page('options-general.php','Grogger', 'Grogger', 'manage_options', 'grogger_settings', 'grogger_settings_options');
}

//! Add Settings Link to the Plugin Page
add_filter('plugin_action_links', 'grogger_plugin_page_settings_link', 10, 2);
function grogger_plugin_page_settings_link($links, $file) 
{
	if($file == 'grogger/grogger.php') 
	{
		$link = '<a href="'.admin_url('options-general.php?page=grogger_settings').'">Settings</a>';
		array_unshift($links,$link); 
	}

	return $links;
}

//! Inject Grogger Settings CSS
add_action('admin_head', 'grogger_settings_css');
function grogger_settings_css()
{
	echo '<style type="text/css">';
	echo '.grogger-column { float:left; width:160px; }';
	echo '.grogger-column.header { font-weight:bold; }';
	echo '.grogger-column.first,.grogger-column.header.first { clear:left; }';
	echo '</style>';
}

//! TODO: unify select building
function grogger_settings_form_row($name, $title, $instance)
{
	$pos = array
	(
		'ac'=>'After Content',
		'bc'=>'Before Content'
	);
	foreach($pos as $n=>$t)
	{
		$selected = ($n == $instance['promote'][$name]['pos']) ? ' selected="selected"' : '';
	   	$positions .= '<option value="'.$n.'"'.$selected.'>'.$t.'</option>';
	}

	$float = array
	(
		'left'=>'Left',
		'right'=>'Right',
		'none'=>'None'
	);
	foreach($float as $n=>$t)
	{
		$selected = ($n == $instance['promote'][$name]['float']) ? ' selected="selected"' : '';
	   	$floats .= '<option value="'.$n.'"'.$selected.'>'.$t.'</option>';
	}
	$selected = ($instance['promote'][$name]['active'] == 'on') ? 'checked="checked"' : '';
	$css = $instance['promote'][$name]['css'];

	return '<div class="grogger-column first" ><input type="checkbox" '.$selected.'  name="grogger_settings[promote]['.$name.'][active]"/>'.$title.'</div>
			<div class="grogger-column"><select name="grogger_settings[promote]['.$name.'][pos]">'.$positions.'</select></div>
			<div class="grogger-column"><select name="grogger_settings[promote]['.$name.'][float]">'.$floats.'</select></div>
			<div class="grogger-column"><input  name="grogger_settings[promote]['.$name.'][css]" type="text" class="regular-text" value="'.$css.'"></div> ';
}

function grogger_settings_form($instance)
{
	global $GROGGER_DEFAULT_SETTINGS;
	$instance = wp_parse_args((array) $instance, $GROGGER_DEFAULT_SETTINGS);

	//! Build Categories drop-down
	$args=array
	(
		  'orderby' => 'name',
		  'order' => 'DESC'
	);
   	$cats = get_categories($args); 
    foreach($cats as $cat) 
	{
		$selected = ($cat->cat_ID == $instance['category']) ? ' selected="selected"' : '';
	   	$categories .= '<option value="'.$cat->cat_ID.'"'.$selected.'>'.$cat->cat_name.'</option>';
	}

	$promo = array
	(
		'tud'=>'Thumbnail, User\'s Name, Date',
		'tu'=>'Thumbnail, User\'s Name',
		'u'=>'User\'s Name'
	);
	foreach($promo as $name=>$title)
	{
		$selected = ($name == $instance['promote']['elements']) ? ' selected="selected"' : '';
	   	$promote .= '<option value="'.$name.'"'.$selected.'>'.$title.'</option>';
	}

	$pages = array
	(
		'home'=>'Main Blog Page',
		'post'=>'Individual Entry',
		'category'=>'Category Listing',
		'archive'=>'Archive Page'
	);

	foreach($pages as $name=>$title)
		$rows .= grogger_settings_form_row($name, $title, $instance);

	$site_url = base64_encode(urlencode(admin_url('options-general.php?page=grogger_settings&from_grogger=true')));
	$create = '<tr><td><a href="'.GROGGER_START_URL.'/site/index/plugin/'.$site_url.'">Create Account</a></td></tr>';

	if($_REQUEST['from_grogger'] == 'true')
		$create = '<span style="font-weight:bold;">Your Grog will be ready in a few moments.</span>';

	$login='
	<tr>
		<td>Username:</td>
		<td><input type="text" value="'.$instance['username'].'" class="regular-text" name="grogger_login[username]"/></td>
		<td></td>
	</tr>
	<tr>
		<td>Password:</td>
		<td><input type="password" value="" class="regular-text" name="grogger_login[password]"/></td>
		<td></td>
	</tr>
	<tr>
		<td>Your Grog URL:</td>
		<td><input type="text" value="'.$instance['url'].'" class="regular-text" name="grogger_login[url]"/></td>
		<td><input type="submit" name="submit"  class="button-primary" value="Login"/></td>
	</tr>';

    if(!empty($instance['profile_name']) && !empty($instance['profile_url']))
	{
		$login = 'Logged in as, <a href="'.$instance['profile_url'].'" target="_blank">'.$instance['profile_name'].'</a>';
		$login.= '<br/><a href="'.$_SERVER['REQUEST_URI'].'&grogger_logout=true">Logout</a>';
	}

	echo
	'<div>
	<h3>Account Information</h3>
	<blockquote>
	<div style="float:left;margin-right:200px;">
	<p>If you have an account, login below:</p>
	<blockquote>
	<form action="" method="post">
	<table>
	'.$login.'
	</table>
	</form>
	</blockquote>
	</div>
	<div style="float:left;">
	<p>If you don\'t have an account create one:</p>
	<blockquote>
	<form action="" method="post">	
	<table style="float:left">'.$create.'</table>
	</form>
	</blockquote>
	</div>
	<div style="clear:both;"></div>
	</blockquote>';

	echo '
		<form action="" method="post">
		<h3>Publishing Options</h3>
			<blockquote>
				<p>Tag published posts with this category: <select name="grogger_settings[category]">'.$categories.'</select></p>
			</blockquote>
		<h3>Attribution Options</h3>
			<blockquote>
				<p>Use this dropdown to select the elements that will be in the widget that appear in each "Promoted" post.</p>
				<select name="grogger_settings[promote][elements]">'.$promote.'</select>
				<p><h4>Button Placement</h4>
				You can add a module to your theme that will display the author\'s name, image and date of creation.
				<blockquote>
					<div class="grogger-column header first">Display on Page</div>
					<div class="grogger-column header">Position Button</div>
					<div class="grogger-column header">Float</div>
					<div class="grogger-column header">Additional CSS</div>
					
					'.$rows.'

					<div style="clear:both"></div>
				</blockquote></p>
			</blockquote>
			<p class="submit"><input type="submit" value="Update Settings" class="button-primary" name="submit"/></p>
		</form>
	</div>';
}

//! Handshake with Grogger
function grogger_settings_form_register($instance)
{
	$pass= '';
	$uid = grogger_create_user(GROGGER_COMMUNITY_USER, $pass);
	if(empty($pass))
		return -1;

	$data = array('username'=>$instance['username'],
				 'password'=>$instance['password'],
				 'PromoteCallbackURL'=>get_bloginfo('url').'/xmlrpc.php',
				 'PromoteUsername'=>strtolower(GROGGER_COMMUNITY_USER),
				 'PromotePassword'=>$pass,
				 'PromoteCategory'=>get_cat_name($instance['category']));

	$req = grogger_request_json($instance['url'].'/api/register.json', $data, true);

	if($req['code'] == 200 && is_object($req['body']))
		return $req['body'];

	return $req['code'];
}

//! End
function grogger_settings_form_update_finish($instance, $msg)
{
	//! Update the settings and spit out the message
	update_option('grogger_settings', $instance);

	if(!empty($msg))
		echo "<div class=\"updated fade\" id=\"message\" style=\"background-color:#fffbcc;\"><p><strong>{$msg}</strong></p></div>";

	//! Flush all caches
	grogger_cache_flush();

	return $instance;
}

//! Add / Update Login Settings
function grogger_settings_form_update_login($new_instance, $old_instance, $silent=false)
{
	$instance = $old_instance;

	$instance['username'] = strip_tags(stripslashes($new_instance['username']));
	$instance['password'] = $new_instance['password'];
	$instance['url'] = strip_tags(clean_url($new_instance['url']));

	unset($instance['profile_name']);
	unset($instance['profile_url']);

	$res = grogger_settings_form_register($instance);

	if(is_numeric($res))
	{
		//! Reset our password
		$instance['password'] = '';

		switch($res)
		{
			case -1:
				$msg = "A Community user already exists.";
				break;

			case 401:
				$msg = "Invalid credentials.";
				break;
		}
	}
	else if(is_object($res) && $res->status == 'oK')
	{
		$instance['profile_name'] = $res->username;
		$instance['profile_url'] = $res->profile_url;

		$msg = "Successfully registered.";
	}

	//! Enable XMLRPC
	update_option('enable_xmlrpc', 1);

	return grogger_settings_form_update_finish($instance, ($silent == false) ? $msg : '');
}

//! Add / Update for LogOut
function grogger_settings_form_update_logout($new_instance, $old_instance)
{
	$instance = $old_instance;

	unset($instance['username']);
	unset($instance['password']);
	unset($instance['profile_name']);
	unset($instance['profile_url']);

	$msg = "Successfully logged out.";
	return grogger_settings_form_update_finish($instance, $msg);
}

//! Add / Update Settings
function grogger_settings_form_update($new_instance, $old_instance)
{
	$instance = $old_instance;

	$instance['category'] = strip_tags(stripslashes($new_instance['category']));
	$instance['promote']['elements'] = strip_tags(stripslashes($new_instance['promote']['elements']));

	$pages = array
	(
		'home'=>'Main Blog Page',
		'post'=>'Individual Entry',
		'category'=>'Category Listing',
		'archive'=>'Archive Page'
	);

	foreach($pages as $name=>$title)
	{
		$op = $new_instance['promote'][$name];
		if($op['active'] == 'on')
		{
			$instance['promote'][$name]['active'] = 'on';
			$instance['promote'][$name]['pos'] = strip_tags(stripslashes($new_instance['promote'][$name]['pos']));
			$instance['promote'][$name]['float'] = strip_tags(stripslashes($new_instance['promote'][$name]['float']));
			
			//! Take care of the custom user CSS
			$css = trim(strip_tags(stripslashes($new_instance['promote'][$name]['css'])));
 			if( !empty($css) && (substr($css, -1) != ';') ) $css .= ';';

			$instance['promote'][$name]['css'] = $css;
		}
		else
		{
			unset($instance['promote'][$name]);
		}
	}

	$msg = "Settings successfully updated.";
	return grogger_settings_form_update_finish($instance, $msg);
}

function grogger_settings_options() 
{
    if(!current_user_can('manage_options'))  
        wp_die('You do not have sufficient permissions to access this page.');

	echo '<div class="wrap"><h2>Grogger Configuration</h2>';

	$msg = 'Grogger Social Publishing is being replaced with the Kapost Social Publishing plugin.<br/><br/>
	Both are made by the same team but future updates and releases will be in the Kapost plugin 
	and we recommend you switch to that.<br/><br/>To install the Kapost plugin, go 
	<a href="http://wordpress.org/extend/plugins/kapost-community-publishing/">here</a> .';

	echo "<div class=\"updated fade\" id=\"message\" style=\"background-color:#fffbcc;\"><p><strong>{$msg}</strong></p></div>";
	echo "</div>";

	return;

	echo '<div><p>The field below allows you to associate your community site with your blog. ';
	echo 'You will need to either sign-in with the username ';
	echo 'and password you used to create your account, or create a new community site.';

	//! Get the existing settings
	$old_instance = get_option('grogger_settings');

	//! Update
	if(isset($_REQUEST['submit']))
	{
		//! Settings
		if(isset($_POST['grogger_settings']))
			$old_instance = grogger_settings_form_update($_POST['grogger_settings'], $old_instance);
		//! Login
		else if(isset($_POST['grogger_login']))
			$old_instance = grogger_settings_form_update_login($_POST['grogger_login'], $old_instance);
	} 
	else if($_REQUEST['grogger_logout'] == 'true')
	{
		$old_instance = grogger_settings_form_update_logout(array(), $old_instance);
	}

	//! Display Form
	grogger_settings_form($old_instance);

	echo '</div>';
}
?>
