<?php

global $form;

function create_settings_form($form) {
	
	if (!is_admin()) return;
	
	global $form;
	
	if( !isset($form['#settings']['id']) ) wp_die(__( 'Error: Form id is not set.'));
	if( !isset($form['#settings']['page_title']) ) {
		if( !isset($form['#settings']['title']) ) wp_die(__( 'Error: Form title is not set.'));
		$form['#settings']['page_title'] = $form['#settings']['title'];
	}
	if( !isset($form['#settings']['menu_title']) ) {
		if( !isset($form['#settings']['title']) ) wp_die(__( 'Error: Form title is not set.'));
		$form['#settings']['page_menu'] = $form['#settings']['title'];
	}
	
	if(! isset($form['#settings']['capability'])) $form['#settings']['capability'] = 'manage_options';
	if(! isset($form['#settings']['menu_slug'])) $form['#settings']['menu_slug'] = $form['#settings']['id'];
	
	$form['#settings']['page'] = $form['#settings']['id']; 
	$form['#settings']['option_group'] = $form['#settings']['id'] . '_options';
	
	add_action('admin_menu', 'admin_menu_callback');
	add_action('admin_init', 'admin_init_callback');
	
}

function admin_menu_callback() {
	global $form;
	extract($form['#settings']);
	add_options_page($page_title, $menu_title, $capability, $menu_slug, 'option_page_callback_new');
}

function admin_init_callback() {
	
	global $form;
	extract($form['#settings']);
	
	foreach($form as $key => &$element) {
		if($key == '#settings') continue;
		
		if($element['#type'] == 'section') {
			
			$section = $key;
			
			// Create new section
			
			$element['#option_name'] = $option_group . '_' . $section;
			$element['#id'] = $page . '_' . $section;
			
			register_setting($option_group, $element['#option_name'], 'options_validate');
			
			$options = get_option($option_group);
			$save_defaults = FALSE;
			
			add_settings_section($element['#id'], $element['#title'], 'build_section', $page);
			
			foreach($element as $field => &$field_settings) {
				
				if($field[0] == '#') continue;
		
				add_field($field, &$field_settings, $section);
			}
			
			if($save_defaults) {
				update_option($option_group, $options);
			}
			
		} else {
			
			if($key[0] == '#') continue;
			
			// Create form element
			
			if(!isset($form['#settings']['#base_option_name'])) {
				$form['default']['#id'] = 'default';
				$form['default']['#type'] = 'section';
			}

			add_field($key, $element);
			
		}
		
	}

}

function add_field($field, &$field_settings, $section='default') {
	
	global $form;
	extract($form['#settings']);
	$options = get_option($option_group);
	
	$field_settings['#key'] = ($section ? $section . '^' : '') . $field;
	$field_settings['#id'] = $page . '_' . ($section ? $section . '_' : '') . $field;
	$field_settings['#section'] = $section;
	$field_settings['#option_name'] = $section ? $form[$section]['#option_name'] : $form['#settings']['#base_option_name'];
	
	if(!isset($options[$field_settings['#id']])) {
		$default_value = isset($field_settings['#default_value']) ? $field_settings['#default_value'] : FALSE;
		$options[$field_settings['id']] = $default_value;
		$save_defaults = TRUE;
	} 

	add_settings_field($field_settings['#id'], $field_settings['#title'], 'build_field', $page, $form[$section]['#id'], $field_settings);
	
}

/**
 * Function to output HTML for the options page.
 */
function option_page_callback_new() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	global $form;
	extract($form['#settings']);
	echo sprintf('<div class="wrap"><h2>%s</h2>', $page_title);
	echo '<form method="post" action="options.php">';
	settings_fields($option_group);
	do_settings_sections($page);
	submit_button();
	echo '</form>';
	echo '</div>';
}

/**
 * Create the section header HTML.
 */
function build_section($section_passed) {
	global $form;
	extract($form['#settings']);
	$id = str_replace($page . '_', '', $section_passed['id']);
	if(isset($form[$id]['#description'])) {
		echo '<p>'.$form[$id]['#description'].'</p>';
	}
}

