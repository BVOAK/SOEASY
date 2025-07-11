<?php

defined('ABSPATH') || exit();
//INCLUDED IN CLASS CSS
if ( $json_settings['header_wide'] === 'true' && $json_settings['header_pill'] != 'compact') {
    $css .= '
    @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
        body {
            --uicore-header--wide-spacing:70px;
        }
        .uicore-boxed{
            --uicore-header--wide-spacing:50px;
        }
      }
    ';
}
//generic for all pill headers
if ( $json_settings['header_pill'] != 'false') {
    $css .= '
    @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
        .uicore-navbar{
            position:absolute;
            width:100%;
            border:10px solid transparent;
            transition: all .3s ease;
        }
        .uicore-navbar .uicore-header-wrapper{
            margin-top: '.$json_settings['header_pill_top_spacing'].'px;
        }
        .uicore-navbar.uicore-sticky{
            position:fixed;
        }
        .uicore-mobile-nav-show #wrapper-navbar{
            border:0 solid transparent;
        }
    }';
}else{
    //disable position absolute if is transparent enabled
    if( $json_settings['header_transparent'] === 'true') {
        $css .= '
        .uicore-navbar.uicore-transparent .uicore-header-wrapper{
            position: absolute;
            width: 100%;
        }';
    }
}

if ( $json_settings['header_pill'] === 'true' || $json_settings['header_pill'] === 'compact') {
    $css .= '
    @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
        #wrapper-navbar .uicore-header-wrapper,
        #wrapper-navbar .uicore-header-wrapper:before {
            border-radius: '.$json_settings['header_pill_radius'].'px
        }
    }
    ';

    if ( $json_settings['header_wide'] === 'true' && $json_settings['header_pill'] != 'compact') {
        $offset_val = $json_settings['gen_siteborder'] == 'true' ? ((float)$json_settings['gen_siteborder_w'] * 2 + 40) : 40;
        $css .= '
        @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
            #wrapper-navbar{
                margin: 20px;
                max-width: calc(var(--uicore-boxed-width) - '.$offset_val.'px);
                --uicore-header--wide-spacing:'.$json_settings['header_padding'].'px;
            }
        }
        ';
    }else{
        $offset_val = $json_settings['gen_siteborder'] == 'true' ? ((float)$json_settings['gen_siteborder_w'] * 2 + 20) : 20;
        $css .= '
        @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
            #wrapper-navbar{
                margin: 10px max(0px, calc(calc(calc(min(var(--uicore-boxed-width), 100vw) - var(--ui-container-size)) - '.$offset_val.'px) / 2));
                max-width:calc(var(--ui-container-size) + 20px);
                --uicore-header--wide-spacing:'.$json_settings['header_padding'].'px;
            }
            #wrapper-navbar.uicore .uicore-header-wrapper>.uicore.uicore-container{
                width:100%!important;
                max-width:100%!important;
                padding-left: '.$json_settings['header_padding'].'px!important;
                padding-right:'.$json_settings['header_padding'].'px!important;
            }
        }
        ';
    }

    if( $json_settings['header_transparent'] != 'true' && $json_settings['pagetitle']) {
        $css .= '#wrapper-navbar ~ #content header.uicore{
            padding-top:' . ( (float)$json_settings['header_logo_h'] + ( ((float)$json_settings['header_padding'] * 1.5) )) . 'px;
        }';
    }
}
if ( $json_settings['header_pill'] === 'compact') {
    $css .= '
    @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
        .uicore-navbar{
            width:auto!important;
            left: 50%;
            transform: translate3d(-50%, 0, 0);
            margin-left: 0!important;
        }
        #uicore-site-header-cart,
        .uicore-navbar a.uicore-btn{
            white-space: nowrap;
        }
    }';
}

if ( $json_settings['header_pill'] === 'menu' || $json_settings['header_pill'] === 'logo-menu') {
    $css .= '
    @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
        #wrapper-navbar .uicore-menu {
            border-radius: '.$json_settings['header_pill_radius'].'px;
            align-self: center;
            padding:0 calc(var(--uicore-header--items-gap) / '.((float)$json_settings['header_pill_radius'] > 12 ? "1.2" : "2").');
        }
        .uicore-nav-menu .uicore-nav .uicore-menu>.menu-item>a{
            line-height:clamp(36px,'.(float)$json_settings['button_typography_typography']['s']['d']* 2.8.'px,66px);
        }
        .uicore-menu li > a:before{
            z-index: 0!important;
        }
        body #wrapper-navbar .uicore-nav ul.uicore-menu li:last-child:not(.menu-item-has-children) a {
            padding-right: var(--uicore-header--menu-spaceing)!important;
        }

    }';
    if($json_settings['menu_interaction'] === 'button' || $json_settings['menu_interaction'] === 'magnet button') {
        $css .= '
        @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
            #wrapper-navbar .uicore-menu {
                padding:4px;
            }
            #wrapper-navbar .uicore-menu li > a:before {
                height: 100%;
            }
            #wrapper-navbar .uicore-nav ul.uicore-menu>li:last-child:not(.menu-item-has-children) a:before{
                right:0!important;
            }
            ul.uicore-menu::before{
                z-index: 0!important;
            }
        }
        ';
    }
    if ($json_settings['header_shadow'] === 'true') {
        $css .= '
        #wrapper-navbar .uicore-menu {
            box-shadow:0px 3px 10px 0 rgb(0 0 0 / 3%), -2px 3px 90px -20px rgb(0 0 0 / 26%);
        }';
    }
}
if (  $json_settings['header_pill'] === 'logo-menu') {
    $css .= '
    @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
        .uicore-nav-menu .uicore-logo-pill a{
            height:clamp(36px,'.(float)$json_settings['button_typography_typography']['s']['d']* 2.8.'px,66px);
            display: flex;
            align-items: center;
        }
        .uicore-nav-menu .uicore-logo-pill img{
            display:block;
            margin-right:calc(var(--uicore-header--items-gap) / 2);
            max-height:clamp(26px,calc('.(float)$json_settings['button_typography_typography']['s']['d']* 1.7.'px - calc(var(--uicore-header--items-gap) / 2)),56px);
        }

    }';
}

//generic for all pill headers MOBILE
if ( $json_settings['mobile_pill'] != 'false') {
    $css .= '
    @media  (max-width: '.$json_settings['mobile_breakpoint'].'px) {
        #wrapper-navbar{
            position:fixed;
            border:10px solid transparent;
            transition: all .3s ease
        }
        .uicore-mobile-nav-show #wrapper-navbar{
            border:0 solid transparent;
        }
    }';

    if($json_settings['mobile_pill'] === 'true'){
        $css .= '
        @media  (max-width: '.$json_settings['mobile_breakpoint'].'px) {
            #wrapper-navbar .uicore-header-wrapper,
            #wrapper-navbar .uicore-header-wrapper:before {
                border-radius: '.$json_settings['mobile_pill_radius'].'px
            }
        }';
    }
}




