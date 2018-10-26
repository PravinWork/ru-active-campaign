# ru-active-campaign
Repository for plugin of ActiveCampaign. It adds the registered user in wordpress to ActiveCampaign's list.

### Usage

Place this code where your custom rgistration form is submitted. just after creating the user and updated its meta information. 

```sh
do_action( 'rusac_add_new_address' , $user_id );
```
Its very good procedure for custom registration forms.

### Hooks
Plugin is having multiple hooks to gain more control on plugin's data

##### Filters
- rusac_fetch_api_details
- rusac_load_settings
- rusac_fetch_registered_user_data

##### Actions
- rusac_before_send_data_to_ac
- rusac_after_sent_data_to_ac