/**
 * Create the HTML for the input field.
 */
function build_field($field_settings) {
	
	global $option_group;

	// extract values (stripping hashes)
	foreach($field_settings as $k => $v) {
		if($k[0]=='#') $k = substr($k, 1);
		$$k = $v;
	}
	
	if($type == 'radios') $type = 'radio';
	
	$saved_options = get_option($option_name);
	
	if(!isset($default_value)) $default_value = FALSE;
	
	$value = isset($saved_options[$id]) ? $saved_options[$id] : $default_value;
	
	$non_inputs = array('textarea', 'select', 'radio');
	
	if(in_array($type, $non_inputs)) {
		$html = sprintf('<%s ', $type);
	} else {
		$html = sprintf('<input type="%s" ', $type);
	}
	
	$name = sprintf('%s[%s]', $option_name, $id);
	
	$html .= sprintf('name="%1$s" id="%1$s" ', $name);
	
	if($value && !in_array($type, $non_inputs)) {
		if($type=='checkbox') {
			$html .= 'checked="checked" ';
		} else {
			$html .= sprintf('value="%s" ', $value);
		}
	}
	
	if($type=='radio') {
		foreach($options as $k => $v) {
			$selected = ($default_value == $k) ? ' checked="checked"' : '';
			$html .= sprintf('<p><label><input type="radio" value="%s" name="%s"%s', $k, $name, $selected);
			if(isset($attributes)) {
				_add_attrs(&$html, $attributes);
			}
			$html .= sprintf('>%s</label></p>', $v);
		}
	}

	if(isset($attributes) && $type != 'radio') {
		_add_attrs(&$html, $attributes);
	}
	
	if(!in_array($type, $non_inputs)) {
		$html .= '/';
	}
	
	if($type != 'radio') {
		$html .= '>';
	}
	
	if($type=='textarea') {
		if($default_value) {
			$html .= $default_value;
		}
		$html .= '</textarea>';
	} elseif($type == 'select') {
		
		foreach($options as $k => $v) {
			$selected = ($default_value == $k) ? ' selected="selected"' : '';
			$html .= sprintf('<option value="%s"%s>%s</option>', $k, $selected, $v);
		}
		$html .= '</select>';
	}
	
	if(isset($prefix)) echo $prefix;

	echo $html;
	
	if(isset($description) && $description) echo '<p class="description">'.$description.'</p>';
	
	if(isset($suffix)) echo $suffix;
	
}

function _add_attrs(&$html, $attrs) {
	
	foreach($attrs as $attr => $attr_val) {
		if($attr == 'class') {
			$attr_val = implode(' ', $attr_val);
		}
		$html .= sprintf('%s="%s" ', $attr, $attr_val);
	}
	
}

/**
 * Sanitize the form input.
 */
function options_validate($input) {
	
	global $form;
	extract($form['#settings']);
	$options = get_option($option_group);

	foreach($form as $key => $element) {
		
		if($key == '#settings') continue;
		
		// for now skip non sections
		if($element['#type'] != 'section') continue;
		
		if($key[0] == '#') continue;
		
		$section = $key;
		
		foreach($element as $field => $field_settings) {
			
			if($field[0] == '#') continue;
			
			if(isset($input[$field_settings['#id']])) { // does this field have a value passed from the current submission?
				
				$id = $field_settings['#id'];
				
				$valid = TRUE;
				if(isset($field_settings['#validate'])) {
					$valid = validate_input($input[$id], $field_settings['#validate']);
				}
				if($valid) {
					
					// If checkbox then make sure that the value is boolean;
					if($field_settings['#type'] == 'checkbox') $input[$id] = !!$input[$id];
					
					$options[$id] = $input[$id];
				}
				
			} elseif($field_settings['#type'] == 'checkbox') {
				// As this field is a checkbox and does not have a value in the $input array it must be turned off so set to false.	
				$options[$field_settings['#id']] = FALSE;
			}
		}
	}
	
	return $options;
}

function validate_input($value, $validation) {
	switch($validation) {
		case 'int': return is_int($value);
	}
}