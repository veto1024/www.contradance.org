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
 * Provides an 'predance_cashbox_operations' block.
 *
 * @Block(
 *   id = "ccd_cashbox_predance_operations",
 *   admin_label = @Translation("Predance Operations Button"),
 *   category = @Translation("Dance Night Operations")
 * )
 */
class PredanceCashboxOperationsBlock extends BlockBase {

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
    return \Drupal::formBuilder()->getForm('Drupal\ccd_cashbox\Form\PreDanceCashboxOperationsForm');
  }

}
