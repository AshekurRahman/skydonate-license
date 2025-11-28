/**
 * SkyDonate License Admin JS
 * Handles AJAX actions and UI interactions
 */

jQuery(document).ready(function($) {

    /**
     * Generate License Key
     */
    $('.generate-license-key').on('click', function(e) {
        e.preventDefault();
        var $input = $(this).siblings('input.license-key');
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'skydonate_generate_key',
                _ajax_nonce: skydonate_nonce
            },
            success: function(response) {
                if(response.success) {
                    $input.val(response.key);
                } else {
                    alert(response.message || 'Failed to generate key');
                }
            },
            error: function() {
                alert('AJAX error. Please try again.');
            }
        });
    });

    /**
     * Delete License via AJAX
     */
    $('.delete-license').on('click', function(e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this license?')) return;

        var licenseKey = $(this).data('license');
        var $row = $(this).closest('tr');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'skydonate_delete_license',
                license_key: licenseKey,
                _ajax_nonce: skydonate_nonce
            },
            success: function(response) {
                if(response.success) {
                    $row.fadeOut(400, function() {
                        $(this).remove();
                    });
                } else {
                    alert(response.message || 'Failed to delete license');
                }
            },
            error: function() {
                alert('AJAX error. Please try again.');
            }
        });
    });

    /**
     * Save License via AJAX
     */
    $('#skydonate-license-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if(response.success) {
                    alert(response.message || 'License saved successfully');
                    location.reload();
                } else {
                    alert(response.message || 'Failed to save license');
                }
            },
            error: function() {
                alert('AJAX error. Please try again.');
            }
        });
    });

});
