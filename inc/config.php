<?php

require_once "theme-widgets.php";
require_once "filters.php";
require_once "templates.php";
require_once "shortcodes.php";
require_once "wp-bootstrap-navwalker.php";

require_once "Campaigns.php";
require_once "UserCampaigns.php";
require_once "LmUserAdmin.php";
require_once "UserCampaignData.php";
require_once "ProductManagement.php";
require_once "UserOrderMangement.php";

require_once "wp-ajax.php";

function lm_theme_setup() {
	global $footer_menu_count;
	
	// Theme Directory URL
	$dir = get_bloginfo('stylesheet_directory');
	
	// Register Theme Scripts and Stylesheets

    //  Stylesheets
	wp_register_style('bootstrap', "$dir/css/bootstrap.min.css", '', '3.1.0', 'screen');
    wp_register_style('bootstrap-theme', "$dir/css/bootstrap-theme.min.css", '', '3.1.0', 'screen');
    wp_register_style('helpers', "$dir/css/helpers.css", '', '1.0', 'screen');
	wp_register_style('theme', "$dir/style.css", '', '3.0.3', 'screen');
    wp_register_style('specials', "$dir/css/specials.css", '', '3.0.3', 'screen');
    wp_register_style('user-admin', "$dir/css/user-admin.css", '', '1.0', 'screen');
    wp_register_style('setup-campaign', "$dir/css/setup-campaign.css", '', '1.0', 'screen');
	wp_register_style('font-awesome', "//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.css", '', '4.1.0', 'screen');
	wp_register_style('font-exo-regular', "$dir/fonts/exoregular/stylesheet.css", '', '1.0', 'screen');
    wp_register_style('font-exo-demibold', "$dir/fonts/exodemibold/stylesheet.css", '', '1.0', 'screen');
    wp_register_style('font-florance-sc', "$dir/fonts/florsn17/stylesheet.css", '', '1.0', 'screen');
	wp_register_style('font-florance-sc-black', "$dir/fonts/florsn35/stylesheet.css", '', '1.0', 'screen');
    wp_register_style('font-handwriting', "//fonts.googleapis.com/css?family=Poiret+One", '', '1.0', 'screen');

    //  Scripts
    wp_register_script('theme', "$dir/js/Theme.min.js", array('jquery'), '3.0.3', true);
    wp_register_script('easing', "$dir/js/jquery.easing.js", array('jquery'), '3.0.3', true);
    wp_register_script('fluid-box', "$dir/js/jquery.fluid-box.min.js", array('jquery'), '3.0.3', true);
	wp_register_script('bootstrap', "$dir/js/bootstrap.min.js", array('jquery'), '3.0.3', true);
	wp_register_script('init-footer', "$dir/js/init.footer.js", array('jquery'), '1.0', true);
	wp_register_script('ratio', "$dir/js/ratio.min.js", array('jquery'), '1.0', true);
	wp_register_script('custom-select', "$dir/js/jquery.customSelect.min.js", array('jquery'), '1.0', true);
    wp_register_script('urlquery', "$dir/js/urlquery.js", null, '1.0', true);

    //  Cart66 Scripts
    wp_register_script('cart66-checkout', "$dir/js/cart66Checkout.min.js", null, '1.0', false);

    //  Angular Scripts
    wp_register_script('angularjs', "$dir/js/angular/angular.min.js", null, '1.0', false);
    wp_register_script('angularjs-resource', "$dir/js/angular/lib/resource.min.js", array('angularjs'), '1.0', false);
    wp_register_script('angularjs-route', "$dir/js/angular/lib/route.min.js", array('angularjs'), '1.0', false);
    wp_register_script('angularjs-animate', "$dir/js/angular/lib/animate.min.js", array('angularjs'), '1.0', false);
    wp_register_script('angularjs-ui-bootstrap', "$dir/js/angular/lib/ui-bootstrap.min.js", array('angularjs'), '1.0', false);
    wp_register_script('angularjs-text-sanitize', "$dir/js/angular/lib/textAngular-sanitize.min.js", array('jquery'), '1.0', false);
    wp_register_script('angularjs-text', "$dir/js/angular/lib/textAngular.min.js", array('jquery'), '1.0', false);
    wp_register_script('angularjs-ctrl-user-admin', "$dir/js/angular/modules/userAdmin.min.js", array('jquery'), '1.0', false);
    wp_register_script('angularjs-ctrl-setup-campaign', "$dir/js/angular/modules/setupCampaign.min.js", array('jquery'), '1.0', false);
    wp_register_script('angularjs-module-user-services', "$dir/js/angular/modules/userServices.js", array('jquery'), '1.0', false);
    wp_register_script('angularjs-module-user-filters', "$dir/js/angular/modules/userFilters.js", array('jquery'), '1.0', false);

	// Register Menus
	$menus = array(
		'primary' => 'Top Bar Navigation',
        'social' => 'Social Media Links'
	);
	
	// Number of footer Menus to register
	$footer_menu_count = 3;
	$i = 1;
	while($i <= $footer_menu_count) {
		$menus["footer{$i}"] = "Footer Menu {$i}";
		$i++;
	}
	register_nav_menus($menus);
	
	// Include support for featured images
	add_theme_support( 'post-thumbnails' );

    // Include excerpts for pages
    add_post_type_support( 'page', 'excerpt' );

    // Additional Image Sizes
    add_image_size( 'banner', '9999', '440', true );
    add_image_size( 'userpage', '585', '400', true );

    register_sidebar( array(
        'name'         => __( 'Blog Sidebar' ),
        'id'           => 'sidebar',
        'description'  => __( 'Widgets in this area will be shown on the blog sidebar.' ),
        'before_title' => '<h2>',
        'after_title'  => '</h2>',
    ) );
	
}
add_action('init', 'lm_theme_setup');

