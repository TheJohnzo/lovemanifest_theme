<?php

function bootstrap_modal($args, $echo = true) {

    if(isset($args['id'])) {
        $header = "";
        $body = "";
        $footer = "";

        if($args['title']) {
            $title = "<h4 class='modal-title'>{$args['title']}</h4>";
        }

        if($args['body']) {
            $body = "
                <div class='modal-body'>
                    {$args['body']}
                </div>
            ";
        }

        if($args['footer']) {
            $footer = "
                <div class='modal-footer'>
                    {$args['footer']}
                </div>
            ";
        }

        if($title || $body || $footer) {
            $modal = "
                <div id='{$args['id']}' class='modal fade'>
                    <div class='modal-dialog'>
                        <div class='modal-content'>
                            <div class='modal-header'>
                                <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>
                                {$title}
                            </div>

                            {$body}

                            {$footer}

                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->
            ";

            if($echo) {
                echo $modal;
            } else {
                return $modal;
            }
        }
    }
}