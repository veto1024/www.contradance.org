/**
 * @file
 * Fades the background video in
 */

(function ($, Drupal) {
  Drupal.behaviors.videoFade = {
    attach: function (context) {
      $('.video-background', context).once('videoFade').each(function () {
        $(this).css('opacity', 0).animate(
          {opacity: .3},
          {
            duration:5000,
            specialEasing:
              {
                width: "linear",
                height: "easeOutBounce",
              },
          });
      });
    }
  }
}(jQuery, Drupal));
