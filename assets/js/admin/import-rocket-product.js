jQuery(function ($) {

    $(document).on('click', '.wc-import-product-notice-wrap .import-rocket-sample-product', function (e) {
        e.preventDefault();
        $('.wc-rocket-admin-loader-wrap .wc-rocket-admin-loader').removeClass('hide');
        $('.wc-import-product-notice-wrap .wc-rocket-admin-notice-error').addClass('hide').html('');
        var current_elem = $(this);
        $.post(
                import_rocket_product.ajax_url,
                {
                    action: 'import_wc_rocket_product',
                    _wc_rocket_notice_nonce: current_elem.data('nonce')
                },
                function (response) {
                    $('.wc-rocket-admin-loader-wrap .wc-rocket-admin-loader').addClass('hide');
                    if (response.success) {
                        window.location.href = response.data.product_page;
                    } else {
                        $('.wc-import-product-notice-wrap .wc-rocket-admin-notice-error').html(response.data.message).removeClass('hide');
                    }
                }
        );

    });

});