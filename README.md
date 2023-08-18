# UM Unique Membership ID
Extension to Ultimate Member for setting a prefixed Unique Membership ID per UM User Role.

## UM Settings
UM Settings -> Appearance -> Registration Form
1. Unique Membership ID - Role ID:prefix or meta_key format - Enter the UM Role ID and the Unique Membership ID Prefix or meta_key format one setting per line.
2. Unique Membership ID - Number of digits - Enter the number of digits in the Unique Membership ID. Default value is 5.
3. Unique Membership ID - meta_key - Enter the meta_key name of the Unique Membership ID field. Default name is 'um_unique_membership_id'

### Prefix format
<code>um_prospect : ABCD
um_member : EFGH-
um_senior : Qwerty- : random
</code>

Numbers are based on WP 'user_id' field prefilled with zeros except when 'random' is specified.
1. Meta values for User Role ID <code>um_prospect</code> and 5 digits: <code>ABCD00345</code> 
2. Meta values for User Role ID <code>um_member</code> and 5 digits: <code>EFGH-00345</code>
3. Meta values for User Role ID <code>um_senior</code> and 5 random digits: <code>Qwerty-73528</code>

### meta_key format
<code>um_prospect : meta_key : um-field-name
um_member : meta_key : um-field-name : - 
um_junior : meta_key : um-field-name : - : random
um_senior : meta_key : um-field-name : : random
</code>

The Registration form user entered value for the meta_key 'um-field-name' will be used as prefix. In these examples user entered 'CompanyName'.

Numbers are based on  WP 'user_id' field prefilled with zeros except when 'random' is specified.
1. Meta values for User Role ID <code>um_prospect</code> and 6 digits: <code>CompanyName000456</code>
2. Meta values for User Role ID <code>um_member</code> and 6 digits: <code>CompanyName-000456</code>
3. Meta values for User Role ID <code>um_junior</code> and 6 random digits: <code>CompanyName-834602</code>
4. Meta values for User Role ID <code>um_senior</code> and 6 random digits: <code>CompanyName246739</code>

## Translations or Text changes
1. Use the "Say What?" plugin with text domain ultimate-member

## Installation
1. Download the zip file and install as a WP Plugin, activate the plugin.
