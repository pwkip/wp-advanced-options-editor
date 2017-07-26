<?php
/**
 * Options Management Administration Screen.
 *
 * If accessed directly in a browser this page shows a list of all saved options
 * along with editable fields for their values. Serialized data is not supported
 * and there is no way to remove options via this page. It is not linked to from
 * anywhere else in the admin.
 *
 * This file is also the target of the forms in core and custom options pages
 * that use the Settings API. In this case it saves the new option values
 * and returns the user to their page of origin.
 *
 * @package WordPress
 * @subpackage Administration
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

$action = array_key_exists('action', $_GET) ? $_GET['action'] : '';

$capability = 'manage_options';

if ( ! current_user_can( $capability ) || (is_multisite() && ! current_user_can( 'manage_network_options' ) && 'update' != $action) ) {
    wp_die(
        '<h1>' . __( 'Cheatin&#8217; uh?' ) . '</h1>' .
        '<p>' . __( 'Sorry, you are not allowed to manage these options.' ) . '</p>',
        403
    );
}

add_thickbox();

?>
    <div class="notice">
        <p><span style="font-size: 30px; color: red;">WARNING: This page can break your website!</span></p>
        <p>This is a developer tool. You need to have a good knowledge about WP core before you start messing around on this page.
        <br>If you are not sure about what you are doing, please do not change anything on this page.</p>
        <p>Even if you know what you are doing, <strong style="color:#3c00ff;">make sure to have a backup of your database handy</strong>.
            <br>Changes made on this page will immediatly alter your database, and <strong style="color:#3c00ff;">cannot be undone</strong> after this page is refreshed.</p>
        <p><strong>Hint</strong>: Press <strong>Ctrl+F</strong> (or Cmd+F) to find the option you would like to change.</p>
    </div>

    <div class="wrap">
        <h1><?php esc_html_e( 'Advanced Options Editor' ); ?></h1>
            <?php wp_nonce_field('options-options') ?>
            <input type="hidden" name="action" value="update" />
            <input type="hidden" name="option_page" value="options" />
            <table class="form-table">
                <?php
                $options = $wpdb->get_results( "SELECT * FROM $wpdb->options ORDER BY option_name" );

                $wp_options = wp_load_alloptions();

                // new option
                ?>
                <tr class="add-new-option-container">
                    <th scope="row"><label for="bdwmeo-new-option-name">Create new option</label></th>
                    <td>
                        <input type="text" id="bdwmeo-new-option-name" placeholder="Option Name">
                    </td>
                    <td>
                        <input type="hidden" data-id="noncenew" value="<?php echo esc_attr(wp_create_nonce( 'bdwmoe-add-option' )) ?>">
                        <input type="button" class="button" value="Add new" id="add-new-option">
                    </td>
                </tr>
                <script>
                    (function($) {

                        $('#add-new-option').click(function() {
                            $this = $(this);
                            $tr = $this.closest('tr');

                            var data = {
                                'action': 'bdwmoe_add_option',
                                'name': $('#bdwmeo-new-option-name').val(),
                                'nonce' : $('[data-id="noncenew"]').val()
                            };

                            $('td',$tr).append('<span class="doing-ajax"><i class="fa fa-circle-o-notch fa-spin fa-fw"></i><span class="sr-only">Loading...</span></span>');

                            $.post(ajaxurl, data, function(response) {
                                $('.doing-ajax').remove();


                                $('#bdwmeo-new-option-name').val('');

                                if (response == '1') {
                                    alert('option added. Page will be refreshed.');
                                    location.reload();
                                } else {
                                    alert('could not add option. make sure to use only valid characters and make sure the name is unique.');
                                }

                            });

                            return false;
                        });
                    })( jQuery );
                </script>
                <?php


                foreach ( $wp_options as $wp_option_name => $wp_option_value ) :
                    $is_serialized = false;
                    $is_multiline = strpos( $wp_option_value, "\n" );
                    if ( $wp_option_name == '' ) {
                        continue;
                    } if ( is_serialized( $wp_option_value ) ) {
                        $wp_option_value_b64 = base64_encode($wp_option_value);
                        $is_serialized = true;
                    }

                    $wp_option_value_attr = esc_attr($wp_option_value);
                    $wp_option_name_attr =  esc_attr($wp_option_name);

                    $class_name_id = 'class="bdwmoe-option" name="'.$wp_option_name_attr.'" data-id="wp-option-'.$wp_option_name_attr.'"';

                    ?>
                    <tr data-option-name="<?php echo $wp_option_name_attr ?>" class="<?php echo $is_serialized?'is-serialized':'is-string' ?>">
                        <th scope="row"><label for="<?php echo $wp_option_name_attr ?>"><?php echo $wp_option_name_attr; ?></label></th>
                        <td>
                            <input type="hidden" class="original-value" data-name="<?php echo $wp_option_name_attr ?>" value="<?php echo $wp_option_value_attr ?>">

                            <textarea <?php echo $class_name_id ?> cols="30" rows="5" <?php echo $is_serialized?'disabled':'' ?>><?php echo esc_textarea( $wp_option_value ); ?></textarea>

                            <div class="inline data-type">
                                <span class="select-serialized">Serialized</span> | <span class="select-string">Plain Text</span>
                            </div>
                            <a class="edit-json thickbox bdwmoe-not-active" href="<?php echo admin_url( 'admin-ajax.php' ) ?>?action=bdwmoe_edit_serialized_data&height=540&width=800&serialized_name=<?php echo urlencode($wp_option_name) ?>">edit as JSON</a>
                            <div class="inline edited-text">
                                Saved | <a href="#" class="undo">undo</a>
                            </div>
                            <div class="inline saving-text">
                                <span class="doing-ajax"><i class="fa fa-circle-o-notch fa-spin fa-fw"></i> Saving...</span>
                            </div>
                        </td>
                        <td>
                            <input type="hidden" data-id="nonce-<?php echo $wp_option_name_attr ?>" value="<?php echo esc_attr(wp_create_nonce( 'bdwmoe-edit-option-'.$wp_option_name )) ?>">
                            <input type="button" data-option-name="<?php echo $wp_option_name_attr ?>" class="button update-option" value="Update">
                            <input type="button" data-option-name="<?php echo $wp_option_name_attr ?>" class="button delete-option" value="Delete">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

    </div>
    <script>
        jQuery(document).ready(function( $ ) {

            $('.data-type > span').click(function(){
                $span = $(this);
                $tr = $span.closest('tr');
                if ($span.hasClass('select-serialized')) {
                    $tr.removeClass('is-string').addClass('is-serialized');
                    $('textarea',$tr).attr('disabled',true);
                } else if ($span.hasClass('select-string')) {
                    $tr.removeClass('is-serialized').addClass('is-string');
                    $('textarea',$tr).attr('disabled',false);
                }
            });

            $('.undo').click(function() {
                $this = $(this);
                $tr = $this.closest('tr');
                var option_name = $tr.data('option-name');

                var data = {
                    'action': 'bdwmoe_update_option',
                    'name': option_name,
                    'serializeddata' : $tr.find('.original-value').eq(0).val(),
                    'is_object' : $('#is_object').is(':checked'),
                    'nonce' : $('[data-id="nonce-'+option_name+'"]').val()
                };

                $('.edited-text',$tr).append('<span class="doing-ajax"><i class="fa fa-circle-o-notch fa-spin fa-fw"></i><span class="sr-only">Loading...</span></span>');

                $.post(ajaxurl, data, function(response) {

                    $('[data-option-name="'+data.name+'"]').removeClass('edited');

                    $('textarea[name="'+data.name+'"]').val(response);
                    $('textarea[name="'+data.name+'"]').attr('title',response);

                    $('.edited-text .doing-ajax').remove();

                });

                return false;
            });

            $('.bdwmoe-not-active').removeClass('bdwmoe-not-active');


            var timeoutId;
            $('.update-option').on('click', function() {

                $tr = $(this).closest('tr');

                $tr.removeClass('edited').addClass('saving');

                new_value = $tr.find('.bdwmoe-option').eq(0).val();

                var option_name = $tr.data('option-name');

                var data = {
                    'action': 'bdwmoe_update_option',
                    'name': option_name,
                    'serializeddata': new_value,
                    'nonce' : $('[data-id="nonce-'+option_name+'"]').val()
                };

                $.post(ajaxurl, data, function(response) {

                    $('[data-option-name="'+data.name+'"]').removeClass('edited');

                    $('textarea[name="'+data.name+'"]').val(response);
                    $('textarea[name="'+data.name+'"]').attr('title',response);

                    $('textarea[name="'+data.name+'"]').closest('tr').removeClass('saving').addClass('edited');

                });
            });

            $('.delete-option').click(function() {
                $this = $(this);
                $td = $this.closest('td');

                var option_name = $this.data('option-name');

                if (!confirm("Are you sure? The option and all of it's data will be deleted permanently from the database.")) {
                    return false;
                }

                var data = {
                    'action' : 'bdwmoe_delete_option',
                    'name' : option_name,
                    'nonce' : $('[data-id="nonce-'+option_name+'"]').val()
                };

                $td.append('<span class="doing-ajax"><i class="fa fa-circle-o-notch fa-spin fa-fw"></i>Deleting</span>');

                $.post(ajaxurl, data, function(response) {

                    $('.doing-ajax').remove();

                    if (response == '1') {
                        $('textarea[name="'+data.name+'"]').closest('tr').remove();
                    } else if( response == 'nonce_err')  {
                        alert('could not verify nonce. Please refresh this page and try again.');
                    } else {
                        alert('could not delete option. Not sure why..');
                    }

                });

                return false;
            });

        });
    </script>

<?php
