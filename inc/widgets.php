<?php
/**
 * LoveManifest Theme Widgets
 *
**/

if(!function_exists('register_theme_widgets')) {
    function register_theme_widgets() {
        register_sidebar(array(
            'name' => 'Modal Login',
            'id' => 'login-widget',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ));
    }
}
add_action('widgets_init', 'register_theme_widgets' );
?>