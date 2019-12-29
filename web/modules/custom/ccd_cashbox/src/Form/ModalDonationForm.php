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
use Drupal\paragraphs\Entity\Paragraph;
use CommerceGuys\Addressing\AddressFormat\AddressField;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Validation;

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
    //$form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    //$form['#attached']['library'][] = 'core/drupal.ajax';

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
      '#type' => 'number',
      '#name' => 'donation_cash',
      '#title' => $this->t('Amount donated in cash: $'),
      '#default_value' => 0.0,
      '#step' => .01,
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
      '#type' => 'number',
      '#name' => 'donation_check',
      '#title' => $this->t('Amount donated by check: $'),
      '#default_value' => 0.0,
      '#step' => .01,
      '#attributes' => [
        'name' => 'field_check_donation',
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
      '#type' => 'number',
      '#name' => 'donation_venmo',
      '#title' => $this->t('Amount donated by venmo: $'),
      '#default_value' => 0.0,
      '#step' => .01,
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
      '#name' => 'address',
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
    $form['actions']['submit'] = [
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
        'callback' => '::submitAjaxForm',
        'wrapper' => 'donation_form',
      ],
    ];

    $form['nid'] = array(
      '#type' => 'hidden',
      '#title' => $this->t('The nid of the submission'),
    );

    return $form;
  }

  public function rebuildForm(array $form, FormStateInterface $form_state) {
    return $form;
  }
  /**
   * AJAX callback handler that displays any errors or a success message.
   * @param array $form
   * @param FormStateInterface $form_state
   * @return AjaxResponse
   */
  public function submitAjaxForm(array $form, FormStateInterface $form_state) {
    $form['#cache'] = ['max-age' => 0];
    $input =  $form_state->getUserInput();
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
    $form_state->clearErrors();
    $input = $form_state->getUserInput();
    if ($input['field_donation_letter']=='letter') {
      $validator = Validation::createValidator();
      $address = new Address();
      $address = $address
        ->withCountryCode($input['donor_address']['country_code'])
        ->withLocality($input['donor_address']['locality'])
        ->withPostalCode('33a')
        ->withAddressLine1($input['donor_address']['address_line1'])
        ->withAdministrativeArea($input['donor_address']['administrative_area'])
        ->withGivenName('DonorFirst')
        ->withFamilyName('DonorLast');
      $violations = $validator->validate($address, new AddressFormatConstraint());
      if ($violations->count()) {
        $form_state->setErrorByName('donor_address', t('Address not valid. Please recheck.'));
      }
    }
    if (!$input['field_donor_name']) {
      $form_state->setErrorByName('donor_name', t('Please provide a name!'));
    }
    if (!$input['field_donation_purpose']) {
      $form_state->setErrorByName('donation_purpose', t('Please provide a donation purpose (e.g., General Funds, Sponsor a Dance)'));
    }
    if ($input['field_donation_method']=='cash') {
      if (!$input['field_cash_donation']) {
        $form_state->setErrorByName('donation_cash', t('Please provide a cash donation amount'));
      } elseif (!is_numeric($input['field_cash_donation'])) {
        $form_state->setErrorByName('donation_cash', t('Invalid amount entered'));
      }
    }
    if ($input['field_donation_method']=='check') {
      if (!$input['field_check_donation']) {
        $form_state->setErrorByName('donation_check', t('Please provide a check donation amount'));
      } elseif (!is_numeric($input['field_check_donation'])) {
        $form_state->setErrorByName('donation_check', t('Invalid amount entered'));
      }
    }
    if ($input['field_donation_method']=='venmo') {
      if (!$input['field_venmo_donation']) {
        $form_state->setErrorByName('donation_venmo', t('Please provide a Venmo donation amount'));
      } elseif (!is_numeric($input['field_check_donation'])) {
        $form_state->setErrorByName('donation_venmo', t('Invalid amount entered'));
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $input = $form_state->getUserInput();
    $nid=$form_state->getvalue('nid');
    $response->addCommand(new OpenModalDialogCommand("Success!", 'The donation has been submitted! Click anywhere to exit.'));

    /* $nid=\Drupal::entityTypeManager()->getStorage('node')->load($nid); */
    $node=\Drupal\node\Entity\Node::load($nid);

    // Create single new paragraph
    $paragraph = Paragraph::create([
      'type' => 'donation',
      'field_donor_name' => $input['field_donor_name'],
      'field_donation_purpose' => $input['field_donation_purpose'],
      'field_donation_letter' => $input['field_donation_letter'],
      'field_cash_donation' => $input['field_cash_donation'],
      'field_check_donation' => $input['field_check_donation'],
      'field_venmo_donation' => $input['field_venmo_donation'],
      'field_donor_address' => $input['field_donor_address'],
    ]);
    $paragraph->isNew();
    $paragraph->save();

    $current = $node->get('field_donation_info')->getValue();
    $current[] = array(
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    );
    $node->set('field_donation_info', $current);
    $node->save();

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
  return ['config.ccd_cashbox_modal_donation_form'];
  }

}
