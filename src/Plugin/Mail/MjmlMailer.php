<?php

namespace Drupal\mjml\Plugin\Mail;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Mail\Plugin\Mail\PhpMail;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Utility\Error;
use NotFloran\MjmlBundle\Renderer\RendererInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * MJML mailer plugin definition.
 *
 * @Mail(
 *   id = "mjml_mailer",
 *   label = @Translation("MJML mailer"),
 *   description = @Translation("Creates responsive HTML emails from MJML format.")
 * )
 */
final class MjmlMailer extends PhpMail implements ContainerFactoryPluginInterface {

  /**
   * Content type of an email that body contains an MJML template.
   */
  public const CONTENT_TYPE_MJML = 'text/mjml';

  /**
   * Content type of an email that body has been pre-rendered with Twig.
   *
   * (From MJML to HTML.)
   */
  public const CONTENT_TYPE_MJML_TWIG = 'text/mjml+twig';

  /**
   * The MJML renderer.
   *
   * @var \NotFloran\MjmlBundle\Renderer\RendererInterface*/
  private $mjmlRenderer;

  /**
   * Settings.
   *
   * @var \Drupal\Core\Site\Settings
   */
  private $settings;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private $logger;

  /**
   * MjmlMailer constructor.
   *
   * @param \NotFloran\MjmlBundle\Renderer\RendererInterface $mjml_renderer
   *   The MJML renderer.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(RendererInterface $mjml_renderer, ConfigFactoryInterface $config, LoggerInterface $logger) {
    $this->mjmlRenderer = $mjml_renderer;
    $this->logger = $logger;
    parent::__construct();
    // DI the config factory instead of retrieve it from the global static
    // container.
    $this->configFactory = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('mjml.renderer.default'),
      $container->get('config.factory'),
      $container->get('logger.channel.mjml')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function format(array $message): array {
    $content_type = $message['headers']['Content-Type'] ?? '';

    if ($content_type === self::CONTENT_TYPE_MJML || $content_type === self::CONTENT_TYPE_MJML_TWIG) {
      $message['headers']['Content-Type'] = 'text/html';

      if (count($message['body']) > 1) {
        $this->logger->warning('Message body contained more than one item. MJML always generate a complete HTML page. The message body can only contain one HTML page so we kept only the first item. Message body: <pre>@message_body</pre>', ['@message_body' => print_r($message['body'], TRUE)]);
      }

      $message['body'] = reset($message['body']);

      // Unless content type is CONTENT_TYPE_MJML_TWIG the MJML has to be
      // rendered to HTML.
      if ($content_type === self::CONTENT_TYPE_MJML) {
        try {
          $message['body'] = $this->mjmlRenderer->render($message['body']);
        }
        catch (\Exception $e) {
          $context = [
            '@message_body' => $message['body'],
          ];
          $context += Error::decodeException($e);
          $this->logger->error('Unable to parse MJML message body: <pre>@message_body</pre> Error: @message %function (line %line of %file). <pre>@backtrace_string</pre>', $context);
          $message = parent::format($message);
        }
      }
    }
    else {
      $message = parent::format($message);
    }

    return $message;
  }

}
