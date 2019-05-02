<?php
/**
 * Fields for the KickOff Forms Settings acf options page
 */
if(!defined('ABSPATH')){ exit; }

acf_add_local_field_group(array(
	'key' => 'group_5ccb598466c04',
	'title' => esc_html__('KickOff Form Settings', 'caims'),
	'fields' => array(
		array(
			'key' => 'field_5ccb5995c506e',
			'label' => esc_html__('Form Post Type', 'caims'),
			'name' => 'form_post_type',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array(
			'key' => 'field_5ccb59acc506f',
			'label' => esc_html__('Form Steps Ids', 'caims'),
			'name' => 'form_steps_ids',
			'type' => 'repeater',
			'instructions' => esc_html__('Field key for the form step - starts with group_ followed by random string of characters. Steps will show in order listed here.', 'caims'),
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'collapsed' => '',
			'min' => 0,
			'max' => 0,
			'layout' => 'table',
			'button_label' => esc_html__('Add Form Step Id', 'caims'),
			'sub_fields' => array(
				array(
					'key' => 'field_5ccb59bec5070',
					'label' => esc_html__('Form Steps Id', 'caims'),
					'name' => 'form_steps_id',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
				),
			),
		),
		array(
			'key' => 'field_5ccb5a3cc5071',
			'label' => esc_html__('KickOff Form Complete Message', 'caims'),
			'name' => 'kickoff_form_complete_message',
			'type' => 'wysiwyg',
			'instructions' => esc_html__('The message to display when the form is finished.', 'caims'),
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'tabs' => 'all',
			'toolbar' => 'full',
			'media_upload' => 1,
			'delay' => 0,
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'options_page',
				'operator' => '==',
				'value' => 'kickoff-form-settings',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
));