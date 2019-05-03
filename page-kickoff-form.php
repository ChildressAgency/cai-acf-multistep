<?php acf_form_head(); ?>
<?php get_header(); ?>
<section id="primary">
  <main id="main" class="site-main">
    <article>
      <div class="entry-content">
        <?php
          //$groups = acf_get_field_groups(array('post_type' => 'kickoff_form'));
          //var_dump($groups);
        /*
          $post_types = get_post_types(array('_builtin' => false), 'objects');
          //var_dump($post_types);
          $post_types_list = array();
          foreach($post_types as $post_type){
            if($post_type->name == 'acf-field-group' || $post_type->name == 'acf-field'){
              continue;
            }

            $post_types_list[] = $post_type->label;
          }

          foreach($post_types_list as $list_item){
            echo '<p>' . $list_item . '</p>';
          }
          */
          if(have_posts()){
            while(have_posts()){
              the_post();
              the_content();
            }
          }
        ?>
      </div>
    </article>
  </main>
</section>
<?php get_footer();