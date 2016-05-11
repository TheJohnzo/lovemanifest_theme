<?php
    global $lmuserdata;
    $completion = $lmuserdata->get_earnings($post->ID);
    $completion_dollar = '$'.round($completion, 2);
    $completion_percentage = $lmuserdata->get_completion_percentage($post->ID);

    $user = get_userdata( $post->post_author );

    if(get_current_user_id() == 1) {
        var_dump($completion_percentage);
    }
?>
<?php get_header(); ?>
<?php the_post(); //Initialize page post ?>
    <article>
        <div class="content container">
            <h1 id="page-title"  class="default-title"><span><?php the_title(); ?></span></h1>
            <div class="container">
                <div class="page-<?php the_ID(); ?> row">
                    <div class="col-sm-6 pull-right">
                        <div class="row">
                            <?php
                                if(class_exists('Cart66Product')) {

//                                    if(get_current_user_id() == 1) {
//                                        print_r($post->bw_meta);
//                                    }

                                    $image_url = $lmuserdata->get_campaign_image('userpage', $post->bw_meta['campaign_id'], $post->ID);
                                    if($image_url) { $styles = "background-image: url({$image_url});background-repeat:no-repeat"; }

                                    $permalink = get_permalink($post->ID);

                                    $classes = array('campaign');
                                    $class_str = implode(' ', $classes);

                                    $product = ProductManagement::get_product_data($post->ID);

                                    $product_url = get_permalink($post->ID);

                                    $cart_url = home_url("store/cart/");
                                    $output .= "
                                            <div data-ratio='585:400' class='ptop bg-center user-campaign-image' style='{$styles}' data-ratio-valign='.sponsor'>

                                                <form id='donation-form' action='{$cart_url}' method='post'>
                                                    <div class='sponsor text-center'>
                                                        <label class='h3 text-shadow text-white padding-horz-half'>Sponsor {$user->data->display_name}'s Campaign</label>
                                                        <input class='form-control overlay-control-blue input-tall' name='item_user_price' placeholder='Enter a Donation Amount' />
                                                        <button class='btn btn-outline btn-lg margin-vert-half'><i class='fa fa-heart'></i> Donate</button>
                                                    </div>

                                                    <input type='hidden' value='addToCart' name='task'>
                                                    <input type='hidden' value='{$product['id']}' name='cart66ItemId'>
                                                    <input type='hidden' value='{$product_url}' name='product_url'>
                                                    <input type='hidden' value='user_campaign_id' name='{$post->ID}'>
                                                </form>
                                            </div>
                                    ";

                                    echo $output;
                                }
                            ?>
                            <div class="space-top">
                                <?php
                                $output = "
                                    <p class='clearfix'>
                                        <small></small>
                                        <small></small>
                                    </p>
                                    <h2 class='h4 text-muted margin-vert-half' style='text-align:center'>{$completion_dollar} out of {$post->bw_meta['campaign_goal']} Raised</h2>
                                    <div class='bar-holder'>
                                        <div id='progress' class='completion-bar'></div>
                                    </div>
                                ";

                                $output .= "
                                <script>
                                    (function($) {
                                        setTimeout(function() { $('#progress').width( {$completion_percentage}*100+'%' ) }, 1000);
                                    })(jQuery)
                                </script>
                                ";

                                echo $output;
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 padding-left-none padding-left-half-xs">
					<?php if ($post->bw_meta['campaign_status'] === 'complete') { ?>
						<h4 style="color: red;">Please note: this campaign has ended.</h4>
					<?php
					}
					the_content(); ?>
                    </div>
                </div>
            </div>
        </div>
    </article>
<?php get_footer(); ?>