<?php
/**
 * @file
 * Contains \Drupal\agenda_items\Form\AgendaForm.
 */

namespace Drupal\agenda_items\Form;
use CommerceGuys\Addressing\Address;
use Drupal\address\Plugin\Validation\Constraint\AddressFormatConstraint;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\Validator\Validation;

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
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attached']['library'][] = 'core/drupal.ajax';

    $form['#prefix'] = '<div id="agenda_submission_form">';
    $form['#suffix'] = '</div>';

    // The status messages that will contain any form errors.
    $form['status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -10,
    ];

    $form['submitted_by'] = [
      '#type' => 'textfield',
      '#title' => t('Submitted By'),
      '#description' => t('Who is requesting this agenda item?'),
      '#required' => TRUE,
      '#name' => 'field_submitted_by',
    ];

    $form['short_description'] = [
      '#type' => 'textfield',
      '#title' => t('Short Description'),
      '#description' => t('Please provide a short description to act as the agenda item title'),
      '#required' => TRUE,
      '#name' => 'field_short_description',
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => t('Description'),
      '#description' => t('Describe this agenda item. Preferably include objectives and estimated time needed'),
      '#required' => TRUE,
      '#name' => 'field_description',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit Agenda Item'),
      '#attributes' => [
        'class' => [
          'btn',
          'btn-large',
          'btn-success',
          'use-ajax',
        ],
      ],
      '#ajax' => [
        'callback' => '::submitAjaxForm',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    $form_state->clearErrors();
    $input = $form_state->getUserInput();
    if (!$input['field_submitted_by']) {
      $form_state->setErrorByName('submitted_by', t('Please provide the name of the person submitting this agenda item'));
    }
    if (!$input['field_short_description']) {
      $form_state->setErrorByName('short_description', t('Please provide a short description to quickly link to your agenda item'));
    }
    if (!$input['field_description']) {
      $form_state->setErrorByName('description', t('Please describe your agenda item'));
    }
  }


/**
 * {@inheritdoc}
 */
  public function submitAjaxForm(array &$form, FormStateInterface $form_state) {
    $form['#cache'] = ['max-age' => 0];
    $response = new AjaxResponse();

    // If there are any form errors, re-display the form.
    if ($form_state->hasAnyErrors()) {
      $response->addCommand(new ReplaceCommand('#agenda_submission_form', $form));
    }
    else {
      $response->addCommand(new InvokeCommand('.view-display-id-agenda_items_public_collecting', 'trigger', ['RefreshView']));
      $response->addCommand(new InvokeCommand('.view-display-id-agenda_items_private_collecting', 'trigger', ['RefreshView']));
      $response->addCommand(new OpenModalDialogCommand("Success!", 'Agenda Item Submitted', ['width' => 800]));
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $input = $form_state->getUserInput();
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
      'field_submitted_by' => $input['field_submitted_by'],
      'field_short_description' => $input['field_short_description'],
      'field_item_description' => $input['field_description'],
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
