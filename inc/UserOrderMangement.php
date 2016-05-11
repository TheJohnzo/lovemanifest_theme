<?php
/**
 * Created by PhpStorm.
 * User: anthonybrown
 * Date: 4/3/14
 * Time: 10:49 PM
 */

//  Use a lower priority so plugins can easily add filter hooks using the init hook with default priority
if(is_user_logged_in()) {
    add_action('init', array('UserOrderMangement', 'setup'), 20);
}

class UserOrderMangement {

    public static $setup = false;
    public static $orders;
    public static $user_id;
    public static $meta_key = 'user_order_id';

    public static function setup() {

        //  We only want to setup once... cause then we're set up!
        if(self::$setup || !class_exists('Cart66Product')) return;
        self::$setup = true;

        self::$user_id = get_current_user_id();

        //  Maybe we're going to make a product... maybe we won't...
        add_filter('query', array(__CLASS__, 'attach_order_to_user'));



        //  Test code for capturing order ids from order item inserts
        /*global $wpdb;
        $table = Cart66Common::getTableName('order_items');
        $wpdb->insert($table, array('poop' => 'gross', 'order_id' => '13', 'order_id' => '36'));
        */
    }

    public function set_orders() {
        if(is_null(self::$orders)) {
            self::$orders = get_user_meta(self::$user_id, self::$meta_key);
        }
    }

    public static function attach_order_to_user($query) {

        $table = Cart66Common::getTableName('order_items');
        if(preg_match("/INSERT INTO `$table` (.*)/", $query, $match)) {

            //  Extract data from wp formatted insert statement
            preg_match_all("/`([^`]*)`/", $match[1], $key_matches);
            preg_match_all("/'([^']*)'/", $match[1], $value_matches);
            //  Merge key value pairs
            $data = array_combine($key_matches[1], $value_matches[1]);

            //  Try to add our shiny order id!
            self::maybe_add_user_order( $data['order_id'] );
        }

        return $query;
    }


    public static function maybe_add_user_order($order_id) {
        if(!$order_id || !is_numeric($order_id)) return false;

        //  Set the orders variable
        self::set_orders();

        //  Don't add an order id twice
        if(!in_array($order_id, self::$orders)) {
            add_user_meta(self::$user_id, self::$meta_key, $order_id, false);
            return true;
        }

        return false;
    }



    function get_user_order_total() {

        $table = Cart66Common::getTableName('orders');

        global $wpdb;

        //  Get Donation Total for a user
        $query = "
            SELECT SUM(total) as 'total'
            FROM $table
            WHERE id IN
                (
                    select meta_value
                    from $wpdb->usermeta
                    where meta_key = %s
                      and user_id = %d
                )
        ";

        $query = $wpdb->prepare($query, self::$meta_key, self::$user_id);

        $result = $wpdb->get_var($query);

        return $result ? $result : 0;
    }
} 