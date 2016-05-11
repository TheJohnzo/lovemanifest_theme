<?php while(have_posts()) : the_post(); ?>
    <article>
        <div class="content container">
            <?php if($post->bw_meta['show_default_title'] == 'true') : ?>
                <h1 id="page-title" class="default-title">
                    <a href="<?php the_permalink(); ?>" title="<?php echo sprintf('Permalink to %s', the_title_attribute( 'echo=0' )); ?>" rel="bookmark">
                        <?php the_title(); ?>
                    </a>
                </h1>
            <?php endif; ?>


            <a class="post-date" href="<?php the_permalink(); ?>" title="<?php the_time(); ?>" rel="bookmark">
                <time class="entry-date" datetime="<?php the_date( 'c' ); ?>" pubdate><?php echo get_the_date('M. d, Y'); ?></time>
            </a>


            <p class="by-author text-muted">
                <em> by </em>
                                <span class="author vcard">
                                    <a
                                        class="url fn n"
                                        href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>"
                                        title="<?php printf( __( 'View all posts by %s', 'gozoomex' ), get_the_author() ); ?>"
                                        rel="author">
                                        <?php the_author(); ?>
                                    </a>
                                </span>
            </p>

            <?php get_template_part('content', 'header'); ?>

            <div class="container margin-vert">
                <div class="row page-<?php the_ID(); ?>">
                    <?php the_content( 'Continue Reading...' ); ?>
                </div>
            </div>
        </div>
    </article>
<?php endwhile; ?>