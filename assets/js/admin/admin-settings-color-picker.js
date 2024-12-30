jQuery(function ($) {
  $('.colorpicker').on('input', function() {
    var colorpicker_id = $(this).attr("id");
    $(`#hexcolor${colorpicker_id.split("colorpicker")[1]}`).val(this.value);
  });
  $('.hexcolor').on('input', function() {
    var hexcolor_id = $(this).attr("id");
    $(`#colorpicker${hexcolor_id.split("hexcolor")[1]}`).val(this.value);
  });
});
