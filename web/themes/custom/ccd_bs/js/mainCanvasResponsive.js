/**
 * @file
 * Ensures that the video on the screen pushes the main canvas down suficiently
 */

(function ($, Drupal) {
  Drupal.behaviors.mainCanvasResponsive = {
    attach: function (context) {
      if ($('.navbar-collapse').height() > 100) {
        $(' .main-container', context).css('margin-top', $('.video-background').height()  - $('.navbar-header').height())
        console.log($('.navbar-collapse').height())
        console.log($('.navbar-header').height())
      }
      else if ($('.navbar-collapse').height() > 50) {
        $(' .main-container', context).css('margin-top', $('.video-background').height() -  $('.navbar-header').height() - 4.0 * $('.navbar-collapse').height())
        console.log($('.navbar-collapse').height())
        console.log($('.navbar-header').height())
      }
      else {
        $(' .main-container', context).css('margin-top', $('.video-background').height() -  $('.navbar-header').height() - 8.0 * $('.navbar-collapse').height())
        console.log($('.navbar-collapse').height())
        console.log($('.navbar-header').height())
      }
    }
  }
}(jQuery, Drupal));

