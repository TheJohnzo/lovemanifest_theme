<?php
/**
 *
 * Modole Window Page template
 *
**/

require_once("inc/qr-page-setup.php");
$classes = array($GLOBALS['qr_page_type'])
?>
<!DOCTYPE html>
<html lang=en>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta name="robots" content="noindex, nofollow, noarchive" />
	<meta name="format-detection" content="telephone=no">
	<?php wp_head(); ?>
</head>
<body <?php body_class($classes); ?>>
<?php if($GLOBALS['qr_page_type'] === 'facebook') : ?>
<div id="fb-root"></div>
<script src="//connect.facebook.net/en_US/all.js"></script>
<?php endif;
foreach($GLOBALS['qr_output_order'] as $part)
	printf("<div class='row-fluid qr_%s_cont'>%s</div>", $part, $parts[$part]);
	
wp_footer();
if($GLOBALS['qr_page_type'] === 'facebook') : ?>
<script>
  var appId = '433154440050132';

  // Initialize the JS SDK
  FB.init({
    appId: appId,
    frictionlessRequests: true,
    cookie: true,
  });

  /*FB.getLoginStatus(function(response) {
  	if(response.status !== 'not_authorized')
    	uid = response.authResponse.userID ? response.authResponse.userID : null;
  });*/
</script>
<?php endif; ?>
</body>
</html>