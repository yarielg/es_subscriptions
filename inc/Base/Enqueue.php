<?php

/*
*
* @package Yariko
*
*/

namespace Es\Inc\Base;

class Enqueue{

    public function register(){

        add_action( 'wp_enqueue_scripts',  array($this,'enqueue_frontend'));

    }

    /**
     * Enqueueing the main scripts with all the javascript logic that this plugin offer
     */
    function enqueue_frontend(){
        wp_enqueue_style('toastr-css', 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css');
        wp_enqueue_style('main-css', ES_PLUGIN_URL . '/assets/css/main.css');


        wp_enqueue_script('toastr-js', 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js');
        wp_enqueue_script('pristine-js', ES_PLUGIN_URL  . '/assets/js/pristine.min.js');
        wp_enqueue_script('main-js', ES_PLUGIN_URL  . '/assets/js/main.js' ,array('jquery', 'toastr-js', 'pristine-js'),'1.0', false);

        wp_localize_script( 'main-js', 'parameters', ['ajax_url'=> admin_url('admin-ajax.php'), 'plugin_url' => ES_PLUGIN_URL]);


    }

}