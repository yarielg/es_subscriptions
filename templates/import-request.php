
<?php
if(isset($_POST['import_users'])){

    $file_name = sanitize_file_name($_FILES['file_import']['name']);
    $file_tmp = sanitize_text_field($_FILES['file_import']['tmp_name']);
    move_uploaded_file($file_tmp, ES_PLUGIN_PATH . 'uploads/' . $file_name );

    $users_file =  array();

    if (($handle = fopen(ES_PLUGIN_PATH . 'uploads/' . $file_name, "r")) !== FALSE) {
        $cont=0;
        $current_primary_account = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE && $cont < 5) {
            if($cont === 0){ $cont++; continue; }
            if('' != trim($data[0]) && '' != trim($data[2]) && '' != trim($data[3])){
                $type = $data[0];
                $organization = $data[1];
                $name = 'XXX ' . $data[2];
                $email = $data[3];
                $address = $data[4];
                $address2 = $data[5];
                $city = $data[6];
                $state = $data[7];
                $country = $data[8];
                $zip = $data[9];
                $business_phone = $data[10];
                $phone1 = $data[11];
                $phone2 = $data[12];
                $notes = $data[13];
                $status = $data[14];

                $user = get_user_by('login', $email);

                if($user){
                    echo "User on index " . $cont . " was not imported, it already exist on site" . PHP_EOL;
                    continue;
                }

                $account_id = wp_insert_user( array(
                    'user_pass' => 'Hello',
                    'user_email' => $email,
                    'first_name' => $name,
                    'user_login' => $email,
                    'description' => $notes,
                ) );

                update_user_meta( $user, "billing_first_name", $name );
                update_user_meta( $user, "billing_last_name", '');
                update_user_meta( $user, "billing_company", $organization );
                update_user_meta( $user, "billing_email", $email );
                update_user_meta( $user, "billing_address_1", $address);
                update_user_meta( $user, "billing_address_2", $address2 );
                update_user_meta( $user, "billing_city", $city);
                update_user_meta( $user, "billing_postcode", $zip );
                update_user_meta( $user, "billing_country", $country);
                update_user_meta( $user, "billing_state", $state );
                update_user_meta( $user, "billing_phone", $phone1 );
                update_user_meta( $user, "billing_phone2", $phone2 );
                update_user_meta( $user, "billing_business_phone", $business_phone );

                $new_user = get_user_by( 'ID', $account_id );

                if($type == 'Secondary'){

                    $accounts_serialized = get_user_meta($current_primary_account, 'subaccount_ids', true);
                    $accounts = $accounts_serialized && count(unserialize($accounts_serialized)) > 0 ? unserialize($accounts_serialized) : [];

                    $new_user->add_role( 'sub-account' );
                    $accounts[] = $account_id;

                    //Create the relation main account / sub-accounts
                    update_user_meta($current_primary_account,'subaccount_ids',serialize($accounts));
                    //Create the relation sub-account / main account
                    update_user_meta($account_id,'main_account_id',$current_primary_account);
                }else{
                    $new_user->add_role( 'business' );
                    $current_primary_account = $account_id;
                }

            }else{
                echo "User on index " . $cont . " was not imported, required info was not found" . PHP_EOL;
            }

            $cont++;
        }
        fclose($handle);
    }else{
        echo "There was an error in your file.";
    }

    echo "<strong> Import Finished!</strong>" . PHP_EOL;

}

?>
