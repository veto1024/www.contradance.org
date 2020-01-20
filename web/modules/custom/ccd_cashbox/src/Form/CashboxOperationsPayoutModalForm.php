<?php
namespace Drupal\ccd_cashbox\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Element;

/**
 * CashboxOperationsPayoutModalForm class.
 */
class CashboxOperationsPayoutModalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ccd_cashbox_operations_payout_modal_form';
  }

  public static function create(ContainerInterface $container) {
    // Create a new form object and inject its services.
    $form = new static();
    $form->setRequestStack($container->get('request_stack'));
    $form->setStringTranslation($container->get('string_translation'));
    $form->setMessenger($container->get('messenger'));
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attached']['library'][] = 'core/drupal.ajax';

    $form['#prefix'] = '<div id="cashbox_operations_payout_modal_form">';
    $form['#suffix'] = '</div>';

    // The status messages that will contain any form errors.
    $form['status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -10,
    ];
    $form['#tree'] = TRUE;

    $form['num_of_payees'] = [
      '#type' => 'radios',
      '#title' => $this->t('How many people are being paid tonight?'),
      '#options' => [
        1 => '1',
        2 => '2',
        3 => '3',
        4 => '4',
        5 => '5',
        6 => '6',
        7 => '7',
        8 => '8',
        9 => '9',
        10 => '10',
      ],
      '#default_value' => 1,
      '#attributes' => [
        'name' => 'field_num_of_payees',
      ],
      '#ajax' => [
        'callback' => '::addFormCallback',
        'event' => 'change',
        'wrapper' => 'person-row-wrapper',
      ],
    ];


    $form['person_fieldset'] = [
      '#type' => 'details',
      '#prefix' => '<div id="person-row-wrapper">',
      '#suffix' => '</div>',
      '#attributes' => [
        'class' => [
          'container-inline',
        ],
      ],
      '#open' => TRUE,
    ];

    for ($i = 0; $i < $form_state->getUserInput()['field_num_of_payees']; $i++) {

      $form['person_fieldset'][$i] = [
        '#type' => 'fieldset',
      ];

      $form['person_fieldset'][$i]['name'] = [
        '#type' => 'entity_autocomplete',
        '#title' => $this->t('Person paid'),
        '#target_type' => 'node',
        '#selection_settings' => [
          'target_bundles' => [
            'person',
          ],
        ],
        '#tags' => TRUE,
        '#required' => TRUE,
        '#attributes' => [
          'name' => 'field_person_fieldset_name_' . $i,
        ],
      ];

      $form['person_fieldset'][$i]['amount_paid'] = [
        '#type' => 'number',
        '#step' => .01,
        '#default_value' => 0,
        '#title' => $this->t('Amount paid $'),
        '#attributes' => [
          'name' => 'field_person_fieldset_amount_paid_' . $i,
        ],
      ];
    }

    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit Post-Dance Form'),
      '#attributes' => [
        'class' => [
          'use-ajax-submit',
          'btn',
          'btn-success',
        ],
      ],
      '#ajax' => [
        'callback' => '::submitAjaxForm',
        'wrapper' => 'cashbox_operations_payout_modal_form',
      ],
    ];

    $form['nid'] = array(
      '#type' => 'hidden',
      '#title' => $this->t('The nid of the submission'),
    );

    return $form;
  }

  /**
  * Generates the initial number of submissions
  */
  public function addFormCallback(array &$form, FormStateInterface $form_state) {

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#person-row-wrapper', $form['person_fieldset']));
    return $response;
  }


  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $form_state->clearErrors();
    $num_fields = $form_state->getUserInput()['field_num_of_payees'];
    $form_state->set('num_rows', $num_fields);
  }

  /**
   * AJAX callback handler that displays any errors or a success message.
   * @param array $form
   * @param FormStateInterface $form_state
   * @return AjaxResponse
   */
  public function submitAjaxForm(array $form, FormStateInterface $form_state) {
    $form['#cache'] = ['max-age' => 0];
    $response = new AjaxResponse();

    // If there are any form errors, re-display the form.
    if ($form_state->hasAnyErrors()) {
      $response->addCommand(new ReplaceCommand('#cashbox_operations_payout_modal_form', $form));
    }
    else {
      $response->addCommand(new OpenModalDialogCommand("Success!", 'Payout Form Completed.', ['width' => 800]));
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $input = $form_state->getUserInput();
    $nid = $form_state->getvalue('nid');
    $node = Node::load($nid);
    $num = $form_state->getUserInput()['field_num_of_payees'];
    $i = 0;
    for ($i = 0; $i < $num; $i++) {
      // Create a new paragraph
      $name = $form_state->getUserInput()['field_person_fieldset_name_' . $i];
      $id = Element\EntityAutocomplete::extractEntityIdFromAutocompleteInput($name);
      $amount = $form_state->getUserInput()['field_person_fieldset_amount_paid_' . $i];
      $paragraph = Paragraph::create([
        'type' => 'people_and_payments',
        'field_amount_paid' => $amount,
        'field_person_paid' => $id,
      ]);
      $paragraph->isNew();
      $paragraph->save();

      $current = $node->get('field_person_paid')->getValue();
      $current[] = array(
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      );
      $node->set('field_person_paid', $current);
      $node->save();
    }
    $node->save();
    $response->addCommand(new OpenModalDialogCommand("Success!", 'Payout data recorded.'));

    return $response;
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *
   * An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
  return ['config.ccd_cashbox_operations_payout_modal_form'];
  }

}
