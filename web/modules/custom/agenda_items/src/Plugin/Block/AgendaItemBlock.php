<?php
/**
 * @file
 * Contains \Drupal\agenda_items\Plugin\Block\AgendaItemBlock.
 */
namespace Drupal\agenda_items\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

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
    if ($this->isVisible()){
    $form = \Drupal::formBuilder()->getForm('\Drupal\agenda_items\Form\AgendaForm');
    }
    return $form;
  }

  protected function isVisible () {
    $nid = \Drupal::routeMatch()->getParameter('node')->id();
    $node = Node::load($nid);
    $status = $node->get('field_meeting_status');
    $tid = $status->getValue()[0]['target_id'];
    $term = Term::load($tid);
    if ($term->get('name')->value == 'Collecting agenda items') {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

}
