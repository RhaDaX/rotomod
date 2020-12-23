<?php get_header(); ?>

<?php
global $porto_settings;

$post_layout = $porto_settings['post-layout'];
if ( is_category() ) {
    global $wp_query;

    $term    = $wp_query->queried_object;
    $term_id = $term->term_id;

    $post_options = get_metadata( $term->taxonomy, $term->term_id, 'post_options', true ) == 'post_options' ? true : false;

    $post_layout = $post_options ? get_metadata( $term->taxonomy, $term->term_id, 'post_layout', true ) : $post_layout;

    if ( 'grid' == $post_layout || 'masonry' == $post_layout ) {
        global $porto_blog_columns;
        $grid_columns = get_metadata( $term->taxonomy, $term->term_id, 'post_grid_columns', true );
        if ( $grid_columns ) {
            $porto_blog_columns = $grid_columns;
        }
    }
}

$skeleton_lazyload = apply_filters( 'porto_skeleton_lazyload', ! empty( $porto_settings['show-skeleton-screen'] ) && in_array( 'blog', $porto_settings['show-skeleton-screen'] ) && ! porto_is_ajax(), 'blog' );
$el_class          = $skeleton_lazyload ? ' skeleton-loading' : '';

$post_loop_start = '';
$post_loop_end   = '';
if ( 'timeline' == $post_layout ) {
    global $prev_post_year, $prev_post_month, $first_timeline_loop, $post_count;

    $prev_post_year      = null;
    $prev_post_month     = null;
    $first_timeline_loop = false;
    $post_count          = 1;

    $post_loop_start .= '<div class="blog-posts posts-' . esc_attr( $post_layout ) . ( ! $porto_settings['post-style'] ? '' : 'blog-posts-' . esc_attr( $porto_settings['post-style'] ) ) . '">';
    $post_loop_start .= '<section class="timeline' . ( $skeleton_lazyload ? ' skeleton-loading-wrap' : '' ) . '">';
    $post_loop_start .= '<div class="timeline-body posts-container' . $el_class . '">';
    $post_loop_end   .= '</div></section></div>';

} elseif ( 'grid' == $post_layout || 'masonry' == $post_layout ) {

    $post_loop_start .= '<div class="blog-posts posts-' . esc_attr( $post_layout ) . ( ! $porto_settings['post-style'] ? '' : 'blog-posts-' . esc_attr( $porto_settings['post-style'] ) ) . '">';
    $post_loop_start .= '<div class="row posts-container' . $el_class . '">';
    $post_loop_end   .= '</div></div>';
} else {

    $post_loop_start .= '<div class="blog-posts posts-' . esc_attr( $post_layout ) . $el_class . ' posts-container">';
    $post_loop_end   .= '</div>';
}
?>

<div id="content" role="main">
   <h1>Accès Commerciaux</h1>
   <?php  $all_meta_for_user = get_user_meta( 5 );

    $actions = array();
   $current_user = wp_get_current_user();

   $user_query = new WP_User_Query( array(  'exclude' => array( $current_user->ID),'meta_key' => '_rtm_commercial_user_meta', 'meta_value' => $current_user->ID ) );

   if ( ! empty( $user_query->get_results() ) ) {
       $nonce = wp_create_nonce( 'my-nonce' );
       echo '<section>';
       foreach ( $user_query->get_results() as $user ) {

           echo '<article style="margin-bottom: 25px;">';
           echo '<h2 style="display: block; margin-bottom: 5px; margin-right: 15px; line-height: 20px;">' . $user->display_name . '</h2>';
           echo '<span class="switch_to_user"><a href="https://rotomod.linkweb.fr/wp-login.php?action=switch_to_user&user_id='. $user->ID .'&nr=1_wpnonce='. esc_attr( $nonce ) .'">Se connecter en tant que '.$user->display_name.' </a>  </span>';
           echo '</article>';
       }
   } else {
       echo 'No users found.';
   }
   echo '</section>';
   ?>
</div>
<?php get_footer(); ?>
