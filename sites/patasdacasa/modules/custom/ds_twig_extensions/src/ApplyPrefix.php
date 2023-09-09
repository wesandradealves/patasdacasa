<?php

namespace Drupal\ds_twig_extensions;

Use Drupal\taxonomy\Entity\Vocabulary;
use \Drupal\path_alias\AliasManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\core\Url;
use Drupal\block\Entity\Block;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Link;
use Drupal\Component\Utility\Html;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use \Drupal\user\Entity\User;
use Drupal\views\ViewExecutable;

function slugify($text, string $divider = '-')
{
  // replace non letter or digits by divider
  $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);

  // trim
  $text = trim($text, $divider);

  // remove duplicate divider
  $text = preg_replace('~-+~', $divider, $text);

  // lowercase
  $text = strtolower($text);

  if (empty($text)) {
    return 'n-a';
  }

  return $text;
}

/**
 * Custom twig functions.
 */
class ApplyPrefix extends AbstractExtension {
    public function getFunctions() {
        return [
          new TwigFunction('get_term', [$this, '_get_term']),
          new TwigFunction('get_image_from_id', [$this, '_get_image_from_id']),
          new TwigFunction('is_int', [$this, '_is_int']),
          new TwigFunction('mtranslator', [$this, '_mtranslator']),
          new TwigFunction('getNode', [$this, '_getNode']),
          new TwigFunction('getUser', [$this, '_getUser']),
          new TwigFunction('moduleExists', [$this, '_moduleExists']),
          new TwigFunction('getQuiz', [$this, '_getQuiz']),
          new TwigFunction('getNodeIdByAlias', [$this, '_getNodeIdByAlias']),
          new TwigFunction('getTermByAlias', [$this, '_getTermByAlias']),
          new TwigFunction('getTermAlias', [$this, '_getTermAlias']),
        ];
    }

    public function _getTermAlias($str) {
        $aliasManager = \Drupal::service('path_alias.manager');
        $alias = $aliasManager->getAliasByPath('/taxonomy/term/'.$str);
        return $alias;
    }    

    public function _getTermByAlias($str) {
        $vocabularies = Vocabulary::loadMultiple();
        foreach($vocabularies as $voc) {
        $voc_name = $voc->label();    
        $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
            ->loadByProperties(['vid' => $voc->id()]);
        foreach ($terms as $term) {
            $term_url = '';
            $term_id = $term->id();
            $aliasManager = \Drupal::service('path_alias.manager');
            $alias = $aliasManager->getAliasByPath('/taxonomy/term/'.$term_id);

            $alias = str_replace('/','',$alias);
            if($alias == $str) {
                $tid = $term_id;
                $term_obj = Term::load($tid);
                $parent = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($tid);
                if($parent) {
                    $parent = reset($parent);
                    $ptid = $parent->id();
                    $pterm_obj = Term::load($ptid);
                }      
                if($tid && !empty($term_obj)) {
                    return [
                        'tid' => $tid,
                        'url' => \Drupal::service('path_alias.manager')->getAliasByPath('/taxonomy/term/' . $tid),
                        'name' => $term_obj->name->value,
                        'description' => strip_tags($term_obj->get('description')->value),
                        'field_taxonomy_image' => $term_obj->get('field_taxonomy_image')->entity && $term_obj->get('field_taxonomy_image')->entity->uri ? file_create_url($term_obj->get('field_taxonomy_image')->entity->uri->value) : '',
                        'field_taxonomy_theme' => $term_obj->get('field_taxonomy_theme')->getValue()[0]['color'],
                        'field_cargo_especialista' => $term_obj->get('field_cargo_especialista')->getValue() ? $term_obj->get('field_cargo_especialista')->getValue()[0]['value'] : '',
                        'field_documento_especialista' => $term_obj->get('field_documento_especialista')->getValue() ? $term_obj->get('field_documento_especialista')->getValue()[0]['value'] : ''
                    ];
                }                          
            }
        }
             
        }        
    }    

