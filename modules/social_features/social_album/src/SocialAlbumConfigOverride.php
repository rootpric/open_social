<?php

namespace Drupal\social_album;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;

/**
 * Class SocialAlbumConfigOverride.
 *
 * @package Drupal\social_album
 */
class SocialAlbumConfigOverride implements ConfigFactoryOverrideInterface {

  /**
   * Dependency to the field with reference to the album entity.
   */
  const DEPENDENCY = [
    'dependencies' => [
      'config' => [
        'field.field.post.photo.field_album' => 'field.field.post.photo.field_album',
      ],
    ],
  ];

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs the configuration override.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The Drupal configuration factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    $overrides = [];

    $config_name = 'core.entity_form_display.post.photo.default';

    if (in_array($config_name, $names)) {
      $overrides[$config_name] = self::DEPENDENCY + [
        'content' => [
          'field_album' => [
            'weight' => 2,
            'settings' => [],
            'third_party_settings' => [],
            'type' => 'social_album_options_select',
            'region' => 'content',
          ],
        ],
      ];
    }

    $config_names = [
      'core.entity_form_display.post.photo.group',
      'core.entity_form_display.post.photo.profile',
      'core.entity_view_display.post.photo.default',
    ];

    foreach ($config_names as $config_name) {
      if (in_array($config_name, $names)) {
        $overrides[$config_name] = self::DEPENDENCY + [
          'hidden' => [
            'field_album' => TRUE,
          ],
        ];
      }
    }

    $config_name = 'core.entity_view_display.post.photo.activity';

    if (in_array($config_name, $names)) {
      $overrides[$config_name] = self::DEPENDENCY + [
        'dependencies' => [
          'module' => [
            'social_album' => 'social_album',
          ],
        ],
        'content' => [
          'field_album' => [
            'type' => 'social_album_entity_reference_label',
            'weight' => 5,
            'region' => 'content',
            'label' => 'hidden',
            'settings' => [
              'link' => TRUE,
            ],
            'third_party_settings' => [],
          ],
        ],
      ];
    }

    $config_name = 'like_and_dislike.settings';

    if (in_array($config_name, $names)) {
      $overrides[$config_name] = [
        'enabled_types' => [
          'node' => [
            'album' => 'album',
          ],
        ],
      ];
    }

    $config_names = [
      'block.block.socialblue_profile_hero_block',
      'block.block.socialblue_pagetitleblock_content',
      'block.block.socialblue_profile_statistic_block',
      'block.block.socialblue_groupheroblock',
      'block.block.socialblue_group_statistic_block',
    ];

    foreach ($config_names as $delta => $config_name) {
      if (in_array($config_name, $names)) {
        $pages = $this->configFactory->getEditable($config_name)
          ->get('visibility.request_path.pages');

        $overrides[$config_name] = [
          'visibility' => [
            'request_path' => [
              'pages' => sprintf(
                "$pages\r\n/%s/*/albums",
                $delta > 2 ? 'group' : 'user'
              ),
            ],
          ],
        ];
      }
    }

    $config_name = $config_names[1];

    if (isset($overrides[$config_name])) {
      $overrides[$config_name]['visibility']['request_path']['pages'] .= "\r\n/group/*/albums";
    }

    return $overrides;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'SocialAlbumConfigOverride';
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