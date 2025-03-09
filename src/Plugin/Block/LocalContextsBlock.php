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
    $display_option = $session->get('tk_label_display_option', 'both'); // Default to 'both'
 
    // Fetch data from the Local Contexts controller.
    $data = $this->localContextsController->fetchProjectData();

    $form = \Drupal::formBuilder()->getForm('Drupal\local_contexts_integration\Form\TkLabelDisplayForm');
    \Drupal::logger('local_contexts_integration')->notice('Form: ' . print_r(\Drupal::formBuilder()->getForm('Drupal\local_contexts_integration\Form\TkLabelDisplayForm'), TRUE));

    
    // Ensure the data structure is valid and defaults are set.
    $unique_id = $data['unique_id'] ?? 'N/A';
    $title = $data['title'] ?? 'Untitled Project';
    $tk_labels = $data['tk_labels'] ?? [];

    if ($display_option === 'name_only' && !empty($tk_labels)) {
      foreach ($tk_labels as &$label) {
        // changed label_text to defailt_text
          if (isset($label['default_text'])) {
              unset($label['default_text']); // Ensure correct key is removed
          }
      }
      unset($label); // Important: Unset the reference to prevent side effects
    }

    // Render the block with structured data.
    return [
      '#theme' => 'local_contexts_block',
      '#unique_id' => $unique_id,
      '#title' => $title,
      '#tk_labels' => $tk_labels,
      '#form' => $form,
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
