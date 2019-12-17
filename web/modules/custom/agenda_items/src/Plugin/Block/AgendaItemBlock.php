<?php
/**
 * @file
 * Contains \Drupal\agenda_items\Plugin\Block\AgendaItemBlock.
 */
namespace Drupal\agenda_items\Plugin\Block;
use Drupal\Core\Block\BlockBase;

/**
 * Provides an 'agenda_items' block.
 *
 * @Block(
 *   id = "agenda_items_block",
 *   admin_label = @Translation("Agenda Item Submission"),
 *   category = @Translation("Form for requesting agenda items")
 * )
 */
class AgendaItemBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('\Drupal\agenda_items\Form\AgendaForm');
    return $form;
  }
}