//classic center
if ( $json_settings['header_layout'] === 'classic_center') {
    $css .= '
    .uicore-branding{
        padding-right:0px!important;
    }

    .uicore-h-classic .uicore-nav-menu{
        position:absolute;
        left:var(--uicore-header--wide-spacing,10px);
    }
    .uicore-h-classic .uicore-socials{
        margin: 0 -10px;
    }
    .uicore-navbar nav.uicore ul.uicore-menu > li > ul.sub-menu{
        position:fixed;
    }
    .uicore-scrolled nav.uicore ul.sub-menu {
        top: var(--uicore-header--menu-typo-h,0);
    }
    @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
        .uicore-h-classic .uicore.uicore-extra,
        .uicore-navbar .uicore-ham.uicore-toggle{
            position:absolute;
            right:var(--uicore-header--wide-spacing,10px);
        }
        .uicore-nav-menu .uicore-nav .uicore-menu > .menu-item:first-child > a {
            padding-left:0!important;
        }
        ';

        if($json_settings['menu_interaction'] === 'button') {
            $css .= '
            .uicore-nav-menu .uicore-nav .uicore-menu > .menu-item:first-child > a:before {
                left: calc(10px - var(--uicore-header--menu-spaceing));
            }';
        }
        $css .= '
		.uicore-h-classic nav.uicore{
	        position:relative;
	        justify-content: center;
	    }
    }
    ';
}

//center creative
if ( $json_settings['header_layout'] === 'center_creative') {
    $css .= '
    div[class^=\'ui-header-row\'], div[class*=\' ui-header-row\']{
        display:flex;
        justify-content: center;
        position:relative;
    }
    .uicore-h-classic .uicore-socials{
        margin: 0 -10px;
    }
    .ui-header-left,
    .ui-header-right{
        position:absolute;
        top: 0;
        bottom: 0;
        align-items: center;
        gap:var(--uicore-header--items-gap);
        left:0;
        display:none;
    }
    .ui-header-right{
        left:auto;
        right:0
    }
    .uicore-navbar nav.uicore ul.sub-menu{
        position:fixed;
    }
    @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
        .uicore-h-classic nav.uicore{
            position:relative;
            justify-content: center;
        }
        .ui-header-left,
        .ui-header-right{
            display:flex
        }
        .uicore-h-classic .uicore-header-wrapper nav.uicore{
            display:block;
        }
        .uicore-h-classic .uicore.uicore-extra,
        .uicore-navbar .uicore-ham.uicore-toggle{
            position:absolute;
            right:var(--uicore-header--wide-spacing,10px);
        }
        .uicore-navbar .uicore-menu-container{
            --uicore-header--menu-typo-h: '. (($json_settings['header_2_padding'] * 2) + $json_settings['menu_typo']['s'] ).'px;
        }
        .uicore-scrolled .uicore-header-wrapper {
            top: calc(var(--uicore-header--menu-typo-h,0) * -1);
        }
        .uicore-navbar .uicore-header-wrapper {
            transition: transform .3s ease,top .2s ease-in, opacity .3s ease;
        }
    }
    ';
}

if ($json_settings['header_layout'] === 'classic' || $json_settings['header_layout'] === 'classic_center') {
    $css .= '.uicore-transparent ~ #content header.uicore{
        padding-top:' . ( (float)$json_settings['header_logo_h'] + ( ((float)$json_settings['header_padding'] * 1.5) )) . 'px;
    }';
}

//Shadow
if ($json_settings['header_shadow'] === 'true' && $json_settings['header_pill'] != 'menu' && $json_settings['header_pill'] != 'logo-menu') {
    $css .= '
    #wrapper-navbar .uicore-header-wrapper:before {
        box-shadow: -2px 3px 90px -20px rgb(0 0 0 / 25%);
    }';
}

// Desktop menu align
if ($json_settings['header_layout'] === 'classic' || $json_settings['header_layout'] === 'classic_center' || strpos($json_settings['header_layout'], 'ham') !== false) {
    $css .= '.uicore-transparent ~ #content header.uicore{
        padding-top:' . ( (float)$json_settings['header_logo_h'] + ( ((float)$json_settings['header_padding'] * 1.5) )) . 'px;
    }';

    if($json_settings['menu_position'] === 'left' || $json_settings['header_pill'] === 'logo-menu'){
        $css .= '
        .uicore-navbar nav .uicore-nav {
            display: flex;
            justify-content: flex-start;
        }
        .uicore-navbar nav .uicore-nav .uicore-socials {
            display: flex;
          }
        ';
    }
    if($json_settings['menu_position'] === 'center' && $json_settings['header_pill'] != 'logo-menu'){
        $css .= '
        .uicore-navbar nav .uicore-nav {
            display: flex;
            justify-content: center;
        }
        ';
    }
}

// Mobile menu align
if($json_settings['mmenu_center'] === 'center'){
    $css .= '
    .uicore-navigation-wrapper {
        text-align: center;
    }
    ';
}
if($json_settings['mmenu_center'] === 'right'){
    $css .= '
    .uicore-navigation-wrapper {
        text-align: right;
    }
    .uicore-navigation-wrapper ul .menu-item-has-children > a {
        padding-right: 35px !important;
    }
	.uicore-navigation-wrapper .uicore-menu-container ul .menu-item-has-children>a:after {
		left: 15px;
		right: auto;
	}
    ';
}


if ($json_settings['header_bg']['blur'] === 'true') {
    $css .= '.uicore-header-wrapper:before {
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }';
}
$css_selector_border = '.uicore-header-wrapper';
if($json_settings['header_pill'] === 'logo-menu'){
    $css_selector_border = '.uicore-menu';
}
if ($json_settings['header_transparent_border'] == 'true' && $json_settings['header_layout'] == 'classic') {
    $css .= '.uicore-navbar '. $css_selector_border .'
    {
        box-shadow:0 0px 0 1px transparent;
    }';
}

//for case when you want border only for nontransparent header
if ($json_settings['header_transparent_border'] == 'false' && $json_settings['header_layout'] == 'classic') {
    $css .= '
    .uicore-navbar.uicore-transparent:not(.uicore-scrolled) '.$css_selector_border.'{
        box-shadow:0 0px 0 1px  transparent;
    }';
}

