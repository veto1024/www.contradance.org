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
 * CashboxOperationsPostDanceModalForm class.
 */
class CashboxOperationsPostDanceModalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ccd_cashbox_operations_postdance_modal_form';
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

    $form['#prefix'] = '<div id="cashbox_operations_post_dance_modal_form">';
    $form['#suffix'] = '</div>';

    // The status messages that will contain any form errors.
    $form['status_messages'] = [
    '#type' => 'status_messages',
      '#weight' => -10,
    ];

    $nid = \Drupal::routeMatch()->getParameter('node')->id();
    $node = Node::load($nid);
    if (!empty($node->get('field_cash_to_be_deposited'))) {
      $previousATMOut = $node->get('field_cash_to_be_deposited')->getValue()[0];
    }
    else {
      $previousATMOut = 300.;
    }
    $form['cash_ATM'] = [
      '#type' => 'number',
      '#default_value' => $previousATMOut,
      '#title' => $this->t('How much cash is being removed for deposit? DO NOT include checks.'),
      '#description' => $this->t('Note: If the deposit will not be physically separated from the rest of the money for deposit later (i.e., if the treasurer
         or another SC member is not taking the money away from the cashbox tonight, it is not considered "removed for deposit").
         If you are unsure, set this as 0 and leave all of the money in the cashbox'),
      '#step' => .01,
      '#required' => TRUE,
      '#attributes' => [
        'name' => 'field_cash_to_ATM',
      ],
    ];
    if (!empty($node->get('field_ending_cash'))) {
      $previousEndingCash = $node->get('field_ending_cash')->getValue()[0];
    }
    else {
      $previousEndingCash = 300.;
    }
    $form['cashbox_out'] = [
      '#type' => 'number',
      '#default_value' => $previousEndingCash,
      '#title' => $this->t('How much cash (not checks) will be left in the cashbox for use next week? We try to
        keep $300 in the cashbox for next week and deposit the rest.'),
      '#step' => .01,
      '#required' => TRUE,
      '#attributes' => [
        'name' => 'field_cash_out',
      ],
    ];
    if(!empty($node->get('field_checks_for_dance_admission'))) {
      $previousEndingCheck = $node->get('field_checks_for_dance_admission')->getValue()[0];
    } else {
      $previousEndingCheck = 0.;
    }

    $form['checks_admission'] = [
      '#type' => 'number',
      '#default_value' => $previousEndingCheck,
      '#title' => $this->t('How much money was collected for dance admissions as checks (in $)?'),
      '#step' => .01,
      '#required' => TRUE,
      '#attributes' => [
        'name' => 'field_checks_admission',
      ],
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit Post-Dance Form'),
      '#attributes' => [
        'class' => [
          'use-ajax',
          'btn',
          'btn-success',
        ],
      ],
      '#ajax' => [
        'callback' => '::submitAjaxForm',
        'wrapper' => 'cashbox_operations_post_dance_modal_form',
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
    if (!$input['field_cash_out']) {
      $form_state->setErrorByName('cashbox_out', t('Please provide the ending cash!'));
    }
    if (!is_numeric($input['field_cash_out'])) {
      $form_state->setErrorByName('cashbox_out', t('Please provide a numeric value!'));
    }
    if (!$input['field_checks_admission']) {
      if (!$input['field_checks_admission'] == 0) {
        $form_state->setErrorByName('checks_admission', t('Please provide dance admission amount (in $) paid with check ATM'));
      }
    }
    if (!is_numeric($input['field_checks_admission'])) {
      $form_state->setErrorByName('checks_admission', t('Please provide a numeric value!'));
    }
    if (!is_numeric($input['field_cash_to_ATM'])) {
      $form_state->setErrorByName('cash_ATM', t('Please provide a numeric value!'));
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
      $response->addCommand(new ReplaceCommand('#cashbox_operations_post_dance_modal_form', $form));
    }
    else {
      $response->addCommand(new OpenModalDialogCommand("Success!", 'Post-dance Form Completed. Please refresh the page to verify data.', ['width' => 800]));
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
    $node->set('field_ending_cash', $input['field_cash_out']);
    $node->set('field_checks_for_dance_admission', $input['field_checks_admission']);
    $node->set('field_cash_to_be_deposited', $input['field_cash_to_ATM']);
    ccd_cashbox_node_recalculate($node);
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
  return ['config.ccd_cashbox_operations_postdance_modal_form'];
  }

}
