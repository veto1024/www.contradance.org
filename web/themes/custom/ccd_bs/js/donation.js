(function ($) {
  Drupal.behaviors.donateSelect = {
    attach: function (context, settings) {
      $('div.js-form-item-donation-radios-radios input').click(function() {
        $('div.js-form-item-donation-radios-radios input:not(:checked)').parent().parent().removeClass("btn-success").addClass('btn-info');
        $('div.js-form-item-donation-radios-radios input:checked').parent().parent().removeClass("btn-info").addClass("btn-success");
      });
    }
  }
})(jQuery);
