(function ($, Drupal, drupalSettings) {

  "use strict";
  Drupal.behaviors.webformRedirect = {
    attach: function(context, settings) {
      if (drupalSettings['redirect']) {
        setTimeout(function(){window.location.href = drupalSettings['redirect']; }, 3000);
      }
    }
  }
})(jQuery, Drupal, drupalSettings);