if ($json_settings['header_border'] == 'true') {

    //FOR CLASSIC MENU ONLY
    if ($json_settings['header_layout'] == 'classic') {
        $css .= '
        .uicore-navbar '.$css_selector_border.'{
            box-shadow:0 0px 0 1px ' . $this->color($json_settings['header_borderc']) . ';
        }';

        //FOR LEFT MENU ONLY
    } else {
        $css .= '
        .uicore-left-menu {
            border-right: 1px solid ' . $this->color($json_settings['header_borderc']) . ';
        }';
    }
}

if ($json_settings['header_transparent_border'] == 'true') {
    $css .=
        '
    .uicore-transparent:not(.uicore-scrolled) '.$css_selector_border.' {
        box-shadow:0 0px 0 1px ' . $this->color( $json_settings['header_transparent_borderc']) .
        ';
    } ';
}

if ($json_settings['header_layout'] === 'left') {
    $css .= '
    @media (min-width: '.$br_points['lg'].'px) {
        .uicore-left-menu {
            width: '. $json_settings['header_side_width'] .'px
        }
        #uicore-page {
            padding-left: '. $json_settings['header_side_width'] .'px;
        }

        .uicore-custom-area {
            flex-direction:column;
        }
        .uicore-navbar .uicore-extra .uicore-socials,
        .uicore-custom-area .uicore-hca{
            margin-left:0!important;
        }

    }
    ';
    if($json_settings['header_content_align'] === 'left'){
        $css .= '
        @media (min-width: '.$br_points['lg'].'px) {
            .uicore-left-menu.uicore-section .uicore-container,
            .uicore-left-menu .uicore-extra,
            .uicore-left-menu .uicore-extra .uicore-btn  {
                align-items: normal;
            }
        }
        ';
    }
    if($json_settings['header_content_align'] === 'center'){
        $css .= '
        @media (min-width: '.$br_points['lg'].'px) {
            .uicore-left-menu .uicore-nav-menu {
                width: 100%;
                text-align: center;
            }
        }
        ';
    }

}

if ($json_settings['header_transparent_border'] == 'true' && $json_settings['header_layout'] == 'classic') {
    $css .= '.uicore-transparent ~ #content header.uicore{
        padding-top:' . ( intval($json_settings['header_logo_h']) + ( (intval($json_settings['header_padding']) * 2) )) . 'px;
    }';
}

$no_padding_right = ((
    $json_settings['menu_position'] === 'right' &&
    ($json_settings['header_layout'] === 'classic' || $json_settings['header_layout'] === 'classic_center') &&
    $json_settings['header_cta'] === 'false' &&
    $json_settings['header_search'] === 'false' &&
    $json_settings['header_icons'] === 'false' &&
    $json_settings['woo'] === 'false'
) && ($json_settings['header_pill'] != 'logo-menu' || $json_settings['header_pill'] != 'menu'));

//remove menu item last padding for right
if ( $no_padding_right ) {
    $css .=
        '
    #wrapper-navbar .uicore-nav ul.uicore-menu li:last-child:not(.menu-item-has-children) a {
        padding-right:0!important;
    } ';
}

//build the class for background for default and pill headers
$selector = '.uicore-navbar .uicore-header-wrapper:before';
if($json_settings['header_pill'] === 'menu' || $json_settings['header_pill'] === 'logo-menu'){
    $selector = '.uicore-navbar .uicore-menu';
}

$css .= $this->background($json_settings['header_bg'] , '.uicore-mobile-menu-wrapper:before, .uicore-wrapper.uicore-search.uicore-section, '.$selector);
$css .= $this->background($json_settings['mobile_menu_bg'] , '.uicore-navigation-wrapper', 'max-width: '.$json_settings['mobile_breakpoint'].'px');
 //Hmabourger menu
 if (strpos($json_settings['header_layout'], 'ham') !== false) {
    $menu_item_lh = 1;
    $css .= $this->background($json_settings['menu_bg'] , '.uicore-navigation-wrapper', 'min-width: '.$json_settings['mobile_breakpoint'].'px');
    $css.= '
    .uicore-navbar:not(.uicore-transparent) .uicore-ham .bar,
    .uicore-transparent-color .uicore-ham .bar,
    .uicore-transparent.uicore-scrolled .uicore-ham .bar{
        background-color: '. $this->color($json_settings['header_ham_color']['m']) .'
    }
    .uicore-navbar:not(.uicore-transparent) .uicore-ham:hover .bar,
    .uicore-transparent-color .uicore-ham:hover .bar,
    .uicore-transparent.uicore-scrolled .uicore-ham:hover .bar{
        background-color: '. $this->color($json_settings['header_ham_color']['h']) .'
    }
    @media  (min-width: '.$json_settings['mobile_breakpoint'].'px) {
        .uicore-is-ham .uicore-mobile-menu-wrapper:not(.uicore-ham-classic) .uicore-navigation-content .uicore-menu .menu-item-has-children>a:after {
            line-height: '. ($json_settings['menu_typo']['h'] * $json_settings['menu_typo']['s']) .  'px;
        }
        .uicore-mobile-menu-wrapper,
        .uicore-mobile-menu-wrapper li ul.sub-menu {
            --uicore-header--menu-typo-f:' . $this->fam($json_settings['menu_typo']['f']) . ';
            --uicore-header--menu-typo-w:' . $this->wt($json_settings['menu_typo']) . ';
            --uicore-header--menu-typo-h:' . $json_settings['menu_typo']['h'] . ';
            --uicore-header--menu-typo-ls:' . $json_settings['menu_typo']['ls'] . 'em;
            --uicore-header--menu-typo-t:' . $json_settings['menu_typo']['t'] . ';
            --uicore-header--menu-typo-st:' . $this->st($json_settings['menu_typo']) . ';
            --uicore-header--menu-typo-c:' . $this->color($json_settings['menu_typo']['c']) . ';
            --uicore-header--menu-typo-ch:' . $this->color($json_settings['menu_typo']['ch']) . ';
            --uicore-header--menu-typo-s:' . $json_settings['menu_typo']['s'] . 'px;
        }
        .uicore-navbar ul.sub-menu{
            transform:none!important
        }
        body {
            --uicore-header--wide-spacing:70px;
        }
    }
    ';
}else{
    $menu_item_lh = (intval( $json_settings['header_logo_h'])  + ( intval($json_settings['header_padding']) * 2 ) ).'px';
    if($json_settings['header_layout'] === 'left'){
        $menu_item_lh = (intval($json_settings['menu_spacing'])  + intval($json_settings['menu_typo']['s']) ).'px';
    }
    $css.= '
    .uicore-cart-icon.uicore_hide_desktop #uicore-site-header-cart {
        color: var(--uicore-header--menu-typo-c);
    }
    @media only screen and (min-width: 1025px) {
        .uicore-navbar .uicore-extra {
            margin-left: 25px;
        }
    }
    ';
}



