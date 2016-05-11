<?php get_header(); ?>
<?php the_post(); //Initialize page post ?>
    <article>
        <div class="content container">
            <?php if($post->bw_meta['show_default_title'] == 'true') theme_page_title(); ?>
            <div class="container-fluid">
                <div class="row page-<?php the_ID(); ?>">
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
    </article>
<?php get_footer(); ?>