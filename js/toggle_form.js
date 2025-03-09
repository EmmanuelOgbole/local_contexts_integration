(function ($) {
    $(document).ready(function () {
      // Initially hide the form container
      $('#form-container').hide();
  
      // Toggle form visibility when the button is clicked
      $('#toggle-form-button').click(function () {
        $('#form-container').toggle();
      });
    });
  })(jQuery);