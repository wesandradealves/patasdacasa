<?php

namespace Drupal\ds_twig_extensions\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\views\ViewExecutable;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File; 
use Drupal\core\Url; 
use Drupal\block\Entity\Block;
use Drupal\taxonomy\Entity\Term;      
use Drupal\Core\Link;
use Drupal\Component\Utility\Html;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Entity\EntityBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Request;
use Drupal\user\Entity\User;
use Cocur\Slugify\Slugify;

/**
 * Provides a resource to get view modes by entity and bundle.
 * @RestResource(
 *   id = "custom_get_rest_resource",
 *   label = @Translation("Custom Get Rest Resource"),
 *   uri_paths = {
 *     "canonical" = "/tax-filter"
 *   }
 * )
 */
class SampleGetRestResource extends ResourceBase {
  /**
   * A current user instance which is logged in the session.
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $loggedUser;
  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $config
   *   A configuration array which contains the information about the plugin instance.
   * @param string $module_id
   *   The module_id for the plugin instance.
   * @param mixed $module_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A currently logged user instance.
   */
  public function __construct(
    array $config,
    $module_id,
    $module_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
    parent::__construct($config, $module_id, $module_definition, $serializer_formats, $logger);

    $this->loggedUser = $current_user;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $config, $module_id, $module_definition) {
    return new static(
      $config,
      $module_id,
      $module_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('sample_rest_resource'),
      $container->get('current_user')
    );
  }
  /**
   * Responds to GET request.
   * Returns a list of taxonomy terms.
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   * Throws exception expected.
   */
  public function get() {
    global $theme_path;
    // Implementing our custom REST Resource here.
    // Use currently logged user after passing authentication and validating the access of term list.
    if (!$this->loggedUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }	

    $vocabulary_name = 'tags';
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid', $vocabulary_name);
    $tids = $query->execute();
    $terms = Term::loadMultiple($tids);
    $variables['taxonomies'] = array();
    $groupBy = [];
    $theme_handler = \Drupal::service('theme_handler');
    $theme_path = $theme_handler->getTheme($theme_handler->getDefault())->getPath();    
    
    foreach ($terms as $term) {
      $slugify = new Slugify();

      $tid = $term->id();

      $term_obj = Term::load($tid);
      $parent = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($tid);
      
      if($parent) {
          $parent = reset($parent);
          $ptid = $parent->id();
          $pterm_obj = Term::load($ptid);
      }

      $variables['taxonomies'][] = [
          'tid' => $tid,
          'parent' => $parent ? [
              'tid' => $ptid,
              'url' => \Drupal::service('path_alias.manager')->getAliasByPath('/taxonomy/term/' . $ptid),
              'name' => $pterm_obj->get('name')->value,
              'field_taxonomy_image' => $pterm_obj->get('field_taxonomy_image')->entity !== NULL ? file_create_url($pterm_obj->get('field_taxonomy_image')->entity->uri->value) : '',
              'field_taxonomy_theme' => $pterm_obj->get('field_taxonomy_theme')->getValue()[0]['color']
          ] : null,                
          'url' => \Drupal::service('path_alias.manager')->getAliasByPath('/taxonomy/term/' . $tid),
          'name' => $term_obj->name->value,
          'type' => $slugify->slugify($parent ? $pterm_obj->get('name')->value : $term_obj->name->value, '_'),
          'field_taxonomy_image' => $term_obj->get('field_taxonomy_image')->entity !== NULL ? file_create_url($term_obj->get('field_taxonomy_image')->entity->uri->value) : '',
          'field_taxonomy_theme' => $term_obj->get('field_taxonomy_theme')->getValue()[0]['color']
      ];     
    }    

    $variables['taxonomies'] = array_filter($variables['taxonomies'], function($item) {
      if(str_contains($item['name'], 'Filhote') ||
        str_contains($item['name'], 'Adulto') || 
        str_contains($item['name'], 'Idoso') || 
        str_contains($item['name'], 'Castrado')) {
        return true;
      }
      return false;
    });

    foreach($variables['taxonomies'] as $tax) { 
      $groupBy[$tax['type']][] = $tax;
    } 

    $response = [
      $groupBy,
      'images' => [
        '/'. $theme_path .'/assets/images/temporaria/dog-desk-home.png',
        '/'. $theme_path .'/assets/images/temporaria/cat-desk-home.png'
      ],      
    ];
  
    $response = new ResourceResponse($response);
    $response->addCacheableDependency($response);
    return $response;
  }

}