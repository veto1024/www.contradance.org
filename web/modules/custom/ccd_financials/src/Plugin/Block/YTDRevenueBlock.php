<?php
/**
 * @file
 * Contains \Drupal\ccd_financials\Plugin\Block\DonationButtonBlock.
 */
namespace Drupal\ccd_financials\Plugin\Block;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\Feeds\Target\DateTime;
use Drupal\node\Entity\Node;

/**
 * Provides an 'ytd_revenue_block' block.
 *
 * @Block(
 *   id = "ccd_financials_ytd_revenue_block",
 *   admin_label = @Translation("YTD Revenue Block"),
 *   category = @Translation("Block showing CCD Year To Date Info")
 * )
 */
class YTDRevenueBlock extends BlockBase {

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
    $now = new \DateTime('now');
    $now->getTimestamp();
    $query = \Drupal::entityQuery('node')
      ->condition('type','event')
      ->condition('status',1)
      ->condition('field_starting_cash', 0.0,'>')
      ->condition('field_event_date.value', array("2020-01-01T00:00:00", $now->format('Y-m-d\TH:i:s')), 'BETWEEN');

    $results=$query->execute();
    $total_rev = 0.0;
    $total_exp = 0.0;
    foreach ($results as $result) {
      $node = Node::load($result);
      $revenue = $node->get('field_gross_revenue')[0];
      $expenses = $node->get('field_band_pay')[0]->value +
        $node->get('field_band_travel')[0]->value +
        $node->get('field_caller_pay')[0]->value +
        $node->get('field_caller_travel')[0]->value +
        $node->get('field_rent_cost')[0]->value +
        $node->get('field_sound_pay')[0]->value +
        $node->get('field_other_expenses')[0]->value;
      if (($revenue > 0.0) && ($expenses > 0.0)) {
        $total_rev += $revenue->value;
        $total_exp += $expenses;
      }
    }
    return [
      '#theme' => 'ccd_financials_ytd_revenue',
      '#total_rev' => $total_rev,
      '#total_exp' => $total_exp,
      '#net_profit' => $total_rev - $total_exp,
    ];
  }

}
