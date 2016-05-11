<?php
/**
 * User: anthonybrown
 * Date: 2/28/14
 */

global $lmuserdata;

class LM_User_Campaign_Data {

    var $donations = '$400.1.0';
    var $campaigns = NULL;
    var $user_campaigns = NULL;
    var $campaign_earnings = NULL;

    function __construct() {

    }

    function set_campaigns() {
        $this->campaigns = get_posts( array(
            'post_type' => 'usercampaigns',
            'posts_per_page'   => -1,
            'author' => get_current_user_id()
        ) );
    }

    function get_campaign($data) {
        $campaign = get_post($data['id']);

        bw_meta($campaign);

        $user_campaign = array();

        $user_campaign['id'] = $campaign->ID;
        $user_campaign['url'] = get_permalink($campaign->ID);
        $user_campaign['title'] = $campaign->post_title;
        $user_campaign['description'] = $campaign->post_content;
        $user_campaign['team'] = (string)$campaign->bw_meta['campaign_team'];
        $user_campaign['campaign'] = $campaign->bw_meta['campaign_id'];
        $user_campaign['campaign_title'] = get_the_title($campaign->bw_meta['campaign_id']);
        $user_campaign['type'] = $campaign->bw_meta['campaign_type'];
        if(class_exists('LM_User_Campaigns')) $user_campaign['types_readable'] = LM_User_Campaigns::$campaign_types;
        $user_campaign['method'] = $campaign->bw_meta['campaign_method'];
        if(class_exists('LM_User_Campaigns')) $user_campaign['methods_readable'] = LM_User_Campaigns::$campaign_methods;
        $user_campaign['status'] = $campaign->bw_meta['campaign_status'];
        $user_campaign['completion'] = $this->get_completion($campaign->ID);
        $user_campaign['completion_percentage'] = $this->get_completion_percentage($campaign->ID);
        $user_campaign['goal'] = (float)$campaign->bw_meta['campaign_goal'];
        $user_campaign['notification'] = get_post_meta($campaign->ID, '_notification', true);

        // Display Image
        if($img_id = get_post_thumbnail_id($campaign->ID)) {
            list($img_src) = wp_get_attachment_image_src($img_id, 'full');
            $user_campaign['image'] = $img_src;
        } elseif($img_id = get_post_thumbnail_id($user_campaign['campaign'])){
            list($img_src) = wp_get_attachment_image_src($img_id, 'full');
            $user_campaign['image'] = $img_src;
        }

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
                if(class_exists('LM_User_Campaigns')) $user_campaign['types_readable'] = LM_User_Campaigns::$campaign_types;
                $user_campaign['method'] = $campaign->bw_meta['campaign_method'];
                if(class_exists('LM_User_Campaigns')) $user_campaign['methods_readable'] = LM_User_Campaigns::$campaign_methods;
                $user_campaign['status'] = $campaign->bw_meta['campaign_status'];
                $user_campaign['completion'] = $this->get_completion($campaign->ID);
                $user_campaign['completion_percentage'] = $this->get_completion_percentage($campaign->ID);
                $user_campaign['goal'] = (float)$campaign->bw_meta['campaign_goal'];
                $user_campaign['notification'] = get_post_meta($campaign->ID, '_notification', true);



                // Display Image
                if($img_id = get_post_thumbnail_id($campaign->ID)) {
                    list($img_src) = wp_get_attachment_image_src($img_id, 'full');
                    $user_campaign['image'] = $img_src;
                } elseif($img_id = get_post_thumbnail_id($user_campaign['campaign'])){
                    list($img_src) = wp_get_attachment_image_src($img_id, 'full');
                    $user_campaign['image'] = $img_src;
                }

                $this->user_campaigns[] = $user_campaign;
            }
        }

        // Fixes type return property to angularjs
        //if(!$this->user_campaigns) $this->user_campaigns = array(0 => 'empty');

        return $this->user_campaigns;
    }

    function get_campaign_teams() {
        static $teams;

        if(is_null($teams)) $teams = get_user_meta(get_current_user_id(), 'campaign_team', false);

        return $teams;
    }

    function add_campaign_team($team) {
        if(!self::has_campaign_team($team)) add_user_meta(get_current_user_id(), 'campaign_team', $team, false);
    }

    function has_campaign_team($team) {
        $teams = self::get_campaign_teams();
        return in_array($team, $teams);
    }

    function get_completion($id) {
        static $cache = array();

        if(!isset($cache[$id])) $cache[$id] = rand(0, (float)get_post_meta($id, '_campaign_goal', true));

        return $cache[$id];
    }

    function get_completion_percentage($id) {
        $percentage = 0;
        $goal = (float)get_post_meta($id, '_campaign_goal', true);
        if($goal) {
            $percentage = round($this->get_completion($id)/$goal, 4);
        }
        return $percentage;
    }


    function get_campaign_earning_total() {

        if(is_null($this->campaign_earnings)) {
            $this->campaign_earnings = 0;

            // Initialize $campaign variable
            if(is_null($this->campaigns)) $this->set_campaigns();

            foreach($this->campaigns as $campaign) {
                $this->campaign_earnings += $this->get_completion($campaign->ID);
            }
        }

        return $this->campaign_earnings;
    }

    function get_donation_total() {
        /* $campaigns = get_posts(array(
             'post_type' => 'usercampaigns',
             'posts_per_page'   => -1,
             'author' => get_current_user_id()
         ));

         $order_ids = array();
         foreach($campaigns as $campaign) {

         } */

        return $this->donations;
    }

}

$lmuserdata = new LM_User_Campaign_Data();