<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if (version_compare(PHP_VERSION, '5.4.0') < 0) {
    die('you need at least PHP version 5.4.0 to edit serialized data. Your current PHP version is '.PHP_VERSION);
}

$name = $_GET['serialized_name'];
$value = get_option($name);
$is_object = is_object($value);


?>
<div id="bdwmoe-edit-serialized-data-box">
<p>The data below is in <a href="http://www.json.org/" target="_blank">JSON</a> format. Make sure you are familiar with JSON before attempting to edit this data. The edited data will not be saved if the JSON syntax is invalid.</p>
<p><strong>Hint - </strong>Copy paste this JSON string into your favorite code editor or <a href="https://jsonlint.com/" target="_blank">JSON Lint</a> for easier editing and debugging, then paste it back here.</p>
    <textarea id="serialized_json"><?php echo json_encode($value,JSON_PRETTY_PRINT); ?></textarea><br>
    <input type="checkbox" id="is_object" <?php echo $is_object ? 'checked' : ''; ?>> Save as Object (original value was <?php echo $is_object ? 'Object' : 'Array' ?>)<br><br>
<input type="button" id="save_json" class="button button-primary" value="Update serialized value"><span class="button-note"></span>
</div>
<script>
    (function($) {
        $('#save_json').click(function() {
            try {
                var jsonstring = $('#serialized_json').val();
                var jsondata = JSON.parse(jsonstring); // will throw error if invalid JSON

                var data = {
                    'action': 'bdwmoe_update_option',
                    'name': '<?php echo $name ?>',
                    'jsonstring': jsonstring,
                    'is_object' : $('#is_object').is(':checked'),
                    'nonce' : '<?php echo wp_create_nonce( 'bdwmoe-edit-option-'.$name ) ?>'
                };

                $('.button-note').html('<span class="doing-ajax"><i class="fa fa-circle-o-notch fa-spin fa-fw"></i><span class="sr-only">Loading...</span></span>');

                $.post(ajaxurl, data, function(response) {
                    //alert('Got this from the server: ' + response);
                    console.log($('#original_value').val());
                    console.log(response);
                    if ($('#original_value').val() == response) {
                        alert('no changes detected. The new value is identical to the original value.');
                        $('[data-option-name="<?php echo $name ?>"]').removeClass('edited');
                    } else {
                        $('[data-option-name="<?php echo $name ?>"]').addClass('edited');
                    }
                    $('textarea[name="<?php echo $name ?>"]').val(response);
                    $('textarea[name="<?php echo $name ?>"]').attr('title',response);

                    $('.button-note .doing-ajax').remove();

                    tb_remove();

                });


            } catch (err) {
                alert('invalid JSON syntax');
            }
        })
    })( jQuery );
</script>



