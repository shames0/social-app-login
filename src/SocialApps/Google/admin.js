function sal_update_google_settings() {
    
    const request_body = {
        // sal_google_settings_ajax_obj is a globally registered object
        _ajax_nonce:   sal_google_settings_ajax_obj.nonce,
        action:        'socialappslogin_google_settings',
        settings: {
            is_enabled:     jQuery("#enable_google")[0].checked,
            client_secret:  jQuery("#client_secret").val(),
            client_id:      jQuery("#client_id").val(),
        },
    };

    jQuery('#sal-success-msg').addClass('hidden');
    jQuery('#sal-error-msg').addClass('hidden');
    jQuery('#sal-waiting-msg').removeClass('hidden');

    // Fire off the request
    jQuery.post(sal_google_settings_ajax_obj.ajax_url, request_body)
        .done(resp => {
            jQuery('#sal-success-msg').removeClass('hidden');
        })
        .fail((obj, status, error) => {
            let msg;
            try {
                const body = JSON.parse(obj.responseText);
                msg = body.data ? body.data : error;
            }
            catch (e) {
                msg = error;
            }

            jQuery('#sal-error-msg').removeClass('hidden');
            jQuery('#sal-error-details').html(`<p>${msg}</p>`);
        })
        .always(resp => {
            jQuery('#sal-waiting-msg').addClass('hidden');
        });
}

