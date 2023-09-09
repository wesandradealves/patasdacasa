<?php

/**
* @file
* Contains \Drupal\ap_quiz\Controller\QuizResultsController.
*/

namespace Drupal\ap_quiz\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\image\Entity\ImageStyle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides route responses for the ap_quiz module.
 */
class QuizResultsController extends ControllerBase {

  /**
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private $tempStoreFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new WithControllerForm object.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $tempStoreFactory
   *  The Temp Store
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *  The EntityTypeManagerInterface DI
   */
  public function __construct(PrivateTempStoreFactory $tempStoreFactory, EntityTypeManagerInterface $entity_type_manager) {
    $this->tempStoreFactory = $tempStoreFactory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Returns the Quiz results form.
   */
  public function results() {

    $node = \Drupal::entityTypeManager()->getStorage('node')->load(\Drupal::routeMatch()->getParameter('node_type'));
    $view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');
    $renderarray = $view_builder->view($node, 'full');
    $html = \Drupal::service('renderer')->renderPlain($renderarray);

    $response = new Response(
      $html,
      Response::HTTP_OK,
      array('content-type' => 'text/html')
    );

    return $response;

  }

}
