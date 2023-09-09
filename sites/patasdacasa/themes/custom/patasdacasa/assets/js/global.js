/**
 * @file
 * Global utilities.
 *
 */
(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.patasdacasa = {
    attach: function (context, settings) {
      var init = function () {
        $(".header").before($(".header").clone(true).addClass("--sticky"));
        $(window).scroll(function () {
          var t = $(this).scrollTop();
          if (t > $(".header").height()) {
            $(".--sticky").addClass("--stuck");
          } else {
            $(".--sticky").removeClass("--stuck");
          }
        });

        var formToJSON = function(form) {
          var data = {};
          for (var i = 0; i < form.length; i++) {
            var item = form[i];
            data[item.name] = item.value;
          }
          return data;
        }

        var news_hero__slide = $('.news_hero__slide');

        news_hero__slide.on('initialized.owl.carousel', function (event) {
          let arr = event.target.children[0].children[0].children;
          console.log(arr)
          for (const el of arr) {
            if (el.classList.contains("active")) {
              let tag = el.children[0].children[0].children[1].children[0];
              let theme = tag.dataset.theme;

              setTimeout(() => {
                $('[class*="owl"] svg').css('fill', theme)
              }, "100")
            }
          }
        });

        news_hero__slide.owlCarousel({
          items: 1,
          loop: true,
          nav: true,
          dots: false,
          center: false,
          margin: 0,
          URLhashListener: true,
          autoplayHoverPause: true,
          startPosition: 'URLHash',
          navText: ['<svg xmlns="https://www.w3.org/2000/svg" width="42.3" height="41.446" name="circle-svg" fill="#ef1716" transform="matrix(1,0,0,1,0,0)"><path id="icon" d="M41.8,21.832c.5-3.952,2.26-7.553-7.062-16.352C25.6-2.058,14.165-.455,6,5.48S-1.942,26.542,6,34.585c7.258,9.762,19.9,8.8,25.892,2.394C31.965,37.073,41.3,25.783,41.8,21.832Z" transform="translate(0.041 -0.41)"></path><desc></desc><defs></defs></svg>', '<svg xmlns="https://www.w3.org/2000/svg" width="42.3" height="41.446" name="circle-svg" fill="#ef1716" transform="matrix(1,0,0,1,0,0)"><path id="icon" d="M41.8,21.832c.5-3.952,2.26-7.553-7.062-16.352C25.6-2.058,14.165-.455,6,5.48S-1.942,26.542,6,34.585c7.258,9.762,19.9,8.8,25.892,2.394C31.965,37.073,41.3,25.783,41.8,21.832Z" transform="translate(0.041 -0.41)"></path><desc></desc><defs></defs></svg>']
        });

        if($('#edit-nascimento').length) {
          $('#edit-nascimento').mask('00/00/0000');
        };


        if($('.news_hero__thumbs').length){

          news_hero__slide.on('change.owl.carousel', function (event) {
            let arr = event.target.children[0].children[0].children;

            for (const el of arr) {
              if (el.classList.contains("active")) {
                let tag = el.nextElementSibling.children[0].children[0].children[1].children[0];
                let theme = tag.dataset.theme;
                $('[class*="owl"] svg').css('fill', theme)
              }
            }
          });

          var slider = tns({
            container: '.news_hero__thumbs',
            items: 2,
            controls: 0,
            nav: 0,
            startIndex: 1,
            axis: 'vertical'
          });

          document.querySelector('.owl-prev').onclick = function () {
            slider.goTo('prev');
          };

          document.querySelector('.owl-next').onclick = function () {
            slider.goTo('next');
          };
        }



        $( ".hamburger" ).click(function() {
          $(".hamburger").toggleClass('is-active');
          $('body').toggleClass('is-active');
        });

        $('body').on('click', '[id*="search-block"] .button', function(e) {
          $(this).closest('form').submit();
        });

        $('body').on('click', '[class*="owl-"]', function(e) {
          e.preventDefault();
        });

        // remover click no menu mobile para exibir submenus
        $('.header aside nav ul li a').on('click', function(e) {
          if($(document).width() <= '768') {
            e.preventDefault();
            $(this).next('ul').fadeToggle();
          }
        });

        // Menu mobile footer - abrir e fechar
        $('.nav li').on('click', function(e) {
          $(this).find('ul').toggleClass('display');
        });

        let sidenav = $('.--aside-menu-navigation')[0].children;

        for (let i = 0; i < sidenav.length; i++) {
          const element = sidenav[i];
          for (let j = 0; j < element.children.length; j++) {
            const item = element.children[j];
            $(item).click(function(e) {
              if(!$(item.children[0]).attr('href')) {
                e.preventDefault();

                if($(item.children[1])) {
                  $(item.children[1]).toggleClass('expanded')
                }
              }
            });
          }
        }

        // Menu mobile  padrão MR
        $('.header__menu').on('click', function(){
          $(".is-active .header .bottom-header .shortcuts--navigation+.nav>li>ul").css('display', 'block');
        });

        $(".btn-close-menu").on('click', function(){
          $(".is-active .header .bottom-header .shortcuts--navigation+.nav>li>ul").css('display', 'none');
        });

        $.fn.classChange = function(cb) {
          return $(this).each((_, el) => {
            new MutationObserver(mutations => {
              mutations.forEach(mutation => cb && cb(mutation.target, $(mutation.target).prop(mutation.attributeName)));
            }).observe(el, {
              attributes: true,
              attributeFilter: ['class'] // only listen for class attribute changes
            });
          });
        }

        $('.html-quiz').classChange(function(){

          // Js para quiz
          if($('.quadro_quiz').length){

            var total = $('.pergunta').length;
            var acertos = 1;
            var counts = {}

            $('.resultado').text(acertos + '/' + total);

            $(document).on('click',".resposta", function(){

              $('.pergunta').addClass('oculto');

              $(this).parents(':eq(1)').find('div.resposta').removeClass("ativo");
              $(this).addClass('ativo');
              $(this).parents(':eq(1)').attr('data-resposta', $(this).data('valor')).addClass("respondido");
              $(this).parents(':eq(1)').next().removeClass("oculto");

              acertos = $('.pergunta.respondido').length +1;

              if(acertos > total){
                $('.pergunta').last().removeClass('oculto');
                $('.resultado').addClass('aviso');
                $('.quiz-blur').addClass('exibir');
                loadResults();
              }else{
                $('.resultado').text(acertos + '/' + total);
              }

            });
          }
        });

        $(document).on('click',".botao-iniciar-quiz", function(){
          $(".informacoes-quiz").addClass('oculto');
          $(".perguntas-quiz").addClass('exibir');
          $(".pergunta").first().removeClass('oculto');
        });

        function loadResults(){
          var respostas = [];
          $('.pergunta').each(function(){
            respostas.push($(this).data('resposta'));
          });

          var redirect = respostas.sort((a,b) =>
                respostas.filter(v => v===a).length
              - respostas.filter(v => v===b).length
          ).pop();

          jQuery(".resultado-quiz").load("/quizre/"+redirect, function() {
            $('.resultado-quiz').addClass('exibir');
            $('.html-quiz').removeClass('exibir');
            $('.resultado').removeClass('aviso');
            $('.quiz-blur').removeClass('exibir');
          });
        }

      }

      window.onload = init;
    }
  };

})(jQuery, Drupal);