function theme_metabox_data() {
	$featured_image_details = array(
		'label' => 'Featured Image Content',
		'position' => 'normal',
		'priority' => 'default',
		'tabs' => false,
		'limiters' => array(
			'post_type' => 'page,campaigns'
		),
		'fields' => array(
			'feat_img_content_position' => array(
				'type' => 'select',
				'label' => 'Position',
				'options' => array(
					'left' => 'Left',
					'right' => 'Right'
				)
			),
			'feat_img_content_cols' => array(
				'type' => 'select',
				'label' => 'Width (Between 1 and 12)',
				'options' => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
					'7' => '7',
					'8' => '8',
					'9' => '9',
					'10' => '10',
					'11' => '11',
					'12' => '12'
				)
			),
            'feat_img_content_height' => array(
                'type' => 'select',
                'label' => 'Height',
                'options' => array(
                    'default' => 'default',
                    'full' => 'Full'
                )
            ),
			'feat_img_content' => array(
				'type' => 'textarea',
				'label' => false
			)
		)
	);
	add_bw_metabox('featured_image_content', $featured_image_details );

    $page_option_details = array(
        'label' => 'Page Options',
        'position' => 'normal',
        'priority' => 'default',
        'tabs' => false,
        'limiters' => array(
            'post_type' => 'page,campaigns,post'
        ),
        'fields' => array(
            'show_default_title' => array(
                'type' => 'on_off',
                'label' => 'Show Default Page Title',
                'default' => true
            ),
            'featured_background' => array(
                'type' => 'on_off',
                'label' => 'Featured Image as Background',
                'default' => false
            ),
            'logged_in_url' => array(
                'type' => 'text',
                'label' => 'Redirect Url When Logged In'
            ),
            'excerpt_type' => array(
                'label' => 'Excerpt Type',
                'type' => 'select',
                'options' => array(
                    'h2' => 'Second Heading',
                    'p' => 'Paragraph'
                ),
                'default' => 'h2'
            )
        )
    );
    add_bw_metabox('page_options', $page_option_details );
}
add_action('bw_metabox_init', 'theme_metabox_data');


function logged_in_url_redirect_filter() {
    global $post;
    bw_meta($post);
    if($post->bw_meta['logged_in_url'] && is_user_logged_in()) {
        $url = preg_match("/^https?:/", $post->bw_meta['logged_in_url']) ? $post->bw_meta['logged_in_url'] : home_url($post->bw_meta['logged_in_url']);
        wp_redirect($url, 302);
    };
}
add_filter('template_redirect', 'logged_in_url_redirect_filter');


function register_theme_auto_product_post_types($post_types) {
    $post_types[] = 'campaigns';
    $post_types[] = 'usercampaigns';

    return $post_types;
}
add_filter('auto_product_post_types', 'register_theme_auto_product_post_types');


