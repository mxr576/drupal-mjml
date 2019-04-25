## Responsive HTML emails in Drupal with mjml.io

Creates responsive HTML emails with mjml.io.

### Installation

Install the module with Composer in the usual way.

```shell
$ composer require drupal/mjml
```

Enable the module on the administration UI or with Drush.

#### Select and configure the MJML renderer

##### Binary render (default)

1. [Install the MJML NodeJS library](https://mjml.io/documentation/#installation).

2. Configure the path of the NodeJS library in the settings.php:

```php
<?php
$settings['mjml'] = [
  'renderer' => [
    'binary' => [
      'options' => [
        'path' => 'PATH_TO_THE_MJML_BINARY'
      ]
    ]
  ]
];
```
3. Clear all cache in Drupal. (Ex.: Run `drush cr`)

##### API render

https://mjml.io/api.

1. Install [juanmiguelbesada/mjml-php](https://packagist.org/packages/juanmiguelbesada/mjml-php) library with Composer.

```shell
$ composer require juanmiguelbesada/mjml-php
```

2. Set your API credentials and change the default MJML renderer in the settings.php
```php
<?php
$settings['mjml'] = [
  'default_renderer' => 'mjml.renderer.api',
  'renderer' => [
    'api' => [
      'client' => [
        'application-id' => 'YOUR_APPLICATION_ID',
        'secret-key' => 'YOUR_SECRET_KEY',
      ]
    ]
  ]
];
```

3. Clear all cache in Drupal. (Ex.: Run `drush cr`)

#### (Optional) Send a test email

```php
<?php
$params = [
  'name' => 'YOUR_NAME',
];
$addressee = 'YOUR_EMAIL@ADDRESS.COM';
$mail_manager = \Drupal::service('plugin.manager.mail');
$template = \Drupal::moduleHandler()->moduleExists('swiftmailer') ? 'test_mail_mjml_twig_template_swiftmailer' : 'test_mail_mjml_twig_template';
$mail_manager->mail('mjml', $template, $addressee, \Drupal::service('language.default')->get()->getId(), $params);
```

### Usage

Check the mjml.module file for examples how you can use this module with

* the default mail implementation in Drupal,
* the MailSystem module,
* or the SwiftMailer module.

### RoadMap

The current implementation depends on the PHP integration provided by
[notfloran/mjml-bundle](https://packagist.org/packages/notfloran/mjml-bundle) Symfony bundle. This may change in the
future.
