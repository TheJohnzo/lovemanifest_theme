<?php
/**
 * User: anthonybrown
 * Date: 2/28/14
 */

global $lmuserdata;

class UserCampaignData {

    var $donations = 0;
    var $campaigns = NULL;
    var $user_campaigns = NULL;
    var $campaign_earnings = NULL;

    function __construct() {

    }

    function set_campaigns() {
        $this->campaigns = get_posts( array(
            'post_type' => 'usercampaigns',
            'post_status' => 'publish',
            'posts_per_page'   => -1,
            'author' => get_current_user_id()
        ) );
    }

    function get_campaign($data) {
        $user_id = get_current_user_id();
        $campaign = get_post($data['id']);

        if(!$campaign) return new WP_Error('user_error', 'Sorry! The campaign you are looking for doesn\'t exist.' );
        if($campaign->post_author != $user_id) return new WP_Error('user_error', 'Sorry! This campaign doesn\'t belong to you' );

        bw_meta($campaign);

        $user_campaign = array();

        $user_campaign['id'] = $campaign->ID;
        $user_campaign['author'] = $campaign->post_author;
        $user_campaign['url'] = get_permalink($campaign->ID);
        $user_campaign['title'] = $campaign->post_title;
        $user_campaign['description'] = $campaign->post_content;
        $user_campaign['team'] = (string)$campaign->bw_meta['campaign_team'];
        $user_campaign['campaign'] = $campaign->bw_meta['campaign_id'];
        $user_campaign['campaign_title'] = get_the_title($campaign->bw_meta['campaign_id']);
        $user_campaign['type'] = $campaign->bw_meta['campaign_type'];
        if(class_exists('UserCampaigns')) $user_campaign['types_readable'] = UserCampaigns::$campaign_types;
        $user_campaign['method'] = $campaign->bw_meta['campaign_method'];
        if(class_exists('UserCampaigns')) $user_campaign['methods_readable'] = UserCampaigns::$campaign_methods;
        $user_campaign['status'] = $campaign->bw_meta['campaign_status'];
        $user_campaign['completion'] = $this->get_earnings($campaign->ID);
        $user_campaign['completion_percentage'] = $this->get_completion_percentage($campaign->ID);
        $user_campaign['goal'] = (float)$campaign->bw_meta['campaign_goal'];
        $user_campaign['notification'] = get_post_meta($campaign->ID, '_notification', true);
        $user_campaign['image'] = self::get_campaign_image('banner', $user_campaign['campaign'], $campaign->ID);
        $user_campaign['user_is_leader'] = $user_campaign['team'] ? self::is_campaign_leader($user_campaign['team']) : false;

        return $user_campaign;
    }

    function save_campaign($user_campaign) {

        $campaign = array(
            'ID' => $user_campaign['id'],
            'post_title' => $user_campaign['title'],
            'post_content' => $user_campaign['description']
        );

        wp_update_post($campaign);

        if($user_campaign['type'] == 'group') {
            self::add_campaign_team($user_campaign['team']);
            update_post_meta($user_campaign['id'], '_campaign_team', $user_campaign['team']);
        }
        update_post_meta($user_campaign['id'], '_campaign_id', $user_campaign['campaign']);
        update_post_meta($user_campaign['id'], '_campaign_type', $user_campaign['type']);
        update_post_meta($user_campaign['id'], '_campaign_method', $user_campaign['method']);
        update_post_meta($user_campaign['id'], '_campaign_status', $user_campaign['status']);
        update_post_meta($user_campaign['id'], '_campaign_goal', $user_campaign['goal']);

        return 1;

    }

    function delete_campaign($id) {
        $user_id = get_current_user_id();
        $campaign = get_post($id);

        //  Don't let user's delete other's posts
        if($campaign->post_author != $user_id) return new WP_Error('user_error', 'You can\'t delete someone  else\'s campaign!' );

        wp_delete_post($id);

        return array('redirect' => home_url('create-a-new-campaign/'));
    }

