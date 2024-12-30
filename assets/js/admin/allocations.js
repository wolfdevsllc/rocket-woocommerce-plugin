jQuery(function ($) {
  // Edit allocation
  $(".edit-allocation").on("click", function () {
    var $row = $(this).closest("td");
    $row.find(".total-sites-display, .edit-allocation").hide();
    $row.find(".total-sites-edit").show();
  });

  // Cancel edit
  $(".cancel-edit").on("click", function () {
    var $row = $(this).closest("td");
    $row.find(".total-sites-edit").hide();
    $row.find(".total-sites-display, .edit-allocation").show();
  });

  // Save allocation
  $(".save-allocation").on("click", function () {
    var $button = $(this);
    var $row = $button.closest("td");
    var allocationId = $button.data("id");
    var newTotal = $row.find(".total-sites-input").val();

    if (!confirm(wcRocketAdmin.strings.confirmAdjust)) {
      return;
    }

    $button.prop("disabled", true);

    $.ajax({
      url: wcRocketAdmin.ajaxUrl,
      method: "POST",
      data: {
        action: "adjust_site_allocation",
        allocation_id: allocationId,
        total_sites: newTotal,
        nonce: wcRocketAdmin.nonce,
      },
      success: function (response) {
        if (response.success) {
          $row.find(".total-sites-display").text(newTotal);
          $row.find(".total-sites-edit").hide();
          $row.find(".total-sites-display, .edit-allocation").show();
          alert(wcRocketAdmin.strings.success);
        } else {
          alert(response.data || wcRocketAdmin.strings.error);
        }
      },
      error: function () {
        alert(wcRocketAdmin.strings.error);
      },
      complete: function () {
        $button.prop("disabled", false);
      },
    });
  });
});
