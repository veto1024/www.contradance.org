<?php
namespace Drupal\ccd_cashbox\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * CashboxOperationsAddFullIOU class.
 */

class CashboxOperationsAddFullIOU extends FormBase {

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

  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {

    $form['add_full_iou'] = [
      '#type' => 'submit',
      '#value' => $this->t('$10 IOU'),
      '#attributes' => [
        'class' => [
          'use-ajax',
          'btn',
          'btn-lg',
          'btn-primary',
        ],
      ],
      '#ajax' => [
        'callback' => '::addIOU',
        'event' => 'click',
      ],
    ];

  // Attach the library for pop-up dialogs/modals.
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attached']['library'][] = 'core/drupal.ajax';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
  return 'ccd_cashbox_operations_add_full_iou_form';
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
  return ['config.ccd_cashbox_operations_add_full_iou_form'];
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function addIOU(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand('.view-display-id-event_view_attendance_summary', 'trigger', ['RefreshView']));
    $response->addCommand(new InvokeCommand('.view-display-id-event_view_revenue_summary_view', 'trigger', ['RefreshView']));
    $response->addCommand(new OpenModalDialogCommand("$5 IOU Added", 'New $5 IOU added.'));

    return $response;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = $node->id();
    $node = Node::load($nid);
    $prev_value= $node->get('field_iou_dancers_full');
    $node->set('field_iou_dancers_full', $prev_value[0]->value + 1);
    ccd_cashbox_node_recalculate($node);
    $node->save();
  }
}


