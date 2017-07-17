<?php
//! Grogger Recent Posts Widget Class
class Grogger_Recent_Posts_Widget extends WP_Widget 
{
	//! Cache Key
	protected $cache_key = "widget_grogger_recent_posts_cache";
	//! Default Settings
	protected $defaults = array('title' 			=> '', 
							  	'show' 				=> '4', 
							  	'author' 			=> 'on', 
							  	'date' 				=> '', 
							  	'contribute' 		=> '',
							  	'contribute_text' 	=> 'Contribute');
	//! Default Module Title
	protected $default_title = "Recent Submissions";
	//! Default Contribute Button Text
	protected $default_contribute = "Contribute";
	//! API
	protected $api = "/api/node.json";
	//! Flush Callback
	protected $flush_callback = 'grogger_recent_posts_widget_flush';

	//! Constructor
	function Grogger_Recent_Posts_Widget() 
	{
		$options = array('classname' => 'widget_grogger_recent_posts', 'description' => "Display the recently submitted posts." );
		$this->WP_Widget('grogger_recent_posts', 'Grogger: '.$this->default_title, $options); //! call superclass
	}

	//! Get Grogger Settings
	function settings()
	{
		global $GROGGER_DEFAULT_SETTINGS;
		return wp_parse_args((array) get_option('grogger_settings'), $GROGGER_DEFAULT_SETTINGS);
	}

	//! Fetch and Update Cache Data
	function fetch($settings, $flush=false)
	{
		if(!$flush)
		{
			$expired = false;
			
			//! TODO: handle multiple widgets (via $this->number)
			$data = grogger_cache_get($this->cache_key, $expired);

			//! Schedule a flush
			if($expired&&!empty($this->flush_callback))
				add_action('shutdown', $this->flush_callback);
		}
		else
		{
			$data = null;
		}

		if(!is_array($data))
		{
			$data = array();

			$req = grogger_request_json($settings['url'].$this->api);
			if($req['code'] == 200) 
			{
				foreach($req['body'] as $post)
					$data['posts'][] = (array) $post;
			}
			else
			{
				//! TODO: handle different error messages here
				$data['error'] = 'Error: Grogger did not respond. Please wait a few minutes and refresh this page.';
			}

			//! TODO: handle multiple widgets (via $this->number)
			grogger_cache_set($this->cache_key, $data);
		}
	
		return $data;
	}

	//! Flush
	function flush()
	{
		$settings = $this->settings();

		//! Get Username
		$username = $settings['username'];
		if(empty($username)) return;

		//! Get Password
		$password = $settings['password'];
		if(empty($password)) return;

		//! Get Url
		$url = $settings['url'];
		if(empty($url)) return;

		//! Update
		$this->fetch($settings, true);
	}
	
	//! Show the widget
	function widget($args, $instance) 
	{
		extract($args);

		$settings = $this->settings();

		//! Apply any filters on the title
		$title = apply_filters('widget_title', $instance['title']);
		if(empty($title)) $title = $this->default_title;

		//! Get Username
		$username = $settings['username'];
		if(empty($username)) return;

		//! Get Password
		$password = $settings['password'];
		if(empty($password)) return;

		//! Get Url
		$url = $settings['url'];
		if(empty($url)) return;

		//! Get number of posts to show
		$show = $instance['show'];

		//! Show WordPress's Before Widget boilerplate code
		echo $before_widget;
		
		//! Show Widget Title
		echo "{$before_title}{$title}{$after_title}";

		//! Get Data
		$data = $this->fetch($settings);

		//! Handle any error codes here
		if(isset($data['error']))
		{
			echo "<p>".$data['error']."</p>";
		}
		else if(isset($data['posts'])) //! Format and show posts here
		{
			$posts = $data['posts'];
			$i = 0;

			$author = ($instance['author'] == 'on');
			$date = ($instance['date'] == 'on');

			//echo "<ul>";
			foreach($posts as $post)
			{
				echo '<span><a href="'.$post['uri'].'">'.$post['title'].'</a>';
				
				if($author) echo ' by <a href="'.$post['user_profile'].'">'.grogger_trim($post['user_name'],15).'</a>';
				if($date) echo ' '.grogger_time_since($post['changed']).' ago';
				
				echo '</span><br/>';

				if(++$i == $show)
					break;
			}
			//echo "</ul>";
		}

		if($instance['contribute'] == 'on') grogger_contribute_button($url, $instance, $this->default_contribute);

		//! Show WordPress's After Widget boilerplate code
		echo $after_widget;
	}

	//! Save / Update Settings Form
	function update($new_instance, $old_instance) 
	{
		$instance = $old_instance;

		$instance['title'] = strip_tags(stripslashes($new_instance['title']));

		$show =  absint($new_instance['show']);

		//! Don't do anything crazy
		if($show > 10) $show = absint($this->defaults['show']);

		$instance['show'] =	$show;
		$instance['author'] = strip_tags(stripslashes($new_instance['author']));
   		$instance['date'] = strip_tags(stripslashes($new_instance['date']));
		$instance['contribute'] = strip_tags(stripslashes($new_instance['contribute']));
		$instance['contribute_text'] = strip_tags(stripslashes($new_instance['contribute_text']));

		//! Update Cached Data
		if($show) $this->flush();

		return $instance;
	}

	//! Show Settings Form
	function form($instance) 
	{
		//! Merge Widget Settings
		$instance = wp_parse_args((array) $instance, $this->defaults);

		//! Title
		grogger_form_input($this, 'title', 'Give the feed a title (optional):', $instance);

		//! How many posts to show?
		$id = $this->get_field_id('show');
		$name = $this->get_field_name('show');
		echo '<p><label for="'.$id.'">How many items would you like to display? ';
		echo '<select id="'.$id.'" name="'.$name.'">';
		for($i=1;$i<=10;$i++)
		{
			$selected = ($i == $instance['show']) ? ' selected="selected"' : '';
			echo '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
		}
		echo '</select></label></p>';

		//! Checkboxes
		grogger_form_checkbox($this, 'contribute', 'Display "Contribute" button?', $instance);
		grogger_form_input($this, 'contribute_text', 'Text on button:', $instance);
	
		grogger_form_checkbox($this, 'author', 'Display item author?', $instance);
		grogger_form_checkbox($this, 'date', 'Display item date?', $instance);
	}
}
//! Register our Recent Posts widget with the `nervous system`
add_action('widgets_init', 'grogger_recent_posts_widget_init');
function grogger_recent_posts_widget_init() 
{
	register_widget('Grogger_Recent_Posts_Widget');
}

//! Flush Cache Hook
add_action('grogger_cache_flush', 'grogger_recent_posts_widget_flush');
function grogger_recent_posts_widget_flush()
{
	$widget = new Grogger_Recent_Posts_Widget();
	$widget->flush();
}
?>
