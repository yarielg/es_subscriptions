<?php

/*
*
* @package Yariko
*
*/

namespace Es\Inc\Base;

use MailchimpMarketing\ApiClient;

class Settings{

    public function register(){


        add_action( 'after_setup_theme', array($this, 'woocommerce_support') );

        add_action( 'template_redirect', array($this, 'restrict_business_shop_page') );

        add_shortcode( 'services_list', array($this, 'services_list') );
        add_shortcode( 'es_business_form', array($this, 'business_form') );
        add_shortcode( 'es_business_shop', array($this, 'business_shop') );


        add_filter( 'woocommerce_locate_template', array($this, 'kgm_locate_template'), 10, 3 );

        add_filter( 'woocommerce_checkout_fields', array($this, 'remove_checkout_fields') );

        add_action( 'woocommerce_checkout_billing', array($this, 'add_checkout_fields') , 9999, 1 );

        add_action( 'woocommerce_checkout_update_order_meta', array($this, 'update_checkout_fields') , 10, 1);

        add_action( 'woocommerce_after_checkout_validation', array($this, 'custom_fields_validation'), 10, 2 );

        add_action( 'woocommerce_account_content', array($this, 'add_my_tickets_content') );

        add_action( 'init', array($this, 'cptui_register_my_routes') );

        /*add_action( 'init', array($this, 'my_tickets_endpoint')  );
        add_filter( 'woocommerce_get_query_vars', array($this, 'my_tickets_vars'), 0 );
        add_filter( 'woocommerce_account_menu_items', array($this, 'my_tickets_link') );
        add_action( 'woocommerce_account_premium-support_endpoint', array($this, 'my_tickets_content') );*/


    }

    function add_my_tickets_content(){
        echo "<a class='ticket-link' href='/my-tickets'>My tickets</a>";
        echo "<a class='ticket-link' href='/submit-ticket'>New ticket</a><br><br>";
        $current_user = get_current_user_id();
        $route_id = get_field('route_id', 'user_' . $current_user);

        if($route_id){
            $recycling_collection_interval = get_field('recycling_collection_interval', $route_id[0]);
            $recycling_collection_date = get_field('recycling_collection_date', $route_id[0]);

            echo '<p style="text-align: right"><strong>Recycling Collection:</strong> ' . $recycling_collection_interval['label'] . ' ' . $recycling_collection_date['label'] . '</p>';

        }

        $mailchimp = new ApiClient();

        $mailchimp->setConfig([
            'apiKey' => '94db40b731a9ebb658caee4ff7d5ef9c-us21',
            'server' => 'us21',
            'verify' => 'false'
        ]);

        $response = $mailchimp->ping->get();
        print_r($response);

    }

    /*function my_tickets_endpoint() {
        add_rewrite_endpoint( '/my-tickets', EP_ROOT | EP_PAGES );
    }

    function my_tickets_vars( $vars ) {
        $vars[] = 'my-tickets';
        return $vars;
    }

    function my_tickets_link( $items ) {
        $items['my-tickets'] = 'My tickets';
        return $items;
    }

    function my_tickets_content() {
        echo do_shortcode( '[tickets]' );
    }*/

    function restrict_business_shop_page(){

        global $post;
        if($post->post_name === 'business-shop'){

            if(!is_user_logged_in() && isset($_GET['user_id']) && isset($_GET['token'])){

                wp_clear_auth_cookie();
                wp_set_current_user ( $_GET['user_id'] );
                wp_set_auth_cookie  ( $_GET['user_id'] );

                $pass = get_user_meta($_GET['user_id'], '_es_pass', true);

                if($_GET['token'] == $pass){

                    wp_clear_auth_cookie();
                    wp_set_current_user ( $_GET['user_id'] );
                    wp_set_auth_cookie  ( $_GET['user_id'] );

                    do_shortcode('[business_shop]');

                }else{

                    wp_logout();
                    exit();

                }

            }else{

                wp_redirect(site_url('/my-account'));
                exit();

            }



        }
    }

