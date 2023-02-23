<?php

/*
*
* @package Yariko
*
*/

namespace Es\Inc\Base;

class Pages{

    public function register(){

        add_action('admin_menu', function(){
            add_menu_page('Earth Savers Subs', 'Earth Savers Subs', 'manage_options', 'es-main', array($this,'businessAccounts') , ES_PLUGIN_URL. 'assets/img/price-tag.png',110);
        });

        add_action('admin_menu',function(){
            $subs_page =  add_submenu_page( 'es-main', __('Business Accounts','es_subscriptions'), __('Business Accounts','es_subscriptions'),'manage_options', 'es-main', array($this,'businessAccounts'));
            $subs_import =  add_submenu_page( 'es-main', __('Import','es_subscriptions'), __('Import','es_subscriptions'),'manage_options', 'es-import', array($this,'import'));

            add_action( 'load-' . $subs_page, function(){
                add_action( 'admin_enqueue_scripts',function (){

                    wp_enqueue_style('es-bootstrap-css', ES_PLUGIN_URL . '/assets/css/admin/bootstrap.min.css');

                    wp_enqueue_style('es-app-css', ES_PLUGIN_URL . '/dist/app.css'  );
                    wp_enqueue_style('es-vendors-css', ES_PLUGIN_URL . '/dist/vendors.css'  );
                   // wp_enqueue_script( 'es-bootstrap-js', ES_PLUGIN_URL . '/assets/js/admin/bootstrap.bundle.min.js');
                    wp_enqueue_style('main_admin_styles',  ES_PLUGIN_URL . '/assets/css/admin/main.css' );

                    wp_enqueue_script( 'es-runtime-js', ES_PLUGIN_URL . '/dist/runtime.wec.bundle.js', '1.00', true);
                    wp_enqueue_script( 'es-vendors-js', ES_PLUGIN_URL . '/dist/vendors.wec.bundle.js', array('es-runtime-js'),'1.00', true);

                    wp_enqueue_script( 'es-app-js', ES_PLUGIN_URL . '/dist/app.wec.bundle.js', array('es-runtime-js', 'es-vendors-js'),'1.00', true);

                    $args = array(
                        'ajax_url'=> admin_url('admin-ajax.php'),
                        'plugin_url' => ES_PLUGIN_URL,
                        'plugin_path' => ES_PLUGIN_URL,
                    );
                    wp_localize_script( 'es-app-js', 'es_parameters ', $args );

                });
            });

            add_action( 'load-' . $subs_import, function(){
                add_action( 'admin_enqueue_scripts',function (){

                    wp_enqueue_style('es-bootstrap-css', ES_PLUGIN_URL . '/assets/css/admin/bootstrap.min.css');
                     wp_enqueue_script( 'es-bootstrap-js', ES_PLUGIN_URL . '/assets/js/admin/bootstrap.bundle.min.js');

                });
            });
        });

    }


    /* function products(){
         require_once WRPL_PLUGIN_PATH . 'templates/products.php';
     }*/

    function businessAccounts(){
        ?>
        <style>
            #wpcontent {
                padding-left: 0 !important;
            }
            #wrpl-app{
            }
        </style>
        <div id="es-app"></div>
        <?php
    }

    function import(){
        $content = es_template(ES_PLUGIN_PATH . '/templates/import.php',array());
        echo $content;
    }


}
?>