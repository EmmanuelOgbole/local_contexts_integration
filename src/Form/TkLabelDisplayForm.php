<?php

namespace Drupal\local_contexts_integration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for selecting TK label display preferences.
 */
class TkLabelDisplayForm extends FormBase {

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new TkLabelDisplayForm instance.
   */
  public function __construct(RequestStack $request_stack) {
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('request_stack'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tk_label_display_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $session = $this->requestStack->getCurrentRequest()->getSession();
    $default_value = $session->get('tk_label_display_option', 'both');

    $form['display_option'] = [
      '#type' => 'radios',
      '#title' => $this->t('TK Label Display Option'),
      '#options' => [
        'both' => $this->t('Show both name and text'),
        'name_only' => $this->t('Show name only'),
      ],
      '#default_value' => $default_value,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Apply'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $session = $this->requestStack->getCurrentRequest()->getSession();
    $session->set('tk_label_display_option', $form_state->getValue('display_option'));

    \Drupal::messenger()->addStatus($this->t('Display preference updated.'));
  }
}
