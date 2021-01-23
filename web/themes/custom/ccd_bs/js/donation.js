(function ($) {
  Drupal.behaviors.donateSelect = {
    attach: function (context, settings) {
      $('div.js-form-item-donation-radios-radios input').click(function() {
        var radioVal = $('div.js-form-item-donation-radios-radios input:checked').val();
        $('div.js-form-item-donation-radios-radios input:not(:checked)').parent().parent().removeClass("btn-success").addClass('btn-info');
        $('div.js-form-item-donation-radios-radios input:checked').parent().parent().removeClass("btn-info").addClass("btn-success");
        $('input[name="hidden_commerce_price"]').val(radioVal);
      });
      $('input[id^="edit-donation-radios-other"]').change(function() {
        input = $('input[id^="edit-donation-radios-other"]').val();
        $('input[name="hidden_commerce_price"]').val(input);
      })
    }
  }
})(jQuery);
