<?php
if(!empty($_SERVER['SCRIPT_FILENAME']) && 'modole-qr.php' == basename($_SERVER['SCRIPT_FILENAME']))
	die ('Please do not load this page directly. Thanks!');

//set post meta data
bw_meta($post);

set_qr_template();

function set_qr_template() {
	static $cookie_ignore = array('facebook');

	$template_congifs = array(
		'layout1' => array('image', 'title', 'about_title', 'about', 'donate', 'site_link'),
		'layout2' => array('image', 'title', 'about_title', 'about'),
		'layout3' => array('image', 'title', 'about_title', 'about'),
		'layout4' => array('image', 'title', 'about', 'site_link'),
		'facebook' => array('donate', 'site_link')
	);
	
	if(!empty($_GET['layout']))
		$template_name = $_GET['layout'];
	elseif(is_null($GLOBALS['post']->bw_meta['qr_template']) || $GLOBALS['post']->bw_meta['qr_template'] === "-1")
		$page_type_key = rand(1, count($templates));
	else
		$page_type_key = intval($GLOBALS['post']->bw_meta['qr_template']);
	
	if(!is_null($page_type_key))
		$template_name = "layout$page_type_key";
	
	$GLOBALS['qr_output_order'] = $template_congifs[$template_name];
	
	
		if($GLOBALS['post']->post_type === 'lm_project')
		{
			setcookie('QR_TEMPLATE', $template_name, 0, '/', ".lovemanifest.org");
			if(!in_array($template_name, $cookie_ignore))
				setcookie('QR_PAGE_ID', $GLOBALS['post']->ID, 0, '/', ".lovemanifest.org");
		}
		else {
			$template_name = $_COOKIE['QR_TEMPLATE'];
		}
		
	if(is_null($template_name)) $template_name = 'layout1';
	
	$GLOBALS['qr_page_type'] = $template_name;
}

function bw_qr_style_reset() {
    global $qr_page_type, $wp_styles, $wp_scripts, $post;
    $wp_styles->queue = array();
    $wp_scripts->queue = array();

    $dir = get_bloginfo('stylesheet_directory');
    wp_enqueue_style( 'qr-font-awesome', "$dir/qr/font.awesome.css", '', '3.1.0', 'screen' );
    wp_enqueue_style( 'qr-bootstrap-core', "$dir/qr/core.min.css", '', '1.0', 'screen' );
    wp_enqueue_style( 'qr-bootstrap-normalize', "$dir/qr/normalize.min.css", '', '1.0', 'screen' );
    wp_enqueue_style( 'qr-bootstrap-body-link-styles', "$dir/qr/body-link-styles.min.css", '', '1.0', 'screen' );
    wp_enqueue_style( 'qr-bootstrap-grid', "$dir/qr/grid.min.css", '', '1.0', 'screen' );
    wp_enqueue_style( 'qr-base', "$dir/qr/base.css", '', '1.0', 'screen' );
    wp_enqueue_style( "qr-{$qr_page_type}", "$dir/qr/{$qr_page_type}.css", '', '1.0', 'screen' );

    wp_enqueue_script("jquery");
    wp_enqueue_script("jquery-forms", "$dir/qr/jquery.forms.min.js", null, '1.0', false);
    wp_enqueue_script("init-qr", "$dir/qr/init.qr.js", null, '1.0', false);
    
    if($post->post_name === 'checkout')
    	enqueue_bweb_script("checkout_js", true);

    if($qr_page_type === 'facebook')
    	enqueue_bweb_style('bootstrap-forms');
    	
    remove_action('wp_head', '_admin_bar_bump_cb');
}
add_action('wp_enqueue_scripts', 'bw_qr_style_reset', 9999);

function qr_js_vars($var_array) {
	$var_array['qr_template'] = $GLOBALS['qr_page_type'];
	return $var_array;
}
add_filter('bweb_js_vars', 'qr_js_vars');
	
