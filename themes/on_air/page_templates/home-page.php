<?php
/*
 *
 * Template Name: ON AIR homepage
 *
 */

get_header();
$evolve_layout = evolve_get_option('evl_layout', '2cl');

/*
if (evolve_lets_get_sidebar_2() == true):
    get_sidebar('2');
endif;
*/
?>

<div class="row">
  <div class="col-sm-12">
    <h1 class="entry-title">
        <a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title(); ?>">
            <?php
              echo mb_strtoupper(get_the_title());
            ?>
        </a>
    </h1>
  </div>
</div>
<div class='clearfix'></div>
<?php
get_template_part( 'page_templates/components/radio_boxes' );

wp_reset_query();

if (evolve_lets_get_sidebar() == true):
    get_sidebar();
endif;

get_footer();
