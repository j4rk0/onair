<?php
/*
 *
 * Template Name: ON AIR homepage
 *
 */

get_header();
$evolve_layout = evolve_get_option('evl_layout', '2cl');
?>
<div class='clearfix'></div>
<?php
get_template_part( 'page_templates/components/radio_boxes' );

wp_reset_query();

if (evolve_lets_get_sidebar() == true):
    get_sidebar();
endif;

get_footer();