function donation_form($price, $classes = NULL) {
	global $post;

    $product = ProductManagement::get_product_data($post->ID);
    $product_id = $product['id'];
	
	$starter_classes = array('Cart66CartButton','form-controls', 'span6', 'donation-form');
	
	$classes = is_array($classes) ? array_merge($starter_classes, $classes) : $starter_classes;
	
	switch($price) {
		case 'user':
			$price_field = '
			<span class="Cart66UserPrice">
				<h3 for="Cart66UserPriceInput_'.$product_id.'">Or Enter an Amount: </h3><br/>
				<input type="number" value="" placeholder="0.00" name="item_user_price" id="Cart66UserPriceInput_'.$product_id.'" required>
			</span>';
			$submit = '<input class="btn2 theme-icon donate Cart66ButtonPrimary purAddToCart ajax-button input-block-level" type="submit" id="addToCart_'.$product_id.'" name="addToCart_'.$product_id.'" value="Donate">';
			break;
		default:
			$price_field = '<input type="hidden" value="'.$price.'" name="item_user_price" id="Cart66UserPriceInput_'.$product_id.'" required>';
			$classes[] = "fixed-price";
			$submit = '
				<input class="btn2 theme-icon donate Cart66ButtonPrimary purAddToCart ajax-button input-block-level" type="submit" id="addToCart_'.$product_id.'" name="addToCart_'.$product_id.'" value="$'.$price.'">';
			break;
	}
	$form = sprintf('
		<form data-ajax="redirect" data-redirect="%4$s" data-submit-type="html" action="%2$s" method="post" class="%7$s" id="cartButtonForm_%1$s" target="_top">
			<input type="hidden" value="addToCart" id="task_%1$s" name="task">
			<input type="hidden" value="%1$s" name="cart66ItemId">
			<input type="hidden" value="%3$s" name="product_url">
			%5$s
			%6$s
		</form>',
        $product_id,
		home_url('store/cart/'),
		get_permalink($post->ID),
		home_url('store/checkout/'),
		$price_field,
		$submit,
		implode(' ', $classes)
	);
	
	return $form;
}

function project_dropdown() {
	if(!is_archive())
		return '';
		
	$projects = get_posts(array( 'post_type' => 'lm_project', 'numberposts' => -1 ));
	if(!empty($projects))
	{
		$urls = array();
		$output = '<select id="donation_controller" name="donation_id" class="input-block-level">';
		foreach($projects as $project)
		{
            $product = ProductManagement::get_product_data($project->ID);
            $product_id = $product['id'];
			bw_meta($project);
			if(!$product_id)
				continue;
			
			$output .= "<option value='{$product_id}'>{$project->post_title}</option>";
			
			$urls[$product_id] = get_permalink($project->ID);
		}
		$output .= '</select>';
		ob_start();
		?>
		<script type="text/javascript">
			(function($) {
				$(function() {
					var controller = $('#donation_controller'), forms = $('.donation-form'), product_links = <?php echo json_encode($urls); ?>;
					controller.on('change', function(e) {
						update_forms(controller.val());
					});
					
					update_forms(controller.val());
					
					function update_forms(id)
					{
						forms.each(function() {
							$(this).attr('id', 'cartButtonForm_'+id);
							$('[id*=task_]', this).attr('id', 'task_'+id);
							$('[name=cart66ItemId]', this).val(id);
							$('[name=product_url]', this).val(product_links[id]);
							$('[for*=Cart66UserPriceInput_]', this).attr('for', 'Cart66UserPriceInput_'+id);
							$('[id*=Cart66UserPriceInput_]', this).attr('id', 'Cart66UserPriceInput_'+id);
							$('[id*=addToCart_]', this).attr('id', 'addToCart_'+id).attr('name', 'addToCart_'+id);
						});
					}
				});
			})(jQuery);
		</script>
		<?php
		$output .= ob_get_clean();
	}
	return $output;
}

$classes = explode(',', $post->bw_meta['qr_classes']);

$parts = array();
$parts['title'] = "<h1 class='qr_page_title'>{$post->post_title}</h1>";
$parts['about_title'] = sprintf("<h2 class='qr_about_title'>%s</h2>", $post->bw_meta['qr_about_title']);
$parts['about'] = sprintf("<div class='qr_about_content'>%s%s</div>", $about_title, do_shortcode(shortcode_unautop(wpautop($post->bw_meta['qr_about_content']))));

if(has_post_thumbnail($post->ID)  && $post->post_type == 'lm_project')
{
	$parts['image'] = get_the_post_thumbnail($post->ID, array(480,1000));
} elseif(isset($_COOKIE['QR_PAGE_ID']) && $post->post_type != 'lm_project') {
	$page_id = (int)$_COOKIE['QR_PAGE_ID'];
	if(has_post_thumbnail($page_id))
		$parts['image'] = get_the_post_thumbnail($page_id, array(480,1000));
}
	
$parts['donate'] = sprintf("
	<div class='qr_donate clearfix'>
		<h2 class='qr_donate_title'>Donate <span class='icon-chevron-down'></span></h2>
		%s
		%s
		%s
		%s
		%s
		%s
	</div>",
	project_dropdown(),
	donation_form(1, array('no-m-left')),
	donation_form(5),
	donation_form(25, array('no-m-left')),
	donation_form(50),
	donation_form('user', array('no-m-left', 'user-input'))
);

$parts['site_link'] = sprintf('<a href="%s" class="qr_site_link" target="_blank"><img src="%s" width="%s" height="%s" /></a>', home_url('/?clear_qr'), theme_url('img/assets/logo.png', true), 208, 100);
?>