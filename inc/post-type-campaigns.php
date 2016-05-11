<?php
class LM_Campaigns {

	public static $name = 'campaigns';
	public static $labels = array(
		'name' => 'Campaigns',
		'singular_name' => 'Campaign' ,
		'add_new_item' => 'Add New Campaign'
	);
	public static $public = true;
	public static $has_archive = false;
	public static $show_ui = true;
	public static $menu_position = 20;
	public static $hierarchical = false;
    public static $capability_type = 'page';
	public static $supports = array (
		'title',
		'thumbnail',
		'editor',
        'excerpt'
	);

    public static  $campaigns = false;
	
	public static function init() {
		self::post_type();

        //add_filter('lm_ajax_assignments', array(__CLASS__,'ajax_assignments'));

        add_shortcode('campaigns', array(__CLASS__, 'list_campaigns'));
        add_shortcode('user campaign selection', array(__CLASS__, 'user_campaign_select'));

		add_action('bw_metabox_init', array(__CLASS__, 'add_metabox_data'));
		//$this->taxonomies();
	}

    public function ajax_assignments($actions) {
        $actions['add-new-campaign'] = array(__CLASS__, 'add_new_campaign');

        return $actions;
    }

    public function add_new_campaign($data) {
        if(is_user_logged_in()) {
            $post  = array();

            $user = wp_get_current_user();

            $post['post_type'] = self::$name;
            $post['post_author'] = $user->data->ID;

            wp_insert_post( $post );
        }
    }
	
	public function add_metabox_data() {
        global $wpdb;
		$donation_details = array(
			'label' => 'What Happens With The Donation?',
			'position' => 'normal',
			'priority' => 'default',
			'tabs' => false,
			'limiters' => array(
				'post_type' => self::$name
			),
			'fields' => array(
				'donation_description' => array(
					'type' => 'textarea',
					'label' => false
				)
			)
		);
		add_bw_metabox(
			'donation_details',
            $donation_details
		);

        /*$product_table = $wpdb->prefix . "cart66_products";
        $products_result = $wpdb->get_results("SELECT id, name FROM {$product_table}");

        $product_numbers_list = array();
        foreach($products_result as $result) {
            $product_numbers_list[$result->id] = $result->name;
        }

        $product_information = array(
            'label' => 'Shopping Cart Information',
            'position' => 'side',
            'priority' => 'default',
            'tabs' => false,
            'limiters' => array(
                'post_type' => self::$name
            ),
            'fields' => array(
                'product_item_number' => array(
                    'type' => 'select',
                    'label' => 'Shopping Cart Item',
                    'options' => $product_numbers_list
                )
            )
        );
        add_bw_metabox(
            'shoping_cart',
            $product_information
        );*/
	}

	private static function post_type() {
		register_post_type( self::$name,
			array(
				'labels' => self::$labels,
				'public' => self::$public,
				'has_archive' => self::$has_archive,
				'show_ui' => self::$show_ui,
				'menu_position' => self::$menu_position,
				'hierarchical' => self::$hierarchical,
				'supports' => self::$supports,
                'capability_type' => self::$capability_type
			)
		);
	}

	private function make_tax_labels($p, $s) {
		return array(  
			'name'                          => __( "$p", 'wcd' ),  
			'singular_name'                 => __( "$s", 'wcd' ),  
			'search_items'                  => __( "Search $p", 'wcd' ),  
			'popular_items'                 => __( "Popular $p", 'wcd' ),  
			'all_items'                     => __( "All $p", 'wcd' ),  
			'parent_item'                   => __( "Parent $s", 'wcd' ),  
			'edit_item'                     => __( "Edit $s", 'wcd' ),  
			'update_item'                   => __( "Update $s", 'wcd' ),  
			'add_new_item'                  => __( "Add New $s", 'wcd' ),  
			'new_item_name'                 => __( "New $s", 'wcd' ),  
			'separate_items_with_commas'    => __( "Separate $p with commas", 'wcd' ),  
			'add_or_remove_items'           => __( "Add or remove $p", 'wcd' ),  
			'choose_from_most_used'         => __( "Choose from most used $p", 'wcd' )  
		);
	}

