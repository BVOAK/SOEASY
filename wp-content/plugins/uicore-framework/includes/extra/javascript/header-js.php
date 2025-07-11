<?php
defined('ABSPATH') || exit();

//INCLUDED IN CLASS JS
if($settings['menu_interaction'] === 'focus'){
  $js .= "
  document.body.classList.add('uicore-menu-focus');
  ";
}elseif($settings['menu_interaction'] === 'text flip'){
    $js .= "
    if(window.innerWidth >= ".$settings["mobile_breakpoint"]."){
      jQuery('.uicore-menu li').on('mouseenter',function(e) {
          var animClass = 'ui-anim-flip';
          if(!jQuery(this).hasClass(animClass)){
            
            var btnContent = jQuery(this).children('a').find('.ui-menu-item-wrapper')
            btnContent.after(btnContent.clone())
            jQuery(this).children('a').find('.ui-menu-item-wrapper').wrapAll('<div class=\"ui-flip-anim-wrapp\"></div>');
            setTimeout(() => {
              this.classList.add(animClass)
            }, 10)
          }
        })
      }
    ";
}elseif($settings['menu_interaction'] === 'magnet button'){
  $js .= '
  if(window.innerWidth >= '.$settings["mobile_breakpoint"].'){
    (function($) {
      const $nav = $("ul.uicore-menu");
      const $menu = $("#wrapper-navbar .uicore-header-wrapper");
      const $ctaBtn = $(".uicore-cta-wrapper a");
 
';
if( $settings['header_pill'] === 'menu' || $settings['header_pill'] === 'logo-menu'){
  $js.='
      const heightFallback = ($nav.outerHeight() - 8)+"px";
      const topFallback = ($nav.offset().top - jQuery(window).scrollTop() + 4)+"px";
      const isPill = true;
      ';
}else{
  $js.='
      const heightFallback = "2.4rem";
      const topFallback = "calc(calc(var(--uicore-header--menu-typo-h) / 2) - 1.2rem)";
      const isPill = false;
      ';
}

$js .= '
      const fakeBtnHeight = $ctaBtn.length ? $ctaBtn.outerHeight()+"px" : heightFallback;
      const fakeBtnTop = $ctaBtn.length ? ($ctaBtn.offset().top - $menu.offset().top)+"px" : topFallback;
      const fakeBtnRadius = $ctaBtn.length ? $ctaBtn.css("border-radius") : "var(--ui-radius)";
    
      const updateActiveItemStyles = (anchor) => {
        const isLastChild = anchor.parentElement === anchor.parentElement.parentElement.lastElementChild;
        const anchorBounds = anchor.getBoundingClientRect();
        const navBounds = isPill ? 0 : $menu[0].getBoundingClientRect().left;
        const relativeLeft = anchorBounds.left - navBounds;
        let width = anchorBounds.width +"px";
        if(!isPill && isLastChild){
          width = "calc("+anchorBounds.width+"px + "+window.getComputedStyle(anchor).paddingLeft+")";
        }
    
        $nav.css("--item-active-x", `${relativeLeft}px`);
        $nav.css("--item-active-width", width);
      };
    
      $nav.on("pointerenter", "li > a", function() {
        updateActiveItemStyles(this);
      });
    
      const deactivate = async () => {
        const transitions = $nav[0].getAnimations();
        if (transitions.length) {
          const fade = transitions.find(t => t.effect.target === $nav[0].firstElementChild && t.transitionProperty === "opacity");
          await Promise.allSettled([fade.finished]);
          $nav.css("--item-active-x", "");
          $nav.css("--item-active-width", "");
        }
      };
    
      $nav.on("pointerleave blur", deactivate);
      $nav.css("--item-active-height", `${fakeBtnHeight}`);
      $nav.css("--item-active-y", `${fakeBtnTop}`);
      $nav.css("--item-active-radius", `${fakeBtnRadius}`);
    })(jQuery);
  }
  ';
}
if($settings['submenu_trigger'] === 'click' && strpos($settings['header_layout'], 'ham') == false){
  $js .= "
  if (window.innerWidth >= ".$settings["mobile_breakpoint"].") {
    jQuery('.uicore-navbar nav .menu-item-has-children').on('click', function(e) {

      const target = jQuery(e.target);
      const parentLi = target.closest('li');
      if ((target.is('li') || (target.is('a') && target.parent().is('li')) || target.hasClass('ui-menu-item-wrapper')) &&
          (parentLi.hasClass('menu-item-has-children') && (!target[0].classList.length || (target[0].classList.length === 1 && target[0].classList[0] === 'ui-menu-item-wrapper')))) {
          e.preventDefault();
          e.stopPropagation();
      }
      const sub = jQuery(this).children('.sub-menu');

      // Hide other open sub-menus
      const siblings = sub.parent().siblings();
      siblings.children('.sub-menu').css('display', 'none');
      siblings.find('.sub-menu').css('display', 'none');

      if (sub.is(':hidden')) {
        sub.css({
          'display': 'flex',
          'opacity': 1,
          'transform': 'none',
          'pointer-events': 'all'
        });
      } else {
        sub.css('display', 'none');
      }
    });
    
    jQuery(document).on('click', function(e) {
      const clickedElement = e.target;
      if (!jQuery(clickedElement).closest('.sub-menu').length) {
        jQuery('.sub-menu').css('display', 'none');
      }
    });
  }  
  ";
}
if($settings['animations_submenu'] === 'scale bg'){
  $js .= "
  jQuery(function($) {
    let timeout;
    $('.uicore-menu').on('mouseenter', 'li.menu-item-has-children', function() {
      const subMenu = $(this).find('.sub-menu');
      const offset = subMenu.offset();
      const distanceFromTop = offset ? (offset.top - $(window).scrollTop() + subMenu.outerHeight() - 10 ) : 0;
      const topOffset = $(this).offset().top - $(window).scrollTop();
      clearTimeout(timeout);
      $('.uicore-header-wrapper').css('--ui-bg-height', (distanceFromTop - topOffset) + 'px');
      const menu = $('.uicore-transparent');
      if (menu.length) {
        menu.addClass('uicore-transparent-color');
      }
    }).on('mouseleave', 'li.menu-item-has-children', function() {
      clearTimeout(timeout);
      timeout = setTimeout(function() {
        $('.uicore-header-wrapper').css('--ui-bg-height', '100%');
        setTimeout(() => {
          $('.uicore-transparent-color').removeClass('uicore-transparent-color');
        }, 70);
      }, 65);
    });
  });
  ";
}
if($settings['animations_submenu'] === 'website blur'){
  $js .= "
  jQuery(function($) {
    $('#content').css({
      'transition': 'all 0.3s cubic-bezier(.33,1,.68,1)',
      'will-change': 'filter transform'
    });

    let removeBlurTimeout;
    $('.uicore-menu').on('mouseenter', 'li.menu-item-has-children', function() {
      clearTimeout(removeBlurTimeout); // Clear the timeout if it exists
      $('body').addClass('uicore-blur-on');
    }).on('mouseleave', 'li.menu-item-has-children', function() {
      removeBlurTimeout = setTimeout(() => {
        $('body').removeClass('uicore-blur-on');
      }, 70);
    });
  });
  
  ";
}

if($settings['mmenu_animation'] === 'expand'){
  $js .= "

  window.uicoreBeforeMobileMenuShow = function() {
    const height = jQuery('.uicore-mobile-menu-wrapper nav').height() + jQuery('.uicore-mobile-menu-wrapper ul.uicore-active').last().height() + jQuery('.uicore-mobile-menu-wrapper .uicore-extra').height() + 30;
    const heightCalc = 'calc('+height+'px + 2em)';
    jQuery('.uicore-mobile-menu-wrapper').css('max-height',heightCalc);
  };
  window.uicoreBeforeMobileMenuHide = function() {
    jQuery('.uicore-mobile-menu-wrapper').css('max-height','0');
  };

  ";
}