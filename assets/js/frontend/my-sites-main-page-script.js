jQuery(function ($) {
  $(document).on("click", ".delete-rocket-site", function (e) {
    e.preventDefault();
    var site_id = $(this).data("site-id");
    var error_div = $("div#error_div");
    error_div.hide();

    $(".wc-rocket-loader").removeClass("hide");
    $.ajax({
      url: ajax.ajax_url,
      method: "POST",
      data: {
        action: "delete_rocket_site",
        site_id: site_id,
      },
      success: function (response) {
        $(".wc-rocket-loader").addClass("hide");
        if (response.success) {
          window.location.href = window.location.href;
        } else {
          error_div.html(response.data.message);
          error_div.show();
          $("html, body").animate(
            {
              scrollTop: $("div#error_div").offset().top - 40,
            },
            2000
          );
        }
      },
    });
  });

  $(document).on("click", ".rocket-site-name-edit", function (e) {
    e.preventDefault();
    var rocket_site_name_wrap = $(this).closest(".rocket-site-name-wrapper");

    // show save , cancel, site name
    rocket_site_name_wrap
      .find(
        ".rocket-site-name, .rocket-site-name-save, .rocket-site-name-cancel"
      )
      .removeClass("hide");
    // hide site display name
    rocket_site_name_wrap
      .find(".site-name-display, .rocket-site-name-edit")
      .addClass("hide");
  });

  $(document).on("click", ".rocket-site-name-cancel", function (e) {
    e.preventDefault();
    var rocket_site_name_wrap = $(this).closest(".rocket-site-name-wrapper");

    hide_edit_site_name_buttons(rocket_site_name_wrap);
  });

  $(document).on("click", ".rocket-site-name-save", function (e) {
    e.preventDefault();
    var site_id = $(this).data("site-id"),
      rocket_site_name_wrap = $(this).closest(".rocket-site-name-wrapper"),
      site_name = rocket_site_name_wrap.find(".rocket-site-name").val(),
      site_msg_div = rocket_site_name_wrap.find(".rocket-site-msg-container");
    site_msg_div.hide();

    $(".wc-rocket-loader").removeClass("hide");
    $.ajax({
      url: ajax.ajax_url,
      method: "POST",
      data: {
        action: "update_rocket_site",
        site_id: site_id,
        site_name: site_name,
      },
      success: function (response) {
        $(".wc-rocket-loader").addClass("hide");
        if (response.success) {
          site_msg_div.html(response.data.message);
          site_msg_div.show();
          rocket_site_name_wrap.find(".rocket-site-name");
          rocket_site_name_wrap.find(".site-name-display").html(site_name);
          hide_edit_site_name_buttons(rocket_site_name_wrap);
          //                    window.location.href = window.location.href;
        } else {
          site_msg_div.html(response.data.message);
          site_msg_div.show();
          //                    $('html, body').animate({
          //                        scrollTop: $("div#error_div").offset().top - 40
          //                    }, 2000);
        }
      },
    });
  });

  function hide_edit_site_name_buttons(rocket_site_name_wrap) {
    rocket_site_name_wrap
      .find(
        ".rocket-site-name, .rocket-site-name-save, .rocket-site-name-cancel"
      )
      .addClass("hide");
    rocket_site_name_wrap
      .find(".site-name-display, .rocket-site-name-edit")
      .removeClass("hide");
  }

  // Show create site form
  $(document).on("click", ".create-new-site-btn", function (e) {
    e.preventDefault();
    var $form = $(".wc-rocket-create-site-form");

    // Get available allocations for this user
    $.ajax({
      url: wc_rocket_params.ajax_url,
      method: "POST",
      data: {
        action: "get_available_allocations",
        nonce: wc_rocket_params.nonce,
      },
      success: function (response) {
        if (response.success) {
          // Update allocation details and ID
          $("#allocation_details").html(response.data.html);
          $("#allocation_id").val(response.data.allocation_id);
          $form.removeClass("hide");
        } else {
          alert(response.data.message || "Error loading allocations");
        }
      },
    });
  });

  // Hide create site form
  $(document).on("click", ".cancel-create-site", function (e) {
    e.preventDefault();
    $(".wc-rocket-create-site-form").addClass("hide");
    $(".create-new-site-btn").removeClass("hide");
  });

  // Handle site creation form submission
  $("#rocket-create-site-form").on("submit", function (e) {
    e.preventDefault();

    var $form = $(this);
    var $submitButton = $form.find("button[type='submit']");
    var allocation_id = $("#allocation_id").val();

    if (!allocation_id) {
      alert("No allocation available");
      return;
    }

    $submitButton.prop("disabled", true);

    $.ajax({
      url: wc_rocket_params.ajax_url,
      method: "POST",
      data: {
        action: "create_rocket_site",
        nonce: wc_rocket_params.nonce,
        site_name: $("#site_name").val(),
        site_location: $("#site_location").val(),
        allocation_id: allocation_id,
      },
      success: function (response) {
        if (response.success) {
          location.reload();
        } else {
          alert(response.data.message || "Error creating site");
        }
      },
      complete: function () {
        $submitButton.prop("disabled", false);
      },
    });
  });

  function validateSiteName(name) {
    return /^[a-zA-Z0-9-]+$/.test(name);
  }
});
