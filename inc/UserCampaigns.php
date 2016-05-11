<?php
class UserCampaigns {

	public static $name = 'usercampaigns';
	public static $labels = array(
		'name' => 'User Campaigns',
		'singular_name' => 'User Campaign' ,
		'add_new_item' => 'Add New User Campaign'
	);
	public static $public = true;
	public static $has_archive = false;
	public static $show_ui = true;
	public static $menu_position = 21;
	public static $hierarchical = false;
    public static $capability_type = 'page';
	public static $supports = array (
		'title',
		//'thumbnail',
		'editor'
        //'excerpt'
	);

    public static $campaign_types = array(
        'single' => 'Individual',
        'group' => 'Team'
    );

    public static $campaign_methods = array(
        'occasion' => 'Donate An Occasion',
        'activity' => 'Be Athletic',
        'idea' => 'Do it Your Way'
    );
	
	public static function init() {
		self::post_type();

        add_filter('lm_ajax_assignments', array(__CLASS__,'ajax_assignments'));

		add_action('bw_metabox_init', array(__CLASS__, 'add_metabox_data'));
	}

    public function current_user_campaigns() {

    }

    public function ajax_assignments($actions) {
        $actions['add-new-campaign'] = array(__CLASS__, 'add_new_campaign');

        return $actions;
    }

    public static function add_new_campaign($data) {
        if(is_user_logged_in()) {
            $post  = array();

            $user = wp_get_current_user();

            $post['post_title'] = $data['title'];
            $post['post_type'] = self::$name;
            $post['post_author'] = $user->data->ID;
            $post['post_status'] = 'publish';

            $id = wp_insert_post( $post );

            add_post_meta($id, '_campaign_id', $data['campaign'], true);
            add_post_meta($id, '_campaign_type', $data['type'], true);
            add_post_meta($id, '_campaign_status', 'pending', true);

            return $id;
        }
    }
	
	public function add_metabox_data() {
		$campaign_details = array(
			'label' => 'Campaign Details',
			'position' => 'normal',
			'priority' => 'default',
			'tabs' => false,
			'limiters' => array(
				'post_type' => self::$name
			),
			'fields' => array(
                'campaign_id' => array(
                    'type' => 'text',
                    'label' => 'Sponsored Campaign ID',
                    'disabled' => true
                ),
                'campaign_goal' => array(
                    'type' => 'text',
                    'label' => 'Campaign Goal',
                    'default' => '0'
                ),
                'campaign_status' => array(
                    'type' => 'select',
                    'label' => 'Campaign Status',
                    'default' => 'pending',
                    'options' => array(
                        'pending' => 'Pending Setup',
                        'active' => 'Active',
                        'complete' => 'Completed'
                    )
                ),
				'campaign_type' => array(
					'type' => 'select',
                    'label' => 'Type',
                    'options' => self::$campaign_types
				),
                'campaign_method' => array(
                    'type' => 'select',
                    'label' => 'Method',
                    'options' => self::$campaign_methods
                ),
                'campaign_team' => array(
                    'type' => 'text',
                    'label' => 'Team'
                ),
                'notification'=> array(
                    'type' => 'on_off',
                    'label' => 'notification',
                    'default' => 'true'
                )
			)
		);

		add_bw_metabox(
			'campaign_details',
            $campaign_details
		);
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
}

UserCampaigns::init();