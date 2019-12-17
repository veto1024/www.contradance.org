<?php
/**
 * @file
 * Contains \Drupal\ccd_cashbox\Plugin\Block\DonationButtonBlock.
 */
namespace Drupal\ccd_cashbox\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * Provides an 'donation_button' block.
 *
 * @Block(
 *   id = "ccd_cashbox_donation_button",
 *   admin_label = @Translation("Donation Submission"),
 *   category = @Translation("Button to pull up donation modal")
 * )
 */
class DonationButtonBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $url = Url::fromRoute('ccd_cashbox.donation.open_modal_form');
    return $url->toRenderArray();
//    return [
//     '#markup' => $this->t('This is a simple block!'),
//    ];
  }
}
