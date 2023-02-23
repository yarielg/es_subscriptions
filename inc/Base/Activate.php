<?php

/*
*
* @package yariko
*
*/
namespace Es\Inc\Base;

class Activate{

    public static function activate(){

        //Set the Business Account Role

        add_role(
            'business',
            'Business Account',
            array(
                'read'         => true,
                'delete_posts' => false
            )
        );
    }
}
