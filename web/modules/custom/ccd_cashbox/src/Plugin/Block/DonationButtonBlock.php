<?php
/**
 * @file
 * Contains \Drupal\ccd_cashbox\Plugin\Block\DonationButtonBlock.
 */
namespace Drupal\ccd_cashbox\Plugin\Block;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Form\FormStateInterface;

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
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    return $form;
  }


  /**
   * {@inheritdoc}
   */

  public function build() {
    return \Drupal::formBuilder()->getForm('Drupal\ccd_cashbox\Form\DonationForm');
  }

}
