</div>
<?php global $footer_menu_count; ?>
<section class="container networking">
    <div class="row">
        <nav class="col-md-5 navbar" role="navigation" title="Social Networking">
            <?php wp_nav_menu(array( 'menu_id' => 'social-menu', 'menu_class' => 'nav navbar-nav', 'theme_location' => 'social', 'container' => false, 'walker' => new wp_bootstrap_navwalker() )); ?>
        </nav>
        <h2 class="col-md-2 call-out">
            100%<br />
            <small>NON-PROFIT</small>
        </h2>
        <div class="col-md-5 col-xs-12 blue-block members clearfix">
            <div class="col-sm-8">
                All members of LoveManifest contribute freely from the love in their hearts and take absolutely no financial gain.
            </div>
            <a href="<?php echo home_url('members/'); ?>" class="btn col-sm-4 col-xs-12">
                Get to Know<br />
                The Team
            </a>
        </div>
    </div>
</section>
<div class="footer">
  <footer class="container">
  	<div class="row">
    	<div class="col-md-6">
  			<div class="row">
			<?php
                $i = 1;
                $cols = floor(12/$footer_menu_count);
                $remainder = 12%$footer_menu_count;
				$menu_locations = get_nav_menu_locations();
                while($i <= $footer_menu_count) {
                    if($remainder && $i == $footer_menu_count) $cols += $remainder;
                    if(has_nav_menu( "footer{$i}" ))
                    {
						$title = get_term( $menu_locations["footer{$i}"], 'nav_menu' );
                        echo "<nav role='navigation' class='col-sm-{$cols} h6'>";
						if(!is_wp_error($title)) echo "<h2>{$title->name}</h2>";
                        wp_nav_menu(
                            array(
                                'menu_id' => "footer{$i}",
                                'menu_class' => "menu",
                                'theme_location' => "footer{$i}",
                                'container' => false
                            )
                        );
                        echo '</nav>';
                    }
                    $i++;
                }
            ?>
        	</div>
        </div>
        <div class="col-md-6">
        </div>
    </div>
  </footer>
</div>
<?php
    if($GLOBALS['login_modal'] === true) {
        ob_start();
        dynamic_sidebar('Modal Login');
        $login_body = ob_get_clean();

        bootstrap_modal(array(
            'id' => 'login',
            'body' => $login_body
        ));
    };
    wp_footer();








?>
</body>
</html>