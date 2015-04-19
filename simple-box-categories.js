jQuery(document).ready(function($) {

  $(".sbc-color-field").wpColorPicker();  
  $(document).ajaxComplete(function(e, xhr, settings) {
    $(".sbc-color-field").wpColorPicker();
  });


  $(".sbc-advanced-options-button").on("click", function() {
    $(".sbc-advanced-options").toggle();
  });
});