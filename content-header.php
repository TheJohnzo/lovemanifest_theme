<?php
// Make Sure meta is loaded for this post
bw_meta($post);

if( has_post_thumbnail() ) {

    if( $post->bw_meta['featured_background'] ) {

        list($img_src, $img_width, $img_height) = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');

        $styles = '';

        $styles .= '
            .has-featured-image .content-wrap > article {
                padding-top: 160px;
                padding-bottom: 180px;
                background-image: url('. $img_src .');
                background-position: center top;
                background-attachment:fixed;
            }
            .content > .container-fluid {
                padding: 30px 45px;
                background: rgba(255,255,255, 0.9);
            }
        ';

        echo '<style>'. $styles . '</style>';

    } else {

        list($img_src, $img_width, $img_height) = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large');
        $scale_at = $img_width >= 1170 ? 1170 : $img_width;
        $style = "height:{$img_height}px;background:url({$img_src}) center top no-repeat;";

        $section_html = "<div id='page-intro' class='featured-image' style='{$style}' data-ratio='{$scale_at}:{$img_height}' data-smRatio='767:{$img_height}' data-xsRatio='600:{$img_height}'>";

        if($post->bw_meta['feat_img_content']) {
            $classes = array('page-header');
            $content = do_shortcode($post->bw_meta['feat_img_content']);

            // direction to place content
            $position = 'left';
            if($post->bw_meta['feat_img_content_position']) $position = $post->bw_meta['feat_img_content_position'];
            $classes[] = "pull-{$position}";

            $cols = 4;
            if($post->bw_meta['feat_img_content_cols']) $cols = $post->bw_meta['feat_img_content_cols'];
            $cols_sm = max(6, $cols);
            $classes[] = "col-md-{$cols}";
            $classes[] = "col-sm-{$cols_sm}";

            $height = 'default';
            if($post->bw_meta['feat_img_content_height']) $height = $post->bw_meta['feat_img_content_height'];
            $classes[] = "height-{$height}";

            $class_str = implode(' ', $classes);
            $section_html .= "<section class='container'>";
            $section_html .= "<div id='featured-image-content' class='{$class_str}'>{$content}</div>";
            $section_html .= "</section>";
        }

        $section_html.= "</div>";

        echo $section_html;

    }
}