    function get_campaign_image($size, $id, $user_page_id = 0) {
        $image = "";

        // Display Image
        if($user_page_id && $img_id = get_post_thumbnail_id($user_page_id)) {
            list($img_src) = wp_get_attachment_image_src($img_id, $size);
            $image = $img_src;
        } elseif($img_id = get_post_thumbnail_id($id)){
            list($img_src) = wp_get_attachment_image_src($img_id, $size);
            $image = $img_src;
        }

        return $image;

    }

    function get_campaigns() {

        // Initialize $campaign variable
        if(is_null($this->campaigns)) $this->set_campaigns();

        // Initialize $user_campaigns variable
        if(is_null($this->user_campaigns))  {
            $this->user_campaigns = (Array)$this->user_campaigns;

            foreach($this->campaigns as $campaign) {

                bw_meta($campaign);

                $user_campaign = array();

                $user_campaign['id'] = $campaign->ID;
                $user_campaign['url'] = get_permalink($campaign->ID);
                $user_campaign['title'] = $campaign->post_title;
                $user_campaign['description'] = $campaign->post_content;
                $user_campaign['team'] = $campaign->bw_meta['campaign_team'];
                $user_campaign['campaign'] = $campaign->bw_meta['campaign_id'];
                $user_campaign['campaign_title'] = get_the_title($campaign->bw_meta['campaign_id']);
                $user_campaign['type'] = $campaign->bw_meta['campaign_type'];
                if(class_exists('UserCampaigns')) $user_campaign['types_readable'] = UserCampaigns::$campaign_types;
                $user_campaign['method'] = $campaign->bw_meta['campaign_method'];
                if(class_exists('UserCampaigns')) $user_campaign['methods_readable'] = UserCampaigns::$campaign_methods;
                $user_campaign['status'] = $campaign->bw_meta['campaign_status'];
                $user_campaign['completion'] = $this->get_earnings($campaign->ID);
                $user_campaign['completion_percentage'] = $this->get_completion_percentage($campaign->ID);
                $user_campaign['goal'] = (float)$campaign->bw_meta['campaign_goal'];
                $user_campaign['notification'] = get_post_meta($campaign->ID, '_notification', true);
                $user_campaign['image'] = self::get_campaign_image('banner', $user_campaign['campaign'], $campaign->ID);
                $user_campaign['user_is_leader'] = $user_campaign['team'] ? self::is_campaign_leader($user_campaign['team']) : false;

                $this->user_campaigns[] = $user_campaign;
            }
        }

        // Fixes type return property to angularjs
        //if(!$this->user_campaigns) $this->user_campaigns = array(0 => 'empty');

        return $this->user_campaigns;
    }

    function sanitize_team_name($name) {
        $name = preg_replace("/\W/", "", $name);
        $name = strtolower($name);
        $name = trim($name);
        $name = str_replace(" ", "_", $name);
        return $name;
    }

    function get_team_leads() {

        $team_lead_ids = self::get_team_lead_ids();
        $team_lead_names =  self::get_team_names($team_lead_ids);

        $teams = array_combine($team_lead_ids, $team_lead_names);

        return $teams ? $teams : new stdClass();

    }

    function get_team_lead_ids($user_id = null) {
        if(is_null($user_id)) $user_id = get_current_user_id();
//        var_dump($user_id);
        return get_user_meta($user_id, 'campaign_team_leader', false);
    }


    function get_team_names($team_ids) {
        global $wpdb;

        $team_id_str = implode(",", $team_ids);
        $team_id_str = str_replace($team_ids, '%s', $team_id_str);

        $query = "
            SELECT option_value
            FROM $wpdb->options
            WHERE option_name IN ($team_id_str)
        ";

        $query = $wpdb->prepare($query, $team_ids);

        return $wpdb->get_col($query);
    }

    function add_campaign_leader($team_id, $user_id = 0) {
        if($user_id && !self::is_campaign_leader($team_id)) return false;

        if(!$user_id) $user_id = get_current_user_id();
        add_user_meta($user_id, 'campaign_team_leader', $team_id, false);
        return true;
    }

    function is_campaign_leader($team_id, $user_id = null) {
        $team_leads = self::get_team_lead_ids($user_id);
//        echo "Team Leads:$team_id\n";
//        print_r($team_leads);
        return in_array($team_id, $team_leads);
    }

