<?php
namespace Drupal\ccd_cashbox\Form;


use Drupal\Core\Ajax\InvokeCommand;
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
 * CashboxOperationsMemoModalForm class.
 */
class CashboxOperationsMemoModalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ccd_cashbox_operations_memo_modal_form';
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

    $form['#prefix'] = '<div id="cashbox_operations_memo_modal_form">';
    $form['#suffix'] = '</div>';

    // The status messages that will contain any form errors.
    $form['status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -10,
    ];
    $form['#tree'] = TRUE;
    $nid = \Drupal::routeMatch()->getParameter('node')->id();
    $node = Node::load($nid);

    $form['reporter'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name of person reporting this information).'),
      '#attributes' => [
        'name' => 'field_reporter',
      ],
    ];

    if ((isset($node->field_dance_reporter_name->value))) {
      $form['compete']['#default_value'] = $node->field_dance_reporter_name->value;
    }

    $form['memo'] = [
      '#type' => 'textarea',
      '#rows' => 4,
      '#resizable' => 'vertical',
      '#title' => $this->t('Please include additional information about tonight\'s dance (e.g., # of lines at the start/end, any pertinent weather conditions, nearby events, or issues at the dance.'),
      '#attributes' => [
        'name' => 'field_memo',
      ],
      '#default_value' => "None",
    ];

    if ((isset($node->field_additional_information->value))) {
      $form['memo']['#default_value'] = $node->field_additional_information->value;
    }

    $form['compete'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Were there any competing events (e.g., regional contra weekends that might have reduced attendance).'),
      '#attributes' => [
        'name' => 'field_compete',
      ],
    ];

    if ((isset($node->field_competing_events->value))) {
      $form['compete']['#default_value'] = $node->field_competing_events->value;
    }




    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit Memo Form'),
      '#attributes' => [
        'class' => [
          'use-ajax-submit',
          'btn',
          'btn-success',
        ],
      ],
      '#ajax' => [
        'callback' => '::submitAjaxForm',
        'wrapper' => 'cashbox_operations_memo_modal_form',
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
      $response->addCommand(new ReplaceCommand('#cashbox_operations_memo_modal_form', $form));
    }
    else {
      $response->addCommand(new InvokeCommand('.view-display-id-event_view_memo_view', 'trigger', ['RefreshView']));
      $response->addCommand(new OpenModalDialogCommand("Success!", 'Memo Form Completed.', ['width' => 800]));
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
    $node->set('field_competing_events', $input['field_compete']);
    $node->set('field_additional_information', $input['field_memo']);
    $node->set('field_dance_reporter_name', $input['field_reporter']);
    $node->save();
    $response->addCommand(new OpenModalDialogCommand("Success!", 'Memo data recorded.'));

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
  return ['config.ccd_cashbox_operations_memo_modal_form'];
  }

}
