jQuery(function ($) {
    if (typeof data == 'object' && Object.keys(data).length > 0) {
        $('.wc-rocket-loader').removeClass('hide');
        window.Rocket('init', {
            token: data.site_access_token,
            siteId: data.site_id,
            header: 'hide',
            elementId: 'rocket-container',
            colors: {
                bodyBackground: data.bodyBackground_color,
                iconPrimary: data.icon_primary_color,
                iconSecondary: data.icon_secondary_color,
                primary: data.primary_color,
                primaryHover: data.primary_hover_color,
                primaryActive: data.primary_active_color,
                primaryMenuHover: data.primary_menu_hover_color,
                primaryMenuActive: data.primary_menu_active_color
            }
        });
    }
});

jQuery(window).load(function () {
    if (typeof data == 'object' && Object.keys(data).length > 0) {
        setTimeout(function () {
            jQuery('.wc-rocket-loader').addClass('hide');
        }, 4000);
    }
});