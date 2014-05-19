<?php
/**
 * Plugin Name: Fancybox2
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: Enables the jquery fancybox2 plugin on all jpg, png and gif links
 * Version: 1.0
 * Author: Felix Eve
 * Author URI: http://felixeve.co.uk
 * License: GPL2
 * 
 * Copyright 2014  Felix Eve  (email : dahousecat@gmail.com)
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
 
if (!defined('WP_CONTENT_URL')) define('WP_CONTENT_URL', get_option('siteurl').'/wp-content');
if (!defined('WP_PLUGIN_URL')) define('WP_PLUGIN_URL', WP_CONTENT_URL.'/plugins');

if (!defined('WP_CONTENT_DIR')) define('WP_CONTENT_DIR', ABSPATH.'wp-content');
if (!defined('WP_PLUGIN_DIR')) define('WP_PLUGIN_DIR', WP_CONTENT_DIR.'/plugins');

function fancybox() {
?>
<script type="text/javascript">
  jQuery(document).ready(function($){
    var select = $('a[href$=".bmp"],a[href$=".gif"],a[href$=".jpg"],a[href$=".jpeg"],a[href$=".png"],a[href$=".BMP"],a[href$=".GIF"],a[href$=".JPG"],a[href$=".JPEG"],a[href$=".PNG"]');
    select.attr('rel', 'fancybox');
    select.fancybox({
		prevEffect	: 'none',
		nextEffect	: 'none',
		helpers	: {
			title	: {
				type: 'outside'
			},
			thumbs	: {
				width	: 50,
				height	: 50
			}
		}
	});
  });
</script>
<?php
}

 
if (!is_admin()) {
	
  function load_styles() {
    // Add fancyBox	
    wp_enqueue_style('jquery.fancybox', WP_PLUGIN_URL.'/fancybox2/source/jquery.fancybox.css', false, '2.1.5');
	// Optionally add helpers - button, thumbnail and/or media
    wp_enqueue_style('jquery.fancybox.buttons', WP_PLUGIN_URL.'/fancybox2/source/helpers/jquery.fancybox-buttons.css', false, '1.0.5');
    wp_enqueue_style('jquery.fancybox.thumbs', WP_PLUGIN_URL.'/fancybox2/source/helpers/jquery.fancybox-thumbs.css?v=1.0.7', false, '1.0.7');
  }
  
  function load_scripts() {
  	
	wp_enqueue_script('my_script');
	$data = array('site_url' => __(site_url()));
	wp_localize_script('my_script', 'php_data', $data);
		
    wp_enqueue_script('jquery');
	
	// Add mousewheel plugin (this is optional)
    wp_enqueue_script('jquery.mousewheel', WP_PLUGIN_URL.'/fancybox2/lib/jquery.mousewheel-3.0.6.pack.js?v=3.0.6', array('jquery'), '3.0.6');
    
	// Add fancyBox
    wp_enqueue_script('jquery.fancybox', WP_PLUGIN_URL.'/fancybox2/source/jquery.fancybox.pack.js?v=2.1.5', array('jquery'), '2.1.5');
    
	// Optionally add helpers - button, thumbnail and/or media
    wp_enqueue_script('jquery.fancybox.buttons', WP_PLUGIN_URL.'/fancybox2/source/helpers/jquery.fancybox-buttons.js?v=1.0.5', array('jquery.fancybox'), '1.0.5'); 
    wp_enqueue_script('jquery.fancybox.media', WP_PLUGIN_URL.'/fancybox2/source/helpers/jquery.fancybox-media.js?v=1.0.6', array('jquery.fancybox'), '1.0.6'); 
    wp_enqueue_script('jquery.fancybox.thumbs', WP_PLUGIN_URL.'/fancybox2/source/helpers/jquery.fancybox-thumbs.js?v=1.0.7', array('jquery.fancybox'), '1.0.7');
	
	
	
	// Load init script
	wp_enqueue_script('jquery.fancybox.init', WP_PLUGIN_URL.'/fancybox2/fancybox2_init.js', array('jquery.fancybox'));

  }

	// load our settings
	// wp_enqueue_script('fancybox2_options');
	// $options = get_option('fancybox2_options');
	// $options = array(0 => 'test');
	// wp_localize_script('fancybox2_options', 'fancybox2_options', $options);
  

  
  add_action('wp_enqueue_scripts', 'load_styles');
  add_action('wp_enqueue_scripts', 'load_scripts');
  add_action('wp_head', 'fancybox');
  
}

/** Admin page */

