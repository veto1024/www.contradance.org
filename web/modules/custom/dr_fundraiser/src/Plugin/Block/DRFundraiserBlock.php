<?php

namespace Drupal\dr_fundraiser\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'DRFundraiserBlock' block.
 *
 * @Block(
 *  id = "drfundraiser_block",
 *  admin_label = @Translation("DR fundraiser block"),
 * )
 */
class DRFundraiserBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\commerce_store\CurrentStoreInterface definition.
   *
   * @var \Drupal\commerce_store\CurrentStoreInterface
   */
  protected $commerceStoreCurrentStore;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->commerceStoreCurrentStore = $container->get('commerce_store.current_store');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $total_donated = 0.0;
    $orders = \Drupal::entityTypeManager()
      ->getStorage('commerce_order')
      ->loadByProperties(['type' => 'dr_donation']);
    /* @var $order \Drupal\commerce_order\Entity\Order */
    foreach ($orders as $order) {
      $total_donated += floatval($order->getTotalPaid()->getNumber());
    }
    $build = [];
    $build['#theme'] = 'drfundraiser_block';
    $build['#total_donated'] = $total_donated;
    return $build;
  }

  public function getCacheMaxAge()
  {
    return 0;
  }

}
