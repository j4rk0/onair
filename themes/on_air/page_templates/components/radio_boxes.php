<?php $loop = new WP_Query( array( 'post_type' => 'radio', 'posts_per_page' => -1 ) ); ?>
<div class="radio-boxes">
    <div class="row">
    <?php while ( $loop->have_posts() ) : $loop->the_post();
            get_template_part( 'page_templates/components/radio_box' );
          endwhile; wp_reset_query(); ?>
    </div>
</div>
<div class='clearfix'></div>
