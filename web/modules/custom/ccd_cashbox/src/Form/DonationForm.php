<?php
namespace Drupal\ccd_cashbox\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * DonationForm class.
 */

class DonationForm extends FormBase {

  /**
   * {@inheritdoc}
   */

  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
    $node = \Drupal::routeMatch()->getParameter('node');
    if (!$node) {
      $nid = 1;
    } else {
      $nid = $node->id();
    }
    $form['create_donation'] = [
      '#type' => 'link',
      '#title' => $this->t('Create Donation'),
      '#url' => Url::fromRoute('ccd_cashbox.donation.open_modal_form', ['node' => $nid]),
      '#attributes' => [
        'class' => [
          'use-ajax',
          'button',
          'btn',
          'btn-lg',
          'btn-primary',
          'modal-classy',
        ],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => Json::encode(array(
          'width' => 600,
        )),
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
    public function submitForm(array &$form, FormStateInterface $form_state) {
      $response = new AjaxResponse();
      $response->addCommand(new InvokeCommand('.view-display-id-event_view_cashbox_summary_view', 'trigger', ['RefreshView']));
      $response->addCommand(new InvokeCommand('.view-display-id-event_view_donation_summary_view', 'trigger', ['RefreshView']));
      $response->addCommand(new OpenModalDialogCommand("Success!", 'The donation has been submitted! Note that you will have to refresh to see new donations. Click anywhere to exit.'));
      return $response;
    }
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
  return 'ccd_cashbox_donation_form';
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
  return ['config.ccd_cashbox_donation_form'];
  }

}
