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
 * Provides an 'cashbox_operations_memo_block' block.
 *
 * @Block(
 *   id = "ccd_cashbox_cashbox_operations_memo_block",
 *   admin_label = @Translation("CCD Cashbox Operations Memo Button"),
 *   category = @Translation("Dance Night Operations")
 * )
 */
class CashboxOperationsMemoBlock extends BlockBase {

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
    return \Drupal::formBuilder()->getForm('Drupal\ccd_cashbox\Form\CashboxOperationsMemoForm');
  }

}