    function is_only_leader($team_id) {
        global $wpdb;

        $query = "
            SELECT COUNT(*)
            FROM $wpdb->usermeta
            WHERE meta_key = 'campaign_team_leader'
              AND meta_value = %s
        ";

        $query = $wpdb->prepare($query, $team_id);

        $result = $wpdb->get_var($query);

        return is_null($result) || (int)$result < 2;
    }

    function remove_leader($team_id) {
        if(!self::is_campaign_leader($team_id)) return new WP_Error('remove_from_team', 'Weird, we didn\'t find you as a leader on that team');

        if(self::is_only_leader($team_id)) return new WP_Error('remove_from_team', 'You are the only leader! Please use the Team Management tools below to either promote another user or disband the team.');

        delete_user_meta( get_current_user_id(), 'campaign_team_leader', $team_id );
        return $team_id;
    }

    function get_teams() {

        $team_ids = self::get_team_ids();
        $teams = array();

        foreach($team_ids as $id) {
            $teams[$id] = get_option($id);
        }

        return $teams ? $teams : new stdClass();

    }

    function get_team_ids() {
        return get_user_meta(get_current_user_id(), 'campaign_team', false);
    }

    function new_team($team_name) {
        if( self::campaign_team_name_exists($team_name) ) return new WP_Error('create_team', 'Sorry! That that team name is already in use');

        $team_id = uniqid('team_');
        add_option($team_id, $team_name);

        self::add_campaign_team($team_id);
        self::add_campaign_leader($team_id);

        return array( "$team_id" => $team_name );
    }

    function add_campaign_team($team_id) {
        if(!self::in_campaign_team($team_id) && ($name = get_option($team_id))) {
            add_user_meta(get_current_user_id(), 'campaign_team', $team_id, false);

            $teams = array();
            $teams[$team_id] = $name;

            return $teams;
        }
    }

    function remove_user_from_team($team_id) {
        if(self::is_campaign_leader( $team_id )) {
            $remove_lead = self::remove_leader( $team_id );
            if(is_wp_error($remove_lead))  return $remove_lead;
        }

        if(!self::in_campaign_team($team_id)) return new WP_Error('remove_team', 'Weird, we didn\'t find that campaign');

        delete_user_meta( get_current_user_id(), 'campaign_team', $team_id );
        self::maybe_remove_team($team_id);

        return $team_id;
    }

    function maybe_remove_team($team_id) {
        if( !self::team_has_members($team_id) ) {
            delete_option($team_id);
        };
    }

    function team_has_members($team_id) {
        global $wpdb;

        $query = "
            SELECT COUNT(*)
            FROM $wpdb->usermeta
            WHERE meta_key = 'campaign_team'
              AND meta_value = %s
        ";

        $query = $wpdb->prepare($query, $team_id);

        $result = $wpdb->get_var($query);

        return !is_null($result) && $result != '0';
    }

    function get_team_members($team_id) {
        global $wpdb;

        $query = "
            SELECT user_id
            FROM $wpdb->usermeta
            WHERE meta_key = 'campaign_team'
              AND meta_value = %s
        ";

        $query = $wpdb->prepare($query, $team_id);
        $user_ids = $wpdb->get_col($query);

        $members = array();
        foreach($user_ids as $user_id) {
            $user_data = get_userdata($user_id);
            $members[$user_id] = array(
                'name' => $user_data->first_name . ' ' . $user_data->last_name,
                'is_leader' => self::is_campaign_leader($team_id, $user_id)
            );
        }

        return $members;
    }

    function get_team_member_leader_ids($team_id) {
        global $wpdb;

        $query = "
            SELECT user_id
            FROM $wpdb->usermeta
            WHERE meta_key = 'campaign_team_leader'
              AND meta_value = %s
        ";

        $query = $wpdb->prepare($query, $team_id);

        return $wpdb->get_col($query);
    }

    function in_campaign_team($team_id) {
        $team_ids = self::get_team_ids();
        return in_array($team_id, $team_ids);
    }

