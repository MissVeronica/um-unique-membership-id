# UM Unique Membership ID
Extension to Ultimate Member for setting a prefixed Unique Membership ID per UM User Role.

## UM Settings -> Appearance -> Registration Form -> "Unique Membership ID"
1. * Role ID:prefix or meta_key format - Enter the UM Role ID and the Unique Membership ID Prefix or meta_key format one setting per line.
2. * Number of digits - Enter the number of digits in the Unique Membership ID. Default value is 5.
3. * meta_key - Enter the meta_key name of the Unique Membership ID field. Default name is 'um_unique_membership_id'
4. * Prefix update at Role change - Tick to only update the prefix when the role is changed in prefix format.

### Prefix format
<code>um_prospect : ABCD
um_member : EFGH-
um_member : AB#year#CD
um_member : ABCD-:#year#
um_senior : Qwerty- : random : 100000
</code>

Numbers are based on WP 'user_id' field prefilled with zeros except when 'random' is specified.
1. Meta values for User Role ID <code>um_prospect</code> and 5 digits: <code>ABCD00345</code> 
2. Meta values for User Role ID <code>um_member</code> and 5 digits: <code>EFGH-00345</code>
3. Meta values for User Role ID <code>um_senior</code> and 5 random digits: <code>Qwerty-73528</code>
4. Random can specify a minimum number
5. Prefix will replace #year# with last two digits from current year
6. #year# as the last value will add a year suffix like -25

### meta_key format
<code>um_prospect : meta_key : um-field-name
um_member : meta_key : um-field-name : - 
um_junior : meta_key : um-field-name : - : random : minimum
um_senior : meta_key : um-field-name : : random : minimum : #year#
</code>

The Registration form user entered value for the meta_key 'um-field-name' will be used as prefix. In these examples user entered 'CompanyName'.

Numbers are based on  WP 'user_id' field prefilled with zeros except when 'random' is specified.
1. Meta values for User Role ID <code>um_prospect</code> and 6 digits: <code>CompanyName000456</code>
2. Meta values for User Role ID <code>um_member</code> and 6 digits: <code>CompanyName-000456</code>
3. Meta values for User Role ID <code>um_junior</code> and 6 random digits: <code>CompanyName-834602</code>
4. Meta values for User Role ID <code>um_senior</code> and 6 random digits: <code>CompanyName246739</code>

## Email placeholder
1. Use the UM email placeholder {usermeta:here_any_usermeta_key} https://docs.ultimatemember.com/article/1340-placeholders-for-email-templates

## Translations or Text changes
1. Use the "Say What?" plugin with text domain ultimate-member

## References
1. Unique User Account ID - https://github.com/MissVeronica/um-unique-user-account-id
2. Extra Custom Username Field - https://github.com/MissVeronica/um-custom-username-field

## Updates
1. Version 1.1.0 Updated for UM 2.8.3
2. Version 1.2.0 #year# and minimum value for random
3. Version 1.3.0 Caching issue solved
4. Version 1.4.0/1.5.0/1.6.0 Code improvements
5. Version 1.7.0 Prefix update at Role change. Code improvements

## Installation & Updates
1. Download the zip file via the green Code button and install or update as a new WP Plugin to upload, activate the plugin.
