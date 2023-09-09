<?php

namespace Drupal\ap_quiz\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;


/**
 * Provides a form to configure the ap_main module.
 */
class AdotaPatasQuizAdminForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'ap_quiz.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ap_quiz_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Retrieve the stored configuration settings.
    $config = $this->config(static::SETTINGS);
    // Start the form.
    $form['homepage'] = [
      '#type' => 'details',
      '#title' => t('Block at the Homepage'),
    ];

    $form['homepage']['quizzes_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#description' => $this->t('The block title displayed in the top of the block.'),
      '#required' => TRUE,
      '#default_value' => $config->get('quizzes_title'),
      '#placeholder' => t('Type the block title'),
    ];
    $form['homepage']['quizzes_subtitle'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subtitle'),
      '#description' => $this->t('The block subtitle displayed under the title of the block.'),
      '#required' => FALSE,
      '#default_value' => $config->get('quizzes_subtitle'),
      '#placeholder' => t('Type the block subtitle'),
    ];
    $form['homepage']['quizzes_internal'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Internal Text'),
      '#description' => $this->t('The text displayed in the middle of the block.'),
      '#required' => FALSE,
      '#default_value' => $config->get('quizzes_internal'),
      '#placeholder' => t('Type the internal text here'),
    ];
    $form['homepage']['quizzes_button'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button Label'),
      '#description' => $this->t('The text used in the CTA button'),
      '#required' => FALSE,
      '#default_value' => $config->get('quizzes_button'),
      '#placeholder' => t('Type the button label text'),
    ];
    $form['homepage']['quizzes_bg_image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Background Image'),
      '#description' => $this->t('Upload an image to be used like background.'),
      '#required' => FALSE,
      '#default_value' => $config->get('quizzes_bg_image'),
      '#upload_location' => 'public://',
      '#upload_validators' => [
        'file_validate_extensions' => ['jpg jpeg'],
        'file_validate_size' => [25600000],
        'file_validate_image_resolution' => ['1600x1200', '1100x320'],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable(static::SETTINGS)
      ->set('quizzes_title', $form_state->getValue('quizzes_title'))
      ->set('quizzes_subtitle', $form_state->getValue('quizzes_subtitle'))
      ->set('quizzes_internal', $form_state->getValue('quizzes_internal'))
      ->set('quizzes_button', $form_state->getValue('quizzes_button'))
      ->set('quizzes_bg_image', $form_state->getValue('quizzes_bg_image'))
      ->save();
      $form_file = $form_state->getValue('quizzes_bg_image', 0);
      if (isset($form_file[0]) && !empty($form_file[0])) {
        $file = File::load($form_file[0]);
        $file->setPermanent();
        $file->save();
      }
    parent::submitForm($form, $form_state);
  }

}
