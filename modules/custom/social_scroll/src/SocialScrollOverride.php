<?php

namespace Drupal\social_scroll;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;

/**
 * Class SocialScrollOverride.
 *
 * @package Drupal\social_scroll
 */
class SocialScrollOverride implements ConfigFactoryOverrideInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The SocialScrollManager manager.
   *
   * @var \Drupal\social_scroll\SocialScrollManager
   */
  protected $socialScrollManager;

  /**
   * Constructs the configuration override.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\social_scroll\SocialScrollManager $social_scroll_manager
   *   The SocialScrollManager manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, SocialScrollManager $social_scroll_manager) {
    $this->configFactory = $config_factory;
    $this->socialScrollManager = $social_scroll_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    $overrides = [];
    $enabled_views = $this->socialScrollManager->getEnabledViewIds();

    foreach ($enabled_views as $key => $status) {
      $config_name = $this->socialScrollManager->getConfigName($key);

      if (in_array($config_name, $names)) {
        $current_view = $this->configFactory->getEditable($config_name);
        $displays = $current_view->getOriginal('display');

        $scroll_config = $this->configFactory->getEditable('social_scroll.settings');
        $button_text = $scroll_config->getOriginal('button_text');
        $automatically_load_content = $scroll_config->getOriginal('automatically_load_content');

        $pages = [];

        foreach ($displays as $id => $display) {
          if (isset($display['display_options']['pager']) || $display['display_plugin'] !== 'block') {
            $pages[] = $id;
          }
        }

        foreach ($pages as $display_page) {
          $display_options = $current_view->getOriginal('display.' . $display_page . '.display_options');
          $overrides[$config_name]['display'][$display_page]['display_options'] = array_merge($display_options, [
            'pager' => [
              'type' => 'infinite_scroll',
              'options' => [
                'views_infinite_scroll' => [
                  'button_text' => $button_text,
                  'automatically_load_content' => $automatically_load_content,
                ],
              ],
            ],
            'use_ajax' => TRUE,
          ]);
        }

      }
    }

    return $overrides;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'SocialScrollOverride';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) {
    return new CacheableMetadata();
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    return NULL;
  }

}