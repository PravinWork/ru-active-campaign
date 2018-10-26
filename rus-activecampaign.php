<?php
/*
Plugin Name: Registered User Sync ActiveCampaign
Plugin URI: http://www.activecampaign.com/apps/wordpress
Description: Allows you to sync registered users to the Active Campaigns Email Marketing app. It will auto sync the user who is added by register_user() method.
Author: Pravin Durugkar
Version: 1.0
Author URI: https://profiles.wordpress.org/pravind
Text Domain: rus-activecampaign
Domain Path: /languages
*/

define( 'rusac_DIR',  plugin_dir_path( __FILE__ ) );
define( 'rusac_URI', plugin_dir_url( __FILE__ ), true );
//define( 'rusac_API_URL', 'http://account.api-us1.com');

class RUSActiveCampaign {
    
    function __construct(){      
 
        add_action( 'admin_init', array($this, 'rusac_register_settings' ) );
        add_action('admin_menu', array($this, 'rusac_register_options_page') );

        //add_action('user_register', array($this, 'rusac_send_data_to_ac'));
        //profile_update
        //add_action('profile_update', array($this, 'rusac_send_data_to_ac'));
        add_action('rusac_add_new_address', array($this, 'rusac_send_data_to_ac'));
    }

    function rusac_load_api_details() {
        $api_key = get_option('rusac_api_key');
        $api_url = get_option('rusac_api_url');
        $api_output = 'serialize';
        $api_action = 'contact_add';

        $params = array(

            // the API Key can be found on the "Your Settings" page under the "API" tab.
            // replace this with your API Key
            //'api_key'      => '875007a60a311ade8c1f038b1d918bd437af9970aeab03584e2ab3580e3def928fd71868',
            'api_key'      => $api_key,

            // this is the action that adds a contact
            'api_action'   => $api_action,

            // define the type of output you wish to get back
            // possible values:
            // - 'xml'  :      you have to write your own XML parser
            // - 'json' :      data is returned in JSON format and can be decoded with
            //                 json_decode() function (included in PHP since 5.2.0)
            // - 'serialize' : data is returned in a serialized format and can be decoded with
            //                 a native unserialize() function
            'api_output'   => $api_output,
            'api_url'     => $api_url
        );
        return apply_filters('rusac_fetch_api_details' ,$params);
    }

    function rusac_fetch_general_settings() {
        $settings = array();
        $settings['autoresponder'] = 1;
        $settings['tags'] = '';
        $settings['status'] = 1;

        $primary_list_id = get_option('rusac_list_id');
        
        $settings['primary_list_id'] = ($primary_list_id != '') ? $primary_list_id : 1;
        return apply_filters('rusac_load_settings', $settings);
    }

    function rusac_prepare_registered_user_data($user_id) {
        // here we define the data we are posting in order to perform an update
        $user_obj = get_userdata($user_id);
        $lang_tags = ($user_info->i_speak == '') ? 'English' : $user_info->i_speak;
        //$lang_tags == 'en'
        switch ($lang_tags) {
            case 'en':
                $lang_tags = 'English';
                break;
            case 'fr':
                $lang_tags = 'French';
                break;
            case 'fr':
                $lang_tags = 'English';
                break;
            default:
                $lang_tags = 'English';
                break;
        }

        $settings = $this->rusac_fetch_general_settings();
        
        $settings['tags'] = $lang_tags;
        
        $first_name = get_user_meta( $user_id, 'first_name', true );
        $last_name  = get_user_meta( $user_id, 'last_name', true );
        $mobile     = get_user_meta( $user_id, 'mobile', true );
        
        $user_data = array(
            'email'                    => $user_obj->user_email,
            'first_name'               => $first_name,
            'last_name'                => $last_name,
            'phone'                    => $mobile,
            //'orgname'                  => 'Acme, Inc.',
            'tags'                     => $settings['tags'],
            //'ip4'                    => '127.0.0.1',

            // any custom fields
            //'field[345,0]'           => 'field value', // where 345 is the field ID
            //'field[%PERS_1%,0]'      => 'field value', // using the personalization tag instead (make sure to encode the key)

            // assign to lists:
            'p['.$settings['primary_list_id'].']' => $settings['primary_list_id'], // example list ID (REPLACE '123' WITH ACTUAL LIST ID, IE: p[5] = 5)
            'status['.$settings['status'].']'     => 1, // 1: active, 2: unsubscribed (REPLACE '123' WITH ACTUAL LIST ID, IE: status[5] = 1)
            //'form'          => 1001, // Subscription Form ID, to inherit those redirection settings
            //'noresponders[123]'      => 1, // uncomment to set "do not send any future responders"
            //'sdate[123]'             => '2009-12-07 06:00:00', // Subscribe date for particular list - leave out to use current date/time
            // use the folowing only if status=1
            'instantresponders['.$settings['autoresponder'].']' => 1, // set to 0 to if you don't want to sent instant autoresponders
            //'lastmessage[123]'       => 1, // uncomment to set "send the last broadcast campaign"

            //'p[]'                    => 345, // some additional lists?
            //'status[345]'            => 1, // some additional lists?
        );
        return apply_filters('rusac_fetch_registered_user_data' ,$user_data, $user_id);
    }

