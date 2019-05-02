<?php
/**
 * Plugin Name: CAI ACF MultiStep
 * Description: ACF MultiStep FrontEnd Forms
 * Author: The Childress Agency
 * Author URI: https://childressagency.com
 * Version: 1.0
 * Text Domain: caims
 */

if(!defined('ABSPATH')){ exit; }

define('CAI_MULTISTEP_PLUGIN_DIR', dirname(__FILE__));
define('CAI_MULTISTEP_PLUGIN_URL', plugin_dir_url(__FILE__));

if(!class_exists('CAI_MultiStep')){
  class CAI_MultiStep{
    /**
     * the form ID, used to identify this specific form
     * 
     * @var string
     */
    private $form_id;

    /**
     * The name of the post type this form is for
     * Get the post type from an acf option field
     * 
     * @var string
     */
    private $post_type;

    /**
     * List of form groups used as steps
     * Each array item is is an acf group id to display in each step
     * Get the group ids from a seperate acf field so the user can add more as needed
     * 
     * @vra array;
     */
    private $step_ids;

    public function __construct(){
      $this->load_dependencies();

      $this->form_id = 'cai-multistep';
      $this->post_type = $this->get_form_post_type();
      $this->step_ids = $this->get_form_steps_ids();

      add_shortcode('cai_mulitstep_form', array($this, 'output_shortcode'));

      add_action('acf/init', array($this, 'create_acf_options_page'));
      add_action('init', array($this, 'load_textdomain'));
      add_action('init', array($this, 'caims_create_post_types'));

      //process ACF form submission
      add_action('acf/save_post', array($this, 'process_acf_form'), 20);
    }

    public function load_dependencies(){
      if(!class_exists('acf')){
        require_once CAI_MULTISTEP_PLUGIN_DIR . '/vendors/advanced-custom-fields-pro/acf.php';
        add_filter('acf/settings/path', array($this, 'acf_settings_path'));
        add_filter('acf/settings/dir', array($this, 'acf_settings_dir'));
      }

      require_once CAI_MULTISTEP_PLUGIN_DIR . '/includes/custom-fields/kickoff-form-settings.php';
      require_once CAI_MULTISTEP_PLUGIN_DIR . '/includes/caims-create-post-types.php';
    }

    public function load_textdomain(){
      load_plugin_textdomain('caims', false, basename(dirname(__FILE__)) . '/languages');
    }

    public function acf_settings_path($path){
      $path = plugin_dir_path(__FILE__) . 'vendors/advanced-custom-fields-pro';
      return $path;
    }

    public function acf_setting_dir($dir){
      $dir = plugin_dir_url(__FILE__) . 'vendors/advanced-custom-fields-pro';
      return $dir;
    }

    public function create_acf_options_page(){
      acf_add_options_page(array(
        'page_title' => esc_html__('KickOff Form Settings', 'caims'),
        'menu_title' => esc_html__('Kickoff Form Settings', 'caims'),
        'menu_slug' => 'kickoff-form-settings',
        'capability' => 'edit_posts',
        'redirect' => false
      ));
    }

    public function get_form_post_type(){
      $post_type = get_field('form_post_type', 'option');
      return $post_type;
    }

    public function get_form_steps_ids(){
      $form_steps = array();
      if(have_rows('form_steps_ids', 'option')){
        while(have_rows('form_steps_ids', 'option')){
          the_row();
          $form_steps[] = get_sub_field('form_steps_id');
        }
      }

      return $form_steps;
    }

    public function output_shortcode(){
      //check if user is logged in first
      if(!is_user_logged_in()){
        echo '<p>Please login</p>';

        return false;
      }

      ob_start();

      if(!function_exists('acf_form')){ return; }

      //user is currently filling out the form
      if(!this->current_multistep_form_is_finished()){
        $this->output_acf_form(array('post_type' => $this->post_type));
      }
      else{
        //form has been filled entirely
        $form_complete_message = get_field('kickoff_form_complete_message', 'option');
        echo apply_filters('the_content', wp_kses_post($form_complete_message));
      }

      return ob_get_clean();
    }

    /**
     * Output the acf frontend form if logged in,
     * otherwise show login/register
     * Requires 'acf_form_head()' in the header of the theme
     */
    private function output_acf_form($args = []){
      //if step 1 create new post, otherwise get post_id from url
      $requested_post_id = $this->get_requested_post_id();

      //get the current step we are in
      $requested_step = $this->get_requested_step();

      $args = wp_parse_args(
        $args,
        array(
          'post_id' => $requested_post_id,
          'step' => 'new_post' === $requested_post_id ? 1 : $requested_step,
          'post_type' => $this->post_type,
          'post_status' => 'publish',
        )
      );

      $submit_label = $args['step'] < count($this->step_ids) ? esc_html__('Save and Continue', 'caims') : esc_html__('Finish', 'caims');
      $current_step_fields = ($args['post_id'] !== 'new_post' && $args['step'] > 1) ? $this->step_ids[(int) $args['step'] - 1] : $this->step_ids[0];

      //show the progress bar before the form
      $this->display_progress_bar($args);

      /**
       * display the form with acf_form()
       */
      acf_form(
        array(
          'id' => $this->form_id,
          'post_id' => $args['post_id'],
          'new_post' => array(
            'post_type' => $args['post_type'],
            'post_status' => $args['post_status']
          ),
          'field_groups' => $current_step_fields,
          'submit_value' => $submit_label,
          'html_after_fields' => $this->output_hidden_fields($args)
        )
      );
    }

    /**
     * hidden fields
     * 
     * @param array $args - form arguments passed to acf_form()
     * @return string html hidden input fields
     */
    private function output_hidden_fields($args){
      $inputs = array();
      $inputs[] = sprintf('<input type="hidden" name="caims-form-id" value="%1$s" />', $this->form_id);
      inputs[] = isset($args['step']) ? sprintf('<input type="hidden" name="caims-current-step" value="%1$s" />', $args['step']) : '';

      return implode(' ', $inputs);
    }

    /**
     * show the progress bar
     * 
     * @param array $args - the arguments passed to acf_form() in $this->output_acf_form()
     */
    private function display_progress_bar($args){
      $number_of_steps = count($this->step_ids);
      $current_step = $args['step'];

      echo '<ul class="list-unstyled">';
      for($i = 1; $i < $number_of_steps - 1; $i++){
        echo '<li style="display:inline-block; margin-right:15px;">Step ' . $i . '</li>';
      }
      echo '</ul>';
    }

    /**
     * if current $_GET['post_id'] is valid return the id, otherwise create new post
     */
    private function get_requested_post_id(){
      if(isset($_GET['post_id']) && $this->requested_post_is_valid() && $this->can_continue_current_form()){
        return (int) $_GET['post_id'];
      }

      return 'new_post';
    }

    /**
     * Get requested step, fallback to 1
     */
    private function get_requested_step(){
      if(isset($_POST['caims-current-step']) && absint($_POST['caims-current-step']) <= count($this->step_ids)){
        return absint($_POST['caims-current-step']);
      }
      elseif(isset($_GET['step']) && absint($_GET['step']) <= count($this->step_ids)){
        return absint($_GET['step']);
      }

      return 1;
    }

    /**
     * si the requested post the right one?
     */
    private function requested_post_is_valid(){
      return (get_post_type((int) $_GET['post_id']) === $this->post_type && get_post_status((int) $_GET['post_id']) === 'publish');
    }

    /**
     * is the user allowed to edit this form.
     * check token passed in url matches a post meta so that someone
     * can't pass a random $_GET['post_id'] parameter without its secret token
     * Any logged in verification should be done here
     */
    private function can_continue_current_form(){
      if(!isset($_GET['token'])){ return false; }
      if(!is_user_logged_in()){ return false; }

      $allowed = false;

      //check token
      $token_from_url = sanitize_text_field($_GET['token']);
      $token_from_post_meta = get_post_meta((int) $_GET['post_id'], 'secret_token', true);

      //check author
      $author_id = get_post_field('post_author', $_GET['post_id']);
      $current_user_id = get_current_user_id();

      if(($token_from_url === $token_from_post_meta) && ($author_id == $current_user_id)){
        $allowed = true;
      }

      return $allowed;
    }

    private function current_multistep_form_is_finished(){
      return (isset($_GET['finished']) && 1 === (int) $_GET['finished']);
    }

    /**
     * process the form
     * post has been created/updates, now update the progress bar
     * and redirect user to the next step or finished form
     */
    public function process_acf_form($post_id){
      //don't do anything if in admin or working on different front end acf form
      if(is_admin() || !isset($_POST['caims-form-id']) || $_POST['caims-form-id'] !== $this->id){
        return;
      }

      $current_step = $this->get_requested_step();

      //if it was a new post create a title and security token for it
      if($current_step == 1){
        $company_name = get_field('company_name', $post_id);
        wp_update_post(array(
          'ID' => $post_id,
          'post_type' => $this->post_type,
          'post_title' => esc_html($company_name)
        ));

        $token = wp_generate_password(rand(10,20), false, false);
        update_post_meta((int)$post_id, 'secret_token', $token);
      }

      //if not done with the form put post_id and step number in the url
      if($current_step < count($this->$step_ids)){
        $query_args = array(
          'step' => ++$current_step,
          'post_id' => $post_id,
          'token' = isset($token) ? $token : $_GET['token'];
        );
      }
      else{
        //we are done so put finished in the url
        $query_args = array('finished' => 1);

        //maybe send an email to someone here
      }

      exit();
    }
  } //end class
} //end class check