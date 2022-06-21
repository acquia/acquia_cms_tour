<?php

namespace Drupal\acquia_cms_tour\Plugin\AcquiaCmsStarterKit;

use Drupal\acquia_cms_tour\Form\AcquiaCMSStarterKitBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\geocoder\GeocoderProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the acquia_cms_tour.
 *
 * @AcquiaCmsStarterKit(
 *   id = "acquia_cms_starter_kit_config",
 *   label = @Translation("Extend Starter Kit"),
 *   weight = 2
 * )
 */
class StarterKitConfigForm extends AcquiaCMSStarterKitBase {

  /**
   * Provides module name.
   *
   * @var string
   */
  protected $form_name = 'acquia_cms_starter_kit_config';

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var static $instance */
    $instance = parent::create($container);
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'starter_kit_configure_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Text input for Google Maps. ACMS can use the Gmaps API in two totally
    // different features (Site Studio and Place nodes). Site Studio is always
    // enabled in ACMS, but Place may not.
    // Initialize an empty array
    $form_name = $this->form_name;
    $form[$form_name] = [
      '#type' => 'details',
      '#title' => $this->t('Extend Starter Kit'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];
    $form[$form_name]['demo'] = [
      '#type' => 'select',
      '#title' => $this->t('Do you want to enable demo content?'),
      '#options' => ['none' => 'Please select', 'No' => 'No', 'Yes' => 'Yes'],
      '#default_value' => $this->state->get('acquia_cms.starter_kit_demo'),
    ];
    $form[$form_name]['content_model'] = [
      '#type' => 'select',
      '#title' => $this->t('Do you want to enable the content model?'),
      '#options' => ['none' => 'Please select', 'No' => 'No', 'Yes' => 'Yes'],
      '#default_value' => $this->state->get('acquia_cms.starter_kit_content_model'),
      '#states' => [
        'visible' => [
          ':input[name="demo"]' => ['value' => 'No'],
        ],
      ],
    ];
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $starter_kit_demo = $form_state->getValue(['demo']) ?? 'No';
    $starter_kit_content_model = $form_state->getValue(['content_model']) ?? 'No';
    if ($starter_kit_demo && $starter_kit_content_model) {
      $this->state->set('acquia_cms.starter_kit_demo', $starter_kit_demo);
      $this->state->set('acquia_cms.starter_kit_content_model', $starter_kit_content_model);
      $this->state->set('acquia_cms_tour_staretr_kit_demo_progress', TRUE);
      $this->messenger()->addStatus('The configuration options have been saved.');
    }
    $starter_kit = $this->state->get('acquia_cms.starter_kit');
    \Drupal::service('acquia_cms_tour.starter_kit')->enableModules($starter_kit, $starter_kit_demo, $starter_kit_content_model);
    $this->messenger()->addStatus('The required starter kit has been installed. Also, the related modules & themes have been enabled.');
    // Update state.
    $this->setConfigurationState();
  }

}
