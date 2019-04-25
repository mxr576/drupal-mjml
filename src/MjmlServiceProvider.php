<?php

namespace Drupal\mjml;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Drupal\mjml\Renderer\ApiRenderer;
use Mjml\Client;
use NotFloran\MjmlBundle\Renderer\BinaryRenderer;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Dynamic service provider.
 */
final class MjmlServiceProvider implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container): void {
    $mjml_settings = $container->get('settings')->get('mjml', []);
    try {
      $mjml_configuration = $container->getParameter('mjml');
    }
    catch (InvalidArgumentException $e) {
      $mjml_configuration = [];
    }
    $this->registerBinaryProvider($container, $mjml_settings, $mjml_configuration);
    $this->registerApiRenderer($container, $mjml_settings, $mjml_configuration);
  }

  /**
   * Registers the binary renderer service.
   *
   * @param \Drupal\Core\DependencyInjection\ContainerBuilder $container
   *   The ContainerBuilder to register services to.
   * @param array $mjml_settings
   *   The MJML settings from the settings.php which can override configuration
   *   coming from the "mjml" container parameter.
   * @param array $mjml_configuration
   *   The MJML module settings from the "mjml" container parameter.
   */
  private function registerBinaryProvider(ContainerBuilder $container, array $mjml_settings, array $mjml_configuration): void {

    $binary_path = $mjml_settings['renderer']['binary']['options']['path'] ?? $mjml_configuration['renderer']['binary']['options']['path'] ?? '';
    $minify = $mjml_settings['renderer']['binary']['options']['minify'] ?? $mjml_configuration['renderer']['binary']['options']['minify'] ?? FALSE;

    $service = new Definition(BinaryRenderer::class, [$binary_path, $minify]);
    $container->setDefinition('mjml.renderer.binary', $service);
  }

  /**
   * Registers the API renderer service if juanmiguelbesada/mjml-php installed.
   *
   * @param \Drupal\Core\DependencyInjection\ContainerBuilder $container
   *   The ContainerBuilder to register services to.
   * @param array $mjml_settings
   *   The MJML settings from the settings.php which can override configuration
   *   coming from the "mjml" container parameter.
   * @param array $mjml_configuration
   *   The MJML module settings from the "mjml" container parameter.
   */
  private function registerApiRenderer(ContainerBuilder $container, array $mjml_settings, array $mjml_configuration): void {
    if (class_exists('Mjml\Client')) {
      $api_client_application_id = $mjml_settings['renderer']['api']['client']['application-id'] ?? $mjml_configuration['renderer']['api']['client']['application-id'] ?? '';
      $api_client_secret_key = $mjml_settings['renderer']['api']['client']['secret-key'] ?? $mjml_configuration['renderer']['api']['client']['secret-key'] ?? '';

      $api_client_service = new Definition(Client::class, [$api_client_application_id, $api_client_secret_key]);
      $container->setDefinition('mjml.api_client', $api_client_service);

      $api_renderer = new Definition(ApiRenderer::class, [new Reference('mjml.api_client')]);
      $container->setDefinition('mjml.renderer.api', $api_renderer);
    }
  }

}
