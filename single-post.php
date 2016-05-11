<?php get_header(); ?>
    <div class="container padding-top">
        <div class="row">
            <div class="col-md-8 col-xs-12">
                <div class="content-column">
                    <?php get_template_part('content', 'post'); ?>
                </div>

                <div class="clearfix pagination-links margin-vert">
                    <div class="pull-left"><?php previous_post_link( '%link', '<i class="fa fa-arrow-circle-left"></i> %title' ); ?></div>
                    <div class="pull-right"><?php next_post_link( '%link', '%title <i class="fa fa-arrow-circle-right"></i>' ); ?></div>
                </div>
            </div>
            <div class="col-md-4 col-xs-12">
                <?php get_sidebar('blog'); ?>
            </div>
        </div>
    </div>
<?php get_footer(); ?>