<?php

//    if(get_current_user_id() == 1) {
//        wp_set_current_user(2);
//        wp_set_auth_cookie(2);
//    }


	require_once "inc/config.php";


	function enqueue_styles_and_scripts() {
        global $post;

		// Stylesheets
		wp_enqueue_style(
			array(
				'bootstrap',
                'helpers',
                //'bootstrap-theme',
				'font-awesome',
				'font-exo-regular',
                'font-exo-demibold',
                'font-florance-sc',
				'font-florance-sc-black',
                'font-handwriting',
				'theme',
                'specials'
			)
		);

		// Scripts
		wp_enqueue_script(
			array(
				'jquery',
                'easing',
                'urlquery',
				'bootstrap',
				'ratio',
                'custom-select',
                'theme',
				'init-footer',
                'fluid-box'
			)
		);

        //  Cart66 Checkout Form Script
        if(has_shortcode( $post->post_content, 'checkout_authorizenet' )) {
            wp_enqueue_script('cart66-checkout');
        }
	}
	add_action('wp_enqueue_scripts', 'enqueue_styles_and_scripts');



    function lp_enqueue_styles_and_scripts() {
        wp_deregister_style('cart66-css');
    }
    add_action('wp_enqueue_scripts', 'lp_enqueue_styles_and_scripts', 100);


    function body_classes() {
        global $post, $body_classes;

        bw_meta($post);

        (array) $body_classes;

        if( $post->post_type == 'post') $body_classes[] = 'blog';
        if(has_post_thumbnail($post->ID)) $body_classes[] = 'has-featured-image';
        if($post->bw_meta['show_default_title']) $body_classes[] = 'has-page-title';
        if( $post->bw_meta['featured_background'] ) $body_classes[] = 'has-featured-background';
    }
    add_action('wp', 'body_classes');


    function get_user_campaign_title_by_name($name) {
        global $wpdb;

        $query = $wpdb->prepare("SELECT post_title from $wpdb->posts WHERE post_name = %s", $name);

        return $wpdb->get_var($query);
    }

    //QR Pages
    function lm_page_init() {
        global $post;
//        if(isset($_COOKIE['QR_PAGE_ID']) && !isset($_GET['modole']))
//        {
//            bw_meta($post);
//            if(isset($post->bw_meta['_qr_about_content']) && (int)$_COOKIE['QR_PAGE_ID'] == $post->ID || $post->post_name == 'receipt' || $post->post_name == 'checkout')
//            {
//                $url = get_permalink();
//                $url .= '?';
//                if(!empty($_GET))
//                {
//                    foreach($_GET as $key => $val)
//                        $url .= "{$key}={$val}&";
//                }
//                $url .= "modole=qr";
//                wp_redirect($url);
//            }
//        } else {
//            setcookie('QR_PAGE_ID', "", time() - 3600, '/');
//        }

        $classMods = mylm_class_mods();
        $template_parts=array('modole');
        if(is_array($classMods) && in_array('modole', $classMods)) {
            show_admin_bar(false);
            get_template_part('modole', $_GET['modole']);
            die();
        }
    }
    add_action('wp', 'lm_page_init');

    function mylm_class_mods() {
        global $post;

        $classMods = array();

        if(is_page_template('my-lovemanifest.php')
            || is_page_template('mylm_setup.php')
            || is_page_template('getting-started.php')
            || is_page_template('login.php')
            || $post->post_type == 'lm_user_page'
            || is_page_template('profile.php')
            || is_page_template('campaigns.php')
        ) { $classMods[] = 'my'; }

        $modoles = array('donate', 'message', 'qr');
        if(isset($_GET['modole']) && in_array($_GET['modole'], $modoles)) {
            $classMods[] = 'modole';
            $classMods[] = $_GET['modole'];
        }

        if(isset( $_GET['submitLoveStory'] )) { submitLoveStory($_POST['name'], $_POST['story']); }

        return $classMods;
    }

    if(!function_exists('theme_page_title')) {
        function theme_page_title() {
            global $post;

            $wrap_tag = $post->bw_meta['excerpt_type'] == 'h2' ? 'hgroup' : 'div';

            $excerpt = '';
            if($post->post_excerpt) {
                $excerpt =
                    '<'. $post->bw_meta['excerpt_type'] .' class="post-excerpt">' .
                        $post->post_excerpt .
                    '</'. $post->bw_meta['excerpt_type'] .'>';
            }

            echo '
                <'. $wrap_tag .' class="default-title">
                    <h1 id="page-title">'. get_the_title() .'</h1>
                    '. $excerpt .'
                </'. $wrap_tag .'>
            ';
        }
    }

    function create_account_link_for_login_modal() {
        $url = home_url('create-an-account/');

        echo '
            <p>Not a member yet? <a href="'. $url .'">Create An Account <i class="fa fa-arrow-circle-right"></i></a></p>
        ';
    }
    add_action('login_form', 'create_account_link_for_login_modal');

?>