function sign_in_user($user_id) {
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    if($_POST['team_invite_expired']) {
        LmUserAdmin::add_user_prompt( 'User', 'danger', 'Invite Expired!');
    } elseif($_POST['team_invite']) {
        $invite = get_transient( $_POST['team_invite'] );
        if(!$invite) {
            LmUserAdmin::add_user_prompt( 'User', 'danger', 'Invite Expired!');
        } else {
            $message = "You have been invited to join team {$invite['name']}!";
            LmUserAdmin::add_user_prompt( 'User', 'info', $message, array('join_team' => $invite));
        }
    }
}
add_action('gform_user_registered', 'sign_in_user');

function __intercept_private_page( $posts, &$wp_query )
{
    // remove filter now, so that on subsequent post querying we don't get involved!
    remove_filter( 'the_posts', '__intercept_private_page', 5, 2 );

    if ( !( $wp_query->is_page && empty($posts) ) )
        return $posts; // bail, not page with no results

    // Check if *is* private
    if ( !empty( $wp_query->query['page_id'] ) )
        $page = get_post( $wp_query->query['page_id'] );
    else
        $page = get_page_by_path( $wp_query->query['pagename'] );

    if ( $page && $page->post_status == 'private') {
        $url = home_url('my-lovemanifest/');
        $url = add_query_arg(array('prompt' => 'sign-in'), $url);
        wp_redirect( $url, 302 );
        exit;
    }

    return $posts;
}
is_admin() || add_filter( 'the_posts', '__intercept_private_page', 5, 2 );

function add_qr_page_options() {
    $checkout_page = get_page_by_title('Checkout');
    $receipt_page = get_page_by_title('Receipt');
    $args = array(
        'label' => 'QR Page Options',
        'priority' => 'default',
        'limiters' => array(
            'post_type' => 'campaigns'
        ),
        'tabs' => false,
        'fields' => array(
            'qr_template' => array(
                'type' => 'select',
                'label' => 'QR Template',
                'tab' => 'QR Code Page',
                'options' => array(
                    "-1" => "Randomize",
                    "1" => "Template 1",
                    "2" => "Template 2",
                    "3" => "Template 3",
                    "4" => "Checkout"
                ),
                'limiters' => array(
                    'post_type' => 'lm_project',
                    'post_id' => "{$checkout_page->ID},{$receipt_page->ID}"
                )
            ),
            'qr_about_title' => array(
                'type' => 'text',
                'label' => 'About Title',
                'tab' => 'QR Code Page',
                'limiters' => array(
                    'post_type' => 'lm_project',
                    'post_id' => "{$checkout_page->ID},{$receipt_page->ID}"
                )
            ),
            'qr_about_content' => array(
                'type' => 'editor',
                'label' => 'About Content',
                'tab' => 'QR Code Page',
                'limiters' => array(
                    'post_type' => 'lm_project',
                    'post_id' => "{$checkout_page->ID},{$receipt_page->ID}"
                )
            ),
            'qr_classes' => array(
                'type' => 'text',
                'label' => 'Optional HTML Classes',
                'tab' => 'QR Code Page',
                'category' => 'blank',
                'limiters' => array(
                    'post_type' => 'lm_project',
                    'post_id' => "{$checkout_page->ID},{$receipt_page->ID}"
                )
            )
        )
    );
    add_bw_metabox('qr_page_options', $args);
}
add_action( 'bw_metabox_init', 'add_qr_page_options');

//  region Login Page Customizations
    function lm_login_logo_url() {
        return home_url('my-lovemanifest/');
    }
    add_filter( 'login_headerurl', 'lm_login_logo_url' );

    function my_login_logo_url_title() {
        return 'myLoveManifest';
    }
    add_filter( 'login_headertitle', 'my_login_logo_url_title' );
    function my_login_logo() { ?>
        <style type="text/css">
            #login h1 a {
                height: 100px;
                padding-bottom: 0px;
                background-image: url(https://www.lovemanifest.org/wp-content/uploads/2014/01/tree-medium.png);
                background-size: 80px 100px;
            }
        </style>
    <?php }
    add_action( 'login_enqueue_scripts', 'my_login_logo' );
//  endregion