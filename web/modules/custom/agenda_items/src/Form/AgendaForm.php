<?php
/**
 * @file
 * Contains \Drupal\agenda_items\Form\AgendaForm.
 */

namespace Drupal\agenda_items\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Agenda form.
 */
class AgendaForm extends FormBase
{
  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'agenda_items_agenda_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $form['submitted_by'] = array(
      '#type' => 'textfield',
      '#title' => t('Submitted By'),
      '#description' => t('Who is requesting this agenda item?'),
      '#required' => TRUE,
    );
    $form['short_description'] = array(
      '#type' => 'textfield',
      '#title' => t('Short Description'),
      '#description' => t('Please provide a short description to act as the agenda item title'),
      '#required' => TRUE,
    );
    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => t('Description'),
      '#description' => t('Describe this agenda item. Preferably include objectives and estimated time needed'),
      '#required' => TRUE,
    );
    $form['submit'] = array
    (
      '#type' => 'submit',
      '#value' => t('Submit Agenda Item'),
      '#attributes' => [
        'class' => ['btn btn-large btn-success'],
      ],
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      // You can get nid and anything else you need from the node object.
      $nid = $node->id();
    }
    /* $nid=\Drupal::entityTypeManager()->getStorage('node')->load($nid); */
    $node=\Drupal\node\Entity\Node::load($nid);

    // Create single new paragraph
    $paragraph = Paragraph::create([
      'type' => 'agenda_item',
      'field_submitted_by' => $form['submitted_by']['#value'],
      'field_short_description' => $form['short_description']['#value'],
      'field_item_description' => $form['description']['#value'],
    ]);
    $paragraph->isNew();
    $paragraph->save();

    $current = $node->get('field_agenda_items')->getValue();
    $current[] = array(
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    );
    $node->set('field_agenda_items', $current);
    $node->save();
  }
}
