<?php

namespace Drupal\ccd_booking\Form;


use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Class NodeCreate.
 */
class NodeCreate extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'node_create';
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $i = 0;

    $node_field = $form_state->get('num_nodes');
    $form['#tree'] = TRUE;
    $form['event_type'] = [
      '#type' => 'entity_autocomplete',
      '#placeholder' => $this->t('Event Type'),
      '#target_type' => 'taxonomy_term',
      '#wrapper_attributes' => [
        'class' => [
          'container',

        ],
      ],
      '#selection_settings' => [
        'target_bundles' => [
          'event_type',
        ],
      ],
      '#required' => TRUE,
      '#attributes' => [
        'class' => [
          'col-lg-12',
        ],
      ],
      '#prefix' => '<div id="event-type-node-create-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];

    $form['node_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('New Events To Create'),
      '#prefix' => '<div id="node-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];
    if (empty($node_field)) {
      $form_state->set('num_nodes', 1);
      $node_field = 1;
    }

    for ($i = 0; $i < $node_field; $i++) {
      $j = 0;

      $form['node_fieldset'][$i] = [
        '#type' => 'fieldset',
        '#attributes' => [
          'class' => [
            'container-fluid',
            'well',
          ],
          'id' => 'create-node-fieldset-' . $i,
        ],
      ];

      $form['node_fieldset'][$i]['event_date'] = [
        '#type' => 'datetime',
        '#title' => t('Start Date'),
        '#prefix' => '<div class="col-lg-3">',
        '#suffix' => '</div>',
        '#wrapper_attributes' => [
          'class' => [
            'col-lg-2',
          ],
        ],
        '#format' => 'm/d/Y H:m:s',
        '#description' => $this->t('Date of event'),
        '#required' => TRUE,
      ];

      $form['node_fieldset'][$i]['event_date_end'] = [
        '#type' => 'datetime',
        '#title' => t('End Date'),
        '#prefix' => '<div class="col-lg-3">',
        '#suffix' => '</div>',
        '#wrapper_attributes' => [
          'class' => [
            'col-lg-2',
          ],
          'id' => 'create-node-end-date-' . $i,
        ],
        '#format' => 'm/d/Y H:m:s',
        '#description' => $this->t('End of Event'),
        '#required' => TRUE,

      ];

      $form['node_fieldset'][$i]['band'] = [
        '#type' => 'entity_autocomplete',
        '#placeholder' => $this->t('Band'),
        '#target_type' => 'node',
        '#wrapper_attributes' => [
          'class' => [
            'col-lg-3',
          ],
        ],
        '#selection_settings' => [
          'target_bundles' => [
            'band',
          ],
        ],
        '#required' => TRUE,
        '#ajax' => [
          'callback' => '::getBandBody',
          'wrapper' => 'create-node-description-' . $i,
          'event' => 'change',
        ],
      ];

      $form['node_fieldset'][$i]['caller'] = [
        '#type' => 'entity_autocomplete',
        '#placeholder' => $this->t('Caller'),
        '#target_type' => 'node',
        '#wrapper_attributes' => [
          'class' => [
            'col-lg-3',
          ],
        ],
        '#selection_settings' => [
          'target_bundles' => [
            'person',
          ],
        ],
        '#required' => TRUE,
      ];

      $form['node_fieldset'][$i]['sound'] = [
        '#type' => 'entity_autocomplete',
        '#placeholder' => $this->t('Sound'),
        '#target_type' => 'node',
        '#wrapper_attributes' => [
          'class' => [
            'col-lg-3',
          ],
        ],
        '#selection_settings' => [
          'target_bundles' => [
            'person',
          ],
        ],
        '#required' => TRUE,
      ];

      $form['node_fieldset'][$i]['location'] = [
        '#type' => 'textfield',
        '#placeholder' => $this->t('Location'),
        '#wrapper_attributes' => [
          'class' => [
            'col-lg-3',
          ],
        ],
        '#required' => TRUE,
      ];

      $form['node_fieldset'][$i]['title'] = [
        '#type' => 'textfield',
        '#placeholder' => $this->t('Event Title'),
        '#wrapper_attributes' => [
          'class' => [
            'col-lg-2',
          ],
        ],
        '#required' => TRUE,
      ];

      $form['node_fieldset'][$i]['description'] = [
        '#type' => 'text_format',
        '#placeholder' => $this->t('Event Description'),
        '#format' => 'restricted_html',
        '#allowed_formats' => [
          'restricted_html',
        ],
        '#wrapper_attributes' => [
          'class' => [
            'col-lg-4',
          ],
          'id' => 'create-node-description-' . $i,
        ],
        '#required' => TRUE,
        '#resizable' => 'none',
        '#rows' => 3,
      ];

      $form['node_fieldset'][$i]['band_travel'] = [
        '#type' => 'number',
        '#title' => $this->t('Band Travel'),
        '#wrapper_attributes' => [
          'class' => [
            'col-lg-2',
          ],
        ],
        '#required' => TRUE,
        '#step_size' => 1,
        '#default_value' => 0.,
      ];
      $form['node_fieldset'][$i]['caller_travel'] = [
        '#type' => 'number',
        '#title' => $this->t('Caller Travel'),
        '#wrapper_attributes' => [
          'class' => [
            'col-lg-2',
          ],
        ],
        '#required' => TRUE,
        '#step_size' => 1,
        '#default_value' => 0.,
      ];

      $form['node_fieldset'][$i]['actions'] = [
        '#type'  => 'actions',
      ];

      $form['node_fieldset'][$i]['actions']['modal'] = [
        '#type'  => 'button',
        '#value' => 'Enter Payments',
        '#title' => $this->t("Modal me"),
        '#attributes' => [
          'class' => [
            'btn',
            'btn-large',
            'btn-info',
            'use-ajax',
          ],
          'data-toggle' => [
            'modal',
          ],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode([
            'width' => 800,
          ]),
          'data-target' => [
            '#myModal-' . $i,
          ],
        ],
        '#limit_validation_errors' => array(),
        '#ajax' => [
          'callback' => '::populateModal',
          'wrapper' => 'myModal-' . $i,
        ],
      ];

      // Begin Modal Dialog stuff

      $form['node_fieldset'][$i]['modal'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'modal',
            'z-900',
            'top-100',
          ],
          'id' => [
            'myModal-' . $i,
          ],
          'role' => [
            'dialog',
          ],
        ],
      ];

      $form['node_fieldset'][$i]['modal']['modal_dialog'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'modal-dialog',
            'modal-width-800',
          ],
        ],
        '#wrapper_attributes' => [
          'style' => ['z-index:900; top: 150px'],
        ],
      ];

      $form['node_fieldset'][$i]['modal']['modal_dialog']['modal_content'] = [
        '#type' => 'fieldset',
        '#tree' => TRUE,
        '#attributes' => [
          'class' => [
            'modal-content',
          ],
        ],
      ];
      if (!isset($form_state->getUserInput()['node_fieldset'][$i]['modal']['modal_dialog']['modal_content']['num_people'])) {
        $num_people = 1;
      }
      else {
        $num_people = $form_state->getUserInput()['node_fieldset'][$i]['modal']['modal_dialog']['modal_content']['num_people'];
      }
      $form['node_fieldset'][$i]['modal']['modal_dialog']['modal_content']['num_people'] = [
        '#type' => 'select',
        '#title' => $this->t('How many people are being paid tonight? Please select the number of people before entering data.'),
        '#options' => [
          1 => '1',
          2 => '2',
          3 => '3',
          4 => '4',
          5 => '5',
          6 => '6',
          7 => '7',
          8 => '8',
          9 => '9',
          10 => '10',
        ],
        '#limit_validation_errors' => array(),
        '#ajax' => [
          'callback' => '::addFormCallback',
          'event' => 'change',
          'wrapper' => 'person-row-wrapper-'  . $i,
        ],
      ];
      $form['node_fieldset'][$i]['modal']['modal_dialog']['modal_content']['people_rows'] = [
        '#type' => 'details',
        '#prefix' => '<div id="person-row-wrapper-' . $i . '">',
        '#suffix' => '</div>',
        '#attributes' => [
          'class' => [
            'container-inline',
          ],
        ],
        '#open' => TRUE,
      ];

      for ($j = 0; $j < $num_people; $j++) {

        $form['node_fieldset'][$i]['modal']['modal_dialog']['modal_content']['people_rows'][$j] = [
          '#type' => 'fieldset',
        ];

        $form['node_fieldset'][$i]['modal']['modal_dialog']['modal_content']['people_rows'][$j]['name'] = [
          '#type' => 'entity_autocomplete',
          '#title' => $this->t('Person paid'),
          '#target_type' => 'node',
          '#selection_settings' => [
            'target_bundles' => [
              'person',
            ],
          ],
          '#tags' => TRUE,
          '#required' => TRUE,
//          '#attributes' => [
//            'name' => 'field_person_fieldset_name_' . $j,
//          ],
          '#wrapper_attributes' => [
            'class' => [
              'col-lg-7',
            ],
          ],
        ];

        $form['node_fieldset'][$i]['modal']['modal_dialog']['modal_content']['people_rows'][$j]['amount_paid'] = [
          '#type' => 'number',
          '#step' => .01,
          '#default_value' => 0,
          '#title' => $this->t('Amount paid $'),
//          '#attributes' => [
//            'name' => 'field_person_fieldset_amount_paid_' . $j,
//          ],
          '#wrapper_attributes' => [
            'class' => [
              'col-lg-3',
            ],
          ],
        ];
      }

      $form['node_fieldset'][$i]['modal']['modal_dialog']['modal_content']['actions'] = [
        '#type' => 'actions',
      ];

      $form['node_fieldset'][$i]['modal']['modal_dialog']['modal_content']['actions']['submit'] = [
        '#type' => 'button',
        '#value' => $this->t('Continue'),
        '#attributes' => [
          'class' => [
            'btn',
            'btn-info',
            'use-ajax',
            'ajax-submit',
          ],
          'id' => 'payment-modal-submit-' . $i,
        ],
//        '#submit' => [
//          '::modalSubmit',
//          ],
        '#limit_validation_errors' => array(),
        '#ajax' => [
          'callback' => '::hideModal',
          'wrapper' => 'myModal-' . $i,
          'progress' => [
            'type' => 'throbber',
          ],
        ],
      ];

    }
    $form['node_fieldset']['actions'] = [
      '#type' => 'actions',
    ];
    $form['node_fieldset']['actions']['add_node'] = [
      '#type' => 'submit',
      '#value' => t('Add Row'),
      '#attributes' => [
        'class' => [
          'btn-success',
          'btn',
          'use-ajax',
          'ajax-submit',
        ],
      ],
      '#submit' => [
        '::addOne',
      ],
      '#limit_validation_errors' => array(),
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => 'node-fieldset-wrapper',
      ],
    ];
    if ($node_field > 1) {
      $form['node_fieldset']['actions']['remove_node'] = [
        '#type' => 'submit',
        '#value' => t('Remove Event'),
        '#attributes' => [
          'class' => [
            'btn-danger',
            'btn',
            'use-ajax',
            'ajax-submit',
          ],
        ],
        '#submit' => [
          '::removeOne',
        ],
        '#limit_validation_errors' => array(),
        '#ajax' => [
          'callback' => '::removeCallback',
          'wrapper' => 'node-fieldset-wrapper',
        ],
      ];
    }
    $form_state->setCached(FALSE);
    $form['node_fieldset']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * Generates the initial number of submissions
   */
  public function addFormCallback(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $i = $trigger['#parents'][1];
    $form_state->setRebuild(TRUE);
    $element = $form['node_fieldset'][$i]['modal']['modal_dialog']['modal_content']['people_rows'];
    return $element;
  }

