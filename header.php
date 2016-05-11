<?php
    global $body_classes;
    $dir = get_bloginfo('stylesheet_directory');
?>
<!DOCTYPE html>
<html lang=en>
<head>
	<meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no">
	<?php wp_head(); ?>
</head>
<body <?php body_class($body_classes); ?>>
<div class="header">
    <header class="container">
    	<div class="row">
        <h1 class="col-md-2"><a href="<?php echo home_url(); ?>" class="logo"><?php echo "<img src='{$dir}/media/logo.png' width='161' height='50' alt='Lovemanifest'/>"; ?></a></h1>
        <nav class="col-md-10 navbar" role="navigation">
        <?php
        wp_nav_menu(
            array(
                'menu_id' => 'menu',
                'menu_class' => 'nav navbar-nav',
                'theme_location' => 'primary',
                'container' => false,
                'link_before' => '<span class="menu-item-text">',
                'link_after' => '</span>',
                'walker' => new wp_bootstrap_navwalker()
            )
        );
        ?>
        </nav>
        </div>
    </header>
</div>

<?php if($post->post_type !== 'post') get_template_part('content', 'header'); ?>
<div class="content-wrap clearfix">