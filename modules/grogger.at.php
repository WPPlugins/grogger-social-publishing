<?php
//! Attributions
function grogger_filter_the_content($content)
{
	global $post;

	//! We only touch posts created by our community user
	if($post->post_author != grogger_get_user(GROGGER_COMMUNITY_USER)) return $content;

	//! Get Grogger Post Metadata
	$author = get_post_meta($post->ID, "grogger_author", true);
	$author_profile = get_post_meta($post->ID, "grogger_author_profile", true);
	$author_avatar  = get_post_meta($post->ID, "grogger_author_avatar", true);
	$timestamp = get_post_meta($post->ID, "grogger_post_timestamp", true);

	//! Manipulate the content only if the given post has at least these metadata set ...
	if(empty($author) || empty($author_profile) || empty($author_avatar) || !$timestamp)
		return $content;

	//! No need to merge the defaults here
	$settings = get_option('grogger_settings');
	
	//! Check if we need to manipulate the content on this page or not
	$page = '';
	$pages = array
	(
		'home'=>'is_home',
		'post'=>'is_single',
		'category'=>'is_category',
		'archive'=>'is_archive'
	);
	foreach($pages as $param=>$func)
	{
		if(call_user_func($func))
		{
			$page = $param;

			if($settings['promote'][$page]['active'] != 'on')
				return $content;

			break;
		}
	}
	
	//! No Page?
	if(empty($page)&&!is_feed()) return $content;

	$elements = $settings['promote']['elements'];
	if(is_archive() || is_category() || is_feed())
	{
		$attr .= '[';

		$attr .= 'By '.$author;
	
		if($elements == "tud")
			$attr .= ', Created: '.date('F j, Y', $timestamp);

		$attr .= '] ';

		return $attr.$content;
	}

	//! Initial CSS
	$css = 'padding-bottom:10px;';
	switch($settings['promote'][$page]['float'])
	{
		case 'left':
			$css .= 'padding-right:10px;float:left;';
			break;
		case 'right':
			$css .= 'padding-left:10px;float:right;';
			break;
	}

	//! Add Custom User CSS if necessary
	$css .= $settings['promote'][$page]['css'];

	$attr = '<div style="'.$css.'">';

	if($elements == "tud" || $elements == "tu")
		$attr .= '<img style="float:left;width:48px;height:48px;" src="'.$author_avatar.'"/>';
	
		$attr .= '<div style="float:left;margin-top:16px;margin-left:3px;">By <a href="'.$author_profile.'">'.$author.'</a></div>';
	
	if($elements == "tud")
		$attr .= '<div style="clear:left;"><small>Created: '.date('F j, Y', $timestamp).'</small></div>';
	
	$attr .= '</div>';

	switch($settings['promote'][$page]['pos'])
	{
		case 'bc':
		{
			$content = $attr . $content;
		}
		break;

		case 'ac':
		{
			$content = $content . $attr;
		}
		break;
	}

	return $content;
}
add_filter('the_content', 'grogger_filter_the_content');

//! WordPress doesn't seem to call this hook correctly, or at all!
add_filter('the_content_rss', 'grogger_filter_the_content');
?>