    public function _getNodeIdByAlias($alias, $type) {
        $url = Url::fromUri('internal:' . '/' . $alias);
        if ($url->isRouted()) {
            $params = $url->getRouteParameters();
            $entity = \Drupal::entityTypeManager()->getStorage($type)->load($params[$type]);
            return ['alias' => $alias, 'type' => $type, 'id' => intval($params[$type]), 'entity' => $entity];
        }     

        return [];
    }    

    public function _moduleExists($str) {
        if(\Drupal::moduleHandler()->moduleExists($str)) {
            return \Drupal::moduleHandler()->moduleExists($str);
        }
        return NULL;
    }

    public function _getUser($uid) {
        if($uid) {
            $user = User::load($uid);

            return [
              'object' => $user,
              'uid' => $uid,
              'author_picture' => $user->user_picture && !$user->user_picture->isEmpty() ? file_create_url($user->user_picture->entity->getFileUri()) : null,
              'field_user_fullname' => $user->field_user_fullname->value,
              'field_user_social_role' => $user->field_user_social_role->value,
              'field_user_description' => $user->field_user_description->value
            ];
        }
        return NULL;
    }

    public function _getNode($nid) {
        if($nid) {
            $node = Node::load($nid);
            $tags = [];

            if($node->getType() == 'article') {
                if($node->hasField('field_tags')) {
                    $terms = $node->get('field_tags')->referencedEntities();

                    foreach ($terms as $term) {
                        $term_obj = Term::load($term->tid->value);
                        $parent = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($term->tid->value);

                        if($parent) {
                            $parent = reset($parent);
                            $ptid = $parent->id();
                            $pterm_obj = Term::load($ptid);
                        }

                        $tags[] = [
                          'tid' => $term->tid->value,
                          'parent' => $parent ? [
                              'tid' => $ptid,
                              'url' => \Drupal::service('path_alias.manager')->getAliasByPath('/taxonomy/term/' . $ptid),
                              'name' => $pterm_obj->get('name')->value,
                              'field_taxonomy_image' => $pterm_obj->get('field_taxonomy_image')->entity !== NULL ? file_create_url($pterm_obj->get('field_taxonomy_image')->entity->uri->value) : '',
                              'field_taxonomy_theme' => $pterm_obj->get('field_taxonomy_theme')->getValue()[0]['color']
                          ] : '',
                          'url' => \Drupal::service('path_alias.manager')->getAliasByPath('/taxonomy/term/' . $term->tid->value),
                          'name' => $term_obj->name->value,
                          'field_taxonomy_image' => $term_obj->get('field_taxonomy_image')->entity !== NULL ? file_create_url($term_obj->get('field_taxonomy_image')->entity->uri->value) : '',
                          'field_taxonomy_theme' => $term_obj->get('field_taxonomy_theme')->getValue()[0]['color']
                        ];
                    }
                }

                return [
                    'nid' => $nid,
                    'cid' => $node->get('field_custom_editor') && $node->get('field_custom_editor')->entity ? $node->get('field_custom_editor')->entity->uid->value : null,
                    'uid' =>  $node->get('uid')->entity->uid->value,
                    'title' => $node->title->value,
                    'text' => strip_tags($node->body->value),
                    'url' => \Drupal::service('path_alias.manager')->getAliasByPath('/node/'.$nid),
                    'terms' => $tags,
                    'type' => $node->getType(),
                    'image' => isset($node->field_image->entity) ? file_create_url($node->field_image->entity->getFileUri()) : NULL
                ];
            } else {
                return $node;
            }
        }
        return NULL;
    }

    public function _is_int($string) {
        if($string) {
            return is_numeric( $string );
        }
        return NULL;
    }