if($json_settings['header_pill'] != 'menu' && $json_settings['header_pill'] != 'logo-menu' ){
    $css .= '
    .uicore-transparent:not(.uicore-scrolled) {
        --uicore-header--menu-typo-c:' . $this->color($json_settings['header_transparent_color']['m']) . ';
        --uicore-header--menu-typo-ch:' . $this->color($json_settings['header_transparent_color']['h']) . ';
    }
    ';
}else{
    $css .= '
    .uicore-transparent:not(.uicore-scrolled) .uicore-extra{
        --uicore-header--menu-typo-c:' . $this->color($json_settings['header_transparent_color']['m']) . ';
        --uicore-header--menu-typo-ch:' . $this->color($json_settings['header_transparent_color']['h']) . ';
    }';
}
$css .='
body .uicore-transparent-color nav,
.uicore-navbar {
    --uicore-header--logo-h:' . $json_settings['header_logo_h'] . 'px;
    --uicore-header--logo-padding:' . $json_settings['header_padding'] . 'px;
    --uicore-header--menu-spaceing:' . (intval($json_settings['menu_spacing']) / 2 ).'px;

    --uicore-header--menu-typo-f:' . $this->fam($json_settings['menu_typo']['f']) . ';
    --uicore-header--menu-typo-w:' . $this->wt($json_settings['menu_typo']) . ';
    --uicore-header--menu-typo-h:' . $menu_item_lh .';
    --uicore-header--menu-typo-ls:' . $json_settings['menu_typo']['ls'] . 'em;
    --uicore-header--menu-typo-t:' . $json_settings['menu_typo']['t'] . ';
    --uicore-header--menu-typo-st:' . $this->st($json_settings['menu_typo']) . ';
    --uicore-header--menu-typo-c:' . $this->color($json_settings['menu_typo']['c']) . ';
    --uicore-header--menu-typo-ch:' . $this->color($json_settings['menu_typo']['ch']) . ';
    --uicore-header--menu-typo-s:' . $json_settings['menu_typo']['s'] . 'px;

    --uicore-header--items-gap:25px;

}';
if($json_settings['animations_submenu'] != 'scale bg'){
    $css .= '
    @media only screen and (min-width: '.$json_settings['mobile_breakpoint'].'px) {
        .uicore-navbar li ul {
            --uicore-header--menu-typo-f:' . $this->fam($json_settings['submenu_color']['f']) . ';
            --uicore-header--menu-typo-w:' . $this->wt($json_settings['submenu_color']) . ';
            --uicore-header--menu-typo-h:' . $menu_item_lh .';
            --uicore-header--menu-typo-ls:' . $json_settings['submenu_color']['ls'] . 'em;
            --uicore-header--menu-typo-t:' . $json_settings['submenu_color']['t'] . ';
            --uicore-header--menu-typo-st:' . $this->st($json_settings['submenu_color']) . ';
            --uicore-header--menu-typo-c:' . $this->color($json_settings['submenu_color']['c']) . ';
            --uicore-header--menu-typo-ch:' . $this->color($json_settings['submenu_color']['ch']) . ';
            --uicore-header--menu-typo-s:' . $json_settings['submenu_color']['s'] . 'px;
        }
    }
    ';
}


$css .= '
.uicore-ham .bar,
#mini-nav .uicore-ham .bar{
    background-color: var(--uicore-header--menu-typo-c);

}
@media only screen and (min-width: 1025px) {
    .uicore-shrink:not(.uicore-scrolled) {
        --uicore-header--logo-padding:' . $json_settings['header_padding_before_scroll'] . 'px;
        --uicore-header--menu-typo-h:' . (intval( $json_settings['header_logo_h'])  + ( intval($json_settings['header_padding_before_scroll']) * 2 ) ) . 'px;
    }
}

@media (max-width: ' . $br_points['md'] . 'px) {
    .uicore-navbar{
        --uicore-header--logo-h:' . $json_settings['mobile_logo_h'] . 'px;
    }
    #wrapper-navbar nav{
        max-width:95%;
    }
}';
if($json_settings['animations_submenu'] != 'scale bg'){
    $css .= '
    .uicore-nav-menu .sub-menu:not(.uicore-megamenu){
        background-color:' . $this->color($json_settings['submenu_bg']) .';
    }
    .uicore-nav-menu .sub-menu:not(.uicore-megamenu) a, .uicore-nav-menu .sub-menu:not(.uicore-megamenu) li,
    .uicore-nav-menu .uicore-simple-megamenu:not(.uicore-megamenu) > .sub-menu > li.menu-item-has-children{
        color:' . $this->color($json_settings['submenu_color']['c']) . '!important;
    }
    .uicore-nav-menu .sub-menu:not(.uicore-megamenu) a:hover, .uicore-nav-menu:not(.uicore-megamenu) .sub-menu li:hover{
        color:' .$this->color($json_settings['submenu_color']['ch']) . '!important;
    }
    ';
}
 // CTA on MOBILE
$css .= '@media (max-width: ' . $br_points['md'] . 'px) {
    .uicore-navbar .uicore-btn{
        font-size: ' . $json_settings['mmenu_typo']['s'] . 'px;
        font-weight: ' . $this->wt($json_settings['mmenu_typo']) .';
        font-style: ' . $this->st($json_settings['mmenu_typo']) . ';
        font-family: ' . $this->fam($json_settings['menu_typo']['f']) .';
        letter-spacing: ' . $json_settings['mmenu_typo']['ls'] . 'em;
        text-transform: ' . $json_settings['mmenu_typo']['t'] .';
    }
}
.uicore-menu-left #uicore-page nav div .uicore ul a{
    padding: calc(' . $json_settings['menu_spacing'] . 'px / 2) 0;
}
.uicore-menu-left #uicore-page nav div.uicore-extra .uicore-btn{
    margin: ' . $json_settings['header_padding'] . 'px auto;
}

