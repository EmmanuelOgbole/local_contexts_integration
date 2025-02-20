<?php

namespace Drupal\Tests\local_contexts_integration\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the Local Contexts Controller functionality.
 *
 * @group local_contexts_integration
 */
class LocalContextsControllerFunctionalTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['node', 'block', 'local_contexts_integration'];

  /**
   * The default theme to use for the test.
   *
   * @var string
   */
  protected $defaultTheme = 'stark';

  /**
   * A basic test to verify the test framework is working.
   */
 public function testBasicFunctionality() {
    // Debugging file paths
    $temp_dir = sys_get_temp_dir();
    echo "Using temporary directory: $temp_dir\n";

    $private_dir = \Drupal::config('system.file')->get('path.private');
    echo "Private directory: " . ($private_dir ?? 'Not configured') . "\n";

    $public_dir = \Drupal::config('system.file')->get('path.public');
    echo "Public directory: $public_dir\n";

    $drupal_temp_dir = \Drupal::config('system.file')->get('path.temporary');
    echo "Drupal temp directory: " . ($drupal_temp_dir ?? 'Not configured') . "\n";

    // Visit a node page where the Local Contexts Block should appear
    $this->drupalGet('node/1');  // Adjust the path if needed

    // Assertions to verify block functionality
    $this->assertSession()->statusCodeEquals(200); // Check if page loads successfully
    $this->assertSession()->pageTextContains('Local Contexts Block'); // Check if block title exists
    $this->assertSession()->elementExists('css', '.tk-label'); // Check if TK Labels are rendered

    // Assert that the environment is still correct
    $this->assertTrue(TRUE, 'The test environment is set up correctly.');
 }
}
