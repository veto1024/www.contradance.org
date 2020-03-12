<?php
namespace Drupal\ccd_booking\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Booking Page module.
 */
class BookingPage extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function booking() {
    $element = [
      'form' => \Drupal::formBuilder()->getForm('\Drupal\ccd_booking\Form\NodeCreate'),
    ];
    return $element;
  }

}
