jQuery(function ($) {
  $(".toggle-site-access").on("click", function () {
    var $button = $(this);
    var userId = $button.data("user-id");
    var currentStatus = $button.data("status");

    if (!confirm(wcRocketUserManager.strings.confirmToggle)) {
      return;
    }

    $button.prop("disabled", true);

    $.ajax({
      url: wcRocketUserManager.ajaxUrl,
      method: "POST",
      data: {
        action: "toggle_site_access",
        user_id: userId,
        status: currentStatus === "enabled" ? "disable" : "enable",
        nonce: wcRocketUserManager.nonce,
      },
      success: function (response) {
        if (response.success) {
          // Update button appearance
          $button.data("status", response.data.new_status);
          $button.text(
            response.data.new_status === "enabled" ? "Enabled" : "Disabled"
          );
          $button.toggleClass(
            "button-primary",
            response.data.new_status === "enabled"
          );
        } else {
          alert(wcRocketUserManager.strings.error);
        }
      },
      error: function () {
        alert(wcRocketUserManager.strings.error);
      },
      complete: function () {
        $button.prop("disabled", false);
      },
    });
  });
});
