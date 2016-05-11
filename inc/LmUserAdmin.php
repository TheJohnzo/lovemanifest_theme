<?php
class LmUserAdmin {

    function ajax_assignments($actions) {

        $actions['get-user-application'] = array(__CLASS__, 'get_user_application');
        $actions['get-user-info'] = array(__CLASS__, 'get_user_info');
        $actions['save-user-info'] = array(__CLASS__, 'save_user_info');
        $actions['get-user-teams'] = array(__CLASS__, 'get_user_teams');
        $actions['add-user-campaign'] = array(__CLASS__, 'add_user_campaign');
        $actions['close-user-campaign'] = array(__CLASS__, 'close_user_campaign');
        $actions['get-user-campaign'] = array(__CLASS__, 'get_user_campaign');
        $actions['save-user-campaign'] = array(__CLASS__, 'save_user_campaign');
        $actions['delete-user-campaign'] = array(__CLASS__, 'delete_user_campaign');
        $actions['get-user-campaigns'] = array(__CLASS__, 'get_user_campaigns');

        $actions['create-new-team'] = array(__CLASS__, 'create_new_team');
        $actions['remove-from-team'] = array(__CLASS__, 'remove_from_team');
        $actions['disband-team'] = array(__CLASS__, 'disband_team');
        $actions['promote-team-member'] = array(__CLASS__, 'promote_team_member');
        $actions['team-to-manage'] = array(__CLASS__, 'team_to_manage');
        $actions['team-email-invites'] = array(__CLASS__, 'team_email_invites');

        $actions['dismiss-prompt'] = array(__CLASS__, 'dismiss_prompt');
        $actions['accept-prompt'] = array(__CLASS__, 'accept_prompt');


        return $actions;
    }

    function obj_to_array($thing, $recursive = true) {

        if(is_object($thing)) $thing = (array)$thing;

        if($recursive) {
            foreach($thing as &$prop) {
                if(is_object($prop)) $prop = (array)$prop;

                if(is_array($prop)) $prop = self::obj_to_array($prop);
            }
        }

        return $thing;
    }

    function add_user_prompt($code, $level, $message, $accept = null, $dismiss = null) {
        static $calls = 1;

        $prompt = array();

        $prompt[$code] = array (
            'id' => uniqid($code.$calls),
            'level' => $level,
            'message' => $message
        );

        if($accept) $prompt[$code]['accept'] = $accept;
        if($dismiss) $prompt[$code]['dismiss'] = $dismiss;

        add_user_meta(get_current_user_id(), 'admin_prompt', $prompt, false);

        $calls++;

        return $prompt[$code]['id'];
    }

    function get_user_prompts() {
        $prompts_array = get_user_meta(get_current_user_id(), 'admin_prompt', false);

        $user_prompts = array();

        foreach($prompts_array as $prompts) {
            foreach($prompts as $code => $prompt) {
                if(!isset($prompts[$code])) {
                    $user_prompts[$code] = array();
                }

                $user_prompts[$code][$prompt['id']] = $prompt;
            }

        }

        return $user_prompts ? $user_prompts : new stdClass();
    }

    function prompt_fix($prompt, $group) {
        $prompt_item = array();
        $prompt_item[$group] = self::obj_to_array($prompt);

        return $prompt_item;
    }

    function do_prompt_actions($actions) {
        $response = array();
        foreach($actions as $fn => $fn_data) {
            if(method_exists(__CLASS__, $fn)) {
                $response = array_merge( $response, self::$fn( (array)$fn_data ) );
            }
        }

        return $response;
    }

    function accept_prompt($data) {
        $prompt = self::prompt_fix( $data['prompt'], $data['prompt_group'] );

        $response = array();

        if(isset($prompt[$data['prompt_group']]['accept'])) {
            $response = array_merge($response, self::do_prompt_actions($prompt[$data['prompt_group']]['accept']));
        }

        delete_user_meta(get_current_user_id(), 'admin_prompt', $prompt);

        return array_merge($response, array( 'dismiss_prompt' =>  $data['prompt']->id, 'prompt' => $prompt ));
    }


    function dismiss_prompt($data) {
        $prompt = self::prompt_fix( $data['prompt'], $data['prompt_group'] );

        $response = array();

        if(isset($prompt[$data['prompt_group']]['dismiss'])) {
            array_merge($response, self::do_prompt_actions($prompt[$data['prompt_group']]['dismiss']));
        }

        delete_user_meta(get_current_user_id(), 'admin_prompt', $prompt);

        return array_merge($response, array( 'dismiss_prompt' =>   $data['prompt']->id));
    }


