<?php

namespace Drupal\acquia_cms_tour\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Extension\ThemeInstallerInterface;

/**
 * Defines a service that toggle modules based on environment.
 */
class StarterKitService {

  /**
   * The module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * The theme installer.
   *
   * @var \Drupal\Core\Extension\ThemeInstallerInterface
   */
  protected $themeInstaller;

  /**
   * The config factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new AcmsService object.
   *
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $moduleHandler
   *   The ModuleHandlerInterface.
   * @param \Drupal\Core\Extension\ThemeInstallerInterface $themeInstaller
   *   The ThemeInstallerInterface.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(
    ModuleInstallerInterface $moduleInstaller,
    ThemeInstallerInterface $themeInstaller,
    ConfigFactoryInterface $config_factory) {
    $this->moduleInstaller = $moduleInstaller;
    $this->themeInstaller = $themeInstaller;
    $this->configFactory = $config_factory;
  }

  /**
   * Handler for enabling modules.
   *
   * @param string $starter_kit
   *   Variable holding the starter kit selected.
   * @param string $demo_question
   *   Variable holding the demo question option selected.
   * @param string $content_model
   *   Variable holding the content model option selected.
   */
  public function enableModules(string $starter_kit, string $demo_question, string $content_model) {
    $enableThemes = [
      'admin'   => 'acquia_claro',
      'default' => 'olivero',
    ];
    $enableModules = [];
    switch ($starter_kit) {
      case 'acquia_cms_enterprise_low_code':
        $enableModules = [
          'acquia_cms_page',
          'acquia_cms_search',
          'acquia_cms_site_studio',
          'acquia_cms_toolbar',
          'acquia_cms_tour'
        ];
        $enableThemes = [
          'admin'   => 'acquia_claro',
          'default' => 'cohesion_theme',
        ];
        break;
      case 'acquia_cms_community':
        $enableModules = [
          'acquia_cms_search',
          'acquia_cms_toolbar',
          'acquia_cms_tour'
        ];
        $enableThemes = [
          'admin'   => 'acquia_claro',
          'default' => 'olivero',
        ];
        break;
      case 'acquia_cms_headless':
        $enableModules = [
          'acquia_cms_headless',
          'acquia_cms_search',
          'acquia_cms_toolbar',
          'acquia_cms_tour'
        ];
        $enableThemes = [
          'admin'   => 'acquia_claro',
          'default' => 'olivero',
        ];
        break;
      default:
        $enableThemes = [
          'admin'   => 'acquia_claro',
          'default' => 'olivero',
        ];
        $enableModules = ['acquia_cms_search', 'acquia_cms_toolbar', 'acquia_cms_tour'];
    }
    if($demo_question == 'Yes'){
      $enableModules = array_merge(
        $enableModules, ['acquia_cms_starter'],
      );
    }
    elseif($content_model == 'Yes'){
      $enableModules = array_merge(
        $enableModules, [
          'acquia_cms_article',
          'acquia_cms_page',
          'acquia_cms_event'
        ],
      );
    }
    if (!empty($enableModules)) {
      $this->moduleInstaller->install($enableModules);
    }
    foreach ($enableThemes as $key => $theme) {
      $this->themeInstaller->install([$theme]);
    }
    $this->configFactory
      ->getEditable('system.theme')
      ->set('default', $enableThemes['default'])
      ->save();
    $this->configFactory
      ->getEditable('system.theme')
      ->set('admin', $enableThemes['admin'])
      ->save();
  }

}