    function business_shop(){
        ob_start();

        //get product business
        $products = get_posts(array(
            'numberposts'	=> -1,
            'post_type'		=> 'product',
            'meta_key'		=> 'es_is_business',
            'meta_value'	=> '1'
        ));


        ?>
        <style>.pill{ display: none; }</style>
        <h2>Please select the service you want</h2>
        <br>
        <h4>TWO 32-gallon bins for plastics/cans</h4>
        <form action="">
            <div class="es_row">
                <div class="col">
                    <input disabled checked type="checkbox" name="initial_service" id="initial_service">
                    <label for="initial_service" class="es_label">Initial fee includes ($40)</label>
                </div>
            </div>
            <h4>Do you want inside collection?</h4>
            <div class="es_row">
                <div class="col">
                    <input type="radio" name="collect_inside" class="collect_inside" id="collect_inside_yes">
                    <label for="collect_inside_yes" value="1" class="es_label">Yes, I want inside collection ($12)</label>
                </div>
                <div class="col">
                    <input type="radio" name="collect_inside" class="collect_inside" id="collect_inside_no">
                    <label for="collect_inside_no" value="0" class="es_label">No, I don't want inside collection</label>
                </div>
            </div>
        </form>

        <div class="es_row">
            <div class="es_col">
                <input class="btn btn-primary btn-lg" type="submit" value="Send Request" id="btn_send_business_selection">
            </div>
        </div>

        <?php
        return  ob_get_clean();
    }

    function business_form(){

        ob_start();

        ?>

        <form id="es_business_form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
            <div class="mask">
                <p>Loading...</p>
            </div>
            <input type="hidden" name="action" value="send_business_data" />
            <div class="es_row">
                <div class="es_col_50">
                    <label for="es_first" class="es_label">First Name</label>
                    <input required class="es_input" type="text" name="es_first" id="es_first">
                </div>
                <div class="es_col_50">
                    <label for="es_last" class="es_label">Last Name</label>
                    <input required class="es_input" type="text" name="es_last" id="es_last">
                </div>
            </div>
            <div class="es_row">
                <div class="es_col_50">
                    <label for="es_company" class="es_label">Company Name</label>
                    <input required class="es_input" type="text" name="es_company" id="es_company">
                </div>
                <div class="es_col_50">
                    <label for="es_business_type" class="es_label">Business Type</label>
                    <input required class="es_input" type="text" name="es_business_type" id="es_business_type">
                </div>
            </div>
            <div class="es_row">
                <div class="es_col_50">
                    <label for="es_email" class="es_label">Email</label>
                    <input required class="es_input" type="email" name="es_email" id="es_email">
                </div>
                <div class="es_col_50">
                    <label for="es_phone" class="es_label">Phone</label>
                    <input required class="es_input" type="number" name="es_phone" id="es_phone" pattern="/^[+]*[(]{0,1}[0-9]{1,3}[)]{0,1}[-\s\./0-9]*$/g">
                </div>
            </div>
            <div class="es_row">
                <div class="es_col">
                    <label for="es_address" class="es_label">Service Location(s) Business Address(es)</label>
                    <textarea type="text" name="es_address" id="es_address"></textarea>
                </div>
            </div>
            <div class="es_row">
                <div class="es_col">
                    <label for="es_number_of_employees" class="es_label">Number of Employees</label>
                    <input required class="es_input" type="number" min="1" max="100000"  step="1" value="10" name="es_number_of_employees" id="es_number_of_employees">
                </div>
            </div>

            <div class="es_row">
                <div class="es_col">
                    <label for="es_referred" class="es_label">Who Referred You? Or Enter Promo Code</label>
                    <input required class="es_input" type="text" name="es_referred" id="es_referred">
                </div>
            </div>

            <div class="es_row">
                <div class="es_col">
                    <label for="es_description" class="es_label">Description of what you are looking for or any comments</label>
                    <textarea required type="text" name="es_description" id="es_description"></textarea>
                </div>
            </div>

            <div class="es_row">
                <div>
                    <input required type="checkbox" name="es_terms" id="es_terms">
                    <label for="es_terms">I have read and agree to the terms and conditions for service. See the <a
                                href="/biz-t-and-c/" target="_blank">terms and conditions</a>  for service.</label>
                </div>

            </div>

            <div class="es_row">
                <div class="es_col">
                    <?php wp_nonce_field( 'es_business_form', 'validate_business_form' ); ?>
                    <input class="btn btn-primary btn-lg" type="submit" value="Send Request" id="btn_send_business_info">
                </div>
            </div>
        </form>
        
        <?php
        return  ob_get_clean();
    }

    /**
     * @return void
     * @description Add Woo Support to the theme
     */
    function woocommerce_support() {
        add_theme_support( 'woocommerce' );
        if( function_exists('acf_add_options_page') ) {

            acf_add_options_page(array(
                'page_title' 	=> 'General Settings',
                'menu_title'	=> 'General Settings',
                'menu_slug' 	=> 'es-general-settings',
                'capability'	=> 'edit_posts',
                'redirect'		=> false
            ));

        }
    }