include_once(WP_PLUGIN_DIR . '/fancybox2/options-page-helpers.php');

$form['#settings'] = array(
	'page_title' => 'Fancybox2 Options',
	'menu_title' => 'Fancybox2',
	'id' => 'fancybox2',
);
$form['selector'] = array(
	'#type' => 'section',
	'#title' => 'Selector',
	'#description' => 'What type of image will open with fancybox.',
);
$form['selector']['jpg'] = array(
	'#type' => 'checkbox',
	'#title' => 'JPG',
	'#default_value' => TRUE,
);
$form['selector']['png'] = array(
	'#type' => 'checkbox',
	'#title' => 'PNG',
	'#default_value' => TRUE,
);
$form['selector']['gif'] = array(
	'#type' => 'checkbox',
	'#title' => 'GIF',
	'#default_value' => TRUE,
);
$form['selector']['bmp'] = array(
	'#type' => 'checkbox',
	'#title' => 'BMP',
	'#default_value' => TRUE,
);
$form['selector']['class'] = array(
	'#type' => 'text',
	'#title' => 'Class',
	'#description' => 'Use a custom class to open fancybox',
);

$form['controls'] = array(
	'#type' => 'section',
	'#title' => 'Controls',
	'#description' => 'Adjust fancybox functionality.',
);
$form['controls']['arrows'] = array(
	'#type' => 'checkbox',
	'#title' => 'Arrows',
	'#description' => 'If set to true, navigation arrows will be displayed.',
	'#default_value' => TRUE,
);
$form['controls']['closeBtn'] = array(
	'#type' => 'checkbox',
	'#title' => 'Close Button',
	'#description' => 'If set to true, close button will be displayed.',
	'#default_value' => TRUE,
);
$form['controls']['click'] = array(
	'#type' => 'radios',
	'#title' => 'Click',
	'#description' => 'What should happen when the user clicks the content',
	'#options' => array(
		'nothing' => 'Do nothing',
		'closeClick' => 'Close the content',
		'nextClick' => 'Navigate to next gallery item',
	),
	'#default_value' => 'nothing',
);
$form['controls']['mouseWheel'] = array(
	'#type' => 'checkbox',
	'#title' => 'Mouse Wheel',
	'#description' => 'If set to true, you will be able to navigate gallery using the mouse wheel.',
	'#default_value' => TRUE,
);
$form['controls']['autoPlay'] = array(
	'#type' => 'checkbox',
	'#title' => 'Autoplay',
	'#description' => 'If set to true, slideshow will start after opening the first gallery item.',
	'#default_value' => TRUE,
);
$form['controls']['playSpeed'] = array(
	'#type' => 'text',
	'#title' => 'Play Speed',
	'#description' => 'Slideshow speed in milliseconds.',
	'#default_value' => 3000,
	'#validate' => 'int',
);
$form['controls']['preload'] = array(
	'#type' => 'text',
	'#title' => 'Preload',
	'#description' => 'Number of gallery images to preload.',
	'#default_value' => 3,
	'#validate' => 'int',
);
$form['controls']['modal'] = array(
	'#type' => 'checkbox',
	'#title' => 'Modal',
	'#description' => 'If set to true, will disable navigation and closing.',
	'#default_value' => FALSE,
);
$form['controls']['loop'] = array(
	'#type' => 'checkbox',
	'#title' => 'Loop',
	'#description' => 'If set to true, enables cyclic navigation. This means, if you click "next" after you reach the last element, first element will be displayed (and vice versa).',
	'#default_value' => TRUE,
);

create_settings_form($form);