.uicore-mobile-menu-wrapper-show .uicore-navigation-wrapper{
    color:' . $this->color($json_settings['mmenu_typo']['c']) . ';
}
.uicore-navigation-content{
    height: calc(100% - ' .( intval($json_settings['mobile_logo_h']) + (intval($json_settings['header_padding']) * 2) ) . 'px);
}
@media only screen and (max-width: '.$json_settings['mobile_breakpoint'].'px) {
    .uicore-mobile-menu-wrapper {
        --uicore-header--menu-typo-f:' . $this->fam($json_settings['mmenu_typo']['f']) . ';
        --uicore-header--menu-typo-w:' . $this->wt($json_settings['mmenu_typo']) . ';
        --uicore-header--menu-typo-h:' . $json_settings['mmenu_typo']['h'] . ';
        --uicore-header--menu-typo-ls:' . $json_settings['mmenu_typo']['ls'] . 'em;
        --uicore-header--menu-typo-t:' . $json_settings['mmenu_typo']['t'] . ';
        --uicore-header--menu-typo-st:' . $this->st($json_settings['mmenu_typo']) . ';
        --uicore-header--menu-typo-c:' . $this->color($json_settings['mmenu_typo']['c']) . ';
        --uicore-header--menu-typo-ch:' . $this->color($json_settings['mmenu_typo']['ch']) . ';
        --uicore-header--menu-typo-s:' . $json_settings['mmenu_typo']['s'] . 'px;
    }
}
';
if ($json_settings['header_sticky'] == 'true' && $json_settings['performance_widgets'] == 'true') {
    $css .=
        '
    .uicore-sidebar .uicore-sticky{
        top: calc(calc(' . $json_settings['header_logo_h'] . 'px + calc(' . $json_settings['header_padding'] . 'px * 2)) + 60px);
    }
    ';
}
if ($json_settings['header_sticky'] == 'true') {
    $css .=
        '
        .ui-hide{
            pointer-events: none;
        }
        .ui-hide .uicore-header-wrapper{
            transform:translate3d(0,-35px,0);
            opacity: 0;
            transition: transform .3s cubic-bezier(0.41, 0.61, 0.36, 1.08), opacity .2s ease;
            pointer-events: none;
        }
        .logged-in.admin-bar .uicore-navbar.uicore-sticky {
            top: 31px;
        }
    ';
}
if($json_settings['mobile_sticky'] === 'true' && $json_settings['header_sticky'] != 'true'){
    $css .= '
    @media  (max-width: '.$json_settings['mobile_breakpoint'].'px) {
        .uicore-navbar {
           position: sticky;
            width: 100%;
            top: 0;
        }
    }
    ';
}
if($json_settings['mobile_sticky'] === 'false' && $json_settings['header_sticky'] === 'true'){
    $css .= '
    @media  (max-width: '.$json_settings['mobile_breakpoint'].'px) {
        .uicore-navbar.uicore-sticky {
           position: relative;
        }
    }
    ';
}

//extras
if($json_settings['header_cta'] === 'true'){
    $css .= '
    @media  (min-width:'.$json_settings['mobile_breakpoint'].'px) {
        .uicore-navbar .uicore-cta-wrapper a {';
                if($json_settings['header_cta_size'] === 'small'){
                    $css .= 'padding:clamp(8px,.7em,13px) clamp(11px,1.3em,32px);';
                }elseif($json_settings['header_cta_size'] === 'medium'){
                    $css .= 'padding:clamp(12px,.9em,18px) clamp(24px,1.9em,26px)';
                }elseif($json_settings['header_cta_size'] === 'full'){
                    $css .= 'padding:0 clamp(24px,3em,40px);
                    line-height: calc(var(--uicore-header--menu-typo-h) + 2px)!important;
                    border-radius: 0!important;
                    ';

                }else{
                    $css .= 'padding:clamp(24px,1.8em,22px) clamp(36px,3.6em,72px);';
                }
                $css.='
          }
    }
    ';

}
if($json_settings['header_search'] === 'true'){
    $css .= '
    .uicore-wrapper.uicore-search.uicore-section {
        height: 100vh;
        position: fixed;
        right:0;
        left: 0;
        top: 0;
        opacity: 0;
        pointer-events: none;
        transition: all .4s ' . $opacityEase . ';
        justify-content: center;
        align-content: center;
        align-items: center;
        display: flex;
    }
    .uicore-search .search-field {
        font-size: 2em !important;
        background: transparent;
        border: none;
    }
    .uicore-search .uicore-close.uicore-i-close {
        position: absolute;
        right: 0;
        top: 0;
        cursor: pointer;
        font-size: 20px;
        padding: 20px;
    }
    .uicore-search-active {
        overflow: hidden !important;
    }
    .uicore-search-active .uicore-wrapper.uicore-search.uicore-section {
        opacity: 1;
        pointer-events: all;
        z-index: 999;
    }
    .win.uicore-search-active {
        margin-right: 17px;
    }
    .uicore-wrapper .search-field, .uicore-wrapper .search-field::placeholder,
    .uicore-close.uicore-i-close {
        color:' . $this->color($json_settings['menu_typo']['c']) . ';
    }
    ';

}


