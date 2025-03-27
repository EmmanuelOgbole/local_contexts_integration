<?php

namespace Drupal\local_contexts_integration\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\local_contexts_integration\Controller\LocalContextsController;

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LocalContextsController $localContextsController) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->localContextsController = $localContextsController;
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
    );
  }


  public function build() {
    // Fetch user preference from session.
    $config = \Drupal::config('local_contexts_integration.settings');
    $display_option = $config->get('display_option') ?: 'show_name';

    // Fetch data from the Local Contexts controller.
    $data = $this->localContextsController->fetchProjectData();

    // Ensure the data structure is valid and defaults are set.
    $unique_id = $data['unique_id'] ?? 'N/A';
    $tk_labels = $data['tk_labels'] ?? [];


    // Check if there are tk_labels and a valid project_id
    if (empty($tk_labels) || $unique_id === 'N/A') {
      return [];
    }

    // Render the block with structured data.
    return [
      '#theme' => 'local_contexts_block',
      '#unique_id' => $unique_id,
      '#tk_labels' => $tk_labels,
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
