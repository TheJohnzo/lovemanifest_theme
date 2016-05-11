<?php get_header(); ?>
<?php the_post(); //Initialize page post ?>
    <article>
        <div class="content container">
            <?php if($post->bw_meta['show_default_title'] == 'true') theme_page_title(); ?>
            <div class="container-fluid">
                <?php if($post->post_type == 'campaigns') : ?>
                <div class="text-center campaign-quicklinks">
                    <p class="text-muted">Get Involved!</p>
                    <a href="<?php echo home_url('donate/?campaign='.$post->ID); ?>" class="fa-stack fa-lg" data-toggle="tooltip" data-placement="bottom" title="Donate">
                        <i class="fa-stack-2x fa fa-circle"></i>
                        <i class="fa fa-heart fa-stack-1x text-white fa-inverse"></i>
                    </a>
                    <a href="<?php echo home_url('my-account/new-campaign/?campaign='.$post->ID); ?>" class="fa-stack fa-lg" data-toggle="tooltip" data-placement="bottom" title="Campaign">
                        <i class="fa-stack-2x fa fa-circle"></i>
                        <i class="fa fa-flag fa-stack-1x text-white fa-inverse"></i>
                    </a>
                </div>
                <?php endif; ?>
                <div class="row page-<?php the_ID(); ?>">
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
    </article>
<?php get_footer(); ?>