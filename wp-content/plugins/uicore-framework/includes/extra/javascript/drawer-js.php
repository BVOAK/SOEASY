<?php
defined('ABSPATH') || exit();
//INCLUDED IN CLASS JS

$toggle = $settings['header_sd_toggle'];

$js .= '
jQuery(document).on("click", ".uicore-drawer-toggle, .ui-header-drawer .ui-sd-backdrop", function() {
    ui_show_sd()
});
';

if($toggle === 'hover'){
    $js .= '
    jQuery(document).on("mouseenter", ".uicore-drawer-toggle", function() {
        ui_show_sd()
    });
    jQuery(document).on("mouseleave", ".ui-drawer-content", function() {
        ui_show_sd()
    });
 ';
}

//Toggle Function (common)
$js .= '
function ui_show_sd(elClassName=".ui-header-drawer"){
    jQuery(elClassName).toggleClass("ui-active");
}
';

//used for filters drawer
$js .= '
jQuery(document).on("click", ".ui-drawer-toggle, .ui-filters-drawer .ui-sd-backdrop", function() {
    ui_show_sd(\'.ui-filters-drawer\')
});
';