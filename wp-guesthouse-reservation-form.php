<?php
/**
 * Plugin Name: WP Guesthouse Reservation Form
 * Plugin URI: 
 * Description: Easy generate reservation form for guesthouse, hotel business
 * Version: 1.1
 * Author: BoostPress
 * Author URI: http://boostpress.com
 * Text Domain: wp-guesthouse-reservation-form
 * Domain Path: /languages/
 * License:     GPL2
 * 
 * {Plugin Name} is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 * {Plugin Name} is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with {Plugin Name}. If not, see {License URI}.
 * 
 */

load_plugin_textdomain( 'wp-guesthouse-reservation-form', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

class WP_Guesthouse_Reservation_Form
{
    static function activate()
    {
        // Create reservation page for shortcode.
        $page = get_page_by_path( 'reservation' );

        if(!is_object($page)){
            global $user_ID;

            $page = array(
                'post_type' => 'page',
                'post_name' => 'reservation',
                'post_title' => 'Reservation',
                'post_content' => '[reservation_form]',
                'post_parent' => 0,
                'post_author' => $user_ID,
                'post_status' => 'publish',
                'ping_status' => 'closed',
                'comment_status' => 'closed',
            );

            wp_insert_post ($page);
        }

        // Insert demo data
        $has_resevation_demo = get_option('has_resevation_demo', 'false');

        if($has_resevation_demo == 'false'){

            $post = array(
                'post_title' => '',
                'post_content' => '',
                'post_type' => 'room',
                'post_status' => 'publish',
            );

            // Standard Single Fan Room
            $post['post_title'] = 'Standard Single Fan Room 700 ฿';
            wp_insert_post( $post );

            // Standard Double Fan Room
            $post['post_title'] = 'Standard Double Fan Room 900 ฿';
            wp_insert_post( $post );

            // Standard Single A/C Room
            $post['post_title'] = 'Standard Single A/C Room 1000 ฿';
            wp_insert_post( $post );

            // Standard Double A/C Room
            $post['post_title'] = 'Standard Double A/C Room 1,200 ฿';
            wp_insert_post( $post );

            // Superior Single A/C Room
            $post['post_title'] = 'Superior Single A/C Room 1,500 ฿';
            wp_insert_post( $post );

            // Superior Double A/C Room
            $post['post_title'] = 'Superior Double A/C Room 2,000 ฿';
            wp_insert_post( $post );

            // A/C Family 2 Double Room
            $post['post_title'] = 'A/C Family 2 Double Room 2,500 ฿';
            wp_insert_post( $post );

            // Mark we have demo data
            add_option('has_resevation_demo', 'true');
        }

    }

    static function deactivate()
    {
        // Remove reservation page.
        $page = get_page_by_path( 'reservation' );

        if(is_object($page)){
            wp_delete_post( $page->ID, true);
        }
    }

    public function __construct()
    {
        add_action('init', array($this, 'register_shortcode'));
        add_action('init', array($this, 'save_reservation'));
        add_action('admin_menu', array($this, 'register_reservation_page'));
        add_action('init', array($this, 'register_reservation_post_type'), 0);
        add_action('init', array($this, 'register_room_post_type'), 0 );
        add_action('wp_enqueue_scripts', array($this, 'load_stylesheet'));
        add_action('wp_enqueue_scripts', array($this, 'load_javascript'));
        add_filter('plugin_action_links', array($this, 'rooms_link'), 10, 2);
    }

    /*
    * Add settings link under plugin name on plugins page.
    */
    public function rooms_link( $links, $file ) {

        if ( $file != plugin_basename( __FILE__ ) ) {
            return $links;
        }

        $settings_link = '<a href="'.admin_url('edit.php?post_type=room').'">' . __( 'Rooms', 'wp-guesthouse-reservation-form' ) . '</a>';
        array_unshift( $links, $settings_link );

        return $links;
    }

    /**
     * Register reservation menu.
     */
    public function register_reservation_page()
    {
        add_menu_page(
            __( 'Reservations', 'wp-guesthouse-reservation-form' ),
            'Reservations',
            'manage_options',
            'edit.php?post_type=reservation',
            '',
            plugins_url( 'images/calendar.png', __FILE__ ),
            6
        );
    }

    /**
     * Register room post type for room listings.
     */
    public function register_room_post_type()
    {
        $labels = array(
            'name'                  => _x( 'Rooms', 'Post Type General Name', 'wp-guesthouse-reservation-form' ),
            'singular_name'         => _x( 'Room', 'Post Type Singular Name', 'wp-guesthouse-reservation-form' ),
            'menu_name'             => __( 'Room', 'wp-guesthouse-reservation-form' ),
            'name_admin_bar'        => __( 'Room', 'wp-guesthouse-reservation-form' ),
            'archives'              => __( 'Room Archives', 'wp-guesthouse-reservation-form' ),
            'attributes'            => __( 'Room Attributes', 'wp-guesthouse-reservation-form' ),
            'parent_item_colon'     => __( 'Parent Room', 'wp-guesthouse-reservation-form' ),
            'all_items'             => __( 'Rooms', 'wp-guesthouse-reservation-form' ),
            'add_new_item'          => __( 'Add New Room', 'wp-guesthouse-reservation-form' ),
            'add_new'               => __( 'Add New', 'wp-guesthouse-reservation-form' ),
            'new_item'              => __( 'New Room', 'wp-guesthouse-reservation-form' ),
            'edit_item'             => __( 'Edit Room', 'wp-guesthouse-reservation-form' ),
            'update_item'           => __( 'Update Room', 'wp-guesthouse-reservation-form' ),
            'view_item'             => __( 'View Room', 'wp-guesthouse-reservation-form' ),
            'view_items'            => __( 'View Rooms', 'wp-guesthouse-reservation-form' ),
            'search_items'          => __( 'Search Room', 'wp-guesthouse-reservation-form' ),
            'not_found'             => __( 'Not found', 'wp-guesthouse-reservation-form' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'wp-guesthouse-reservation-form' ),
            'featured_image'        => __( 'Featured Image', 'wp-guesthouse-reservation-form' ),
            'set_featured_image'    => __( 'Set featured image', 'wp-guesthouse-reservation-form' ),
            'remove_featured_image' => __( 'Remove featured image', 'wp-guesthouse-reservation-form' ),
            'use_featured_image'    => __( 'Use as featured image', 'wp-guesthouse-reservation-form' ),
            'insert_into_item'      => __( 'Insert into item', 'wp-guesthouse-reservation-form' ),
            'uploaded_to_this_item' => __( 'Uploaded to this item', 'wp-guesthouse-reservation-form' ),
            'items_list'            => __( 'Rooms list', 'wp-guesthouse-reservation-form' ),
            'items_list_navigation' => __( 'Rooms list navigation', 'wp-guesthouse-reservation-form' ),
            'filter_items_list'     => __( 'Filter items list', 'wp-guesthouse-reservation-form' ),
        );
        $args = array(
            'label'                 => __( 'Room', 'wp-guesthouse-reservation-form' ),
            'description'           => __( 'Room list', 'wp-guesthouse-reservation-form' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'editor', 'thumbnail', ),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => 'edit.php?post_type=reservation',
            'menu_position'         => 5,
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
        );
        register_post_type( 'room', $args );
    }

    /**
     * Register reservation post type for keep form's data.
     */
    public function register_reservation_post_type() {

        $labels = array(
            'name'                  => _x( 'Reservations', 'Post Type General Name', 'wp-guesthouse-reservation-form' ),
            'singular_name'         => _x( 'Reservation', 'Post Type Singular Name', 'wp-guesthouse-reservation-form' ),
            'menu_name'             => __( 'Reservation', 'wp-guesthouse-reservation-form' ),
            'name_admin_bar'        => __( 'Reservation', 'wp-guesthouse-reservation-form' ),
            'archives'              => __( 'Item Archives', 'wp-guesthouse-reservation-form' ),
            'attributes'            => __( 'Item Attributes', 'wp-guesthouse-reservation-form' ),
            'parent_item_colon'     => __( 'Parent Item:', 'wp-guesthouse-reservation-form' ),
            'all_items'             => __( 'All Items', 'wp-guesthouse-reservation-form' ),
            'add_new_item'          => __( 'Add New Item', 'wp-guesthouse-reservation-form' ),
            'add_new'               => __( 'Add New', 'wp-guesthouse-reservation-form' ),
            'new_item'              => __( 'New Item', 'wp-guesthouse-reservation-form' ),
            'edit_item'             => __( 'Edit Item', 'wp-guesthouse-reservation-form' ),
            'update_item'           => __( 'Update Item', 'wp-guesthouse-reservation-form' ),
            'view_item'             => __( 'View Item', 'wp-guesthouse-reservation-form' ),
            'view_items'            => __( 'View Items', 'wp-guesthouse-reservation-form' ),
            'search_items'          => __( 'Search Item', 'wp-guesthouse-reservation-form' ),
            'not_found'             => __( 'Not found', 'wp-guesthouse-reservation-form' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'wp-guesthouse-reservation-form' ),
            'featured_image'        => __( 'Featured Image', 'wp-guesthouse-reservation-form' ),
            'set_featured_image'    => __( 'Set featured image', 'wp-guesthouse-reservation-form' ),
            'remove_featured_image' => __( 'Remove featured image', 'wp-guesthouse-reservation-form' ),
            'use_featured_image'    => __( 'Use as featured image', 'wp-guesthouse-reservation-form' ),
            'insert_into_item'      => __( 'Insert into item', 'wp-guesthouse-reservation-form' ),
            'uploaded_to_this_item' => __( 'Uploaded to this item', 'wp-guesthouse-reservation-form' ),
            'items_list'            => __( 'Items list', 'wp-guesthouse-reservation-form' ),
            'items_list_navigation' => __( 'Items list navigation', 'wp-guesthouse-reservation-form' ),
            'filter_items_list'     => __( 'Filter items list', 'wp-guesthouse-reservation-form' ),
        );
        $args = array(
            'label'                 => __( 'Reservation', 'wp-guesthouse-reservation-form' ),
            'description'           => __( 'Use for keep data from reservation form', 'wp-guesthouse-reservation-form' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'editor', 'custom-fields', ),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => 'edit.php?post_type=reservation',
            'menu_position'         => 5,
            'show_in_admin_bar'     => false,
            'show_in_nav_menus'     => true,
            'can_export'            => false,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
        );
        register_post_type( 'reservation', $args );

    }

    /**
     * Save data from form to database reservation post type.
     */
    public function save_reservation()
    {
        $this->session_start();
        $errors = array();
        $has_error = false;

        if (!empty($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'save_reservation'))
        {
            // Validate data
            $fullname = sanitize_text_field($_POST['fullname']);
            $email = sanitize_email($_POST['email']);
            $mobile = sanitize_text_field($_POST['mobile']);
            $address = sanitize_textarea_field($_POST['address']);
            $adult = intval($_POST['adult']);
            $child = intval($_POST['child']);
            $note = sanitize_textarea_field($_POST['note']);
            $room_type = sanitize_text_field($_POST['room-type']);
            $checkin = sanitize_text_field($_POST['checkin']);
            $checkout = sanitize_text_field($_POST['checkout']);

            if(empty($fullname)){
                $errors[] = 'Fullname is required';
                $has_error = true;
            }

            if(empty($email)){
                $errors[] = 'Email is required';
                $has_error = true;
            }

            if(empty($mobile)){
                $errors[] = 'Mobile is required';
                $has_error = true;
            }

            if(empty($address)){
                $errors[] = 'Address is required';
                $has_error = true;
            }

            if($adult <= 0){
                $errors[] = 'Adult is required';
                $has_error = true;
            }

            if(empty($note)){
                $errors[] = 'Note is required';
                $has_error = true;
            }

            if(empty($checkin)){
                $errors[] = 'Checkin date is required';
                $has_error = true;
            }

            if(empty($checkout)){
                $errors[] = 'Checkout date is required';
                $has_error = true;
            }

            // Keep errors
            $_SESSION['errors'] = $errors;

            // If has some error, redirect to form page and display error.
            if($has_error){
                $page = get_permalink(get_page_by_path( 'reservation' ));
                $page = add_query_arg('status', 'fail', $page);

                wp_redirect(esc_url($page));
                exit();
            }

            $content = "
                <p>Firstname - Lastname : {$fullname}</p>
                <p>Email : {$email}</p>
                <p>Mobile : {$mobile}</p>
                <p>Address : {$address}</p>
                <p>Adult : {$adult}</p>
                <p>Child : {$child}</p>
                <p>Room Type : {$room_type}</p>
                <p>More special : {$note}</p>
                <p>Checkin Date : {$checkin}</p>
                <p>Checkout Date : {$checkout}</p>
            ";
            $params = array(
                'post_title'    => 'reservation #'.date('d-m-Y H:i:s'),
                'post_content'  => $content,
                'post_type'     => 'reservation',
                'post_status'   => 'publish',
            );

            // Insert the post into the database
            $ID = wp_insert_post($params);

            // Keep post data to custom field for future.
            update_post_meta($ID, 'fullname', $fullname);
            update_post_meta($ID, 'email', $email);
            update_post_meta($ID, 'mobile', $mobile);
            update_post_meta($ID, 'address', $address);
            update_post_meta($ID, 'adult', $adult);
            update_post_meta($ID, 'child', $child);
            update_post_meta($ID, 'room-type', $room_type);
            update_post_meta($ID, 'note', $note);
            update_post_meta($ID, 'checkin', $checkin);
            update_post_meta($ID, 'checkout', $checkout);

            // Send email to admin and customer
            $headers = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=utf8' . "\r\n";
            $headers .= 'From: WP Reservation Form <' . get_option('admin_email') . '>' . "\r\n" .
            $headers .= 'X-Mailer: PHP/' . phpversion();

            $to = array();
            $to[] = get_option('admin_email');
            $to[] = $email;

            wp_mail($to, 'Reservation', $content, $headers);

            // Redirect to form page, and display success message.
            $page = get_permalink(get_page_by_path( 'reservation' ));
            $page = add_query_arg('status', 'success', $page);

            wp_redirect(esc_url($page));
            exit();
        }
    }

    /**
     * Load custom stylesheet on reservation form.
     */
    public function load_stylesheet()
    {
        wp_enqueue_style('bootstrap', plugins_url('css/bootstrap.min.css', __FILE__));
        wp_enqueue_style('bootstrap-daterangepicker', plugins_url('css/daterangepicker.css', __FILE__));
        wp_enqueue_style('wp-guesthouse-reservation-form', plugins_url('css/wp-reservation-form.css', __FILE__));
    }

    /**
     * Load custom javascripit on reservation form.
     */
    public function load_javascript()
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script('moment', plugins_url('js/moment.min.js', __FILE__), array('jquery'));
        wp_enqueue_script('bootstrap-daterangepicker', plugins_url('js/daterangepicker.js', __FILE__), array('jquery', 'moment'));
        wp_enqueue_script('parsley', plugins_url('js/parsley.min.js', __FILE__),  array( 'jquery' ));
        wp_enqueue_script('wp-guesthouse-reservation-form', plugins_url('js/wp-reservation-form.js', __FILE__),  array( 'jquery' ));
    }

    /**
     * Display errors
     */
    public function display_errors()
    {
        $this->session_start();

        if( empty($_SESSION['errors']) ){
            return;
        }

        $errors = $_SESSION['errors'];
        ?>
        <ul class="errors">
            <?php foreach ( $errors as $error ) : ?>
                <li><?php echo esc_html( $error ); ?></li>
            <?php endforeach; ?>
        </ul>
        <?php

        unset($_SESSION['errors']);
    }

    /**
     * Start session.
     */
    public function session_start()
    {
        if(!isset($_SESSION)){
            session_start();
        }
    }

    /**
     * Register reservation_form shortcode.
     */
    public function register_shortcode(){
        add_shortcode( 'reservation_form', array($this, 'reservation_form') );
    }

    /**
     * Display form when execute reservation_form shortcode.
     */
    public function reservation_form()
    {
        $rooms = get_posts(array('post_type' => 'room', 'posts_per_page' => -1));
    ?>
        <div class="col-md-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2><?php _e('Reservation Form', 'wp-guesthouse-reservation-form') ?></h2>
                    <div class="clearfix"></div>
                    <?php if(!empty($_GET['status']) && $_GET['status'] == 'success'){ ?>
                    <div class="alert-success"><?php _e( 'Data has recieved', 'wp-guesthouse-reservation-form' ) ?></div>
                    <?php }?>
                    <?php $this->display_errors(); ?>
                </div>
                <div class="x_content">
                    <br />
                    <form method="post" name="reservation-form" id="reservation-form" class="form-horizontal form-label-left" data-parsley-validate>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12"><?php _e('Firstname - Lastname', 'wp-guesthouse-reservation-form') ?> <span class="required">*</span></label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                                <input name="fullname" id="fullname" type="text" class="form-control" placeholder="<?php _e('Firstname - Lastname', 'wp-guesthouse-reservation-form') ?>" required="required">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12"><?php _e('Email', 'wp-guesthouse-reservation-form') ?> <span class="required">*</span></label>
                            <div class="col-md-9 col-sm-9 col-xs-12 form-group has-feedback">
                                <input name="email" id="email" class="form-control " placeholder="<?php _e('Email', 'wp-guesthouse-reservation-form') ?>" type="email" required="required">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12"><?php _e('Mobile', 'wp-guesthouse-reservation-form') ?> <span class="required">*</span></label>
                            <div class="col-md-9 col-sm-9 col-xs-12 form-group has-feedback">
                                <input name="mobile" id="mobile" class="form-control " placeholder="<?php _e('Mobile', 'wp-guesthouse-reservation-form') ?>" type="text" required="required">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12"><?php _e('Address', 'wp-guesthouse-reservation-form') ?> <span class="required">*</span></label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                                <textarea name="address" id="address" class="form-control" rows="3" placeholder='<?php _e('Address', 'wp-guesthouse-reservation-form') ?>' required="required"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12"><?php _e('Adult', 'wp-guesthouse-reservation-form') ?> <span class="required">*</span></label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                                <select name="adult" id="adult" class="form-control" required="required">
                                    <option value=""><?php _e( 'Choose option', 'wp-guesthouse-reservation-form' ) ?></option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12"><?php _e('Child', 'wp-guesthouse-reservation-form') ?></label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                                <select name="child" id="child" class="form-control">
                                    <option value=""><?php _e( 'No child', 'wp-guesthouse-reservation-form' ) ?></option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12"><?php _e('Room Type', 'wp-guesthouse-reservation-form') ?> <span class="required">*</span></label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                                <select name="room-type" id="room-type" class="form-control" required="required">
                                    <option value=""><?php _e('Select room type', 'wp-guesthouse-reservation-form') ?></option>
                                    <?php foreach ($rooms as $room): ?>
                                    <option value="<?php echo $room->post_title; ?>"><?php echo $room->post_title; ?></option>
                                    <?php endforeach;?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12"><?php _e('More special', 'wp-guesthouse-reservation-form') ?> <span class="required">*</span></label>
                            <div class="col-md-9 col-sm-9 col-xs-12">
                                <textarea name="note" id="note" class="form-control" rows="3" placeholder='More special' required="required"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12"><?php _e('Checkin Date', 'wp-guesthouse-reservation-form') ?> <span class="required">*</span></label>
                            <div class="col-md-9 col-sm-9 col-xs-12 form-group has-feedback">
                                <input name="checkin" id="checkin" class="datepicker form-control " placeholder="<?php _e('Checkin Date', 'wp-guesthouse-reservation-form') ?>" type="text" required="required">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12"><?php _e('Checkout Date', 'wp-guesthouse-reservation-form') ?> <span class="required">*</span></label>
                            <div class="col-md-9 col-sm-9 col-xs-12 form-group has-feedback">
                                <input name="checkout" id="checkout" class="datepicker form-control " placeholder="<?php _e('Checkout Date', 'wp-guesthouse-reservation-form') ?>" type="text" required="required">
                            </div>
                        </div>

                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-3">
                                <?php wp_nonce_field('save_reservation'); ?>
                                <button type="submit" class="btn btn-success"><?php _e('Submit', 'wp-guesthouse-reservation-form') ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php
    }

}

register_activation_hook(__FILE__, array('WP_Guesthouse_Reservation_Form', 'activate'));
register_deactivation_hook(__FILE__, array('WP_Guesthouse_Reservation_Form', 'deactivate'));

$wp_guesthouse_reservation_form = new WP_Guesthouse_Reservation_Form();
