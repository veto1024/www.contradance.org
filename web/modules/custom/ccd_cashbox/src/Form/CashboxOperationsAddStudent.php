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
 * CashboxOperationsAddStudent class.
 */

class CashboxOperationsAddStudent extends FormBase {

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

    $form['add_student'] = [
      '#type' => 'submit',
      '#value' => $this->t('+1 Student'),
      '#attributes' => [
        'class' => [
          'use-ajax',
          'btn',
          'btn-lg',
          'btn-primary',
        ],
      ],
      '#ajax' => [
        'callback' => '::addStudent',
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
  return 'ccd_cashbox_operations_add_student_form';
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
  return ['config.ccd_cashbox_operations_add_student_form'];
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function addStudent(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand('.view-display-id-event_view_attendance_summary', 'trigger', ['RefreshView']));
    $response->addCommand(new OpenModalDialogCommand("Student Added", 'New Student added'));

    return $response;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = $node->id();
    $node = Node::load($nid);
    $prev_student = $node->get('field_student_dancers');
    $node->set('field_student_dancers', $prev_student[0]->value + 1);
    ccd_cashbox_node_recalculate($node);
    $node->save();
  }
}