    /**
     * @description Custom field checkout validation
     */
    function custom_fields_validation( $data, $errors ) {
        if ( empty( $_POST['who_referred_you'] ) ) {
            $errors->add('required-field', __('PLease, select who referred you.', 'woocommerce'));
        }

        $zipcodes  = get_field('es_zipcode_list', 'option');
        $flag = false;
        if( $zipcodes ) {
            foreach( $zipcodes as $row ) {
                $zipcode = $row['zipcode'];
                if($zipcode == $_POST['billing_postcode']){
                    $flag = true;
                    break;
                }
            }
        }

        if(!$flag){
            $errors->add('required-field', __('There is no service in you area, please check <a href="/residential-services#theform" target="_blank">here</a> to see what area we cover', 'woocommerce'));
        }

    }

    /**
     * @description Add the custom field to the order
     */
    function update_checkout_fields ( $order_id ) {
        if ( isset( $_POST ['who_referred_you'] ) &&  '' != $_POST ['who_referred_you'] ) {
            add_post_meta( $order_id, '_who_referred_you',  sanitize_text_field( $_POST ['who_referred_you'] ) );
        }
    }

    /**
     * @description Add custom checkout fields
     */
    function add_checkout_fields () {
        ?>
        <br>
        <label><strong>Who Referred You?</strong></label>
        <ul class="referred_list">
            <li>
                <input name="who_referred_you" type="radio" id="choice_friend"  value="friend_or_neighbor"  />
                <label for="choice_friend">Friend or Neighbor</label>
            </li>
            <li>
                <input name="who_referred_you" type="radio" id="choice_website"  value="website"  />
                <label for="choice_website">Found your website</label>
            </li>
            <li>
                <input name="who_referred_you" type="radio" id="choice_truck"  value="truck"  />
                <label for="choice_truck">Saw your truck</label>
            </li>
        </ul>
        <!--<br>
        <label><strong>How did you find us?*</strong></label>
        <ul class="referred_list">
            <li>
                <label for="choice_friend">Online Search</label>
                <input name="who_referred_you" type="radio" id="choice_friend"  value="friend_or_neighbor"  />
            </li>
            <li>
                <label for="choice_website">Found your website</label>
                <input name="who_referred_you" type="radio" id="choice_website"  value="website"  />
            </li>
            <li>
                <label for="choice_truck">Saw your truck</label>
                <input name="who_referred_you" type="radio" id="choice_truck"  value="truck"  />
            </li>
        </ul>-->
        <?php
    }

    /**
     * @description Override the checkout fields
     */
    function remove_checkout_fields( $fields ) {
        // Billing fields
        unset( $fields['billing']['billing_company'] );

        // Shipping fields

        // Order fields
        unset( $fields['order']['order_comments'] );
        return $fields;
    }

    /**
     * @return false|string
     * @description Shortcode to create the ui to select the service and the service interval
     */
    function services_list(){
        ob_start();
        $args = array(
            'orderby'  => 'ID',
            'order' => 'ASC'

        );
        $products = wc_get_products( $args );
        ?>
        <label class="gfield_label"><strong>Select Your Service (Fee includes one 96-gallon cart)</strong></label>
        <ul class="gfield_radio" id="es_service_list">
                <?php
                $first = true;
                foreach ($products as $product){

                    ?>
                    <li class="gchoice es_choice">
                        <input name="es_service" <?php echo $first ? 'checked' : '' ?>  class="service_choice" type="radio" value="<?php echo $product->get_id(); ?>" id="choice_<?php echo $product->get_id(); ?>">
                        <label for="choice_<?php echo $product->get_id(); ?>" id="label_<?php echo $product->get_id(); ?>"><?php echo $product->get_title(); ?></label>
                    </li>
                  <?php
                    $first = false;
                }
                ?>
        </ul>
        <br>
        <label class="gfield_label"><strong>Choose the payment interval?</strong></label>
        <ul class="es_interval">
            <li>
                <input name="es_interval" checked class="interval_choice" type="radio" value="monthly" id="choice_monthly">
                <label for="choice_monthly" id="choice_monthly"> Monthly</label>
            </li>
            <li>
                <input name="es_interval" class="interval_choice" type="radio" value="quarterly" id="choice_quarterly">
                <label for="choice_quarterly" id="choice_quarterly"> Quarterly</label>
            </li>
        </ul>
        <br>
        <button id="es_submit_service" class="gform_button button btn btn-primary btn-lg">Sign Up for Residential Recycling</button>
        <?php
        return  ob_get_clean();
    }