    function get_user_info($data) {
        global $lmuserdata;

        $user_id = get_current_user_id();
        $user_data_obj = get_userdata( $user_id );
        $user_data = (Array)$user_data_obj->data;

        unset( $user_data['user_pass'] );
        unset( $user_data['user_activation_key'] );

        $user_data['first_name'] = $user_data_obj->first_name;
        $user_data['last_name'] = $user_data_obj->last_name;

        $user_data['earning_total'] = $lmuserdata->get_campaign_earning_total();
        $user_data['donation_total'] = UserOrderMangement::get_user_order_total();
        $user_data['teams'] = $lmuserdata->get_teams();
        $user_data['team_leads'] = $lmuserdata->get_team_leads();

        $user_data['prompts'] = self::get_user_prompts();

        $user_data['settings'] = array(
            'first_name' => array(
                'label' => 'First',
                'type' => 'text',
                'size' => 6,
                ''
            ),
            'last_name' => array(
                'label' => 'Last',
                'type' => 'text',
                'size' => 6
            ),
            'user_email' => array(
                'label' => 'Email',
                'type' => 'email',
                'size' => 12
            )
        );

        return $user_data;
    }

    private function update_user_account_name($new_email) {
        global $current_user;

        if($new_email != $current_user->user_email) {

            if(email_exists( $new_email )) {
                ajax_error('field_user_email', 'Sorry! That email has already been registered to a user.');
                return false;
            }

            global $wpdb;

            $query = "
                    UPDATE $wpdb->users
                    SET user_login = %s, user_email = %s
                    WHERE ID = %d
                ";

            $query = $wpdb->prepare($query, $new_email, $new_email, $current_user->ID);

            $result = $wpdb->query($query);

            if(is_wp_error($result)) {
                ajax_error('usersection', 'Sorry! Something didn\'t work out quite right.');
                return false;
            }

            $old_email = $current_user->user_email;
            $current_user->user_email = $new_email;
            $current_user->user_login = $new_email;

            global $wp_object_cache;
            $wp_object_cache->cache['users'][$current_user->ID]->user_login = $current_user->user_email;
            $wp_object_cache->cache['users'][$current_user->ID]->user_email = $current_user->user_email;
            unset($wp_object_cache->cache['userlogins'][$old_email]);
            $wp_object_cache->cache['userlogins'][$current_user->user_email] = $current_user->ID;
            unset($wp_object_cache->cache['useremail'][$old_email]);
            $wp_object_cache->cache['useremail'][$current_user->user_email] = $current_user->ID;

            wp_set_current_user( $current_user->ID );
            wp_set_auth_cookie( $current_user->ID );
        }

        return false;
    }

    function save_user_info($data) {
        global $current_user;

        $user_data = $data['user'];

        if( $user_data->ID == $current_user->ID ) {

            $updated = array();

            unset( $user_data->settings->user_email );
            $email_update = self::update_user_account_name( $user_data->user_email );
            if($email_update) {
                $updated[] = 'user_email';
            }

            $update_user = false;
            foreach( $user_data->settings as $field => $settings ) {
                if( $user_data->$field == '') {
                    ajax_error("field_{$field}", 'This field is required');
                } elseif($user_data->$field != $current_user->$field) {
                    $update_user = true;
                    $current_user->$field = $user_data->$field;
                    $updated[] = $field;
                }
            }

            // Unset the encrypted password from the user object so it doesn't over write the existing one
            $update_user = $current_user;
            unset($update_user->data->user_pass);

            wp_update_user($update_user);

            if($updated) {
                return array('done' => true, 'updated' => $updated);
            } else {
                return array( 'done' => true );
            }

        }

        return new WP_Error('usersection', 'Sorry! Something didn\'t work out quite right.' );
    }


    function create_new_team($data) {
        global $lmuserdata;
        $new_team = $lmuserdata->new_team($data['name']);

        if(is_wp_error($new_team)) return $new_team;

        return array( 'add_teams' => $new_team, 'add_team_leads' => $new_team );
    }


    function disband_team($data) {
        global $lmuserdata;

        $disband_team = $lmuserdata->disband_team($data['team_id']);

        if(is_wp_error($disband_team)) return $disband_team;

        return array('remove_team' => $disband_team, 'remove_team_lead' => $disband_team);
    }

