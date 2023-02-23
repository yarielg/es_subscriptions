<?php

/*
*
* @package Yariko
*
*/

namespace Es\Inc\Base;

class Ajax{

    public function register(){

        /**
         * Ajax actions
         */
        add_action( 'wp_ajax_es_add_service', array($this,'addService'));
        add_action( 'wp_ajax_nopriv_es_add_service', array($this,'addService'));

        add_action( 'wp_ajax_nopriv_es_add_business', array($this,'addBusiness'));
        add_action( 'wp_ajax_es_add_business', array($this,'addBusiness'));

        add_action( 'wp_ajax_nopriv_get_business_account', array($this,'getBusinessAccounts'));
        add_action( 'wp_ajax_get_business_account', array($this,'getBusinessAccounts'));

        add_action( 'wp_ajax_nopriv_es_approve_deny_business', array($this,'approveDenyAccount'));
        add_action( 'wp_ajax_es_approve_deny_business', array($this,'approveDenyAccount'));

        add_action( 'wp_ajax_nopriv_es_business_selection', array($this,'businessOrder'));
        add_action( 'wp_ajax_es_business_selection', array($this,'businessOrder'));
    }

    function approveDenyAccount(){
       $user_id = $_POST['user_id'];
       $action_account = $_POST['action_account'];

       if($action_account === 'approve'){
           update_user_meta($user_id, '_es_status', 'Approved');

           $user = get_user_by('id', $user_id);

           if($user){

               $data = [];

               $pass = get_user_meta($user_id, '_es_pass', true);

               $data['link'] = site_url() . '/business-shop?user_id=' . $user_id . '&token=' . $pass;
               $data['name'] = $user->display_name;
               $data['email'] = $user->user_email;
               $data['token'] = $pass;
               $data['my_account'] = site_url() . '/my-account';

               if(es_send_business_shop_email($user->user_email, $data)){
                   echo json_encode(array('success' => true));
                   wp_die();
               }else{
                   echo json_encode(array('success' => false, 'msg' => 'The account was approved but the email was not sent to the customer, please try again in a couple minutes'));
                   wp_die();
               }

           }




       }else{
           update_user_meta($user_id, '_es_status', 'Denied');
       }

        echo json_encode(array('success' => true));
        wp_die();
    }

    function getBusinessAccounts(){

        $number      = $_POST['length'];
        $offset      = $_POST['start'];
        $users       = get_users(array(
            'role'    => 'business',
            'orderby' => 'ID',
        ));
        $query       = get_users(array(
            'role'    => 'business',
            'orderby' => 'ID',
            'order'   => 'DESC',
            'offset' => $offset,
            'number' => $number,
            'search' => '*'.$_POST['search'].'*',
            'search_columns' => array( 'user_login', 'user_email' )
        ));
        $total_users = count($users);
       // $total_pages = intval($total_users / $number) + 1;

        $users_formatted = [];

        foreach ($query as $user){

            $user_obj = [];

            $user_obj['id'] = $user->ID;
            $user_obj['company'] = get_user_meta($user->ID, '_es_company',true);
            $user_obj['pass'] = get_user_meta($user->ID, '_es_pass',true);
            $user_obj['business_type'] = get_user_meta($user->ID, '_es_business_type',true);
            $user_obj['address'] = get_user_meta($user->ID, '_es_address',true);
            $user_obj['referred'] = get_user_meta($user->ID, '_es_referred',true);
            $user_obj['phone'] = get_user_meta($user->ID, '_es_phone',true);
            $user_obj['description'] = get_user_meta($user->ID, '_es_description',true);
            $user_obj['status'] = get_user_meta($user->ID, '_es_status',true) ? get_user_meta($user->ID, '_es_status',true) : 'Pending';
            $user_obj['email'] = $user->data->user_email;
            $user_obj['name'] = $user->data->display_name;

            $users_formatted[] = $user_obj;

        }

        echo json_encode(array('success' => true, 'users' => $users_formatted, 'recordsFiltered' => $total_users ));
        wp_die();
    }

    function addBusiness(){

        //Getting the post fields
        $first = $_POST['first'];
        $last = $_POST['last'];
        $company = $_POST['company'];
        $business_type = $_POST['business_type'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $referred = $_POST['referred'];
        $description = $_POST['description'];
        $number_of_employee = $_POST['number_of_employees'];

        if(email_exists($email)){
            echo json_encode(array('success' => false, 'msg' => 'The email already exists' ));
            wp_die();
        }

        $password = wp_generate_password(15, false, false);

        $userdata = array(
            'user_login' 			=> $email,
            'user_email' 			=> $email,
            'user_pass'             => $password,
            'display_name' 			=> $first . ' ' . $last,
            'nickname' 				=> $first . ' ' . $last,
            'first_name' 			=> $first,
            'last_name' 			=> $last,
            'role' 					=> 'business',

        );

        $user_id = wp_insert_user($userdata);

        if ( ! is_wp_error( $user_id ) ) {
            $user = get_user_by( 'ID', $user_id );
            $user->add_role( 'business' );

            add_user_meta($user_id, '_es_company', $company);
            add_user_meta($user_id, '_es_business_type', $business_type);
            add_user_meta($user_id, '_es_address', $address);
            add_user_meta($user_id, '_es_referred', $referred);
            add_user_meta($user_id, '_es_description', $description);
            add_user_meta($user_id, '_es_phone', $phone);
            add_user_meta($user_id, '_es_number_of_employees', $number_of_employee);

            $userdata['company'] = $company;
            $userdata['business_type'] = $business_type;
            $userdata['address'] = $address;
            $userdata['phone'] = $phone;
            $userdata['number_of_employees'] = $number_of_employee;

            add_user_meta($user_id, '_es_pass', $password);

            //todo we need to send other email to the customer and change the above email for admin one since the above one is fro the admin
           if(es_send_admin_business_request_email(get_option('admin_email'), $userdata)){
               echo json_encode(array('success' => true, 'msg' => 'The account was added, an email was sent to the admin' ));
               wp_die();
           }

        }else{
            echo json_encode(array('success' => false, 'msg' => 'The account was added ' . $email ));
            wp_die();
        }

        echo json_encode(array('success' => true, 'msg' => 'The account was added, it looks like the email was not sent to the admin' ));
        wp_die();
    }

    /**
     * Add Service to the cart
     */
     function addService(){

         $product_id = $_POST['id'];
         $interval = $_POST['interval'];

         $product = wc_get_product($product_id);
         $variations = $product->get_available_variations();

         if(count($variations > 0)){
             foreach ($variations as $variation){
                /* echo json_encode(array('success' => false, 'variation' => $variation['attributes']['attribute_pa_billing-period'] ));
                 wp_die();*/
                 if($variation['attributes']['attribute_pa_billing-period'] == $interval){
                     WC()->cart->empty_cart();
                     WC()->cart->add_to_cart( $variation['variation_id'], 1);
                     echo json_encode(array('success' => true, 'variations' => $interval  ));
                     wp_die();
                 }
             }
         }

         echo json_encode(array('success' => false, 'variations' => [] ));
         wp_die();
     }

    function businessOrder(){
        $collect_inside = $_POST['collect_inside'];

        WC()->cart->empty_cart();

        //Adding the initial product fee
        WC()->cart->add_to_cart( 6225 , 1);

        if($collect_inside === 'yes'){
            WC()->cart->add_to_cart( 6226 , 1);
        }

        echo json_encode(array('success' => true));
        wp_die();

    }
}