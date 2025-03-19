<?php

namespace Drupal\local_contexts_integration\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\local_contexts_integration\Controller\LocalContextsController;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a Local Contexts Data Block.
 *
 * @Block(
 *   id = "local_contexts_block",
 *   admin_label = @Translation("Local Contexts Block"),
 *   category = @Translation("Custom"),
 * )
 */
class LocalContextsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The Local Contexts controller.
   *
   * @var \Drupal\local_contexts_integration\Controller\LocalContextsController
   */
  protected $localContextsController;
  protected $requestStack;

  /**
   * Constructs a new LocalContextsBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the block.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\local_contexts_integration\Controller\LocalContextsController $localContextsController
   *   The Local Contexts controller.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LocalContextsController $localContextsController, RequestStack $request_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->localContextsController = $localContextsController;
    $this->requestStack = $request_stack;  // Assign the RequestStack
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('local_contexts_integration.controller'),
      $container->get('request_stack')
    );
  }

/**
 * {@inheritdoc}
 */
  public function build() {

    // Fetch user preference from session.
    $session = $this->requestStack->getCurrentRequest()->getSession();
    $display_option = $session->get('tk_label_display_option', 'show_name'); // Default to 'show_name'
     
 
    // Fetch data from the Local Contexts controller.
    $data = $this->localContextsController->fetchProjectData();

    $form = \Drupal::formBuilder()->getForm('Drupal\local_contexts_integration\Form\TkLabelDisplayForm');
    
    // Ensure the data structure is valid and defaults are set.
    $unique_id = $data['unique_id'] ?? 'N/A';
    $tk_labels = $data['tk_labels'] ?? [];


    // Render the block with structured data.
    return [
      '#theme' => 'local_contexts_block',
      '#unique_id' => $unique_id,
      '#tk_labels' => $tk_labels,
      '#form' => $form,
      '#display_option' => $display_option,
      '#attached' => [
        'library' => [
          'local_contexts_integration/tk_labels',
        ],
      ],
      '#cache' => [
        'max-age' => 0,
      ],

    ];
  }
  

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }
}
