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

    // Add a container for the toggle button
    $form['toggle_button_container'] = [
      '#markup' => '<div id="toggle-button-container">
                      <div id="toggle-form-button">
                          <i class="fas fa-cog"></i>
                      </div>
                    </div>',
    ];

    // Wrap the form elements in a container that can be toggled
    $form['form_container'] = [
        '#type' => 'container',
        '#attributes' => ['id' => 'form-container'],
    ];

    $form['form_container']['display_option'] = [
        '#type' => 'radios',
        '#title' => $this->t('Display Option'),
        '#options' => [
            'both' => $this->t('Show both name and text'),
            'name_only' => $this->t('Show name only'),
        ],
        '#default_value' => $default_value,
    ];

    $form['form_container']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Apply'),
    ];

    // Attach the library
    $form['#attached']['library'][] = 'local_contexts_integration/toggle_form';

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
