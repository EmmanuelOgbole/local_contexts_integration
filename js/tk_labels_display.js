(function (Drupal) {
  Drupal.behaviors.tkLabels = {
    attach: function (context, settings) {
      document.querySelectorAll('.tk-label', context).forEach(function (element) {
        element.addEventListener('click', function (event) {
          event.preventDefault();

          // Get the associated text container
          const textContainer = this.querySelector('.tk-label-text-container');

          // Toggle open state for the clicked label only
          if (textContainer) {
            textContainer.classList.toggle('open');
          }
        });
      });
    }
  };
})(Drupal);
