<?php
namespace Drupal\ccd_cashbox\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * CashboxOperationsPayoutForm class.
 */

class CashboxOperationsPayoutForm extends FormBase {

  /**
   * {@inheritdoc}
   */

  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
    $node = \Drupal::routeMatch()->getParameter('node');
    if (!is_null($node)) {
      $nid = $node->id();
      $form['payout_submit'] = [
        '#type' => 'link',
        '#title' => $this->t('Payout Step'),
        '#url' => Url::fromRoute('ccd_cashbox.operations.payout_modal', ['node' => $nid]),
        '#attributes' => [
          'class' => [
            'use-ajax',
            'button',
            'btn',
            'btn-lg',
            'btn-success',
            'modal-classy',
          ],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode(array(
            'width' => 600,
          )),
        ],
      ];
    }

  // Attach the library for pop-up dialogs/modals.
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attached']['library'][] = 'core/drupal.ajax';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
    public function submitForm(array &$form, FormStateInterface $form_state) {}
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
  return 'ccd_cashbox_operations_payout_form';
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
  return ['config.ccd_cashbox_operations_payout_form'];
  }

}
