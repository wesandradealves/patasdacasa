<?php

namespace Drupal\ap_quiz\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\Node;

/**
 * Provides the 'Quizzes' Block.
 *
 * @Block(
 *   id = "ap_quiz_quizzes",
 *   admin_label = @Translation("Quizzes"),
 *   category = @Translation("Adota Patas"),
 * )
 */
class QuizzesBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = \Drupal::config('ap_quiz.settings');
    $img = $destination = NULL;

    // Get the image and build the URL.
    if (!empty($config->get('quizzes_bg_image'))) {
      if (is_array($config->get('quizzes_bg_image'))) {
        $img = $config->get('quizzes_bg_image');
        $img = \Drupal\file\Entity\File::load($img[0]);
        if ($img) {
          $img = \Drupal\image\Entity\ImageStyle::load('banner_home')->buildUrl($img->getFileUri());
        }
      }
    }

    return [
      '#theme' => 'ap_quiz_quizzes',
      '#title' => $config->get('quizzes_title'),
      '#subtitle' => $config->get('quizzes_subtitle'),
      '#internal' => $config->get('quizzes_internal'),
      '#label' => $config->get('quizzes_button'),
      '#img' => $img,
      '#destination' => '/quiz',
    ];
  }

}
