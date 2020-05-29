// homeurl = website url
jQuery(document).ready(function() {
    jQuery('.colorpick').each(function() {
        var $this = jQuery(this);
        jQuery(this).colpick({
            color: $this.val().substr(1, 7),
            onChange: function(hsb, hex, rgb, el, bySetColor) {
                jQuery(el).val('#' + hex);
            }
        });
    });
    jQuery('input[name="rolesAllowed"][value=""]').change(function(){
       if(jQuery(this).is(':checked')){
           jQuery('input[name="rolesAllowed"]:not([value=""])').removeProp('checked');
       } 
    });
    jQuery('input[name="rolesAllowed"]:not([value=""])').change(function(){
       if(jQuery(this).is(':checked')){
           jQuery('input[name="rolesAllowed"][value=""]').removeProp('checked');
       } 
    });
});
function fhpc_chooseItemTarget() {
    if (jQuery('#form_item input[name=page]').length > 0 && jQuery('#form_item input[name=page]').val().length > 3) {
        if (jQuery('#form_item input[name=page]').val().indexOf('http') < 0) {
            homeurl = homeurl + jQuery('#form_item input[name=page]').val();
        } else {
            homeurl = jQuery('#form_item input[name=page]').val();
        }
    }
    if (jQuery('#onAdmin').val() == '1') {
        homeurl = adminurl;
    }
    homeurl = homeurl.replace('[ANY]','');
    $frame = jQuery('<iframe id="fhpc_selectDomFrame" src="' + homeurl + '"></iframe>');
    jQuery('body').append($frame);
    $panel = jQuery('<div id="fhpc_selectDomPanel"></div>');
    jQuery('body').append($panel);
    $panel.html('Loading ...');
    fhpc_selectionPanelText();
}
var fhpc_modeSelection = false;
function fhpc_activeItemSelectionMode() {
    jQuery('#fhpc_selectDomFrame').get(0).contentWindow.fhpc_changeSelectionMode(true);
    $panel = jQuery('#fhpc_selectDomPanel');
    $panel.html('<p>Click on the desired element</p>');
}

function fhpc_itemSelected(el) {
    $panel = jQuery('#fhpc_selectDomPanel');
    $panel.html('<h3>Element selected</h3>');

    var elementIdentified = getPath(el);
    $panel.append('<p>The desired element is it the one that shines ?</p>');
    $panel.append('<p><a href="javascript:" class="button-primary" onclick="fhpc_confirmElementSelected(\'' + elementIdentified + '\');">Yes</a>' +
            '<a href="javascript:" class="button-secondary" onclick="fhpc_activeItemSelectionMode();">No</a></p>');
}

function identifyElement(el) {
    var identification = "";
    if (jQuery(el).attr('id')) {
        identification = jQuery(el).attr('id');
    } else {
        identification = getPath(el);
    }
    return identification;
}

function getPath(el) {
    var path = '';
    if (jQuery(el).length > 0 && typeof (jQuery(el).prop('tagName')) != "undefined") {
        if (!jQuery(el).attr('id') ||jQuery(el).attr('id').substr(0,9) == 'ultimate-' ) {
            path = '>' + jQuery(el).prop('tagName') + ':nth-child(' + (jQuery(el).index() + 1) + ')' + path;
            path = getPath(jQuery(el).parent()) + path;
        } else {
            path += '#' + jQuery(el).attr('id');
        }
    }
    return path;
}

function fhpc_selectionPanelText() {
    $panel = jQuery('#fhpc_selectDomPanel');
    $panel.html('<h3>Select a static element</h3>');
    $panel.append('<p>Browse normally through your site. When you locate the target element, click the "Define Target" button, Then click on the element.</p>');
    $panel.append('<p><a href="javascript:" class="button-primary" onclick="fhpc_activeItemSelectionMode();">Define Target</a></p>');
}
function fhpc_confirmElementSelected(path) {
    var page = jQuery('#fhpc_selectDomFrame').get(0).contentWindow.document.location.href;
    if (page.substr(page.length-2,2) == '//'){
        page = page.substr(0,page.length-1);
    }
    jQuery('input[name=page]').val(page);
    jQuery('#domElement').val(path);
    jQuery('#domElement').parent().children('span').html('Element selected');
    jQuery('#fhpc_selectDomFrame,#fhpc_selectDomPanel').remove();
}