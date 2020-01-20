<?php
namespace Drupal\ccd_cashbox\Form;

use CommerceGuys\Addressing\Address;
use Drupal\address\Plugin\Validation\Constraint\AddressFormatConstraint;
use Drupal\commerce_price\Price;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use CommerceGuys\Addressing\AddressFormat\AddressField;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Validation;

/**
 * PreDanceCashboxOperationsModalForm class.
 */
class PreDanceCashboxOperationsModalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ccd_cashbox_pre_dance_cashbox_operations_modal_form';
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

    $form['#prefix'] = '<div id="pre_dance_cashbox_operations_modal_form">';
    $form['#suffix'] = '</div>';

    // The status messages that will contain any form errors.
    $form['status_messages'] = [
    '#type' => 'status_messages',
      '#weight' => -10,
    ];

    $form['cashbox_in'] = [
      '#type' => 'number',
      '#default_value' => 0,
      '#title' => $this->t('How much cash is in the cashbox before any addition from the ATM?'),
      '#step' => .01,
      '#required' => TRUE,
      '#attributes' => [
        'name' => 'field_cashbox_in',
      ],
      '#description' => $this->t('The amount in the cashbox does not include money set aside for deposit or money from the ATM.'),
    ];

    $form['atm_to_cashbox'] = [
      '#type' => 'number',
      '#default_value' => 0,
      '#title' => $this->t('How much cash was added from the ATM before the dance?'),
      '#step' => .01,
      '#required' => TRUE,
      '#attributes' => [
        'name' => 'field_atm_in',
      ],
      '#description' => $this->t('This amount is 0 unless otherwise told differently by the Treasurer, Booker, or Steering Committee member'),
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit Pre-Dance Form'),
      '#attributes' => [
        'class' => [
          'use-ajax',
          'btn',
          'btn-success',
        ],
      ],
      '#ajax' => [
        'callback' => '::submitAjaxForm',
        'wrapper' => 'pre_dance_cashbox_operations_modal_form',
      ],
    ];

    $form['nid'] = array(
      '#type' => 'hidden',
      '#title' => $this->t('The nid of the submission'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $form_state->clearErrors();
    $input = $form_state->getUserInput();
    if (!$input['field_cashbox_in']) {
      $form_state->setErrorByName('cashbox_in', t('Please provide the starting cash!'));
    }
    if (!is_numeric($input['field_cashbox_in'])) {
      $form_state->setErrorByName('cashbox_in', t('Please provide a numeric value!'));
    }
    if (!$input['field_atm_in']) {
      if (!$input['field_atm_in'] == 0) {
        $form_state->setErrorByName('atm_to_cashbox', t('Please provide how much was taken from the ATM before the dance!'));
      }
    }
    if (!is_numeric($input['field_atm_in'])) {
      $form_state->setErrorByName('atm_to_cashbox', t('Please provide a numeric value!'));
    }
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
      $response->addCommand(new ReplaceCommand('#pre_dance_cashbox_operations_modal_form', $form));
    }
    else {
      $response->addCommand(new OpenModalDialogCommand("Success!", 'Pre-dance Form Completed. Please refresh the page to verify data.', ['width' => 800]));
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $input = $form_state->getUserInput();
    $nid = $form_state->getvalue('nid');
    $node = Node::load($nid);
    $node->set('field_starting_cash', $input['field_cashbox_in']);
    $node->set('field_cash_added_from_atm', $input['field_atm_in']);
    $node->save();
    $response->addCommand(new OpenModalDialogCommand("Success!", 'Cashbox sheet updated.'));

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
  return ['config.ccd_cashbox_pre_dance_cashbox_operations_modal_form'];
  }

}
