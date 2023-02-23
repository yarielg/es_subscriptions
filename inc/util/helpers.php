<?php

function es_error_list($error){
    switch  ($error){
        case 'email_exist':
            return 'The email already exist in the system, please try with a new one';
            exit;
    }
}

function es_template( $file, $args ){
    // ensure the file exists
    if ( !file_exists( $file ) ) {
        return '';
    }

    // Make values in the associative array easier to access by extracting them
    if ( is_array( $args ) ){
        extract( $args );
    }

    // buffer the output (including the file is "output")
    ob_start();
    include $file;
    return ob_get_clean();
}

function es_send_admin_business_request_email($email, $data){
    $title   = 'Business Account Request';
    $content = es_template(ES_PLUGIN_PATH . '/templates/admin_business_request_email.php',$data);
    $headers[] = 'Content-Type: text/html; charset=UTF-8';

    return wp_mail( $email, $title, $content,$headers);
}

function es_send_business_shop_email($email, $data){
    $title   = 'Your Account was approved';
    $content = es_template(ES_PLUGIN_PATH . '/templates/business_shop_approval_email.php',$data);
    $headers[] = 'Content-Type: text/html; charset=UTF-8';

    return wp_mail( $email, $title, $content,$headers);
}