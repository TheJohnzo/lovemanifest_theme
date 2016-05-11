<?php if ( is_active_sidebar( 'sidebar' ) ) : ?>
    <aside class="" role="complementary">
        <?php if($post->post_type == 'post' && !is_home()) : ?>
        <p><a href="<?php echo home_url('blog/'); ?>"><i class="fa fa-home"></i> Blog Home</a></p>
        <?php endif; ?>
        <ul class="list-unstyled">
            <?php dynamic_sidebar( 'sidebar' ); ?>
        </ul>
    </aside><!-- #primary-sidebar -->
<?php endif; ?>