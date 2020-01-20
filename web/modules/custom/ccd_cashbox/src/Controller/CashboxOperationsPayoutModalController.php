<?php

namespace Drupal\ccd_cashbox\Controller;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;

/** * CashboxOperationsPayoutModalController class. */
class CashboxOperationsPayoutModalController extends ControllerBase {

  /**   * The form builder.   *
   * @var FormBuilder
   *
   */
  protected $formBuilder;

  /**
   * The CashboxOperationsPayoutModalController constructor.   *
   *
   * @param FormBuilder $formBuilder
   *   The form builder.
   */
  public function __construct(FormBuilder $formBuilder) {
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   *
   * @param
   *
   * \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder')
    );
  }

  /**
   * Callback for opening the modal form.
   */
  public function openModalForm() {
    $response = new AjaxResponse();
    // Get the modal form using the form builder. Attach the nid as a hidden parameter
    $nid = \Drupal::routeMatch()->getParameter('node')->id();
    $modal_form = $this->formBuilder->getForm('Drupal\ccd_cashbox\Form\CashboxOperationsPayoutModalForm');
    $modal_form['nid']['#value'] = $nid;
    // Add an AJAX command to open a modal dialog with the form as the content.
    $response->addCommand(new OpenModalDialogCommand('CCD Cashbox Operations - Payout', $modal_form, ['width' => '800']));
    return $response;
  }

}
