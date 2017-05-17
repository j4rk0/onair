<div class="col-sm-6 col-md-4 col-lg-3">
    <div class="radio-box">
      <!--<i class="fa fa-cube"></i>-->
      <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" class="radio-logo" style="background-image: url(<?php echo get_the_post_thumbnail_url(get_the_ID(),'thumbnail');?>)">
      </a>
      <!--<h2><?php the_title()?></h2>-->
      <div class="radio-button">
          <!--<a class="read-more-box btn t4p-button" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">-->
          <?php $stream = get_post_meta(get_the_ID(), 'stream_128') ? get_post_meta(get_the_ID(), 'stream_128') : ['0'=>'#'];?>
          <a class="read-more-box btn t4p-button" target="_blank" href="<?php echo $stream[0]; ?>" title="<?php the_title_attribute(); ?>">
              <?php the_title()?> <i class="fa fa-chevron-right"></i>
          </a>
      </div>
      <div class='clearfix'></div>
      <div class="row radiosong ajax-onload" data-endpoint="wp-json/scrappy/v1/live/<?php the_ID(); ?>">
        <?php get_template_part( 'page_templates/components/badges' );?>
        <div class="col-sm-12 loader">
          <div class="row">
              <?php get_template_part( 'page_templates/components/loader' );?>
          </div>
        </div>
        <div class="col-sm-12 result">
          <div class="row">
              <?php get_template_part( 'page_templates/components/song_box' );?>
          </div>
        </div>
      </div>
    </div>
</div>