    public function _mtranslator($string) {
        if($string) {
            switch ($string) {
                case '01':
                    return 'Janeiro';
                    break;
                case '02':
                    return 'Fevereiro';
                    break;
                case '03':
                    return 'Março';
                    break;
                case '04':
                    return 'Abril';
                    break;
                case '05':
                    return 'Maio';
                    break;
                case '06':
                    return 'Junho';
                    break;
                case '07':
                    return 'Julho';
                    break;
                case '08':
                    return 'Agosto';
                    break;
                case '09':
                    return 'Setembro';
                    break;
                case '10':
                    return 'Outubro';
                    break;
                case '11':
                    return 'Novambro';
                    break;
                case '12':
                    return 'Dezembro';
                    break;
                default:
                    return FALSE;
                    break;
            }
        }
        return NULL;
    }

    public function _get_term($tid) {
        $term_obj = Term::load($tid);
        $parent = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($tid);

        if($parent) {
            $parent = reset($parent);
            $ptid = $parent->id();
            $pterm_obj = Term::load($ptid);
        }

        if($tid && !empty($term_obj)) {
            return [
                'tid' => $tid,
                'url' => \Drupal::service('path_alias.manager')->getAliasByPath('/taxonomy/term/' . $tid),
                'slug' => str_replace('/','',\Drupal::service('path_alias.manager')->getAliasByPath('/taxonomy/term/' . $tid)),
                'name' => $term_obj->name->value,
                'parent' => $parent ? [
                    'tid' => $ptid,
                    'url' => \Drupal::service('path_alias.manager')->getAliasByPath('/taxonomy/term/' . $ptid),
                    'name' => $pterm_obj->get('name')->value,
                    'description' => strip_tags($pterm_obj->get('description')->value),
                    'field_taxonomy_image' => $pterm_obj->get('field_taxonomy_image')->entity && $pterm_obj->get('field_taxonomy_image')->entity->uri ? file_create_url($pterm_obj->get('field_taxonomy_image')->entity->uri->value) : '',
                    'field_taxonomy_theme' => $pterm_obj->get('field_taxonomy_theme')->getValue()[0]['color']
                ] : null,                   
                'description' => strip_tags($term_obj->get('description')->value),
                'type' => slugify($parent ? $pterm_obj->get('name')->value : $term_obj->name->value, '_'),
                'field_taxonomy_image' => $term_obj->get('field_taxonomy_image')->entity && $term_obj->get('field_taxonomy_image')->entity->uri ? file_create_url($term_obj->get('field_taxonomy_image')->entity->uri->value) : '',
                'field_taxonomy_theme' => $term_obj->get('field_taxonomy_theme')->getValue()[0]['color'],
                'field_cargo_especialista' => $term_obj->hasField('field_cargo_especialista') && $term_obj->get('field_cargo_especialista')->getValue() ? $term_obj->get('field_cargo_especialista')->getValue()[0]['value'] : '',
                'field_documento_especialista' => $term_obj->hasField('field_documento_especialista') && $term_obj->get('field_documento_especialista')->getValue() ? $term_obj->get('field_documento_especialista')->getValue()[0]['value'] : ''                
            ];
        }

        return NULL;
    }

    public function _get_image_from_id($id) {
        if($id) {
            $file = \Drupal\file\Entity\File::load($id);
            $src = file_create_url($file->getFileUri());
            return $src;
        }
        return NULL;
    }

    // função para carregar form de quizz
    public function _getQuiz($id) {
      if($id) {

        $node = Node::load($id);

        //dd($node->field_page_img_quiz->target_id);

        $imagem = $node->field_imagem->target_id != '' ? $this->_get_image_from_id($node->field_imagem->target_id) : '/sites/patasdacasa/themes/custom/patasdacasa/assets/images/topo-quiz.jpg';

        $html = [
          '#type' => 'markup',
          '#markup' => '<img src="'.$imagem.'" class="img-top-quiz" />
          <h2 class="h2">'. $node->getTitle() .'</h2>
          <h4 class="h4 erro-display">Preencha todos os campos para participar.</h4>
          <h3 class="h3 escolha-opcao">É só preencher e começar!</h3>'
        ];

        return \Drupal::formBuilder()->getForm('\Drupal\ap_quiz\Form\AdotaPatasQuizFormAjax', $id, $html);

      }

      return NULL;
  }
}
