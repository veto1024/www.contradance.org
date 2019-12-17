<?php
namespace Drupal\ccd_cashbox\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Url;
use Drupal\paragraphs\Entity\Paragraph;
use CommerceGuys\Addressing\AddressFormat\AddressField;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ModalDonationForm class.
 */
class ModalDonationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ccd_cashbox_modal_donation_form';
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

    $form['#prefix'] = '<div id="donation_form">';
    $form['#suffix'] = '</div>';

    // The status messages that will contain any form errors.
    $form['status_messages'] = [
    '#type' => 'status_messages',
      '#weight' => -10,
    ];

    $form['donor_name'] = [
      '#type' => 'textfield',
      '#default_value' => 'None',
      '#title' => $this->t('Name of donor'),
      '#required' => TRUE,
      '#attributes' => [
        'name' => 'field_donor_name',
      ],
    ];
    $form['donation_purpose'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Purpose of donation'),
      '#required' => TRUE,
      '#attributes' => [
        'name' => 'field_donation_purpose',
      ],
    ];
    $form['donation_method'] = [
      '#type' => 'radios',
      '#title' => 'Donation method',
      '#options' => [
        'cash' => $this->t('Cash'),
        'check' => $this->t('Check'),
        'venmo' => $this->t('Venmo'),
      ],
      '#required' => TRUE,
      '#default_value' => 'cash',
      '#attributes' => [
        'name' => "field_donation_method",
      ],
    ];
    $form['donation_cash'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Amount donated in cash'),
      '#default_value' => 0.0,
      '#attributes' => [
        'name' => 'field_cash_donation',
      ],
      '#states' => [
        'visible' => [
          ':input[name="field_donation_method"]' => [
            'value' => 'cash',
          ],
        ],
        'required' => [
          ':input[name="field_donation_method"]' => [
            'value' => 'cash',
          ],
        ],
      ],
    ];
    $form['donation_check'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Amount donated by check'),
      '#default_value' => 0.0,
      '#attributes' => [
        'name' => 'field_cash_donation',
      ],
      '#states' => [
        'visible' => [
          ':input[name="field_donation_method"]' => [
            'value' => 'check',
          ],
        ],
        'required' => [
          ':input[name="field_donation_method"]' => [
            'value' => 'check',
          ],
        ],
      ],

    ];
    $form['donation_venmo'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Amount donated by venmo'),
      '#default_value' => 0.0,
      '#attributes' => [
        'name' => 'field_venmo_donation',
      ],
      '#states' => [
        'visible' => [
          ':input[name="field_donation_method"]' => [
            'value' => 'venmo',
          ],
        ],
        'required' => [
          ':input[name="field_donation_method"]' => [
            'value' => 'venmo',
          ],
        ],
      ],
    ];
    $form['donation_letter'] = [
      '#type' => 'radios',
      '#title' => $this->t('Does the donor want a donation letter for tax purposes?'),
      '#default_value' => 'noletter',
      '#attributes' => [
        'name' => 'field_donation_letter',
      ],
      '#options' => [
        'letter' => $this->t('Yes, they want a letter.'),
        'noletter' => $this->t('They do not request a letter'),
      ],
    ];
    $form['donor_address'] = [
      '#markup' => $this->t('Donor address for donation letter to be provided'),
      '#type' => 'address',
      '#default_value' => ['country_code' => 'US'],
      '#used_fields' => [
        AddressField::ADDRESS_LINE1,
        AddressField::ADMINISTRATIVE_AREA,
        AddressField::LOCALITY,
        AddressField::POSTAL_CODE,
      ],
      '#available_countries' => ['US'],
      '#states' => [
        'visible' => [
          ':input[name="field_donation_letter"]' => [
            'value' => 'letter',
          ],
        ],
        'required' => [
          ':input[name="field_donation_letter"]' => [
            'value' => 'letter',
          ],
        ],
      ],
    ];
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['send'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit donation'),
      '#attributes' => [
        'class' => [
          'use-ajax-submit',
          'btn',
          'btn-success',
        ],
      ],
      '#ajax' => [
        'callback' => '::submitModalFormAjax',
        'event' => 'click',
      ],
    ];


    return $form;
  }

  /**
   * AJAX callback handler that displays any errors or a success message.
   * @param array $form
   * @param FormStateInterface $form_state
   * @return AjaxResponse
   */
  public function submitModalFormAjax(array $form, FormStateInterface $form_state) {
    $form['#cache'] = ['max-age' => 0];
    $donor = $form_state->getValue('donor_name');
    $purpose = $form_state->getValue('donation_purpose');
    $response = new AjaxResponse();

    // If there are any form errors, re-display the form.
    if ($form_state->hasAnyErrors()) {
      //$response->addCommand(new OpenModalDialogCommand("Failed!", 'The donation was not submitted.', ['width' => 800]));
      $response->addCommand(new ReplaceCommand('#donation_form', $form));
    }
    else {
    $response->addCommand(new OpenModalDialogCommand("Success!", 'The donation has been submitted.', ['width' => 800]));
    }
    return $response;
}

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      // You can get nid and anything else you need from the node object.
      $nid = $node->id();
    }
    /* $nid=\Drupal::entityTypeManager()->getStorage('node')->load($nid); */
    $node=\Drupal\node\Entity\Node::load($nid);

    // Create single new paragraph
    $paragraph = Paragraph::create([
      'type' => 'donation',
      'field_donor_name' => $form['donor_name']['#value'],
      'field_donation_purpose' => $form['donation_purpose']['#value'],
      'field_donation_letter' => $form['donation_letter']['#value'],
      'field_cash_donation' => $form['donation_cash']['#value'],
      'field_check_donation' => $form['donation_check']['#value'],
      'field_venmo_donation' => $form['donation_venmo']['#value'],
      'field_donor_address' => $form['donor_address']['#value'],
    ]);
    $paragraph->isNew();
    $paragraph->save();

    $current = $node->get('donation')->getValue();
    $current[] = array(
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    );
    $node->set('donation', $current);
    $node->save();
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
  return ['config.ccd_cashbox_modal_donation_form'];
  }

}