//animations
if($global_animations && strpos($json_settings['header_layout'], 'ham') !== false){
    $css .= '
    @media  (min-width: 1025px) {
        .uicore-navigation-content .uicore-extra > div {
            opacity: 0;
            transform: translate3d(0,4vw,0);
            transition: transform 1s '.$translateEase.', opacity 0.9s '. $opacityEase.';
        }
        .uicore-navigation-content .uicore-extra > div:nth-child(1) {
            transition-delay: 0.3s;
        }
        .uicore-navigation-content .uicore-extra > div:nth-child(2) {
            transition-delay: 0.6s;
        }
        .uicore-navigation-content .uicore-extra > div:nth-child(3) {
            transition-delay: 0.9s;
        }
        .uicore-navigation-content .uicore-extra > div:nth-child(4) {
            transition-delay: 1.2s;
        }
        .uicore-navigation-content .uicore-extra > div:nth-child(5) {
            transition-delay: 1.5s;
        }
        .uicore-menu li a {
            opacity: 0;
        }

        .uicore-menu li.uicore-visible > a {
            animation-name: uicoreFadeInUp, uicoreFadeIn !important;
            animation-timing-function:'.$translateEase.','. $opacityEase.';
            animation-duration: 1s;
            animation-fill-mode: forwards;
        }
    }
    ';
}
if($global_animations && $json_settings['animations_menu'] != 'none'){

    $css .= '
    @media  (min-width: 1025px) {
        ';
        if($json_settings['header_layout'] === 'center_creative'){
            $css .= '
            body:not(.elementor-editor-active) .ui-header-row1 > *,
            body:not(.elementor-editor-active) .ui-header-row2 > *{
            ';
        }else{
            $css .= '
            body:not(.elementor-editor-active) #wrapper-navbar .uicore-extra > *,
            body:not(.elementor-editor-active) .uicore-header-wrapper .uicore-branding,
            body:not(.elementor-editor-active) .uicore-header-wrapper nav .uicore-ham,
            body:not(.elementor-editor-active) .uicore-header-wrapper ul.uicore-menu > .menu-item > a {
            ';
        }
        $css .= '
        animation-delay: '.$json_settings['animations_menu_delay'].'ms;
        ';

    if($json_settings['animations_menu'] === 'fade'){
        $css .= '
            opacity: 0;
            animation-fill-mode: forwards;
            animation-duration: .6s;
            animation-name: uicoreFadeIn;
            animation-play-state: paused;
            animation-timing-function: '.$opacityEase.';
        ';
    }
    if($json_settings['animations_menu'] === 'fade down'){
        $css .= '
            opacity: 0;
            animation-fill-mode: forwards;
            animation-duration: 1s;
            animation-name: uicoreFadeInDown, uicoreFadeIn;
            animation-play-state: paused;
			animation-timing-function: '.$translateEase.','. $opacityEase.';
        ';
    }
    if($json_settings['animations_menu'] === 'fade up'){
        $css .= '
            opacity: 0;
			animation-fill-mode: forwards;
			animation-duration: 1s;
			animation-name: uicoreFadeInUp, uicoreFadeIn;
			animation-play-state: paused;
			animation-timing-function: '.$translateEase.','. $opacityEase.';
        ';
    }
    if( $json_settings['animations_menu_duration'] === 'fast'){
        $css .= '
            animation-duration: .6s;
        ';
    }
    if( $json_settings['animations_menu_duration'] === 'slow'){
        $css .= '
            animation-duration: 2s;
        ';
    }
    $css .= '}
    }';
}

//Sumbenu animations
if($global_animations && $json_settings['animations_submenu'] != 'none'){

    $css .= '
    @media  (min-width: 1025px) {
        .uicore-navbar ul.sub-menu {';

    if($json_settings['animations_submenu'] === 'fade'){
        if( $json_settings['animations_submenu_duration'] === 'fast'){
            $css .= 'transition: opacity 0.1s cubic-bezier(1, 0.4, 0.5, 0.9),transform 0.3s cubic-bezier(0.4, -0.37, 0.03, 1.29);';
        }elseif( $json_settings['animations_submenu_duration'] === 'slow'){
            $css .= 'transition: opacity 0.5s cubic-bezier(0.68, 0.57, 0.6, 0.92), transform 0.8s cubic-bezier(0.47, 0.4, 0.43, 1);';
        }else{
            $css .= 'transition: opacity 0.3s;';
        }
    }
    if($json_settings['animations_submenu'] === 'fade down' || $json_settings['animations_submenu'] === 'website blur'){
        $css .= '
        transform: translate3d(0,-18px,0);
        ';
        if( $json_settings['animations_submenu_duration'] === 'fast'){
            $css .= 'transition: opacity 0.2s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.2s cubic-bezier(0.1, 0.76, 0.37, 1.19);';
        }elseif( $json_settings['animations_submenu_duration'] === 'slow'){
            $css .= 'transition: opacity 0.6s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.6s cubic-bezier(0.39, 0.56, 0.32, 1.21);';
        }else{
            $css .= 'transition: opacity 0.2s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.4s cubic-bezier(0.1, 0.76, 0.37, 1.19);';
        }
    }
    if($json_settings['animations_submenu'] === 'fade up'){
        $css .= '
        transform: translate3d(0,18px,0);
        ';
        if( $json_settings['animations_submenu_duration'] === 'fast'){
            $css .= 'transition: opacity 0.2s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.2s cubic-bezier(0.39, 0.56, 0.32, 1.21);';
        }elseif( $json_settings['animations_submenu_duration'] === 'slow'){
            $css .= 'transition: opacity 0.6s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.6s cubic-bezier(0.39, 0.56, 0.32, 1.21);';
        }else{
            $css .= 'transition: opacity 0.3s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.4s cubic-bezier(0.1, 0.76, 0.37, 1.19);';
        }
    }
    if($json_settings['animations_submenu'] === 'scale down'){
        $css .= '
        transform-origin: top center;
        transform: scaleY(0);
        ';
        if( $json_settings['animations_submenu_duration'] === 'fast'){
            $css .= 'transition: opacity 0.2s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.2s cubic-bezier(0.1, 0.76, 0.37, 1.19);';
        }elseif( $json_settings['animations_submenu_duration'] === 'slow'){
            $css .= 'transition: opacity 0.5s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.5s cubic-bezier(0.1, 0.76, 0.37, 1.19);';
        }else{
            $css .= 'transition: opacity 0.3s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.3s cubic-bezier(0.1, 0.76, 0.37, 1.19);';
        }
    }
    if($json_settings['animations_submenu'] === 'fade left'){
        $css .= '
        transform: translate3d(-18px,0,0);
        ';
        if( $json_settings['animations_submenu_duration'] === 'fast'){
            $css .= 'transition: opacity 0.2s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.2s cubic-bezier(0.39, 0.56, 0.32, 1.21);';
        }elseif( $json_settings['animations_submenu_duration'] === 'slow'){
            $css .= 'transition: opacity 0.6s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.6s cubic-bezier(0.39, 0.56, 0.32, 1.21);';
        }else{
            $css .= 'transition: opacity 0.2s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.4s cubic-bezier(0.47, 0.4, 0.43, 1);';
        }
    }
    if($json_settings['animations_submenu'] === 'rotate'){
        $css .= '
        transform-origin: top center;
        transform: rotateX(-90deg);
        ';
        if( $json_settings['animations_submenu_duration'] === 'fast'){
            $css .= 'transition: opacity 0.1s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.2s cubic-bezier(0.62, 0.19, 0.2, 1.55);';
        }elseif( $json_settings['animations_submenu_duration'] === 'slow'){
            $css .= 'transition: opacity 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.6s cubic-bezier(0.39, 0.56, 0.32, 1.21);';
        }else{
            $css .= 'transition: opacity 0.2s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.4s cubic-bezier(0.62, 0.19, 0.2, 1.55);';
        }
    }
    if($json_settings['animations_submenu'] === 'scale bg'){
        $css .= '
        transform: translate3d(0,10px,0);
        ';
        if( $json_settings['animations_submenu_duration'] === 'fast'){
            $css .= 'transition: opacity 0.1s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.15s cubic-bezier(0.39, 0.56, 0.32, 1.21);';
        }elseif( $json_settings['animations_submenu_duration'] === 'slow'){
            $css .= 'transition: opacity 0.5s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.6s cubic-bezier(0.39, 0.56, 0.32, 1.21);';
        }else{
            $css .= 'transition: opacity 0.2s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.25s cubic-bezier(0.1, 0.76, 0.37, 1.19);';
        }
    }
    $css .= '}
    }';
}
if($json_settings['animations_submenu'] === 'scale bg'){
    $css .= '
    .uicore-navbar nav.uicore ul.sub-menu{
        box-shadow: none!important;
        padding-top:0;
        margin-top: -15px;
    }
    ';
}elseif($json_settings['animations_submenu'] === 'website blur'){
    //the initial transition is set via js to avoid the blur effect on page load (header-js.php)
    $css .= '
    .uicore-navbar nav.uicore ul.sub-menu, .uicore-navbar nav.uicore ul.sub-menu:not(.uicore-megamenu){
        box-shadow:none!important;
    }
    .uicore-blur-on #content{
        transition:all 0.7s cubic-bezier(0.55, 0.63, 0.37, 1.04)!important;
        filter: blur(30px) saturate(2);
        transform: scale(0.96) translateY(4.6875rem);
        transform-origin: 50% 0;
    }
    ';
}


//Mobile menu animations
$css .= '
@media  (max-width:'.$json_settings['mobile_breakpoint'].'px) {
    ';

if($json_settings['mmenu_animation'] === 'slide on top'){
    $css .= '
       .uicore-navigation-wrapper {
            transform: translate3d(-100%,0,0);
            transition: transform 0.3s cubic-bezier(0.31, 0.87, 0, 0.98);
        }
        .uicore-navigation-wrapper .uicore-toggle {
            opacity: 0;
        }
        .uicore-body-content {
            z-index: 2;
        }
        .uicore-mobile-nav-show .uicore-navigation-wrapper {
				transform: translate3d(0,0,0);
				z-index: 99;
				pointer-events: all;
        }
        .uicore-mobile-nav-show .uicore-navigation-wrapper nav {
            opacity: 1 !important;
            transition: all 0.2s $translateEase 0.4s;
            -webkit-transition: all 0.2s $translateEase 0.4s;
            -moz-transition: all 0.2s $translateEase 0.4s;
            -ms-transition: all 0.2s $translateEase 0.4s;
            -o-transition: all 0.2s $translateEase 0.4s;
        }
        .uicore-mobile-nav-show .uicore-navigation-wrapper .uicore-toggle {
            opacity: 1;
        }

    ';
}
if($json_settings['mmenu_animation'] === 'slide along'){
    $css .= '

       .uicore-navigation-wrapper {
            transform: translate3d(-60%,0,0);
            z-index: 0;
            transition: transform 0.55s cubic-bezier(0.31, 0.87, 0, 0.98);
        }
        .uicore-body-content {
            transition: transform 0.55s cubic-bezier(0.31, 0.87, 0, 0.98);
			z-index: 2;
			transform: translate3d(0,0,0);
			box-shadow: -25px 0 38px -28px rgb(0 0 0 / 25%);
        }
        .uicore-mobile-nav-show .uicore-navigation-wrapper nav {
            opacity: 1 !important;
            transition: all 0.2s $translateEase 0.25s;
        }
        .uicore-mobile-nav-show .uicore-body-content{
            transform: translate3d(107vw,0,0);
        }

    ';
}
if($json_settings['mmenu_animation'] === 'expand'){
    $css .='
    .uicore-mobile-menu-wrapper{
        transition: all 0.3s cubic-bezier(0.31, 0.87, 0, 0.98);
        max-height: 0;
    }
    ';
}

$css .= '
.uicore-mobile-nav-show .uicore-navigation-content {
    opacity: 1;
}
.uicore-mobile-nav-show .uicore-extra {
    opacity: 1 !important;
    transition: all 0.2s '.$translateEase.' 0.25s;
}
.uicore-mobile-nav-show .uicore-navigation-wrapper {
    transform: translate3d(0,0,0);
    pointer-events: all;
    opacity: 1;
}
';

$css .= '}';

if($json_settings['header_layout'] === 'ham center' || $json_settings['header_layout'] === 'ham creative') {
	$css .='
		.uicore-is-ham .uicore-navigation-content .uicore-menu .menu-item-has-children>a:after {
			line-height: inherit !important;
		}
	';
}
//mobile menu layouts
if($json_settings['mobile_layout'] === 'center') {
    $css .= '
    @media  (max-width: '.$json_settings['mobile_breakpoint'].'px) {
        .uicore-header-wrapper > nav{
            display: grid!important;
            grid-template-columns: 1fr auto 1fr;
            gap: 10px;
        }
        .uicore-mobile-head-right {
            justify-self: flex-end;
        }
        .uicore-navbar .uicore-branding{
            padding-right: 0!important;
        }
    }';
}
if($json_settings['mobile_extra_content'] === 'cta') {
    $css .= '
    @media  (max-width: '.$json_settings['mobile_breakpoint'].'px) {
        .uicore-header-wrapper > nav > div .uicore-cta-wrapper a{
            padding: 8px 14px;
            font-size:13px;
            line-height:16px
        }
    }';
}


//Submenu icon/img/description layout

$css .='
.uicore-menu .sub-menu .ui-has-description > a{
        display: grid!important;
        grid-template-columns: auto 1fr;
        grid-template-rows: auto;
        align-items: center;
  }
  .uicore-menu .sub-menu .ui-has-description > a img,
  .uicore-menu .sub-menu .ui-has-description > a .ui-svg-wrapp{
       grid-area: 1 / 1 / 3 / 2;
        max-height:2.6em
  }
  .uicore-menu .sub-menu .ui-has-description>a .ui-svg-wrapp {
      height: 100%;
      width: 100%;
      position: relative;
      min-height: 38px;
      min-width: 53px;
      display: flex;
      justify-content: center;
      align-items: center;
      border-right: solid 15px transparent;
  }
  .uicore-menu .sub-menu .ui-has-description > a .ui-svg-wrapp:before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: var(--ui-radius);
      background-color: currentColor;
    opacity: 0.1;
  }
  .uicore-menu .menu-item-has-children ul .custom-menu-desc{
    margin-top:0;
    max-width: 300px;
    grid-area: 2 / 2 / 2 / 3;
  }
';

if($json_settings['menu_interaction'] === 'underline'){
    $css .= '
    @media only screen and (min-width: ' . $br_points['md'] . 'px) {
        .uicore-menu .ui-menu-item-wrapper {
            position: relative;
        }
        .uicore-menu .ui-menu-item-wrapper:before {
            content: \'\';
            position: absolute;
            z-index: -1;
            bottom: -5px;
            width: 100%;
            height: 2px;
            opacity: .75;
            transform: scale3d(0, 1, 1);
            transform-origin: 100% 50%;
            transition: transform 0.3s;
            transition-timing-function: cubic-bezier(0.2, 1, 0.3, 1);
            background: var(--uicore-header--menu-effect-bg);
        }
        .uicore-menu li.current-menu-item>a .ui-menu-item-wrapper:before {
            transform: scale3d(1, 1, 1);
        }
        .uicore-menu li.menu-item:hover>a .ui-menu-item-wrapper:before {
            transform: scale3d(1, 1, 1);
            transform-origin: 0% 50%;
            transition-timing-function: ease;
        }
        .uicore-nav-menu a.uicore-social-icon:before {
            font-size: 90%;
        }
        .uicore-nav-menu a.uicore-social-icon,
        .uicore-social-icon {
            padding: 0 10px !important;
        }
        .uicore-extra .uicore-custom-area:not(:last-child):after {
            content: "";
            width: 2px;
            height: calc(var(--uicore-header--menu-typo-s) * 1.5);
            background: var(--uicore-header--menu-effect-bg);
            margin-left: 25px;
            align-self: center;
            opacity: .3;
        }
        .uicore-menu li li:not(ui-has-description) > a .ui-menu-item-wrapper:before {
            content:none;
        }
    }
    ';
}elseif($json_settings['menu_interaction'] === 'button' || $json_settings['menu_interaction'] === 'magnet button'){
    $css .= '
    @media only screen and (min-width: ' . $br_points['md'] . 'px) {
        .uicore-menu li > a{
            position:relative;
        }
        .uicore-menu li > a:before{
            content: \'\';
            position: absolute;
            left: 0;
            right: 0;
            top: 51%;
            height: 2.4rem;
            background-color: transparent;
            border-radius: var(--ui-radius);
            z-index: -1;
            transform: translateY(-50%);
            transition: background-color .3s ease;
        }
        .uicore-simple-megamenu:not(.uicore-megamenu)>.sub-menu>li.menu-item-has-children>a:before{
            content: unset;
        }
        .uicore-menu .sub-menu li > a:before{
            left: 16px;
            right: 16px;
            top: 0px;
            bottom: 0px;
            transform: unset;
            height: auto;
        }
        .uicore-menu li.current-menu-item > a:before {
            background-color: var(--uicore-header--menu-effect-bg);
        }
        .uicore-menu ul.sub-menu{
            border-radius: clamp(0px, var(--ui-radius), 10px);
            box-shadow: 8px 25px 65px -10px rgb(0 0 0 / 10%) !important;
        }
        .uicore-navbar .uicore ul.sub-menu:not(.uicore-megamenu) li a {
            padding: 12px 25px;
        }
        .uicore-menu li>a svg {
            margin-right: 0;
        }
        .uicore-navbar nav.uicore ul.sub-menu:not(.uicore-megamenu) {
            padding: 15px 0;
        }
    }
    ';
    if($json_settings['menu_interaction'] === 'button' ){
        $css .= '
        .uicore-menu li.menu-item:hover > a:before {
            background-color: var(--uicore-header--menu-effect-bg);
        }
        ';
    }
	if ( $no_padding_right ) {
	    // $css .=
	    //     '
		// 	#wrapper-navbar .uicore-nav ul.uicore-menu > li:last-child:not(.menu-item-has-children) a:before {
		// 		right:calc(10px - var(--uicore-header--menu-spaceing))
		// 	}';
        $css .= '
        .uicore-menu > li:last-child > a:before{
            right: calc(-1 * var(--uicore-header--menu-spaceing));
        }
        ';
	}
}elseif($json_settings['menu_interaction'] === 'text flip'){
    $css .= '
    @media only screen and (min-width: ' . $br_points['md'] . 'px) {
        .ui-flip-anim-wrapp .ui-menu-item-wrapper:nth-child(2) {
            display: inline-block;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, 90%);
            opacity: 0;
        }
        .ui-anim-flip:hover > a .ui-flip-anim-wrapp .ui-menu-item-wrapper:nth-child(2) {
            transform: translate(-50%, 70%);
            opacity: 1;
        }
        .uicore-menu .ui-menu-item-wrapper,
        .ui-flip-anim-wrapp {
            display: inline-block;
            line-height: 1;
            position:relative;
        }
        .ui-anim-flip:hover > a .ui-flip-anim-wrapp {
            transform: translateY(-100%);
        }
        .ui-anim-flip:hover > a .ui-menu-item-wrapper:nth-child(1) {
            opacity: 0;
        }
        .ui-anim-flip > a .ui-flip-anim-wrapp, .ui-anim-flip > a .ui-flip-anim-wrapp .ui-menu-item-wrapper  {
            transition: opacity .4s, transform .7s;
            transition-timing-function: cubic-bezier(0.15, 0.85, 0.31, 1);
        }
    }
    @media only screen and (max-width: ' . $br_points['md'] . 'px) {
        .ui-flip-anim-wrapp .ui-menu-item-wrapper:nth-child(2) {
            opacity: 0 !important;
        }
    }
    ';
}
if($json_settings['menu_interaction'] === 'magnet button'){
    $css .= '
    @media only screen and (min-width: ' . $br_points['md'] . 'px) {
        ul.uicore-menu::before {
            --transition: 0.18s;
            content: "";
            position: fixed;
            pointer-events: none;
            top: var(--item-active-y);
            left: var(--item-active-x);
            height: var(--item-active-height);
            width: var(--item-active-width);
            opacity: var(--intent, 0);
            z-index: -1;
            border-radius: var(--item-active-radius);
            background: var(--uicore-header--menu-effect-bg, currentColor);
            transition:
                all var(--transition),
                top var(--transition),
                left var(--transition),
            height var(--transition),
            opacity var(--transition),
            color var(--transition),
            width var(--transition);
        transition-timing-function:cubic-bezier(0.53, 0.67, 0.45, 0.91);
        }
        ul.uicore-menu:has(> li:not(.current-menu-item) > a:is(:focus-visible, :hover)) {
            --intent: 1;
        }
    }
    ';
}
$css .='
.container-width .uicore-megamenu>.elementor,
.custom-width .uicore-megamenu>.elementor {
    width: 100%;
}
';
$css .='
ul.uicore-menu {
    --uicore-header--menu-effect-bg: ' . $this->color($json_settings['menu_interaction_color']) . ';
}
';


if($json_settings['submenu_trigger'] === 'click' && strpos($json_settings['header_layout'], 'ham') == false){
    $css .= "
    .uicore-navbar nav .menu-item-has-children .sub-menu .menu-item-has-children:hover>.sub-menu,
    .uicore-navbar nav.uicore ul.sub-menu, .uicore-navbar nav.uicore ul.sub-menu:not(.uicore-megamenu){
      display:none;
    }
    ";
}