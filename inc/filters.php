<?php

//Adds
function bootstrap_input_classes($content, $form) {

    // Classes to add to target inputs
    $classes = array('form-control');

    // Input types to target when adding classes
    $target_types = array('text','email','password');
    $target_type_string = implode('|', $target_types);

    // Target input regular expression. Currently only works with INPUT html fields
    $targets = "<input[^\/>]*type='({$target_type_string})'[^\/>]*(class='[^']*')|<input[^\/>]*type='({$target_type_string})'[^\/>]*";

    //var_dump($content);

    preg_match_all("/$targets/", $content, $matches);

    //var_dump($matches);

    // @ $matches[0] - array of matches for the $targets regular expression
    if($matches[0]) {
        $class_string = implode(' ', $classes);
        // @ $matches[2] - array of class attribute matches (e.g. class='large')
        $class_attributes = $matches[2];
        foreach($matches[0] as $i => $string) {
            if($class_attributes[$i]) {
                // If the target input already has a class attribute, add our classes to it,
                $replace_with = preg_replace("/class='([^']*)'/", "class='\$1 {$class_string}'", $string);
            } else {
                // otherwise append a class attribute with our class names
                $replace_with = $string . " class='{$class_string}'";
            }

            // Modify the gravityforms html string
            $content = str_replace($string, $replace_with, $content);
        }
    }

    return $content;
}
add_filter('gform_field_content', 'bootstrap_input_classes', null, 2);


if(!function_exists('theme_gform_class')) {
    function theme_gform_class($form) {
        return $form;
    }
}
add_filter("gform_pre_render", "theme_gform_class");


// Gravity Forms Submit Button
add_filter("gform_submit_button", "theme_gform_submit_button", 10, 2);
if(!function_exists('theme_gform_submit_button')) {
    function theme_gform_submit_button($button, $form) {
        if($form['id'] == 1) {
            $button = "
                <div class='text-right'>
                    <button class='btn btn-primary' id='gform_submit_button_{$form["id"]}'>
                        <span>Create An Account</span> &nbsp;<i class='fa fa-lg fa-arrow-circle-right'></i>
                    </button>
                </div>";

            if($_GET['invite']) {
                if(get_transient($_GET['invite']) !== false) {
                    $button .= "<input type='hidden' name='team_invite' value='{$_GET['invite']}' />";
                } else {
                    $button .= "<input type='hidden' name='team_invite_expired' value='{$_GET['invite']}' />";
                }
            }
        } else {
            $button =  "<button class='btn btn-primary' id='gform_submit_button_{$form["id"]}'><span>Submit</span> <i class='fa fa-envelope'></i></button>";
        }

        return $button;
    }
}


/* --- Cart66 Label Filters --- */
function filter_user_account_link($menu_items, $args) {

    if(!is_user_logged_in() && $args->theme_location) {
        foreach($menu_items as $key => $item) {
            if(in_array('login-link', $item->classes)) {
                $item->url = "#login";
                $GLOBALS['login_modal'] = true;

                $js_file = dirname(__FILE__) . "/inline-js/login-modal.min.js";
                if(file_exists($js_file)) {
                    $script = file_get_contents($js_file);
                    echo "<script>{$script}</script>";
                }
            }
        }
    }

    return $menu_items;
}
add_filter('wp_nav_menu_objects', 'filter_user_account_link', null, 2);


/* --- Cart66 Label Filters --- */
add_filter('gettext', 'theme_cart66_labels', null, 3);
function theme_cart66_labels($translations, $text, $domain ) {
    if($domain == 'cart66') {
        switch($text) {
            case 'Continue Shopping' :
                $translations = 'Return to Campaign';
                break;
        }
    }

    return $translations;
}