//  public function populateModal(array &$form, FormStateInterface $form_state) {
//    $trigger = $form_state->getTriggeringElement();
//    $i = $trigger['#parents'][1];
//    $form['node_fieldset'][$i]['modal']['#attributes']['style'] = ['display:initial; z-index:900'];
//    $input = $form_state->getUserInput();
//    for($j = 0; $j < count($input['node_fieldset'][$i]['modal']['modal_dialog']['modal_content']['people_rows']); $j++) {
//      $form['node_fieldset'][$i]['modal']['modal_dialog']['modal_content']['people_rows'][$j]['amount_paid']['#value'] = 5.0;
//    }
//    return $form['node_fieldset'][$i]['modal'];
//  }

  public function populateModal(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $input = $form_state->getValues();
    $i = $trigger['#parents'][1];
    $id = $form['node_fieldset'][$i]['modal']['#attributes']['id'][0];
    $response = new AjaxResponse();
    $response->addCommand(new CssCommand('#' . $id, ['display' => 'initial']));
    $form_state->setRebuild(TRUE);
    return $response;
  }
  /**
   * {@inheritdoc}
   */

  public function modalSubmit(array &$form, FormStateInterface $form_state) {

  }

  public function hideModal(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $input = $form_state->getValues();
    $i = $trigger['#parents'][1];
    $id = $form['node_fieldset'][$i]['modal']['#attributes']['id'][0];
    $response = new AjaxResponse();
    $response->addCommand(new CssCommand('#' . $id, ['display' => 'none']));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function getBandBody(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $formId = $trigger['#ajax']['wrapper'];
    $j = substr($formId, -1);
    $input = $form_state->getValues();
    $band = $input['node_fieldset'][$j]['band'];
    $descriptionElement = $form['node_fieldset'][$j]['description'];
    if (!is_null($band)) {
      $node = Node::load($band);
      $bandDescription = $node->get('body')->value;
      $descriptionElement = $form['node_fieldset'][$j]['description'];
      $descriptionElement['value']['#value'] = $bandDescription;
    }
    $form_state->setRebuild(FALSE);
    return $descriptionElement;
  }

  public function dateTransfer(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $formId = $trigger['#ajax']['wrapper'];
    $j = substr($formId, -1);
    $input = $form_state->getValues();
    $date = $input['node_fieldset'][$j]['event_date'];
    $dateElement = $form['node_fieldset'][$j]['event_date_end'];
    $dateElement['#value'] = $date;
    return $dateElement;
  }

  /**
   * {@inheritdoc}
   */

  public function addOne(array &$form, FormStateInterface $form_state) {
    $node_field = $form_state->get('num_nodes');
    $add_button = $node_field + 1;
    $form_state->set('num_nodes', $add_button);
    $form_state->setRebuild(TRUE);
  }

  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    $ajax_response = new AjaxResponse();
    $form['event_type'] = null;
    $ajax_response->addCommand(new ReplaceCommand('node-fieldset-wrapper', $form));
    return $form;
  }

  public function removeOne(array &$form, FormStateInterface $form_state) {
    $node_field = $form_state->get('num_nodes');
    $add_button = $node_field - 1;
    $form_state->set('num_nodes', $add_button);
    $form_state->setRebuild(TRUE);
  }

  public function removeCallback(array &$form, FormStateInterface $form_state) {
    $form['event_type'] = null;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $input = $form_state->getValues();
    $fieldsets = $input['node_fieldset'];
    $ignore = ['actions'];
    $cleanFieldsets = array_diff_key($fieldsets, array_flip($ignore));
    foreach ($cleanFieldsets as $fieldset) {
      $url = Url::fromUri('internal:/directions', ['attributes' => ['target' => '_blank']]);
      $tz = date_default_timezone_get();
      $start = DrupalDateTime::createFromFormat('Y-m-d\TH:i:s', $fieldset['event_date']->format('Y-m-d\TH:i:s'), $tz);
      $end = DrupalDateTime::createFromFormat('Y-m-d\TH:i:s', $fieldset['event_date_end']->format('Y-m-d\TH:i:s'), $tz);
      $interval = \DateInterval::createFromDateString($start->getOffset() . ' seconds');
      $start->sub($interval);
      $end->sub($interval);
      $test = \Drupal\node\Entity\Node::load(420);
      $link = Link::fromTextAndUrl($fieldset['location'], $url);
      $node = Node::create([
        'type' => [
          'target_id' => "event",
        ],
        'title' => $fieldset['title'],
        'field_band' => [
          'target_id' => $fieldset['band'],
        ],
        'field_band_pay' => 300.,
        'field_band_travel' => $fieldset['band_travel'],
        'field_caller' => [
          'target_id' => $fieldset['caller'],
        ],
        'field_caller_pay' => 100.,
        'field_caller_travel' => $fieldset['caller_travel'],
        'field_sound' => [
          'target_id' => $fieldset['sound'],
        ],
        'field_add_to_calendar' => "1",
        'field_cash_added_from_atm' => 0.,
        'field_cash_donations' => 0.,
        'field_cash_to_be_deposited' => 0.,
        'field_check_donations' => 0.,
        'field_checks_for_dance_admission' => 0.,
        'field_cost' => "$10 for adults, $5 for students",
        'field_event_date' => [
          'value' => $start->format('Y-m-d\TH:i:s'),
          'end_value' => $end->format('Y-m-d\TH:i:s'),
        ],
        'field_event_type' => [
          'target_id' => $input['event_type'],
        ],
        'field_rent_cost' => 150.,
        'body' => [
          'value' => $fieldset['description']['value'],
          'format' => $fieldset['description']['format'],
        ],
        'field_location' => [
          'uri' => $url->toUriString(),
          'title' => $fieldset['location'],
          'options' => [
            'attributes' => [
              'target' => '_blank',
            ],
          ],
        ],
      ]);
      $num = intval($fieldset['modal']['modal_dialog']['modal_content']['num_people']);
      for ($i = 0; $i < $num; $i++) {
        // Create a new paragraph
        $id = $fieldset['modal']['modal_dialog']['modal_content']['people_rows'][$i]['name'][0]['target_id'];
        $amount = intval($fieldset['modal']['modal_dialog']['modal_content']['people_rows'][0]['amount_paid']);
        $paragraph = Paragraph::create([
          'type' => 'people_and_payments',
          'field_amount_paid' => $amount,
          'field_person_paid' => $id,
        ]);
        $paragraph->isNew();
        $paragraph->save();

        $current = $node->get('field_person_paid')->getValue();
        $current[] = array(
          'target_id' => $paragraph->id(),
          'target_revision_id' => $paragraph->getRevisionId(),
        );
        $node->set('field_person_paid', $current);
        $node->save();
      }
      $node->isNew();
      $node->save();
    }
    $this->messenger()->addStatus($this->t('The following events have been added:'));
    foreach ($cleanFieldsets as $fieldset) {
      $this->messenger()->addStatus($this->t($fieldset['title']));
    }
  }

}
