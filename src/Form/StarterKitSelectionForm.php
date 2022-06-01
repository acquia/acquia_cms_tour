<?php

namespace Drupal\acquia_cms_tour\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Extension\ThemeInstallerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides a Starter Kit installer form.
 */
class StarterkitSelectionForm extends FormBase {

  /**
   * All steps of the multistep form.
   *
   * @var bool
   */
  protected $useAjax = TRUE;

  /**
   * The state interface.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

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
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'acquia_cms_tour_starterkit_wizard';
  }

  /**
   * Constructs a new StarterkitSelectionForm.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state interface.
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   The acquia cms tour manager class.
   * @param \Drupal\Core\Extension\ThemeInstallerInterface $theme_installer
   *   The acquia cms tour manager class.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config.factory service object.
   */
  public function __construct(StateInterface $state, ModuleInstallerInterface $module_installer, ThemeInstallerInterface $theme_installer, ConfigFactoryInterface $config_factory) {
    $this->state = $state;
    $this->moduleInstaller = $module_installer;
    $this->themeInstaller = $theme_installer;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state'),
      $container->get('module_installer'),
      $container->get('theme_installer'),
      $container->get('config.factory')
    );
  }

  /**
   * Returns wrapper for the form.
   */
  public function getFormWrapper() {
    $form_id = $this->getFormId();
    if ($this->useAjax) {
      $form_id = 'ajax_' . $form_id;
    }
    return str_replace('_', '-', $form_id);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Initialize an empty array
    $rows = [];    
    $header = [
      'starter_kit' => t('Starter Kit'),
      'description' => t('Description'),
    ];
    $kits = [
      'Acquia CMS Minimal' => t('Acquia CMS in a blank slate, ideal for custom PS.'),
      'Acquia CMS Low Code' => t('ACMS with Site Studio but no content opinion.'),
      'Acquia CMS Demo' => t('Low-code demonstration of ACMS with default content.'),
      'Acquia CMS Standard' => t('ACMS with a starter content model, no demo content, classic custom themes.'),
      'Acquia CMS Headless' => t('ACMS with headless functionality.'),
    ];
    $starter_kit_options = [
      'acquia_cms_minimal' => 'Acquia CMS Minimal',
      'acquia_cms_low_code' => 'Acquia CMS Low Code',
      'acquia_cms_demo' => 'Acquia CMS Demo',
      'acquia_cms_standard' => 'Acquia CMS Standard',
      'acquia_cms_headless' => 'Acquia CMS Headless'  
    ];
   // Next, loop through the $kits array
   foreach ($kits as $kit => $description) {
     $rows[$kit] = [
       'starter_kit' => $kit,   
       'description' => $description,
      ];
    }
    $form['tour-dashboard'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'tour-dashboard',
          ],
        ],
    ];
    $form['tour-dashboard']['title'] = [
      '#type' => 'markup',
      '#markup' => '<h3>' . $this->t('Starter Kit selection wizard') . '</h3>',
    ];
    $form['tour-dashboard']['message'] = [
        '#type' => 'markup',
        '#markup' => '<p>' . $this->t("Acquia CMS Starter Kits can be used to install ACMS as per the requirement i.e. with or without content model or Site Studio. 
        You can either select the starter kit now or do it later. Doing this would enable the required modules for the selected starter kit.") . '</p>',
    ];
    $form['tour-dashboard']['starter_kit'] = [
      '#type' => 'select',
      '#options' => $starter_kit_options,
      '#attributes' => [
        'class' => [
          'tour-dashboard',
        ],
      ],
      '#default_value' => $this->state->get('starter_kit_selected'),
    ];
    $form['tour-dashboard']['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#attributes' => [
        'class' => [
          'tour-dashboard-table',
        ],
      ],
    ];
    $form['tour-dashboard']['actions'] = ['#type' => 'actions'];
    $form['tour-dashboard']['actions']['open'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save & Continue'),
      '#attributes' => [
        'class' => [
          'button button--primary',
        ],
      ],
      '#submit' => ['::submitOpenWizard'],
    ];
    $form['tour-dashboard']['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('Do it later'),
      '#attributes' => [
        'class' => [
          'setup-manually',
        ],
      ],
    ];
    $form['#prefix'] = '<div id=' . $this->getFormWrapper() . '>';
    $form['#suffix'] = '</div>';
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitOpenWizard(array &$form, FormStateInterface $form_state) {
    $starter_kit = $form_state->getValue(['starter_kit']);
    $form_state->setValue(['starter_kit'], $starter_kit); 
    if ($starter_kit) {
      $this->state->set('hide_starter_kit_intro_dialog', TRUE);
      $this->state->set('starter_kit_selected', $starter_kit);
      $this->enableModules($starter_kit);
    }
    $this->messenger()->addStatus('The required starter kit has been installed. Also, the related modules & themes have been enabled.');
    $form_state->setRedirect('acquia_cms_tour.enabled_modules');
    $this->messenger()->addStatus('The required starter kit has been installed. Also, the related modules & themes have been enabled.');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $starter_kit = $form_state->getValue(['starter_kit']);
    $form_state->setValue(['starter_kit'], $starter_kit);
    if ($starter_kit) {
      $this->state->set('hide_starter_kit_intro_dialog', TRUE);
      $this->state->set('starter_kit_selected', $starter_kit);
      $this->enableModules($starter_kit);
    }
    $this->messenger()->addStatus('The required starter kit has been installed. Also, the related modules & themes have been enabled.');
    $form_state->setRedirect('acquia_cms_tour.enabled_modules');
    $this->messenger()->addStatus('The required starter kit has been installed. Also, the related modules & themes have been enabled.');
  }

  /**
   * Handler for enabling modules.
   * 
   * @param string $starter_kit
   *   Variable holding the starter kit selected.
   */
  public function enableModules(string $starter_kit) {
    $enableThemes = [
      'admin'   => 'acquia_claro',
      'default' => 'olivero',  
    ];
    // Case for selecting modules as per starter kit selected.
    switch ($starter_kit) {
      case 'acquia_cms_low_code':
        $enableModules = ['acquia_cms_page'];
        $enableThemes = [
          'admin'   => 'acquia_claro',
          'default' => 'cohesion_theme', 
        ];
        break;
      case 'acquia_cms_standard':
        $enableModules = ['acquia_cms_article', 'acquia_cms_event', 'acquia_cms_video'];
        $enableThemes = [
          'admin'   => 'acquia_claro',  
        ];
        break;
      case 'acquia_cms_demo':
        $enableModules = ['acquia_cms_starter'];
        $enableThemes = [
          'admin'   => 'acquia_claro',
          'default' => 'cohesion_theme',
        ];
        break;
      case 'acquia_cms_headless':
        $enableModules = ['acquia_cms_headless'];
        $enableThemes = [
          'admin'   => 'acquia_claro',  
        ];
        break;  
      default:
        $enableThemes = [
          'admin'   => 'acquia_claro',
          'default' => 'olivero',  
        ];
    }
    // Installer service to install modules.
    $this->moduleInstaller->install($enableModules);
    // Installer service to install themes.
    foreach ($enableThemes as $key => $theme) {
      $this->themeInstaller->install([$theme]);
    }
    // Enable the installed themes.
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
