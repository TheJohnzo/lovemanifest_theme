<?php

function shortcode_content($content) {
    // p and br tag fix for wordpress autop
    $bogus_br_start_end = "^<br \/>\s*|<br \/>\s*$";
    $br_between_scs = "(\])<br \/>\s*?(\[)";
    $bogus_p_start_end = "^<\/p>\s*|\s*<p>$";
    $p_with_sc = "<p>(\[.*\]|\[[^\]]*\][^\[]*\[[^\]]*\])<\/p>|<p>(\[)|(\])<\/p>"; // Detect them brackets!

    $content = preg_replace("/{$bogus_br_start_end}|{$br_between_scs}|{$bogus_p_start_end}|{$p_with_sc}/", "\$1\$2\$3\$4\$5", $content);

    // For Debugging
    //if(preg_match("/Select a Campaign to Fundraise For/", $content)) var_dump($content);

    // render internal shortcodes... cause wordpress doesn't
    $content = do_shortcode($content);

    return $content;
}
add_filter('shortcode_content', 'shortcode_content');

function theme_button($atts, $content) {
	static $count = 1;

    if($content) { $content = apply_filters('shortcode_content', $content); }

	extract( shortcode_atts( array(
		'text' => $content ? $content :"Button {$count}",
		'link' => '',
		'type' => 'defaut',
        'classes' => '',
        'style' => ''
	), $atts ) );
	
	// If page id give, get the url
	if(is_numeric($link)) $link = get_permalink($link);
	
	$tag = empty($link) ? 'button' : 'a';
	$href = empty($link) ? "" : "href='{$link}'";
	
	$html = "<{$tag} class='btn btn-{$type} btn-lg $classes' style='{$style}' {$href}>{$text}</{$tag}>";
	
	$count++;
	
	return $html;
}
add_shortcode('button', 'theme_button');

function div_block($atts, $content, $shortcode_tag) {
    static $count = 1;

    //if($shortcode_tag = 'row') var_dump($content);

    $content = apply_filters('shortcode_content', $content);

    $options = array(
        'id' => "divblock{$count}",
        'class' => '',
        'tag' => 'div',
        'size' => '6',
        'type' => 'heart',
        'href' => ''
    );

    if($shortcode_tag == 'icon') $options['tag'] = 'i';
    if($shortcode_tag == 'a') $options['tag'] = 'a';

    $atts = (array)$atts; // force array type to bypass php errors
    foreach($atts as $key => $attr_string)
    {
        //if(is_numeric($key) && preg_match("/([^=]*)=[\"]?([^\"]*)[\"]?/", $attr_string, $matches))
        if(!is_numeric($key)) $options[$key] = $attr_string;
    }

    $data_attrs = "";
    $styles = "";
    $style_attr = "";

    foreach($options as $key => $value)
    {
        //echo $key . ' ';
        if(preg_match("/^data_/", $key))
        {
            $pro_name = str_replace('_', '-', $prop_name);
            $data_attrs .= "{$key}='{$value}' ";
        } elseif(preg_match("/^style_/", $key)) {
            $prop_name = str_replace('style_', '', $key);
            $prop_name = str_replace('_', '-', $prop_name);
            $styles .= "{$prop_name}:{$value};";
        } else {
            extract(array("{$key}" => $value));
        }
    }

    if($styles) $style_attr = " style='{$styles}'";
    if(preg_match("/h[0-9]|^p$|span/", $shortcode_tag)) $tag = $shortcode_tag;
    if($shortcode_tag == 'row') $class .= " row";
    if($shortcode_tag == 'col') $class .= " col-md-{$size}";
    if($shortcode_tag == 'icon') $class .= " fa fa-{$type}";
    if($shortcode_tag == 'a') $style_attr .= " href='{$href}'";

    $html = "<{$tag} {$data_attrs}id='{$id}' class='block {$class}' {$style_attr}>{$content}</{$tag}>";

    $count++;

    return $html;
}


add_shortcode('col', 'div_block');
add_shortcode('row', 'div_block');
add_shortcode('block', 'div_block');
add_shortcode('span', 'div_block');
add_shortcode('icon', 'div_block');
add_shortcode('h1', 'div_block');
add_shortcode('h2', 'div_block');
add_shortcode('h3', 'div_block');
add_shortcode('p', 'div_block');
add_shortcode('a', 'div_block');

function sc_section($atts, $content, $shortcode_tag) {
    static $count = 1;

    $html = '';
    $id = 'section'.$count;
    $class = '';
    $background = '';
    $style = '';

    $options = array(
        'id' => $id,
        'class' => $class,
        'background' => $background
    );

    extract( shortcode_atts( $options, $atts ) );

    $content = apply_filters('shortcode_content', $content);

    if($background !== '') {
        if(is_numeric($background)) {
            $img = wp_get_attachment_image_src($background, 'banner');
            $img_src = $img[0];
        } else {
            $img_src = $background;
        }

        $style = ' style="background: url('.$img_src.') center top no-repeat"';
    }

    $class .= $class ? ' section-wrap' : 'section-wrap';
    $class = ' class="'. $class .'"';

    $html = '
            </div>
        </div>
    </div>
    <section id="'. $id .'"'. $class . $style .'>
        <div class="content container">
            <div class="row">
            '. $content .'
            </div>
        </div>
    </section>
    <div class="content container">
        <div class="container">
            <div class="row">
    ';

    $count++;

    return $html;
}
add_shortcode('section', 'sc_section');

function hide_from_known_users($atts, $content) {

    if(!is_user_logged_in()) {
        // p tag fix for wordpress autop
        $bogus_p_start_end = "^<\/p>\s*|\s*<p>$";
        $p_with_sc = "<p>(\[.*\]|\[[^\]]*\][^\[]*\[[^\]]*\])<\/p>|<p>(\[)|(\])<\/p>"; // Detect them brackets!

        $content = preg_replace("/{$bogus_p_start_end}|{$p_with_sc}/", "\$1\$2\$3", $content);
        // render internal shortcodes... cause wordpress doesn't
        $content = do_shortcode($content);
    } else {
        $content = "";
    }

    return $content;
}
add_shortcode('new_user_content', 'hide_from_known_users');


function homepage_get_involved_links() {

    return '
        <p class="h1">
            <a href="'. home_url('donate/') .'" class="fa-stack fa-lg" data-toggle="tooltip" data-placement="bottom" title="Donate">
                <i class="fa-stack-2x fa fa-circle"></i>
                <i class="fa fa-heart fa-stack-1x text-white fa-inverse"></i>
            </a>
            <a href="'. home_url('my-lovemanifest/') .'" class="fa-stack fa-lg" data-toggle="tooltip" data-placement="bottom" title="Start Your Own Campaign">
                <i class="fa-stack-2x fa fa-circle"></i>
                <i class="fa fa-flag fa-stack-1x text-white fa-inverse"></i>
            </a>
        </p>
    ';

}
add_shortcode('get_involved', 'homepage_get_involved_links');

