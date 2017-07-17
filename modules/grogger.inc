<?php
//! Request Wrapper
function grogger_request($url, $data=array(), $post=false)
{
	//! Initial Url
	$req_url = $url;

	if(!is_array($data))
		$data = array();

	//! Set WordPress Version
	if($post)
	{
		$data['wp'] = GROGGER_WP_VERSION;
	}
	else
	{
		$req_url .= (!strpos($req_url,"?")) ? "?wp=".GROGGER_WP_VERSION : "&wp=".GROGGER_WP_VERSION;

		foreach($data as $k=>$v)
			$req_url .= "&".urlencode($k)."=".urlencode($v);

		$data = array();
	}

	//! Set Custom User Agent and Data
	$args = array('User-Agent' => 'Grogger WordPress Plugin v'.GROGGER_VERSION, 'body'=>$data);
	//! Do a POST or GET
	$req  = ($post) ? wp_remote_post($req_url, $args) : wp_remote_get($req_url, $args);
	//! Get Response Code
	$res['code'] = wp_remote_retrieve_response_code($req);
	//! Get Body
	$res['body'] = wp_remote_retrieve_body($req);

	return $res;
}
function grogger_request_json($url, $data=array(), $post=false)
{
	//! Add JSON class if doesn't exists
	//if(!class_exists('Services_JSON')) require_once(dirname(__FILE__).'/class.json.php');
	$req = grogger_request($url, $data, $post);
	$req['body'] = json_decode($req['body']);

	return $req;
}

//! Widget Helper for Checkbox Ouput
function grogger_form_checkbox($cls, $name, $title, $instance)
{
	$id = $cls->get_field_id($name);
	$nm = $cls->get_field_name($name);

	$checked = ($instance[$name] == 'on') ? ' checked="checked"' : '';

	echo '<p><label for="'.$id.'">';
	echo '<input type="checkbox" id="'.$id.'" name="'.$nm.'"'.$checked.'/> '.$title;
	echo '</label></p>';
}

//! Widget Helper for Input Ouput
function grogger_form_input($cls, $name, $title, $instance)
{
	$id = $cls->get_field_id($name);
	$nm = $cls->get_field_name($name);

	echo '<p><label for="'.$id.'">'.$title.' ';
	echo '<input class="widefat" id="'.$id.'" name="'.$nm.'" type="text" value="'.esc_attr($instance[$name]).'" />';
	echo '</label></p>';
}

//! Widget Helper to Output a contribute button
function grogger_contribute_button($url, $instance, $default)
{
	$text = $instance['contribute_text'];
	if(empty($text)) $text = $default;

	//! HACK, HACK: including jQuery bundled with WordPress here
	//! TODO: find a better way to do this

	$receiver_url = get_bloginfo('url') . "/?grogger_receiver=yes";
	$jquery_url = get_bloginfo('url') . "/" . WPINC . "/js/jquery/jquery.js";
	echo '<script type="text/javascript" src="'.$jquery_url.'"></script>';
	echo '<script type="text/javascript">';
	echo 'var groggerPath="'.$url.'";';
	echo 'var groggerCallback="'.$receiver_url.'";';
	echo '</script>';
	echo '<script type="text/javascript" src="'.$url.'/remote_publish"></script>';
	echo '<div><p><input type="submit" class="submit" style="width:auto;padding:5px;"  onclick="try{ showPopup(); } catch(err){} return false;" value="'.$text.'"/></p></div>';
}

function grogger_trim($text, $max)
{
	if(strlen($text)>$max&&$max>3)
		return substr($text, 0, $max-3)."...";

	return $text;
}

/*
 * Time since function taken from WordPress.com
 */
function grogger_time_since($original, $do_more=0) 
{
	// array of time period chunks
	$chunks = array(
						array(60 * 60 * 24 * 365 , 'year'),
						array(60 * 60 * 24 * 30 , 'month'),
						array(60 * 60 * 24 * 7, 'week'),
						array(60 * 60 * 24 , 'day'),
						array(60 * 60 , 'hour'),
						array(60 , 'minute'),
						array(1, 'second')
					);

	$today = time();
	$since = $today - $original;

    for($i = 0, $j = count($chunks); $i < $j; $i++) 
	{
     	$seconds = $chunks[$i][0];
        $name = $chunks[$i][1];

        if(($count = floor($since / $seconds)) != 0)
			break;
	}

	$print = ($count == 1) ? '1 '.$name : "$count {$name}s";

	if($i + 1 < $j) 
	{
		$seconds2 = $chunks[$i + 1][0];
		$name2 = $chunks[$i + 1][1];

		// add second item if it's greater than 0
		if ((($count2 = floor(($since - ($seconds * $count)) / $seconds2)) != 0) && $do_more)
			$print .= ($count2 == 1) ? ', 1 '.$name2 : ", $count2 {$name2}s";
	}

	return $print;
}

//! Create our 'Community' user on-demand
//! FIXME: this is very very hacky!!
function grogger_create_user($user_name, &$user_pass='')
{
	require_once(ABSPATH . WPINC . '/registration.php');
	$uid = username_exists($user_name);
	if(!$uid) 
	{
		$user_pass 	= wp_generate_password(12,false);
		update_option("grogger_community_password", base64_encode($user_pass));

		$user_login = esc_sql($user_name);
		$role = "editor";
		$user_data = compact('user_login','user_pass','role');

		$uid = wp_insert_user($user_data);
	}
	else
	{
		$user_pass = base64_decode(get_option("grogger_community_password"));
	}

	return $uid;
}

//! Get our 'Community' uid
function grogger_get_user($username)
{
	require_once(ABSPATH . WPINC . '/registration.php');
	return username_exists($username);
}

//! Set Persistent Cache (name, data)
function grogger_cache_set($name, $data, $expire=5)
{
	if(!$expire)
		$expire = 5;

	$cache = array('data'=>$data, 
				   'timestamp'=>time(),
				   'expire'=>$expire * 60);

	update_option($name, $cache);
}

//! Get Persistent Cached Data by Name
function grogger_cache_get($name, &$expired)
{
	$cache = get_option($name);
	if(!$cache) { $expired = true; return null; }

	$timestamp = time();

	if($timestamp - $cache['timestamp'] >= $cache['expire'])
		$expired = true;

	return $cache['data'];
}

//! Delete Persistent Cached Data by Name
function grogger_cache_delete($name)
{
	delete_option($name);
}

//! Flush Caches Hook
function grogger_cache_flush()
{
	do_action('grogger_cache_flush');
}

//! Expose a clear-caches url
add_action('parse_request', 'grogger_parse_request');
function grogger_parse_request($wp)
{
	//! Receiver for Cross-Domain Scripting (required by the popup publisher a.k.a 'Contribute')
	if($_GET['grogger_receiver']=='yes')
	{
		//! Flush Caches On Demand
		if($_GET['grogger_flush_caches']=='yes')
			grogger_cache_flush();

		echo '<script type="text/javascript">';
		echo 'window.onload = function() { try { window.parent.parent.groggerEvent(window.location.hash); } catch(err) {} }';
		echo '</script>';
		die();
	}
}


?>
