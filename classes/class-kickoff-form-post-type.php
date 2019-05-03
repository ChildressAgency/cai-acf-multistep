<?php
/**
 * create the kickoff form post type
 */
if(!defined('ABSPATH')){ exit; }

if(!class_exists('KickOff_Form_Post_Type')){
  class KickOff_Form_Post_Type{
    public function __construct(){
      add_action('init', array($this, 'create_post_type'));
    }

    public function create_post_type(){
      $kickoff_form_labels = array(
        'name' => esc_html_x('KickOff Forms', 'post type name', 'caims'),
        'singular_name' => esc_html_x('KickOff Form', 'post type singular name', 'caims'),
        'menu_name' => esc_html_x('KickOff Forms', 'post type menu name', 'caims'),
        'add_new_item' => esc_html__('Add New KickOff Form', 'caims'),
        'search_items' => esc_html__('Search KickOff Forms', 'caims'),
        'edit_item' => esc_html__('Edit KickOff Form', 'caims'),
        'view_item' => esc_html__('View KickOff Form', 'caims'),
        'all_items' => esc_html__('All KickOff Forms', 'caims'),
        'new_item' => esc_html__('New KickOff Form', 'caims'),
        'not_found' => esc_html__('No KickOff Forms Found', 'caims')
      );
      $kickoff_form_args = array(
        'labels' => $kickoff_form_labels,
        'capability_type' => 'post',
        'public' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-clipboard',
        'query_var' => 'kickoff_form',
        'has_archive' => false,
        'show_in_rest' => true,
        'supports' => array(
          'title',
          'custom-fields',
          'revisions',
          'author'
        )
      );
      register_post_type('kickoff_form', $kickoff_form_args);
    }
  }//end class
}//end if class_exists