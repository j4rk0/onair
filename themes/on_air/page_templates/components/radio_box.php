<div class="col-sm-6 col-md-4 col-lg-3">
    <div class="radio-box">
      <!--<i class="fa fa-cube"></i>-->
      <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" class="radio-logo" style="background-image: url(<?php echo get_the_post_thumbnail_url(get_the_ID(),'thumbnail');?>)">
      </a>
      <!--<h2><?php the_title()?></h2>-->
      <div class="radio-button">
          <a class="read-more-box btn t4p-button" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
              <?php the_title()?> <i class="fa fa-chevron-right"></i>
          </a>
      </div>
    </div>
</div>

