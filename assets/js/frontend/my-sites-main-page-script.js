jQuery(document).ready(function ($) {
  console.log("WC Rocket script loaded");

  if (typeof wc_rocket_params === "undefined") {
    console.error("WC Rocket params not found!");
    return;
  }

  console.log("AJAX URL:", wc_rocket_params.ajax_url);
  console.log("Nonce:", wc_rocket_params.nonce);

  // Show create site form
  $(document).on("click", ".create-new-site-btn", function (e) {
    e.preventDefault();
    var $form = $(".wc-rocket-create-site-form");
    var $mainContent = $(".wc-rocket-my-sites-content");
    var $allocationsInfo = $(".wc-rocket-available-allocations");
    var $loader = $(".wc-rocket-loader");

    console.log("Getting allocations...");

    // Show loader
    $loader.removeClass("hide");

    // Get available allocations for this user
    $.ajax({
      url: wc_rocket_params.ajax_url,
      method: "POST",
      data: {
        action: "get_available_allocations",
        nonce: wc_rocket_params.nonce,
      },
      success: function (response) {
        console.log("Allocation response:", response);
        if (response.success) {
          // Hide main content and allocations info
          $mainContent.addClass("hide");
          $allocationsInfo.addClass("hide");

          // Update allocation details and show form
          $("#allocation_details").html(response.data.html);
          $form.removeClass("hide");
        } else {
          alert(response.data.message || "Error loading allocations");
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error:", error);
        console.error("Status:", status);
        console.error("Response:", xhr.responseText);
        alert("Error loading allocations");
      },
      complete: function () {
        // Hide loader
        $loader.addClass("hide");
      },
    });
  });

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
          // window.location.href = window.location.href;
        } else {
          site_msg_div.html(response.data.message);
          site_msg_div.show();
          // $('html, body').animate({
          //     scrollTop: $("div#error_div").offset().top - 40
          // }, 2000);
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

  // Handle site creation form submission
  $("#rocket-create-site-form").on("submit", function (e) {
    e.preventDefault();

    var $form = $(this);
    var $submitButton = $form.find("button[type='submit']");
    var $loader = $(".wc-rocket-loader");

    $submitButton.prop("disabled", true);

    // Show loader
    $loader.removeClass("hide");

    $.ajax({
      url: wc_rocket_params.ajax_url,
      method: "POST",
      data: {
        action: "create_rocket_site",
        nonce: wc_rocket_params.nonce,
        site_name: $("#site_name").val(),
        site_location: $("#site_location").val(),
      },
      success: function (response) {
        if (response.success) {
          location.reload();
        } else {
          alert(response.data.message || "Error creating site");
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error:", error);
        console.error("Status:", status);
        console.error("Response:", xhr.responseText);
        alert("Error creating site");
      },
      complete: function () {
        $submitButton.prop("disabled", false);
        // Hide loader
        $loader.addClass("hide");
      },
    });
  });

  // Hide create site form and show main content
  $(document).on("click", ".cancel-create-site", function (e) {
    e.preventDefault();
    var $form = $(".wc-rocket-create-site-form");
    var $mainContent = $(".wc-rocket-my-sites-content");
    var $allocationsInfo = $(".wc-rocket-available-allocations");

    // Hide form
    $form.addClass("hide");

    // Show main content and allocations info
    $mainContent.removeClass("hide");
    $allocationsInfo.removeClass("hide");
  });

  function validateSiteName(name) {
    return /^[a-zA-Z0-9- ]+$/.test(name);
  }
});
