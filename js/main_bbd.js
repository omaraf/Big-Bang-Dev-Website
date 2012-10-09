$.extend({
  getUrlVars: function(){
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
      hash = hashes[i].split('=');
      vars.push(hash[0]);
      vars[hash[0]] = hash[1];
    }
    return vars;
  },
  getUrlVar: function(name){
    return $.getUrlVars()[name];
  }
});


$(window).load(function() {
	var destino = $.getUrlVar('target');
	if (destino == 'servicios'){
		$('#menu-link-servicios').click();
	}
	if (destino == 'portafolio'){
		$('#menu-link-portafolio').click();
	}


});

jQuery(function ($) {	
	$("#button_submit").click(function(){
		$('#form_contacto').submit();
	});
	
    $('#form_contacto').ajaxForm({
        dataType: 'json',
        beforeSubmit: before_submit,
        success: success
    });
});

function before_submit(formData, jqForm, options) {
    // si ya mostre algun error, lo borro
    $('.errores').remove();
    $('.successfull').remove();
	$('input').removeClass('red_border');
	$('select').removeClass('red_border');
    
    // todo bien
    return true; 
}


function success(json) {
    if (json.code == '200') {
    $('div.successfull').remove();
        $('#form_contacto').clearForm();
         $('<div class="successfull"><p>Su mensaje ha sido enviado. Pronto nos pondremos en contacto con usted.</p></div>').insertAfter($('#form_contacto')).hide();	
        $('div.successfull').fadeIn('slow');
        $('.errores').remove();
        $('.successfull').remove();
        $('input').removeClass('red_border');
        $('select').removeClass('red_border');
	}
    else if (json.err) {
		if (json.name){
            $('<div class="errores"><p>' + json.name + '</p></div>').insertBefore($('#label_name')).hide();	
		}
		if (json.email){
		    $('<div class="errores"><p>' + json.email + '</p></div>').insertBefore($('#label_email')).hide();	
		}
		if (json.message){
		    $('<div class="errores"><p>' + json.message + '</p></div>').insertBefore($('#label_message')).hide();	
		}
		if (json.no_capth){
		    $('<div class="errores"><p>' + json.no_capth + '</p></div>').insertBefore($('#numb_capth')).hide();	
		}
        $('div.errores').fadeIn('slow');
    }
    else {
        // errores raros
        alert(json.msg);
    }
}