    /**
     * @param $template
     * @param $template_name
     * @param $template_path
     * @return mixed|string
     * @description  Override the default woo templates
     */
    function kgm_locate_template( $template, $template_name, $template_path ) {
        $basename = basename( $template );

        switch ($basename){
            case 'form-checkout.php':
                $template = ES_PLUGIN_PATH . 'templates/form-checkout.php';
                break;
        }

        return $template;
    }

    /**
     *
     */
    function cptui_register_my_routes() {

        /**
         * Post Type: Routes.
         */

        $labels = [
            "name" => esc_html__( "Routes", "custom-post-type-ui" ),
            "singular_name" => esc_html__( "Route", "custom-post-type-ui" ),
            "menu_name" => esc_html__( "My Routes", "custom-post-type-ui" ),
            "all_items" => esc_html__( "All Routes", "custom-post-type-ui" ),
            "add_new" => esc_html__( "Add new", "custom-post-type-ui" ),
            "add_new_item" => esc_html__( "Add new Route", "custom-post-type-ui" ),
            "edit_item" => esc_html__( "Edit Route", "custom-post-type-ui" ),
            "new_item" => esc_html__( "New Route", "custom-post-type-ui" ),
            "view_item" => esc_html__( "View Route", "custom-post-type-ui" ),
            "view_items" => esc_html__( "View Routes", "custom-post-type-ui" ),
            "search_items" => esc_html__( "Search Routes", "custom-post-type-ui" ),
            "not_found" => esc_html__( "No Routes found", "custom-post-type-ui" ),
            "not_found_in_trash" => esc_html__( "No Routes found in trash", "custom-post-type-ui" ),
            "parent" => esc_html__( "Parent Route:", "custom-post-type-ui" ),
            "featured_image" => esc_html__( "Featured image for this Route", "custom-post-type-ui" ),
            "set_featured_image" => esc_html__( "Set featured image for this Route", "custom-post-type-ui" ),
            "remove_featured_image" => esc_html__( "Remove featured image for this Route", "custom-post-type-ui" ),
            "use_featured_image" => esc_html__( "Use as featured image for this Route", "custom-post-type-ui" ),
            "archives" => esc_html__( "Route archives", "custom-post-type-ui" ),
            "insert_into_item" => esc_html__( "Insert into Route", "custom-post-type-ui" ),
            "uploaded_to_this_item" => esc_html__( "Upload to this Route", "custom-post-type-ui" ),
            "filter_items_list" => esc_html__( "Filter Routes list", "custom-post-type-ui" ),
            "items_list_navigation" => esc_html__( "Routes list navigation", "custom-post-type-ui" ),
            "items_list" => esc_html__( "Routes list", "custom-post-type-ui" ),
            "attributes" => esc_html__( "Routes attributes", "custom-post-type-ui" ),
            "name_admin_bar" => esc_html__( "Route", "custom-post-type-ui" ),
            "item_published" => esc_html__( "Route published", "custom-post-type-ui" ),
            "item_published_privately" => esc_html__( "Route published privately.", "custom-post-type-ui" ),
            "item_reverted_to_draft" => esc_html__( "Route reverted to draft.", "custom-post-type-ui" ),
            "item_scheduled" => esc_html__( "Route scheduled", "custom-post-type-ui" ),
            "item_updated" => esc_html__( "Route updated.", "custom-post-type-ui" ),
            "parent_item_colon" => esc_html__( "Parent Route:", "custom-post-type-ui" ),
        ];

        $args = [
            "label" => esc_html__( "Routes", "custom-post-type-ui" ),
            "labels" => $labels,
            "description" => "",
            "public" => true,
            "publicly_queryable" => true,
            "show_ui" => true,
            "show_in_rest" => true,
            "rest_base" => "",
            "rest_controller_class" => "WP_REST_Posts_Controller",
            "rest_namespace" => "wp/v2",
            "has_archive" => false,
            "show_in_menu" => true,
            "show_in_nav_menus" => true,
            "delete_with_user" => false,
            "exclude_from_search" => false,
            "capability_type" => "post",
            "map_meta_cap" => true,
            "hierarchical" => false,
            "can_export" => false,
            "rewrite" => [ "slug" => "route", "with_front" => true ],
            "query_var" => true,
            "supports" => [ "title", "editor", "thumbnail" ],
            "show_in_graphql" => false,
        ];

        register_post_type( "route", $args );
    }



}