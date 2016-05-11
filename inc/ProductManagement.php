<?php
/**
 *  Automatically manage Cart66 Products on registered post types
 */


//  Use a lower priority so plugins can easily add filter hooks using the init hook with default priority
add_action('init', array('ProductManagement', 'setup'), 20);



class ProductManagement {

    public static $setup = false;
    public static $post_types;
    public static $meta_key = '_product_item_number';

    public static function setup() {

        //  We only want to setup once... cause then we're set up!
        if(self::$setup || !class_exists('Cart66Product')) return;
        self::$setup = true;

        //  Allow filter hooks to register post types
        self::$post_types = apply_filters('auto_product_post_types', array());

        //  Add those post filter hooks! ... if anyone registered any post types... anyone?
        if(self::$post_types && is_array(self::$post_types)) {
            //  Maybe we're going to make a product... maybe we won't...
            add_action('save_post', array(__CLASS__, 'maybe_create_product'), null, 2);
            //  To the trash can!... or digital... extinction... Maaaaaybe...
            add_action('before_delete_post', array(__CLASS__, 'maybe_remove_product'));
            //  Add some meta boxes yo!
            add_action('add_meta_boxes', array(__CLASS__, 'add_meta_boxes'));
        }
    }

    public static function get_item_number($post_ID) {
        return get_post_meta($post_ID, self::$meta_key, true);
    }


    public static function get_product($post_ID) {
        $item_number = get_post_meta($post_ID, self::$meta_key, true);

        //  Cart66 Product
        $product = new Cart66Product();
        $product->loadByItemNumber($item_number);

        return $product;
    }

    public static function get_product_data($post_ID) {
        $product = self::get_product($post_ID);

        return $product->getData();
    }

    //  @wp_action save_post
    public static function maybe_create_product($post_ID, $post) {
        //  Start: Checks Before Continuing (Yeah there's a lot of them! Deal with it!)
        if(
        //  This function isn't for trashy posts!
            $post->post_status == 'trash'
        //  Auto drafts?? NO AUTO DRAFTS!!
            || $post->post_status == 'auto-draft'
        //  We only want registered post types
            || !in_array($post->post_type, self::$post_types)
        //  Does the user have the right permissions
            || !current_user_can( 'publish_posts', $post_ID )
        //  We don't want to do anything with revisions!
            || wp_is_post_revision( $post_ID )
        //  No need to create a product if we already made one
            || self::get_item_number($post_ID) != '') {

            // Skipper we got one! Terminate! Terminate!
            return false;
        }
        //  End: Checks Before Continuing

        //  By gosh we made it captain! Full speed ahead!
        self::create_product($post);

        return true;
    }


    private static function create_product($post) {

        //  Product Info
        $item_number = uniqid($post->post_type);

        $info = array(
            'name' => $post->post_title,
            'item_number' => $item_number,
            'is_user_price' => 1
        );


        //  Attach the product number to the post
        add_post_meta($post->ID, self::$meta_key, $item_number);

        //  Cart66 Product
        $product = new Cart66Product();
        $product->setData($info);
        $product->save(true);
        $product->clear();
    }


    //  @wp_action delete_post
    public static function maybe_remove_product($post_ID) {

        //  Start: Checks Before Continuing (Yeah there's a lot of them! Deal with it!)
        if(
        //  Does the user have the right permissions
            !current_user_can( 'edit_post', $post_ID )
        //  We don't want to do anything with revisions!
            || wp_is_post_revision( $post_ID )
        //  No need to delete a product that doesn't exist
            || ($item_number = self::get_item_number($post_ID)) == ''
        //  Leave a product with orders so we can maintain history
            || self::product_has_orders($item_number)) {

            // Skipper we got one! Terminate! Terminate!
            return;
        }
        //  End: Checks Before Continuing

        //  By gosh we made it captain! Full speed ahead!
        self::remove_product($item_number);
    }

    private static function remove_product($item_number) {

        //  Cart66 Product
        $product = new Cart66Product();
        $product->loadByItemNumber($item_number);
        $product->deleteMe();
        $product->clear();
    }

    public static function product_has_orders($item_number) {

        global $wpdb;
        //  Cart66 order_items table name
        $table = Cart66Common::getTableName('order_items');

        //  Count the number of order items with the matching item number
        $query = "
            SELECT
            COUNT(*)
            FROM $table
            WHERE item_number = %s
        ";

        $query = $wpdb->prepare($query, $item_number);

        //  Returns FALSE if no matches are found, TRUE if one or more is found
        return (bool)$wpdb->get_var($query);
    }

    public function get_associated_products($post_ID) {
        global $wpdb;

        //  Get Item Numbers for campaigns marked as sponsors for other campaigns
        $query = "
            SELECT  meta_value
            FROM $wpdb->postmeta
            WHERE meta_key = %s
              AND post_id IN
                (
                    select post_id
                    from $wpdb->postmeta
                    where meta_key = %s
                      and meta_value = %s
                )
        ";

        $query = $wpdb->prepare($query, self::$meta_key, '_campaign_id', $post_ID);

        return $wpdb->get_col($query);
    }

    public static function add_meta_boxes() {
        foreach(self::$post_types as $post_type) {
            add_meta_box(
                $post_type.'_product_manager',
                'Cart66 Product Information',
                array(__CLASS__, 'meta_box'),
                $post_type,
                'normal',
                'high'
            );
        }
    }

    public static function meta_box( $post ) {

        //update_post_meta($post->ID, self::$meta_key, 'PROJ003');
        $item_number = self::get_item_number($post->ID);

        //  Cart66 Product
        $product = new Cart66Product();
        $product->loadByItemNumber($item_number);
        $data = $product->getData();
        $sales = $product->getSalesTotal();
        $sales_totals = 0;
        $sales_totals += $product->getIncomeTotal();
        $sales_totals = Cart66Common::convert_currency_to_number($sales_totals);

        $associated_totals = '';
        $associated_products = self::get_associated_products($post->ID);

        if($associated_products && !is_wp_error($associated_products)) {
            $associated_sales = 0;
            $associated_sales_totals = 0;
            foreach($associated_products as $associate_item_number) {
                $product->loadByItemNumber($associate_item_number);
                $associated_sales .= $product->getSalesTotal();
                $associated_sales_totals .= $product->getIncomeTotal();
            }

            $associated_sales_totals = Cart66Common::convert_currency_to_number( $associated_sales_totals );
            $associated_totals = "<li><strong>Number of User Campaign Donations</strong>: {$associated_sales}</li>";
            $associated_totals .= "<li><strong>User Campaign Sales Totals</strong>: \${$associated_sales_totals}</li>";
        }

        echo "
            <h2>{$data['name']}</h2>
            <ul>
                <li><strong>ID</strong>: {$data['id']}</li>
                <li><strong>Item Number</strong>: {$data['item_number']}</li>
                <li><strong>Number of Donations</strong>: {$sales}</li>
                <li><strong>Total Amount Donated</strong>: \${$sales_totals}</li>
                {$associated_totals}
            </ul>
        ";
        //print_r($product->getData());
    }
}