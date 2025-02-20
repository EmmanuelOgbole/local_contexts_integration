<?php

namespace Drupal\local_contexts_integration\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a configuration form for the Local Contexts Integration module.
 */
class LocalContextsSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['local_contexts_integration.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'local_contexts_integration_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('local_contexts_integration.settings');

    $form['field_identifier'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Field Identifier'),
      '#default_value' => $config->get('field_identifier'),
      '#description' => $this->t('Enter the Field Identifier to use for API calls.'),
      '#required' => TRUE,
    ];


    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#default_value' => $config->get('api_key'),
      '#description' => $this->t('Enter API key for accessing Local Contexts.'),
      '#required' => TRUE,
    ];

    $form['api_url'] = [
      '#type' => 'select',
      '#title' => $this->t('API Base URL'),
      '#options' => [
        'https://localcontextshub.org/api/v2' => $this->t('https://localcontextshub.org/api/v2 - Live Instance of the Local Contexts Hub'),
        'https://sandbox.localcontextshub.org/api/v2' => $this->t('https://sandbox.localcontextshub.org/api/v2 - Sandbox/Testing site for the Local Contexts Hub API'),
      ],
      '#default_value' => $config->get('api_url') ?: 'https://localcontextshub.org/api/v2',
      '#description' => $this->t('Select the base URL for the API.'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('local_contexts_integration.settings')
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('field_identifier', $form_state->getValue('field_identifier'))
      ->set('api_url', $form_state->getValue('api_url'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}