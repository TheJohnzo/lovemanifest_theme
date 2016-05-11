<?php
/*
  Template Name: Parent Page
*/

get_header(); ?>
<?php the_post(); //Initialize page post ?>
    <article>
        <div class="content container">
            <?php if($post->bw_meta['show_default_title'] == 'true') theme_page_title(); ?>
            <div class="container-fluid">
                <div class="row page-<?php the_ID(); ?>">
                    <?php the_content(); ?>
                </div>

                <?php
                $children = get_posts(
                    array(
                        'post_type' => 'page',
                        'post_parent' => $post->ID,
                        'posts_per_page' => -1,
                        'orderby' => 'menu_order'
                    )
                );
                
                foreach($children as $child_post) {

                    $excerpt = "";
                    if($child_post->post_excerpt) {
                        $excerpt = '<p>' . $child_post->post_excerpt .'</p>';
                    }

                    $link = get_permalink($child_post->ID);

                    echo "
                    <div class='campaign col-md-4'>
                        <a class='action-block' href='$link' data-ratio='390:270' data-ratio-valign='.child-description'>
                            <div class='child-description ratio-valign'>
                                <h2>$child_post->post_title</h2>
                                $excerpt
                            </div>
                        </a>
                    </div>
                    ";
                    
                }
                ?>
            </div>
        </div>
    </article>
<?php get_footer(); ?>