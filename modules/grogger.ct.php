<?php
//! Grogger Contribute Widget Class
class Grogger_Contribute_Widget extends WP_Widget 
{
	//! Default Settings
	private $defaults = array('title' 			=> '', 
							  'contribute_text' => 'Contribute');
	//! Default Module Title
	private $default_title = "Submit Button";
	//! Default Contribute Button Text
	private $default_contribute = "Contribute";

	//! Constructor
	function Grogger_Contribute_Widget() 
	{
		$options = array('classname' => 'widget_grogger_contribute', 'description' => "A button allowing others contribute." );
		$this->WP_Widget('grogger_contribute', 'Grogger: '.$this->default_title, $options); //! call superclass
	}

	//! Show the widget
	function widget($args, $instance) 
	{
		extract($args);

		global $GROGGER_DEFAULT_SETTINGS;
		$settings = wp_parse_args((array) get_option('grogger_settings'), $GROGGER_DEFAULT_SETTINGS);

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

		//! Show WordPress's Before Widget boilerplate code
		echo $before_widget;
		
		//! Show Widget Title
		echo "{$before_title}{$title}{$after_title}";

		//! Show Contribute Button
		grogger_contribute_button($url, $instance, $this->default_contribute);

		//! Show WordPress's After Widget boilerplate code
		echo $after_widget;
	}

	//! Save / Update Settings Form
	function update($new_instance, $old_instance) 
	{
		$instance = $old_instance;

		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['contribute_text'] = strip_tags(stripslashes($new_instance['contribute_text']));

		return $instance;
	}
		
	//! Show Settings Form
	function form($instance) 
	{
		//! Merge Widget Settings
		$instance = wp_parse_args((array) $instance, $this->defaults);

		//! Title
		grogger_form_input($this, 'title', 'Title of module (optional):', $instance);
		//! Contribute Text
		grogger_form_input($this, 'contribute_text', 'Title of button (optional):', $instance);
	}
}
//! Register our Contribute widget with the `nervous system`
add_action('widgets_init', 'grogger_contribute_widget_init');
function grogger_contribute_widget_init() 
{
	register_widget('Grogger_Contribute_Widget');
}
?>
