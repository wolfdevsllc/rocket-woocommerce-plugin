jQuery(function ($) {

    $(document).ready(function () {
        $('#wc_product_rocket_settings #enable_rocket').trigger('change');
    });
    $(document).on('change', '#wc_product_rocket_settings #enable_rocket', function (e) {
        if ($(this).is(":checked")) {
            $('#wc_product_rocket_settings .rocket_group_settings').show();
        } else {
            $('#wc_product_rocket_settings .rocket_group_settings').hide();
        }
    });

});