    function promote_team_member($data) {
        global $lmuserdata;

        if(!$lmuserdata->add_campaign_leader( $data['team_id'], $data['user_id'] )) {
            return new WP_Error('member_list', 'Sorry! But you don\'t have permission to promote users.');
        }

        return array('promote_user' => $data['user_id']);
    }


    function remove_from_team($data) {
        global $lmuserdata;
        $response = array();
        $is_leader = $lmuserdata->is_campaign_leader($data['team_id']);

        $remove_team = $lmuserdata->remove_user_from_team( $data['team_id'] );
        if(is_wp_error($remove_team)) {
            ajax_error($remove_team);
        } else {
            $response['remove_team'] = $remove_team;
            if( $is_leader ) {
                $response['remove_team_lead'] = $remove_team;
            }
        }

        return $response;
    }

    function team_to_manage($data) {
        global $lmuserdata;
        if(!$lmuserdata->in_campaign_team($data['team_id'])) return new WP_Error('leader_management', 'Sorry! But you are no longer a leader of this team');

        $info = array();
        $info['id'] = $data['team_id'];
        $info['name'] = get_option($info['id']);
        $info['members'] = $lmuserdata->get_team_members($data['team_id']);
        $info['leaders'] = $lmuserdata->get_team_member_leader_ids($data['team_id']);
        $info['user_is_leader'] = $lmuserdata->is_campaign_leader($data['team_id']);
        $info['campaigns'] = $lmuserdata->get_team_campaigns($data['team_id']);

        return array( 'team_to_manage' => $info );
    }

    function join_team($data) {
        if(get_transient($data['id'])) {
            global $lmuserdata;
            return array( 'add_teams' => $lmuserdata->add_campaign_team($data['team_id']) );
        }
    }

    function team_email_invites($data) {
        global $lmuserdata, $current_user;

        if(!$lmuserdata->is_campaign_leader($data['team_id'])) return new WP_Error('leader_management', 'Sorry! But you do not have permission to invite members');

        //  $message
        $home_url = home_url();
        $create_account_url = home_url('create-an-account/');
        $team_name = get_option($data['team_id']);

        $invite_id = uniqid( $data['team_id'] . 'invite' );
        $invite = array(
            'name' => $team_name,
            'team_id' => $data['team_id'],
            'id' => $invite_id
        );
        set_transient($invite_id, $invite, WEEK_IN_SECONDS);

        //  $to
        $emails = explode(',', $data['emails']);
        $member_emails = array();
        $non_member_emails = array();
        $response = array( 'emailed' => array() );

        $user_now = $current_user->ID;
        foreach($emails as $key => $email) {
            $email = trim($email);
            if(!is_email($email)) {
                ajax_error('member_invite', "{$email} is an invalid email.");
                continue;
            }

            $member = get_user_by( 'email', $email );
            if($member !== false) {
                $member_emails[] = $email;

                $message = "You have been invited to join team {$invite['name']}!";

                wp_set_current_user($member->ID);
                LmUserAdmin::add_user_prompt( 'User', 'info', $message, array('join_team' => $invite));

            } else {
                $non_member_emails[] = $email;
            }
        }

        if($member_emails) wp_set_current_user($user_now);


        //  $subject
        $subject = "{$current_user->first_name} {$current_user->last_name} Wants to Campaign With You!";

        //  $headers
        $headers = 'From:  LoveManifest Teams <noreply@lovemanifest.org>' . "\r\n";

        add_filter('wp_mail_content_type', create_function('', ' return "text/html"; '));

        if($member_emails) {
            $signin_url = home_url('my-account/');

            $member_message = "
                <h1>{$current_user->first_name} {$current_user->last_name} Wants You To Join Their Campaign Team!</h1>
                <p>{$current_user->first_name} is a member of a campaign team on <a href=\"{$home_url}\">LoveManifest</a>, and wants you to be a part of it.</p>
                <p>
                    <a
                        href=\"{$signin_url}\"
                        style=\"display: block; width: 300px; background: #338ff2; padding: 8px 15px; color: #FFFFFF; text-decoration: none;\">
                        Join {$current_user->first_name}'s team \"{$team_name}\" on LoveManifest
                    </a>
                </p>
                <p style=\"margin-top: 20px\">
                    <a href=\"{$home_url}\">
                        <img src=\"". home_url('wp-content/themes/lovemanifest/media/logo.png') ."\" width=\"161\" height=\"50\" alt=\"LoveManifest Logo\" />
                    </a>
                </p>
            ";

            wp_mail( $member_emails, $subject, $member_message, $headers );

            $response['emailed'] = array_merge($response['emailed'], $member_emails);
        }

        if($non_member_emails) {
            $invite_url = add_query_arg(array('invite' => $invite_id), $create_account_url);
            $non_member_message = "
                <h1>{$current_user->first_name} {$current_user->last_name} Wants You To Join Their Campaign Team!</h1>
                <p>{$current_user->first_name} is a member of a campaign team on <a href=\"{$home_url}\">LoveManifest</a>, and wants you to be a part of it.</p>
                <p>
                    <a
                        href=\"{$invite_url}\"
                        style=\"display: block; width: 300px; background: #338ff2; padding: 8px 15px; color: #FFFFFF; text-decoration: none;\">
                        Join {$current_user->first_name}'s team \"{$team_name}\" on LoveManifest
                    </a>
                </p>
                <p style=\"margin-top: 20px\">
                    <a href=\"{$home_url}\">
                        <img src=\"". home_url('wp-content/themes/lovemanifest/media/logo.png') ."\" width=\"161\" height=\"50\" alt=\"LoveManifest Logo\" />
                    </a>
                </p>
            ";

            wp_mail( $non_member_message, $subject, $non_member_message, $headers );

            $response['emailed'] = array_merge($response['emailed'], $non_member_emails);
        }

        if($response['emailed']) {
            $response['messages'] = array(
                'member_invite' => array(
                    'Emailed ' . implode(', ', $response['emailed'])
                )
            );
        }

        return $response;
    }

