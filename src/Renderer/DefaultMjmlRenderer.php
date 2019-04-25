<?php

namespace Drupal\mjml\Renderer;

use Drupal\Core\Site\Settings;
use NotFloran\MjmlBundle\Renderer\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * Returns the default MJML renderer service.
 *
 * By default this is the Binary renderer.
 */
final class DefaultMjmlRenderer implements ContainerAwareInterface, RendererInterface {

  use ContainerAwareTrait;

  /**
   * Settings.
   *
   * @var \Drupal\Core\Site\Settings
   */
  private $settings;

  /**
   * DefaultMjmlRenderer constructor.
   *
   * @param \Drupal\Core\Site\Settings $settings
   *   Settings.
   */
  public function __construct(Settings $settings) {
    $this->settings = $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function render(string $mjml_content): string {
    try {
      $mjml_configuration = $this->container->getParameter('mjml');
    }
    catch (InvalidArgumentException $e) {
      $mjml_configuration = [];
    }

    $mjml_settings = $this->settings->get('mjml');
    // First, look for the default renderer specific setting.
    if (isset($mjml_settings['default_renderer'])) {
      $service_name = $mjml_settings['default_renderer'];
    }
    // Second, use the parameter from the container.
    elseif (isset($mjml_configuration['default_renderer'])) {
      $service_name = $mjml_configuration['default_renderer'];
    }
    else {
      // Fall back to the binary render if nothing else is configured.
      $service_name = 'mjml.renderer.binary';
    }
    return $this->container->get($service_name)->render($mjml_content);
  }

}
