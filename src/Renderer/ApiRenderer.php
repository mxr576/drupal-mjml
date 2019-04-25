<?php

namespace Drupal\mjml\Renderer;

use Mjml\Client;
use NotFloran\MjmlBundle\Renderer\RendererInterface;

/**
 * Renders MJML with the mjml.io API.
 *
 * This service is only available if juanmiguelbesada/mjml-php package is
 * installed.
 */
final class ApiRenderer implements RendererInterface {

  /**
   * The mjml.io API client.
   *
   * @var \Mjml\Client
   */
  private $client;

  /**
   * ApiRenderer constructor.
   *
   * @param \Mjml\Client $client
   *   The mjml.io API client.
   */
  public function __construct(Client $client) {
    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  public function render(string $mjml_content): string {
    return $this->client->render($mjml_content);
  }

}