	private function new_tax($id, $post_type, $p, $s, $r = NULL) {
		$labels = $this->make_tax_labels($p, $s);

		$args = array(
				'labels'                        => $labels,
				'public'                        => false,
				'hierarchical'                  => false,
				'show_ui'                       => true,
				'show_in_nav_menus'             => true,
				'query_var'                     => true
        );

		if(!is_null($r)) $args['rewrite'] = array('slug' => $r);

		register_taxonomy( $id, $post_type, $args );
	}

	private function taxonomies() {

	}

    function user_campaign_select($atts) {

        if(self::$campaigns === false) self::$campaigns = get_posts(array('post_type' => self::$name, 'posts_per_page'   => -1));

        $user = wp_get_current_user();

        $output = "<div id='campaign-user-selection' class='row'>";
        $output .= "<form action='/setup-campaign'>";

        foreach(self::$campaigns as $i => $campaign) {
            if($img_id = get_post_thumbnail_id($campaign->ID)) {
                list($img_src, $img_width, $img_height) = wp_get_attachment_image_src($img_id, 'full');
                $styles = "background-image: url({$img_src});";
            }

            $permalink = get_permalink($campaign->ID);

            $classes = array('campaign', 'col-md-4');
            $class_str = implode(' ', $classes);
            $campaign_data_str = "data-campaign='{$campaign->ID}' data-campaign-title='{$campaign->post_title}'";

            $excerpt = $campaign->post_excerpt ? "" : "<p>{$campaign->post_excerpt}</p>";

            $output .= "
                <div id='campaign-user-select-{$campaign->ID}' class='{$class_str}'>
                    <div class='action-block' data-ratio='390:270' style='{$styles}' data-ratio-valign='.campaign-description'>
                        <div class='campaign-description'>
                            <h2>{$campaign->post_title}</h2>
                            {$excerpt}
                        </div>
                        <div class='show-action'>
                            <a href='{$permalink}' class='blue-block btn'><i class='fa fa-info'></i> Learn More</a>
                            <button class='red-block btn' {$campaign_data_str} data-toggle='modal' data-target='#submit-confirmation'><i class='fa fa-flag'></i> Start Campaign</button>
                        </div>
                    </div>
                </div>
            ";
        }

        $output .= "<input name='campaign' value='' type='hidden' />";
        $output .= "<input name='type' value='' type='hidden' />";
        $output .= "<input name='action' value='add-new-campaign' type='hidden' />";

        $title_tooltip_image = get_bloginfo('template_directory') . "/img/assets/tips/customize-title.png";

        $output .= "
            <input name='campaign' value='' type='hidden' />
            <input name='type' value='' type='hidden' />
            <input name='action' value='add-new-campaign' type='hidden' />

            <div class='modal fade' id='submit-confirmation'>
                <div class='modal-dialog'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>
                        </div>
                        <div class='modal-body text-center'>
                            <label for='title'>Campaign Title</label>
                            <div class='relative'>
                                <img id='title-tooltip' src='{$title_tooltip_image}' width='137' height='149' />
                                <input type='text' name='title' value='' class='form-control overlay-control-blue' />
                            </div>
                            <label class='space-top'>Individual or Team?</label>
                            <p><small>Choose to campaign by yourself or together with group of other members</small></p>
                            <div class='row space-top'>
                                <div class='col-xs-6'>
                                    <button type='button' data-type='single' class='circle'><i class='fa fa-user'></i><span class='text'>Individual</span></button>
                                </div>
                                <div class='col-xs-6'>
                                    <button type='button' data-type='group' class='circle'><i class='fa fa-users'></i><span class='text'>Team</span></button>
                                </div>
                            </div>
                            <div class='modal-footer'></div>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
        ";

        $output .= "</form>";
        $output .= "</div>";

        $js_file = dirname(__FILE__) . "/inline-js/user-campaign-selection.min.js";
        if(file_exists($js_file)) {
            $script = "var display_name = '{$user->display_name}';";
            $script .= file_get_contents($js_file);
        }

        $output .= "<script>{$script}</script>";

        return $output;

    }

