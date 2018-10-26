# ru-active-campaign
Repository for plugin of ActiveCampaign. It syncs the registered user in wordpress to ActiveCampaign.

# Usage

Place this code where your custom rgistration form is submitted. just after creating the user and updated its meta information. 

do_action('rusac_add_new_address',$user_id);

Its very good procedure for custom registration forms.