    function rusac_send_data_to_ac($user_id){

        // By default, this sample code is designed to get the result from your ActiveCampaign installation and print out the result
        $params = $this->rusac_load_api_details();
        $url    = !empty($params['api_url']) ? $params['api_url'] : 'http://account.api-us1.com';
        $post   = $this->rusac_prepare_registered_user_data($user_id);
        // This section takes the input fields and converts them to the proper format
        $query = "";
        foreach( $params as $key => $value ) $query .= urlencode($key) . '=' . urlencode($value) . '&';
        $query = rtrim($query, '& ');

        // This section takes the input data and converts it to the proper format
        $data = "";
        foreach( $post as $key => $value ) $data .= urlencode($key) . '=' . urlencode($value) . '&';
        $data = rtrim($data, '& ');

        // clean up the url
        $url = rtrim($url, '/ ');

        // This sample code uses the CURL library for php to establish a connection,
        // submit your request, and show (print out) the response.
        if ( !function_exists('curl_init') ){ 
            echo ('CURL not supported. (introduced in PHP 4.0.2)');
            return;
        }

        // If JSON is used, check if json_decode is present (PHP 5.2.0+)
        if ( $params['api_output'] == 'json' && !function_exists('json_decode') ) {
            echo ('JSON not supported. (introduced in PHP 5.2.0)');
            return;
        }

        // define a final API request - GET
        $api = $url . '/admin/api.php?' . $query;
        
        do_action('rusac_before_send_data_to_ac', $api, $user_id);

        $request = curl_init($api); // initiate curl object
        curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
        curl_setopt($request, CURLOPT_POSTFIELDS, $data); // use HTTP POST to send form data
        //curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment if you get no gateway response and are using HTTPS
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);

        $response = (string)curl_exec($request); // execute curl post and store results in $response

        // additional options may be required depending upon your server configuration
        // you can find documentation on curl options at http://www.php.net/curl_setopt
        curl_close($request); // close curl object

        if ( !$response ) {
            echo('Nothing was returned. Do you have a connection to Email Marketing server?');
            return;
        } 

        // unserializer
        $result = unserialize($response);
        do_action('rusac_after_sent_data_to_ac', $result);
    }

    function rusac_register_settings() {
        $api_url = get_option('rusac_api_url');
        if(empty($api_url)){ 
            add_option( 'rusac_api_url', '');
        }
        $api_key = get_option('rusac_api_key');
        if(empty($api_key)) {
            add_option( 'rusac_api_key', '');
        }
        $list_id = get_option('rusac_list_id');
        if(empty($list_id)) {
            add_option( 'rusac_list_id', '1');
        } 
        register_setting( 'rusac_options_group', 'rusac_api_url', 'rusac_callback' );
        register_setting( 'rusac_options_group', 'rusac_api_key', 'rusac_callback' );
        register_setting( 'rusac_options_group', 'rusac_list_id', 'rusac_callback' );
    }
    function rusac_register_options_page() {
        add_options_page('RU Active Campaigns Settings', 'RU Active Campaigns Settings', 'manage_options', 'rusac-settings', array($this,'rusac_options_page'));
    }

    function rusac_options_page() { 
    ?>
    <div class="rusac-settings-section">
        <style type="text/css">
        form.rusac-settings input[type="text"]{padding:5px 10px;font-size:14px;width:300px;max-width:100%;}
        form.rusac-settings label{font-size:14px;line-height:30px;}
        </style>
        <?php screen_icon(); ?>
        <h2><?php echo _e('RU Active Campaigns Settings','rusac');?></h2>
        <form method="post" class="rusac-settings" action="options.php">
            <?php settings_fields( 'rusac_options_group' ); ?>
           <p></p>
            <table>
                <tr valign="top">
                    <th scope="row"><label for="rusac_api_url"><?php echo _e('API URL','rus-activecampaign');?></label></th>
                    <td><input type="text" id="rusac_api_url" name="rusac_api_url" value="<?php echo (get_option('rusac_api_url')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="rusac_api_key"><?php echo _e('API Key','rus-activecampaign');?></label></th>
                    <td><input type="text" id="rusac_api_key" name="rusac_api_key" value="<?php echo (get_option('rusac_api_key')); ?>" /> </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="rusac_list_id"><?php echo _e('Listing ID','rus-activecampaign');?></label></th>
                    <td><input type="text" id="rusac_list_id" name="rusac_list_id" value="<?php echo (get_option('rusac_list_id')); ?>" /> </td>
                </tr>
            </table>
            <?php  submit_button(); ?>
        </form> 
    </div>
    <?php
    }
}

// create an instance of rusacTestimonials
if ( !isset($RUSActiveCampaign) ) {
    $RUSActiveCampaign = new RUSActiveCampaign();
}