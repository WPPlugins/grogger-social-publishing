<?php
//! Grogger Most Popular Posts Widget Class
class Grogger_Most_Popular_Posts_Widget extends Grogger_Recent_Posts_Widget
{
	//! Constructor
	function Grogger_Most_Popular_Posts_Widget() 
	{
		$this->cache_key = "widget_grogger_most_popular_posts_cache";
		$this->default_title = "Most Popular";
		$this->api = "/api/popular.json";
		$this->flush_callback = "grogger_most_popular_posts_widget_flush";

		$options = array('classname' => 'widget_grogger_most_popular_posts', 'description' => "Display the most popular posts." );
		$this->WP_Widget('grogger_most_popular_posts', 'Grogger: '.$this->default_title, $options); //! call superclass
	}
}
//! Register our Most Popular Posts widget with the `nervous system`
add_action('widgets_init', 'grogger_most_popular_posts_widget_init');
function grogger_most_popular_posts_widget_init() 
{
	register_widget('Grogger_Most_Popular_Posts_Widget');
}
//! Flush Cache Hook
add_action('grogger_cache_flush', 'grogger_most_popular_posts_widget_flush');
function grogger_most_popular_posts_widget_flush()
{
	$widget = new Grogger_Most_Popular_Posts_Widget();
	$widget->flush();
}

?>
