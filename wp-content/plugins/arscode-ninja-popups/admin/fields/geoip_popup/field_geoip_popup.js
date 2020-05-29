jQuery(document).ready(function(){

    jQuery('.nhp-opts-geoip-popup-remove').on('click', function(){
        jQuery(this).parent().fadeOut('slow', function(){jQuery(this).remove();});
    });

    jQuery('.nhp-opts-geoip-popup-add').on('click', function(){
        var new_input = jQuery('#'+jQuery(this).attr('rel-id')+' li:last-child').clone();
        jQuery('#'+jQuery(this).attr('rel-id')).append(new_input);
        jQuery('#'+jQuery(this).attr('rel-id')+' li:last-child').removeAttr('style');
    });

});
