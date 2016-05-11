<?php
class Lm_User_Admin {

    function ajax_assignments($actions) {
        $actions['get-user-info'] = array(__CLASS__, 'get_user_info');
        $actions['get-user-teams'] = array(__CLASS__, 'get_user_teams');
        $actions['get-user-campaign'] = array(__CLASS__, 'get_user_campaign');
        $actions['save-user-campaign'] = array(__CLASS__, 'save_user_campaign');
        $actions['get-user-campaigns'] = array(__CLASS__, 'get_user_campaigns');

        return $actions;
    }

    function get_user_info($data) {
        global $lmuserdata;
        $user_data = (Array)wp_get_current_user()->data;

        $user_data['earning_total'] = $lmuserdata->get_campaign_earning_total();
        $user_data['donation_total'] = UserOrderMangement::get_user_order_total();
        $user_data['teams'] = $lmuserdata->get_campaign_teams();

        return $user_data;
    }

    function get_user_campaign($data) {
        global $lmuserdata;
        return $lmuserdata->get_campaign($data);
    }

    function save_user_campaign($data) {
        global $lmuserdata;
        $user_campaign = stripslashes($data['user_campaign']);
        $user_campaign = json_decode($user_campaign, true);

        $success = $lmuserdata->save_campaign($user_campaign);

        return array('redirect' => $user_campaign['url']);
    }

    function get_user_campaigns($data) {
        global $lmuserdata;
        return $lmuserdata->get_campaigns();
    }

    function interface_output($attr, $content, $tag) {

        $enqueue_fn_name = str_replace(' ', '_', $tag) . '_enqueue';
        self::$enqueue_fn_name();

        $file_name = str_replace(' ', '-', $tag).'.html';
        $html_file = dirname(__FILE__) . "/html/{$file_name}";



        if(file_exists($html_file)) {
            $html = file_get_contents($html_file);
        }

        return $html;

    }

    function setup_campaign_enqueue() {

        static $queued = false;

        if(!$queued) {
            $queued = true;
            wp_enqueue_style(
                'setup-campaign'
            );
            wp_enqueue_script(
                array(
                    'urlquery',
                    'angularjs',
                    'angularjs-resource',
                    'angularjs-route',
                    'angularjs-animate',
                    'angularjs-module-user-services',
                    'angularjs-module-user-filters',
                    'angularjs-ctrl-setup-campaign',
                    'angularjs-text-sanitize',
                    'angularjs-text'
                ),
                false
            );
        }

    }

    function user_admin_enqueue() {

        static $queued = false;

        if(!$queued) {
            $queued = true;
            wp_enqueue_style(
                'user-admin'
            );
            wp_enqueue_script(
                array(
                    'angularjs',
                    'angularjs-resource',
                    'angularjs-route',
                    'angularjs-animate',
                    'angularjs-module-user-services',
                    'angularjs-module-user-filters',
                    'angularjs-ctrl-user-admin'
                ),
                false
            );
        }

    }

}


add_filter('lm_ajax_assignments', array('Lm_User_Admin','ajax_assignments'));
add_shortcode('user admin', array('Lm_User_Admin','interface_output'));
add_shortcode('setup campaign', array('Lm_User_Admin','interface_output'));