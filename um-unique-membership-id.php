<?php
/**
 * Plugin Name:     Ultimate Member - Unique Membership ID
 * Description:     Extension to Ultimate Member for setting a prefixed Unique Membership ID per UM Role.
 * Version:         1.4.0
 * Requires PHP:    7.4
 * Author:          Miss Veronica
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:      https://github.com/MissVeronica
 * Text Domain:     ultimate-member
 * Domain Path:     /languages
 * UM version:      2.8.5
 */

if ( ! defined( 'ABSPATH' ) ) exit; 
if ( ! class_exists( 'UM' ) ) return;

class UM_Unique_Membership_ID {

    public $um_unique_membership_meta_key = false;

    function __construct() {

        add_action( 'set_user_role',         array( $this, 'um_user_unique_membership_id' ), 10, 3 );
        add_filter( 'um_settings_structure', array( $this, 'um_settings_structure_unique_membership_id' ), 10, 1 );
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

                            $digits = absint( UM()->options()->get( 'um_unique_membership_id_digits' ));
                            if ( empty( $digits )) {
                                $digits = 5;
                            }

                            $this->um_unique_membership_meta_key = sanitize_key( UM()->options()->get( 'um_unique_membership_id_meta_key' ));

                            if ( empty( $this->um_unique_membership_meta_key )) {
                                $this->um_unique_membership_meta_key = 'um_unique_membership_id';
                            }

                            $prefix = '';
                            $string_pad = '';

                            if ( $array[1] == 'meta_key' && isset( $array[2] ) && ! empty( $array[2] )) {

                                $prefix = $array[2];
                                if ( $prefix == '#year#' ) {
                                    $prefix = date_i18n( 'y', current_time( 'timestamp' ));
                                }

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

                                    $min = 0;
                                    if ( isset( $array[5] ) && ! empty( $array[5] )) {
                                        $min = intval( $array[5] );
                                    }

                                    $string_pad = str_pad( rand( $min, pow( 10, $digits ) -1 ), $digits, '0', STR_PAD_LEFT );

                                    while( $this->unique_membership_id_exists( $prefix . $string_pad )) {
                                        $string_pad = str_pad( rand( $min, pow( 10, $digits ) -1 ), $digits, '0', STR_PAD_LEFT );
                                    }

                                } else {

                                    $string_pad = str_pad( strval( $user_id ), $digits, '0', STR_PAD_LEFT );

                                    $i = 1;
                                    $string_pad_saved = $string_pad;

                                    while( $this->unique_membership_id_exists( $prefix . $string_pad )) {
                                        $string_pad = $string_pad_saved . '-' . str_pad( strval( $i++ ), 3, '0', STR_PAD_LEFT );
                                    }
                                }

                            } else {

                                $prefix = $array[1];
                                if ( $prefix == '#year#' ) {
                                    $prefix = date_i18n( 'y', current_time( 'timestamp' ));
                                }

                                if ( isset( $array[2] ) && $array[2] == 'random' ) {

                                    $min = 0;
                                    if ( isset( $array[3] ) && ! empty( $array[3] )) {
                                        $min = intval( $array[3] );
                                    }

                                    $string_pad = str_pad( rand( $min, pow( 10, $digits ) -1 ), $digits, '0', STR_PAD_LEFT );

                                    while( $this->unique_membership_id_exists( $prefix . $string_pad )) {
                                        $string_pad = str_pad( rand( $min, pow( 10, $digits ) -1 ), $digits, '0', STR_PAD_LEFT );
                                    }

                                } else {

                                    $string_pad = str_pad( strval( $user_id ), $digits, '0', STR_PAD_LEFT );

                                    $i = 1;
                                    $string_pad_saved = $string_pad;

                                    while( $this->unique_membership_id_exists( $prefix . $string_pad )) {
                                        $string_pad = $string_pad_saved . '-' . str_pad( strval( $i++ ), 3, '0', STR_PAD_LEFT );
                                    }
                                }
                            }

                            if ( ! empty( $prefix ) && ! empty( $string_pad )) {

                                $um_unique_membership_id = $prefix . $string_pad;
                                update_user_meta( $user_id, $this->um_unique_membership_meta_key, $um_unique_membership_id );

                                UM()->user()->remove_cache( $user_id );
                                um_fetch_user( $user_id );
                            }
                        }
                    }
                }
            }
        }
    }

    public function um_settings_structure_unique_membership_id( $settings_structure ) {

        $settings_structure['appearance']['sections']['registration_form']['form_sections']['unique_membership_id']['title']       = __( 'Unique Membership ID', 'ultimate-member' );
        $settings_structure['appearance']['sections']['registration_form']['form_sections']['unique_membership_id']['description'] = __( 'Plugin version 1.4.0 - tested with UM 2.8.5', 'ultimate-member' );

        $settings_structure['appearance']['sections']['registration_form']['form_sections']['unique_membership_id']['fields'][] = array(
                        'id'          => 'um_unique_membership_id',
                        'type'        => 'textarea',
                        'label'       => __( "Role ID:prefix or meta_key format", 'ultimate-member' ),
                        'description' => __( "Enter the UM Role ID and the Unique Membership ID Prefix or meta_key format one setting per line.", 'ultimate-member' ),
                        'args'        => array( 'textarea_rows' => 6 ));

        $settings_structure['appearance']['sections']['registration_form']['form_sections']['unique_membership_id']['fields'][] = array(
                        'id'          => 'um_unique_membership_id_digits',
                        'type'        => 'number',
                        'label'       => __( "Number of digits", 'ultimate-member' ),
                        'description' => __( "Enter the number of digits in the Unique Membership ID. Default value is 5.", 'ultimate-member' ),
                        'size'        => 'small' );

        $settings_structure['appearance']['sections']['registration_form']['form_sections']['unique_membership_id']['fields'][] = array(
                        'id'          => 'um_unique_membership_id_meta_key',
                        'type'        => 'text',
                        'label'       => __( "meta_key", 'ultimate-member' ),
                        'description' => __( "Enter the meta_key name of the Unique Membership ID field. Default name is 'um_unique_membership_id'", 'ultimate-member' ),
                        'size'        => 'small' );

        return $settings_structure;
    }
}

new UM_Unique_Membership_ID();