    function get_team_campaigns($team_id) {
        $args = array(
            'post_type' => 'usercampaigns',
            'post_status' => 'publish',
            'posts_per_page'   => -1,
            'meta_key' => '_campaign_team',
            'meta_value' => $team_id
        );

        $team_campaign_posts = get_posts( $args );
        $team_campaigns = array();

        foreach($team_campaign_posts as $campaign) {

            bw_meta($campaign);

            $user_campaign = array();

            $user_campaign['id'] = $campaign->ID;
            $user_campaign['url'] = get_permalink($campaign->ID);
            $user_campaign['title'] = $campaign->post_title;
            $user_campaign['description'] = $campaign->post_content;
            $user_campaign['team'] = $team_id;
            $user_campaign['campaign'] = $campaign->bw_meta['campaign_id'];
            $user_campaign['campaign_title'] = get_the_title($campaign->bw_meta['campaign_id']);
            $user_campaign['type'] = $campaign->bw_meta['campaign_type'];
            if(class_exists('UserCampaigns')) $user_campaign['types_readable'] = UserCampaigns::$campaign_types;
            $user_campaign['method'] = $campaign->bw_meta['campaign_method'];
            if(class_exists('UserCampaigns')) $user_campaign['methods_readable'] = UserCampaigns::$campaign_methods;
            $user_campaign['status'] = $campaign->bw_meta['campaign_status'];
            $user_campaign['completion'] = $this->get_earnings($campaign->ID);
            $user_campaign['completion_percentage'] = $this->get_completion_percentage($campaign->ID);
            $user_campaign['goal'] = (float)$campaign->bw_meta['campaign_goal'];
            $user_campaign['notification'] = get_post_meta($campaign->ID, '_notification', true);
            $user_campaign['image'] = self::get_campaign_image('banner', $user_campaign['campaign'], $campaign->ID);
            $user_campaign['user_is_leader'] = self::is_campaign_leader($team_id);

            $team_campaigns[] = $user_campaign;
        }

        return $team_campaigns;
    }

    function campaign_team_name_exists($name) {
        global $wpdb;

        $query = "
            SELECT COUNT(*)
            FROM $wpdb->options
            WHERE option_name LIKE 'team_%%'
              AND option_value = %s
        ";

        $query = $wpdb->prepare($query, $name);

        $result = $wpdb->get_var( $query );

        return !is_null($result) && $result != '0';
    }

    function disband_team($team_id) {
        if(!self::is_campaign_leader( $team_id )) return new WP_Error('disband_team', 'Sorry! But you don\'t have permission to disband this team.');

        global $wpdb;

        $query = "
            DELETE
            FROM $wpdb->usermeta
            WHERE meta_key IN ('campaign_team', 'campaign_team_leader')
              AND meta_value = %s
        ";

        $query = $wpdb->prepare($query, $team_id);

        $wpdb->query($query);

        delete_option($team_id);

        return $team_id;

    }

    function get_earnings($post_ID) {
        if(!class_exists('Cart66Product')) return 0;

        static $cache = array();

        if(!isset($cache[$post_ID])) {
            $item_number = ProductManagement::get_item_number($post_ID);

            $product = new Cart66Product();
            $product->loadByItemNumber($item_number);
            //$sales .= $product->getSalesTotal();
            $sales_totals = floatval($product->getIncomeTotal());

            $cache[$post_ID] = $sales_totals ? $sales_totals : 0;
        }

        return $cache[$post_ID];
    }

    function get_completion_percentage($id) {
        $percentage = 0;
        $goal = (float)preg_replace("/[$,]/", '', get_post_meta($id, '_campaign_goal', true));
        if($goal) {
            $percentage = round($this->get_earnings($id)/$goal, 4);
        }
        return $percentage;
    }


    function get_campaign_earning_total() {

        if(is_null($this->campaign_earnings)) {
            $this->campaign_earnings = 0;

            // Initialize $campaign variable
            if(is_null($this->campaigns)) $this->set_campaigns();

            foreach($this->campaigns as $campaign) {
                $this->campaign_earnings += $this->get_earnings($campaign->ID);
            }
        }

        return $this->campaign_earnings;
    }
}

$lmuserdata = new UserCampaignData();