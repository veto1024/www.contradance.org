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
    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = $node->id();
    $form['nid'] = array(
      '#type' => 'hidden',
      '#title' => $this->t('The nid of the submission'),
    );

    $form['my_embedded_view'] = [
      '#title' => "Tonight's Payout Summary",
      '#type' => 'view',
      '#name' => 'today_payout',
      '#display_id' => 'payout_view',
      '#arguments' => [
        'nid' => $nid,
      ],
    ];
    $form['discrepancy'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Payout Discrepancy"),
      '#description' => $this->t('There was a discrepancy tonight'),
      '#attributes' => [
        'name' => 'field_discrepancy_boolean',
      ],
    ];
    $form['discrepancy_note'] = [
      '#type' => 'textarea',
      '#rows' => 2,
      '#title' => $this->t("Discrepancy Note"),
      '#attributes' => [
        'name' => 'field_discrepancy_memo',
      ],
      '#placeholder' => $this->t("If there was a discrepancy, please provide details, including
1) what the discrepancy was and 2) how it was ultimately addressed"),
      '#states' => [
        'visible' => [
          ':input[name="field_discrepancy_boolean"]' => ['checked' => TRUE],
        ],
      ],
    ];

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
    if ($input['field_discrepancy_boolean']) {
      $node->set('field_payout_discrepancy_', '1');
      $node->set('field_payout_discrepancy_note', $input['field_discrepancy_memo']);
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