    public function add_user_campaign($data) {

        if(!is_user_logged_in()) return new WP_Error('add_campaign', 'Sorry! But you must be logged in to perform this action');

        global $lmuserdata;

        $data = self::obj_to_array($data);

        $campaign_post_id = UserCampaigns::add_new_campaign($data['campaign']);

        $campaign = $lmuserdata->get_campaign( array('id' => $campaign_post_id) );

        $campaign = array_merge($campaign, $data['campaign']);

        $lmuserdata->save_campaign($campaign);

        return array('add_campaign' => $campaign );
    }


    public function close_user_campaign($data) {

        if(!is_user_logged_in()) return new WP_Error('add_campaign', 'Sorry! But you must be logged in to perform this action');

        global $lmuserdata;

        $campaign = $lmuserdata->get_campaign(array('id' => $data['post_id']));

        if(is_wp_error($campaign)) return $campaign;

        $campaign['status'] = 'complete';

        $lmuserdata->save_campaign($campaign);

        return array('complete_campaign' => $campaign['id']);
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


    function delete_user_campaign($data) {
        global $lmuserdata;

        return $lmuserdata->delete_campaign($data['id']);
    }

    function get_user_campaigns($data) {
        global $lmuserdata;
        return $lmuserdata->get_campaigns();
    }

    function get_user_application() {
        global $current_user;

        $app = array();

        $app['links'] = self::get_user_links();
        $app['campaigns'] = Campaigns::get_campaigns();
        $app['campaign_types'] = UserCampaigns::$campaign_types;

        return $app;
    }

    function get_user_links() {

        $links = array();

        $links[] = array( 'title' => 'Dashboard', 'url' => home_url('my-account/'), 'icon' => 'tasks' );
        $links[] = array( 'title' => 'User Settings', 'url' => home_url('my-account/settings/'), 'icon' => 'cog' );
        $links[] = array( 'title' => 'Teams', 'url' => home_url('my-account/teams/'), 'icon' => 'users' );
        $links[] = array( 'title' => 'Launch A Campaign', 'url' => home_url('my-account/new-campaign/'), 'icon' => 'flag' );
        $links[] = array( 'title' => 'Sign Out', 'url' => str_replace('&amp;', '&', wp_logout_url( home_url() )), 'icon' => 'minus-circle', 'self' => true );

        return $links;

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
                    'angularjs-ui-bootstrap',
                    'angularjs-module-user-services',
                    'angularjs-module-user-filters',
                    'angularjs-ctrl-user-admin',
                    'angularjs-text-sanitize',
                    'angularjs-text'
                ),
                false
            );
        }

    }

}

add_filter('lm_ajax_assignments', array('LmUserAdmin','ajax_assignments'));
add_shortcode('user admin', array('LmUserAdmin','interface_output'));
add_shortcode('setup campaign', array('LmUserAdmin','interface_output'));