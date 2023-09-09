<?php

namespace Drupal\ap_quiz\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;
/**
 * Our simple form class.
 */
class AdotaPatasQuizFormAjax extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ap_quiz_quiz_form_ajax';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $idquiz = null, $html = null) {

    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      // You can get nid and anything else you need from the node object.
      $nid = $node->id();
      $form['nid'] = array(
        '#title' => $this->t(''),
        '#type' => 'hidden',
        '#value' => $nid,
      );
    }

    $form['titulo'] = $html;

    $form['nome'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nome'),
      '#placeholder' => $this->t('Nome')
    ];

    $form['sobrenome'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sobrenome'),
      '#placeholder' => $this->t('Sobrenome')
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('E-mail'),
      '#placeholder' => $this->t('E-mail')
    ];

    $form['nascimento'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Dt. de nascimento'),
      '#placeholder' => $this->t('Dt. de nascimento')
    ];

    $opcoes = array(
        'Não tenho pets',
        'Tenho cão',
        'Tenho gato',
        'Tenho cão e gato'
    );

    $form['pet'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Escolha uma opção abaixo:'),
      '#default_value' => 'Não tenho pets',
      '#options' => $opcoes
    );

    $form['politica'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t(''),
      '#return_value' => 1
    );

    $form['quizid'] = array(
      '#title' => $this->t(''),
      '#type' => 'hidden',
      '#value' => $idquiz,
    );

    $form['actions'] = [
      '#type' => 'button',
      '#value' => $this->t('Descubra >>'),
      '#ajax' => [
        'callback' => '::setMessage',
      ]
    ];

    $form['#theme'] = 'quiz_ajax_form';

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {



    if(strlen($form_state->getValue('nome')) < 3 || $form_state->getValue('nome') == '')  {
      $form_state->setErrorByName('nome', 'Quantidade de caracteres para o nome insuficiente.');
    }

    if(strlen($form_state->getValue('sobrenome')) < 3 || $form_state->getValue('sobrenome') == '')  {
      $form_state->setErrorByName('sobrenome','Quantidade de caracteres para o sobrenome insuficiente.');
    }


    if(strlen($form_state->getValue('nascimento')) < 10 || $form_state->getValue('nascimento') == '' )  {
      $form_state->setErrorByName('nascimento','Data de nascimento inválida.');
    }

    if($form_state->getValue('pet') == '')  {
      $form_state->setErrorByName('pet','Selecione se possui ou não um pet.');
    }

    if($form_state->getValue('politica') != 1 || $form_state->getValue('politica') != 1)  {
      $form_state->setErrorByName('politica','Para prosseguir você precisa estar de acordo com nossa política de privacidade.');
    }

    if (!\Drupal::service('email.validator')->isValid($form_state->getValue('email')) || $form_state->getValue('email') == '') {
      $form_state->setErrorByName('email','E-mail inválido.');
    }

    parent::validateForm($form, $form_state);
  }

  public function setMessage(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();


    if(count($form_state->getErrors()) > 0){

      $form_state->getErrors();

      $response->addCommand(new RemoveCommand('.alert-danger'));
      $response->addCommand(new InvokeCommand('.erro-display', 'removeClass', ['show']));
      $response->addCommand(new InvokeCommand('input', 'removeClass', ['error']));
      $response->addCommand(new InvokeCommand('.escolha-opcao', 'removeClass', ['red']));

      foreach ($form_state->getErrors() as $key => $value) {

        if($key == 'email'){
          $response->addCommand(new InvokeCommand('.form-item-' . $key . ' input', 'addClass', ['error']));
        }else if($key == 'pet'){
          $response->addCommand(new InvokeCommand('.escolha-opcao', 'addClass', ['red']));
        }else if($key == 'politica'){
          $response->addCommand(new InvokeCommand('.aviso-politica', 'append', ['<div class="alert alert-danger d-block w-100">Você precisa aceitar nossa política de privacidade.</div>']));
          //$response->addCommand(new InvokeCommand('.aviso-politica', 'addClass', ['<div class="alert alert-danger d-block w-100">Você precisa aceitar nossa política de privacidade.</div>']));
        }else{
          $response->addCommand(new InvokeCommand('.form-item-' . $key . ' input', 'addClass', ['error']));
        }

      }

      $response->addCommand(new InvokeCommand('.erro-display', 'addClass', ['show']));

      return $response;
    }

    // A jQuery selector.
    $selector = '.formulario-quiz';
    // The name of a jQuery method to invoke.
    $method = 'addClass';
    // (Optional) An array of arguments to pass to the method.
    $arguments = ['oculto'];

    $response->addCommand(
      new InvokeCommand($selector, $method, $arguments)
    );

    /* cadastrar o conteudo */
    if($form_state->getValue('pet') == 0){
      $resposta_pet = 'Não tenho pets';
    }else if($form_state->getValue('pet') == 1){
      $resposta_pet = 'Tenho cão';
    }else if($form_state->getValue('pet') == 2){
      $resposta_pet = 'Tenho gato';
    }else if($form_state->getValue('pet') == 3){
      $resposta_pet = 'Tenho cão e gato';
    }else{
      $resposta_pet = 'Não tenho pets';
    }

    $node = \Drupal::entityTypeManager()->getStorage('node')->create([
      'type'              => 'quiz_user',
      'title'             => $form_state->getValue('nome') . ' ' . $form_state->getValue('sobrenome'),
      'langcode'          => 'en',
      'field_date'        => $form_state->getValue('nascimento'),
      'field_ong_email'   => $form_state->getValue('email'),
      'field_name'        => $form_state->getValue('nome'),
      'field_page_home'   => $form_state->getValue('politica'),
      'field_possui_pet'  => $resposta_pet,
      'field_pagina'      => $form_state->getValue('nid'),
      'field_quiz'        => $form_state->getValue('quizid'),
      'field_surname'     => $form_state->getValue('sobrenome'),
    ]);

    $node->save();

    /* retornar o quiz */
    $response->addCommand(
      new InvokeCommand('.html-quiz', $method, ['exibir'])
    );

    $node = \Drupal::entityTypeManager()->getStorage('node')->load($form_state->getValue('quizid'));
    $view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');
    $renderarray = $view_builder->view($node, 'full');
    $html = \Drupal::service('renderer')->renderPlain($renderarray);


    $response->addCommand(
      new HtmlCommand(
        '.html-quiz',
        $html
      )
    );


    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
