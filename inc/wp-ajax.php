<?php
/* Independent class for assigning and handling ajax actions */
add_action('init', array('LM_WP_AJAX', 'init'));

if(!function_exists('ajax_error')) {
    function ajax_error($code, $message = null) {
        LM_WP_AJAX::new_error($code, $message);
    }
}

if(!function_exists('ajax_has_errors')) {
    function ajax_has_errors() {
        !empty( LM_WP_AJAX::$errors->errors );
    }
}

if(!function_exists('ajax_has_errors')) {
    function ajax_has_error($code) {
        isset( LM_WP_AJAX::$errors->errors[$code] );
    }
}


class LM_WP_AJAX {

    public static $errors;
	private static $ajax_dir;
	
	private static $actions = array();
	
	public static function init() {

        self::$errors = new WP_Error();
		self::$ajax_dir = dirname(__FILE__) . '/ajax/';
		
		self::$actions = apply_filters('lm_ajax_assignments', self::$actions);
		
		foreach(self::$actions as $name => $action )
		{
			add_action("wp_ajax_{$name}", array(__CLASS__, 'handle_ajax_request'));

			if(is_string($action) && preg_match("/^nopriv/", $action))
				add_action("wp_ajax_nopriv_{$name}", array(__CLASS__, 'handle_ajax_request'));
		}
			
	}
	
	public function handle_ajax_request()
	{
		if(is_null(self::$ajax_dir))
			die('LM_WP_AJAX class must be initialized before requests can be handled');
		
		$data = empty($_POST) ? $_GET : $_POST;

        $request_body = file_get_contents('php://input');
        if($request_body) $data = array_merge( $data, (array)json_decode($request_body) );
		
		self::do_ajax_action($data);
	}
	
	private static function do_ajax_action($data)
	{
		$response = array( );

        $action = self::$actions[$data['action']];
        if(is_array($action)) {

            $action_result = (array)call_user_func($action, $data);

            $response = array_merge( $response,  $action_result);

        } else if(is_string($action)) {
            if(function_exists($action)) {

                $action_result = call_user_func($action, $data);

                if(is_array($action_result))
                    $response = array_merge( $response,  $action_result);

            } else {
                $dir = apply_filters('lm_ajax_dir', self::$ajax_dir, $data['action']);

                $action_file = $dir . self::$actions[$data['action']];

                //echo $action_file;

                if(!file_exists($action_file))
                    self::exit_ajax_action($response, $data);

                include($action_file);
            }

        }
		
		self::exit_ajax_action($response, $data);
	}
	
	private static function exit_ajax_action($response, $data)
	{
        if(self::$errors->errors) {

            $response = array_merge( $response, (array)self::$errors );

        } elseif(empty($response) && !$data['is_array']) {
            $response['noresponse'] = true;
        }
		
		$print_response = json_encode($response);
		die($print_response);
	}

    function new_error($code, $message = null) {
        if(is_wp_error($code)) {
            $wp_errors = $code;
            foreach($wp_errors->errors as $code => $messages) {
                foreach($messages as $message) {
                    self::$errors->add($code, $message);
                }
            }
        } else {
            self::$errors->add($code, $message);
        }
    }
}
?>