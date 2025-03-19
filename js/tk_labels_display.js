(function (Drupal) {
  Drupal.behaviors.tkLabels = {
    attach: function (context, settings) {
      //click event listeners to each label
      document.querySelectorAll('.tk-label', context).forEach(function (element) {
        element.addEventListener('click', function (event) {
          event.preventDefault();

          // Get the associated text container
          const textContainer = this.querySelector('.tk-label-text-container');

          // Toggle to show or hide the text box
          if (textContainer) {
            const isOpen = textContainer.classList.contains('open');
            // Toggle the current container's visibility without affecting others
            if (isOpen) {
              textContainer.classList.remove('open');
            } else {
              textContainer.classList.add('open');
            }
          }
        });
      });
    }
  };
})(Drupal);
