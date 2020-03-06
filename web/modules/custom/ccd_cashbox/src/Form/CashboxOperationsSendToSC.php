<?php
namespace Drupal\ccd_cashbox\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * CashboxOperationsSendToSC class.
 */

class CashboxOperationsSendToSC extends FormBase {

  public static function create(ContainerInterface $container)
  {
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

  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL)
  {

    $form['submit_email'] = array(
      '#type' => 'submit',
      '#value' => t('Email SC'),
      '#weight' => 99,
      // Use default and an additional submit handler.
      '#submit' => ['ccd_cashbox_mail_submit'],
      '#attributes' => [
        'class' => [
          'btn',
          'btn-lg',
          'btn-success',
        ],
      ],
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'ccd_cashbox_operations_send_to_sc_form';
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames()
  {
    return ['config.ccd_cashbox_operations_send_to_sc_form'];
  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = $node->id();
    $node = Node::load($nid);
    ccd_cashbox_node_recalculate($node);
    $node->save();
  }
}

