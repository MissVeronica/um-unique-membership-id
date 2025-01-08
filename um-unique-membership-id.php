<?php
/**
 * Plugin Name:     Ultimate Member - Unique Membership ID
 * Description:     Extension to Ultimate Member for setting a prefixed Unique Membership ID per UM Role.
 * Version:         1.7.0
 * Requires PHP:    7.4
 * Author:          Miss Veronica
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:      https://github.com/MissVeronica
 * Plugin URI:      https://github.com/MissVeronica/um-unique-membership-id
 * Update URI:      https://github.com/MissVeronica/um-unique-membership-id
 * Text Domain:     ultimate-member
 * Domain Path:     /languages
 * UM version:      2.9.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; 
if ( ! class_exists( 'UM' ) ) return;

class UM_Unique_Membership_ID {

    public $um_unique_membership_meta_key = false;

    function __construct() {

        define( 'Plugin_Basename_UMID', plugin_basename( __FILE__ ));

        add_action( 'set_user_role',         array( $this, 'um_user_unique_membership_id' ), 10, 3 );
        add_filter( 'um_settings_structure', array( $this, 'um_settings_structure_unique_membership_id' ), 10, 1 );
        add_filter( 'plugin_action_links_' . Plugin_Basename_UMID, array( $this, 'plugin_settings_link' ), 10, 1 );
    }

    public function plugin_settings_link( $links ) {

        $url = get_admin_url() . "admin.php?page=um_options&tab=appearance&section=registration_form";
        $links[] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings' ) . '</a>';

        return $links;
    }

    public function unique_membership_id_exists( $value ) {

        global $wpdb;

        return $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = '{$this->um_unique_membership_meta_key}' AND meta_value = '{$value}' " );

    }

    public function um_user_unique_membership_id( $user_id, $role, $old_roles ) {

        if ( ! empty( $role )) {

            $um_unique_membership_id = array_map( 'sanitize_text_field', array_map( 'trim', explode( "\n", UM()->options()->get( 'um_unique_membership_id' ))));
            if ( is_array( $um_unique_membership_id )) {

                foreach( $um_unique_membership_id as $role_prefix ) {

                    $array = array_map( 'trim', array_map( 'sanitize_text_field', explode( ':', $role_prefix )));

                    if ( isset( $array[0] ) && ! empty( $array[0] )) {

                        if ( $role == $array[0] && isset( $array[1] ) && ! empty( $array[1] )) {

                            $this->um_unique_membership_meta_key = sanitize_key( UM()->options()->get( 'um_unique_membership_id_meta_key' ));

                            if ( empty( $this->um_unique_membership_meta_key )) {
                                $this->um_unique_membership_meta_key = 'um_unique_membership_id';
                            }

                            $digits = absint( UM()->options()->get( 'um_unique_membership_id_digits' ));
                            if ( empty( $digits )) {
                                $digits = 5;
                            }

                            if ( ! empty( $old_roles ) && $array[1] != 'meta_key' && UM()->options()->get( 'um_unique_membership_id_prefix' ) == 1 ) {

                                $current_membership_id = get_user_meta( $user_id, $this->um_unique_membership_meta_key, true );

                                foreach( $um_unique_membership_id as $role_prefix ) {

                                    $compare = array_map( 'trim', array_map( 'sanitize_text_field', explode( ':', $role_prefix )));

                                    if ( isset( $compare[0] ) && isset( $compare[1] ) && in_array( $compare[0], $old_roles )) {

                                        $char_count = $digits;
                                        if ( in_array( '#year#', $compare )) {
                                            $char_count += 3;
                                            $char_count += strlen( $compare[1] );

                                        } else {
                                            $char_count += strlen( str_replace( '#year#', '##', $compare[1] ));
                                        }

                                        $new_membership_id = str_replace( $compare[1], $array[1], substr( $current_membership_id, 0, $char_count ));

                                        $i = 1;
                                        $new_unique_id = $new_membership_id;

                                        while( $this->unique_membership_id_exists( $new_unique_id )) {
                                            $new_unique_id = $new_membership_id . '-' . str_pad( strval( $i++ ), 3, '0', STR_PAD_LEFT );
                                        }

                                        update_user_meta( $user_id, $this->um_unique_membership_meta_key, $new_unique_id );
                                        break 2;
                                    }
                                }

                            } else {

                                $suffix = ( in_array( '#year#', $array )) ? '-' . date_i18n( 'y', current_time( 'timestamp' )) : '';

                                if ( $array[1] == 'meta_key' && isset( $array[2] ) && ! empty( $array[2] )) {

                                    $prefix = ( strpos( $array[2], '#year#' ) !== false ) ? str_replace( '#year#', date_i18n( 'y', current_time( 'timestamp' )), $array[2] ) : $array[2];

                                    if ( function_exists( 'mb_strlen' )) {
                                        if ( isset( $array[3] ) && ! empty( $array[3] ) && mb_strlen( $array[3] ) == 1 ) {
                                            $prefix .= $array[3];
                                        }

                                    } else {
                                        if ( isset( $array[3] ) && ! empty( $array[3] ) && strlen( $array[3] ) == 1 ) {
                                            $prefix .= $array[3];
                                        }
                                    }

                                    if ( isset( $array[4] ) && $array[4] == 'random' ) {

                                        $min = ( isset( $array[5] ) && ! empty( $array[5] )) ? absint( $array[5] ) : 0;
                                        $string_pad = str_pad( rand( $min, pow( 10, $digits ) -1 ), $digits, '0', STR_PAD_LEFT );

                                        while( $this->unique_membership_id_exists( $prefix . $string_pad . $suffix )) {
                                            $string_pad = str_pad( rand( $min, pow( 10, $digits ) -1 ), $digits, '0', STR_PAD_LEFT );
                                        }

                                    } else {

                                        $string_pad = str_pad( strval( $user_id ), $digits, '0', STR_PAD_LEFT );

                                        $i = 1;
                                        $string_pad_saved = $string_pad;

                                        while( $this->unique_membership_id_exists( $prefix . $string_pad . $suffix )) {
                                            $string_pad = $string_pad_saved . '-' . str_pad( strval( $i++ ), 3, '0', STR_PAD_LEFT );
                                        }
                                    }

                                } else {

                                    $prefix = ( strpos( $array[1], '#year#' ) !== false ) ? str_replace( '#year#', date_i18n( 'y', current_time( 'timestamp' )), $array[1] ) : $array[1];

                                    if ( isset( $array[2] ) && $array[2] == 'random' ) {

                                        $min = ( isset( $array[3] ) && ! empty( $array[3] )) ? intval( $array[3] ) : 0;
                                        $string_pad = str_pad( rand( $min, pow( 10, $digits ) -1 ), $digits, '0', STR_PAD_LEFT );

                                        while( $this->unique_membership_id_exists( $prefix . $string_pad . $suffix )) {
                                            $string_pad = str_pad( rand( $min, pow( 10, $digits ) -1 ), $digits, '0', STR_PAD_LEFT );
                                        }

                                    } else {

                                        $string_pad = str_pad( strval( $user_id ), $digits, '0', STR_PAD_LEFT );

                                        $i = 1;
                                        $string_pad_saved = $string_pad;

                                        while( $this->unique_membership_id_exists( $prefix . $string_pad . $suffix )) {
                                            $string_pad = $string_pad_saved . '-' . str_pad( strval( $i++ ), 3, '0', STR_PAD_LEFT );
                                        }
                                    }
                                }

                                if ( ! empty( $prefix ) && ! empty( $string_pad )) {

                                    $um_unique_membership_id = $prefix . $string_pad . $suffix;
                                    update_user_meta( $user_id, $this->um_unique_membership_meta_key, $um_unique_membership_id );
                                    break;
                                }
                            }
                        }
                    }
                }

                UM()->user()->remove_cache( $user_id );
                um_fetch_user( $user_id );
            }
        }
    }

    public function um_settings_structure_unique_membership_id( $settings_structure ) {

        $prefix = '&nbsp; * &nbsp;';
        $settings = array();

        $settings['title']       = esc_html__( 'Unique Membership ID', 'ultimate-member' );
        $settings['description'] = esc_html__( 'Plugin version 1.7.0 - tested with UM 2.9.2', 'ultimate-member' );

        $settings['fields'][] = array(
                        'id'          => 'um_unique_membership_id',
                        'type'        => 'textarea',
                        'label'       => $prefix . esc_html__( "Role ID:prefix or meta_key format", 'ultimate-member' ),
                        'description' => esc_html__( "Enter the UM Role ID and the Unique Membership ID Prefix or meta_key format one setting per line.", 'ultimate-member' ),
                        'args'        => array( 'textarea_rows' => 6 ),
                    );

        $settings['fields'][] = array(
                        'id'          => 'um_unique_membership_id_digits',
                        'type'        => 'number',
                        'label'       => $prefix . esc_html__( "Number of digits", 'ultimate-member' ),
                        'description' => esc_html__( "Enter the number of digits in the Unique Membership ID. Default value is 5.", 'ultimate-member' ),
                        'size'        => 'small',
                    );

        $settings['fields'][] = array(
                        'id'          => 'um_unique_membership_id_meta_key',
                        'type'        => 'text',
                        'label'       => $prefix . esc_html__( "meta_key", 'ultimate-member' ),
                        'description' => esc_html__( "Enter the meta_key name of the Unique Membership ID field. Default name is 'um_unique_membership_id'", 'ultimate-member' ),
                        'size'        => 'small',
                    );

        $settings['fields'][] = array(
                        'id'             => 'um_unique_membership_id_prefix',
                        'type'           => 'checkbox',
                        'label'          => $prefix . esc_html__( 'Prefix update at Role change', 'ultimate-member' ),
                        'checkbox_label' => esc_html__( 'Tick to only update the prefix when the role is changed in prefix format.', 'ultimate-member' ),
                    );

        $settings_structure['appearance']['sections']['registration_form']['form_sections']['unique_membership_id'] = $settings;

        return $settings_structure;
    }
}

new UM_Unique_Membership_ID();