    function list_campaigns($atts) {

        extract( shortcode_atts( array(
            'type' => "image list"
        ), $atts ) );

        if(self::$campaigns === false) self::$campaigns = get_posts(array('post_type' => self::$name, 'posts_per_page'   => -1));

        $before_list = "";
        $after_list = "";
        $count = count(self::$campaigns);
        $wrapper_classes = array();


        $wrapper_classes[] = $type == 'about donations box' ? 'container' : 'row';

        foreach(self::$campaigns as $i => $campaign) {
            $styles = "";
            $before = "";
            $after = "";
            $id = "";
            $classes = array();
            $skip = false;
            $description = false;

            $permalink = get_permalink($campaign->ID);
            bw_meta($campaign);

            switch($type) {
                case 'image list':
                    $description = true;
                    if($img_id = get_post_thumbnail_id($campaign->ID)) {
                        list($img_src, $img_width, $img_height) = wp_get_attachment_image_src($img_id, 'full');
                        $styles = "background-image: url({$img_src});";
                    }

                    $classes = array('campaign', 'col-md-4');

                    $before = "<a class='action-block' href='{$permalink}' data-ratio='390:270' style='{$styles}' data-ratio-valign='.campaign-description'>";
                    $after = "</a>";

                    break;
                case 'donation box':
                    $description = true;
                    $classes = array('campaign-donation');

                    if(!$after_list) $after_list = "<select id='campaign-donation-select'>";
                    $after_list .= "<option value='{$i}'>{$campaign->post_title}</option>";
                    if($i == ($count-1)) $after_list .= "</select>";

                    $after = "<form>";
                    $after .= "<input data-prefix='$ ' class='col-md-4' type='text' name='donation' value='5.00' />";
                    $after .= "<input type='hidden' name='campaign_id' value='{$campaign->ID}' />";
                    $after .= "<a href='{$permalink}'>Learn More About {$campaign->post_title} <i class='fa fa-external-link'></i></a>";
                    $after .= "<button class='btn-primary'><i class='fa fa-heart'></i> Donate to {$campaign->post_title}</button>";
                    $after .= "</form>";

                    break;
                case 'about donations box':
                    if(!$campaign->bw_meta['donation_description']) { $skip = true; }

                    $styles = '';

                    if(!$before_list) $before_list = "<div id='about-donations'><button class='blue-block prev'><i class='fa fa-arrow-circle-left'></i></button><button class='blue-block next'><i class='fa fa-arrow-circle-right'></i></button><div class='row'>";

                    if(!$skip) {
                        $id = "about-donation-{$i}";
                        if($img_id = get_post_thumbnail_id($campaign->ID)) {
                            list($img_src, $img_width, $img_height) = wp_get_attachment_image_src($img_id, 'full');
                            $styles = "background-image: url({$img_src});";
                        }
                        $output .= $before;
                        $desc = '';
                        $desc .= "<div class='campaign-description'>";
                        $desc .= "    <h3>{$campaign->post_title}</h3>";
                        if($campaign->post_excerpt) $desc .= "    <p>{$campaign->post_excerpt}</p>";
                        $desc .= "</div>";
                        $before_list .= "<a href='#about-donation-{$i}' class='col-md-3 box-link'><div data-ratio='285:200' style='{$styles}' data-ratio-valign='.campaign-description'>{$desc}</div></a>";
                        $before_list .= "<div id='{$id}'>";$title = "    <h2>{$campaign->post_title}</h2>";
                        $before_list .= "<div class='campaign-donation-description col-md-9 center'>{$title}" . wpautop($campaign->bw_meta['donation_description']) . "</div>";
                        $before_list .= "</div>";
                    }

                    if($i == ($count-1)) {
                        $before_list .= "</div></div>";
                        $before_list .= "<div id='about-donations-placeholder' data-ratio='1080:400'>";
                        $after_list .= "</div>";
                    }

                    $skip = true;

                    break;
            }

            if($skip) continue;

            $class_str = implode(' ', $classes);
            $id_attr_str = $id ? "id='{$id}' " : '';
            $output .= "<div {$id_attr_str}class='{$class_str}'>";
            $output .= $before;
            if($description)
            {
                $output .= "<div class='campaign-description'>";
                $output .= "    <h2>{$campaign->post_title}</h2>";
                if($campaign->post_excerpt) $output .= "    <p>{$campaign->post_excerpt}</p>";
                $output .= "</div>";
            }
            $output .= $after;
            $output .= "</div>";
        }

        $wrapper_class_str = implode(' ', $wrapper_classes);
        $output = "<div id='campaign-". str_replace(' ', '-', $type) ."' class='{$wrapper_class_str}'>{$before_list}" . $output;
        $output .= $after_list;

        $output .= "</div>";


        return $output;
    }
}

LM_Campaigns::init();