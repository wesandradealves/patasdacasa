<?php

/**
 * @file
 * Contains \Drupal\ap_quiz\Form\AdotaPatasQuizQuestions.
 */

namespace Drupal\ap_quiz\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

class AdotaPatasQuizQuestions extends FormBase {

  /**
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private $tempStoreFactory;

  /**
   * Constructs a new WithControllerForm object.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $tempStoreFactory
   *  The Tempo Store
   */
  public function __construct(PrivateTempStoreFactory $tempStoreFactory) {
    $this->tempStoreFactory = $tempStoreFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('tempstore.private'));
  }

  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'ap_quiz_questions';
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $step = NULL) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  public static function buildFormData($step = 0) {

  }

}
