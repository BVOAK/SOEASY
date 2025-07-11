<?php
defined("ABSPATH") || exit();
//INCLUDED IN CLASS JS
// TODO: Update script to handle the mobile versions

$js .= "
window.addEventListener('DOMContentLoaded', () => {
    // Listen to click on swatches list
    jQuery('.uicore-sidebar-toggle').on('click', function() {

        if (jQuery('.uicore-sidebar').hasClass('sidebar-hidden')) {
            // Toggle the sidebar
            if(jQuery('.uicore-sidebar-top')){
                jQuery('.uicore-sidebar').slideToggle(350);
                jQuery('.uicore-sidebar').removeClass('sidebar-hidden');
                
            }else{
                jQuery('.uicore-sidebar').animate({width: '25%'}, 300).removeClass('sidebar-hidden');
            }

            // Update icon style
            jQuery('.uicore-sidebar-toggle span.top').css('transform', 'translateX(8px)');
            jQuery('.uicore-sidebar-toggle span.bottom').css('transform', 'translateX(0)');

            // Update text
            jQuery('.text-wrap .hide').css('display', 'block');
            jQuery('.text-wrap .show').css('display', 'none');

            // Update content section widget
            jQuery('.uicore-archive').css('width', '100%');

            return;
        }

        // Hide the sidebar;
        if(jQuery('.uicore-sidebar-top')){
            jQuery('.uicore-sidebar').slideToggle(350);
             setTimeout(() => {
                jQuery('.uicore-sidebar').addClass('sidebar-hidden');
            }, 100);
        }else{
            jQuery('.uicore-sidebar').animate({width: '0'}, 300).addClass('sidebar-hidden');
        }

        // Update icon style
        jQuery('.uicore-sidebar-toggle span.top').css('transform', 'translateX(0)');
        jQuery('.uicore-sidebar-toggle span.bottom').css('transform', 'translateX(8px)');

        // Update text
        jQuery('.text-wrap .hide').css('display', 'none');
        jQuery('.text-wrap .show').css('display', 'block');

        // Update content section widget
        jQuery('.uicore-archive').css('width', '100%');
    });
});
";