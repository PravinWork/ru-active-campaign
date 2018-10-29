[![Build Status](https://travis-ci.org/joemccann/dillinger.svg?branch=master)](https://github.com/PravinWork/ru-active-campaign)

# ru-active-campaign
Repository for plugin of ActiveCampaign. It adds the registered user in wordpress to ActiveCampaign's list.

Also, a automatic control on sync users. Just select you list id, and little settings your plugin is ready to sync the users.

### Requirements 
- API URL
- API key
- List Id if you need to sync the data with different list.

### Usage

if trigger the sync action manually
Place this code where your custom rgistration form is submitted. just after creating the user and updated its meta information. 

```sh
do_action( 'rusac_add_new_address' , $user_id );
```
Its very good procedure for custom registration forms.

If you want it should sync the data automatically. Just make sync settings on and do some settings.

### Hooks
Plugin is having multiple hooks to gain more control on plugin's data

##### Filters
- rusac_fetch_api_details
- rusac_load_settings
- rusac_fetch_registered_user_data

##### Actions
- rusac_before_send_data_to_ac
- rusac_after_sent_data_to_ac

