/*
validacion form contacto
*/
var jStrap = function(){
    //Mail
    this.isMail = function(sVal){
        var ep = /^(([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+)?$/g;
        return ( ep.test(sVal) && (sVal != '') );
    };
    //Vacio
    this.isEmpty = function(sVal){
        var ep = /\s+$/;
        return ( (sVal.length != 0) || ep.test(sVal) );
    };
    //Num Tlf
    this.onlyNumTlf = function(oField){
        return $(oField).each( function(){
            var t = $(this),
            isShift = false;
            t.keypress( function(e){
                var key = e.keyCode || e.charCode || e.which || window.e ;						
                if(key == 16) isShift = true;							
                return ( key == 8 || key == 9 || key == 32 ||
                    key == 37 || key == 39 ||
                    key == 40 || key == 41 || key == 42 || 
                    key == 45 || key == 35 ||
                    ( key == 48 && isShift == false ) ||
                    ( key == 49 && isShift == false ) ||
                    ( key == 50 && isShift == false ) ||
                    ( key == 51 && isShift == false ) ||
                    ( key == 52 && isShift == false ) ||
                    ( key == 53 && isShift == false ) ||
                    ( key == 54 && isShift == false ) ||
                    ( key == 55 && isShift == false ) ||
                    ( key == 56 && isShift == false ) ||
                    ( key == 57 && isShift == false ) 						
                );						
            });
            t.bind('paste', function(){
                setTimeout(function(){
                    var value = t.val();
                    var newValue = value.replace(/[^0-9-#-*-(-)--]/g,'');
                    t.val(newValue);
                }, 0);
           });				
        });       
    };
    //Num Tipo de documento
    this.onlyNumDoc = function(a){
        return $(a).each( function(){
            var t = $(this),
            isShift = false;
            t.keypress( function(e){				
                    var key = e.keyCode || e.charCode || e.which || window.e ;					
                    if(key == 16) isShift = true;						
                    return ( key == 8 || key == 9 || key == 32 ||
                             key == 37 || key == 39 ||
                            ( key == 48 && isShift == false ) ||
                            ( key == 49 && isShift == false ) ||
                            ( key == 50 && isShift == false ) ||
                            ( key == 51 && isShift == false ) ||
                            ( key == 52 && isShift == false ) ||
                            ( key == 53 && isShift == false ) ||
                            ( key == 54 && isShift == false ) ||
                            ( key == 55 && isShift == false ) ||
                            ( key == 56 && isShift == false ) ||
                            ( key == 57 && isShift == false ) 						
                            );							
            });
            t.bind('paste', function(){
                setTimeout(function() {
                    var value = t.val();
                    var newValue = value.replace(/[^0-9]/g,'');
                    t.val(newValue);
                }, 0);
            });		
        });
    };
    //Num Tipo de documento
    this.onlyNum = function(a){
        return $(a).each( function(){
            var t = $(this),
            isShift = false;
            t.keypress( function(e){				
                    var key = e.keyCode || e.charCode || e.which || window.e ;
                    if(key == 16) isShift = true;						
                    return ( 
                            ( key == 8 ) || ( key == 9 ) || ( key == 13 ) ||    
                            ( key == 37 ) || ( key == 39 ) ||    
                            ( key == 46 && isShift == false ) ||
                            ( key == 48 && isShift == false ) ||
                            ( key == 49 && isShift == false ) ||
                            ( key == 50 && isShift == false ) ||
                            ( key == 51 && isShift == false ) ||
                            ( key == 52 && isShift == false ) ||
                            ( key == 53 && isShift == false ) ||
                            ( key == 54 && isShift == false ) ||
                            ( key == 55 && isShift == false ) ||
                            ( key == 56 && isShift == false ) ||
                            ( key == 57 && isShift == false ) 						
                            );							
            });
            t.bind('paste', function(){
                setTimeout(function() {
                    var value = t.val();
                    var newValue = value.replace(/[^0-9]/g,'');
                    t.val(newValue);
                }, 0);
            });		
        });
    };
    //maxlenght Doc, Carnet Extranjeria
    this.maxLenghtDoc = function(selectN){
        var oSelect = $(selectN),
        oInput = oSelect.next();
        oSelect.bind('change', function(){
            var t = $(this),
            string = (t.val()).split('#'),
            numMax = string[1],
            inputVal = oInput.val();
            oInput.removeAttr('maxlength').attr('maxlength', numMax);
            oInput.val(inputVal);
            oInput.focus();
        });				
        oInput.bind('keyup click blur focus change paste', function(e){
            var t = $(this),
            string = (t.siblings('select').val()).split('#'),
            numMax = parseInt(string[1]),
            valueArea;
            var key = e.keyCode || e.charCode || e.which || window.e ;
            var length = t.val().length;
            if( length > numMax ) {
                valueArea = t.val().substring(numMax, '') ;
                oInput.val(valueArea);
            }
        });						
    };
    //select value requirdo
    this.selectReq = function(sVal){    
        return ( sVal != '' );
    };
    //clear form    
    this.clearForm = function(obj){
        var defaults = {
            form : 'form',
            clases : 'ready good bad',
            classResponse : 'response'
        };
        var objNew = $.extend({}, defaults, obj ); 
        $(objNew.form).get(0).reset();
        $('input, select, textarea', objNew.form).removeClass(objNew.clases);
        $('input.placeH', objNew.form).addClass('cGray');
        $(objNew.form + ' .' + objNew.classResponse).removeClass(objNew.clases).text('');
    };
    //textarea Maxlenght
    this.pasteMaxlength = function(sFiled){   
        $(sFiled).bind('keyup click blur focus change paste', function(e){
            var t = $(this);
            setTimeout(function(){                
                var chars = t.val(), 
                charsSize = t.val().length,
                size = parseInt(t.attr('maxlength'));
                if( charsSize > size ){
                    valField = chars.substring(size, 0);
                    t.val(valField);
                }
            },0);
        });
    };      
    //end
};
$(function(){
    var jstrap = new jStrap();        
    var formContact = {
        validFields : function(obj){
            //function Valid Text
            function validText(inputTxt, type, msjValid){
                $(inputTxt).blur(function(){
                    var t = $(this),
                    resp = t.parents('.block').find('.response');
                    switch(type){
                    case 'text':
                        if(jstrap.isEmpty(t.val())){
                            t.addClass('ready');
                            resp
                            .text(msjValid.good)
                            .addClass('good')
                            .removeClass('def bad');
                        }else{
                            t.removeClass('ready');
                            resp
                            .text(t.attr('errmsg'))
                            .addClass('bad')
                            .removeClass('def good');
                        }
                        break;
                    case 'email':
                        if(jstrap.isMail(t.val())){
                            t.addClass('ready');
                            resp
                            .text(msjValid.good)
                            .addClass('good')
                            .removeClass('def bad');
                        }else{
                            t.removeClass('ready');
                            resp
                            .text(t.attr('errmsg'))
                            .addClass('bad')
                            .removeClass('def good');
                        }                      
                        break;
                    case 'documento':
                        if( t.val().length >= parseInt(t.attr('maxlength')) ){
                            t.addClass('ready');
                            resp
                            .text(msjValid.good)
                            .addClass('good')
                            .removeClass('def bad');
                        }else{
                            t.removeClass('ready');
                            resp
                            .text(t.attr('errmsg'))
                            .addClass('bad')
                            .removeClass('def good');
                        }
                        break;
                    }
                }).keyup(function(){
                    var t = $(this),                
                    resp = t.parents('.block').find('.response');
                    
                    switch(type){
                    case 'text':
                        if(t.val().length >= obj.params.minChar){
                            t.addClass('ready'); 
                            resp
                            .text(msjValid.def)
                            .addClass('def')
                            .removeClass('bad good');
                        }else{
                            t.removeClass('ready');
                            resp
                            .text(t.attr('errmsg'))
                            .addClass('bad')
                            .removeClass('def good');
                        }                      
                        break;
                    case 'email':
                        if(jstrap.isMail(t.val())){
                            t.addClass('ready');
                            resp
                            .text(msjValid.good)
                            .addClass('good')
                            .removeClass('def bad');
                        }else{
                            t.removeClass('ready');
                            resp
                            .text(msjValid.def)
                            .addClass('def')
                            .removeClass('bad good');
                        }                        
                        break;
                    case 'documento':
                        if( t.val().length >= parseInt(t.attr('maxlength')) ){
                            t.addClass('ready'); 
                            resp
                            .text(msjValid.def)
                            .addClass('def')
                            .removeClass('bad good');
                        }else{
                            t.removeClass('ready');
                            resp
                            .text(t.attr('errmsg'))
                            .addClass('bad')
                            .removeClass('def good');
                        }                      
                        break;                        
                    }                     
 
                });  
            }            
            //Nombre                        
            validText(obj.fields[0], 'text', {
                good : obj.msjs.nombre.good,
                def : obj.msjs.nombre.def
            });
            //Apellidos
            validText(obj.fields[1], 'text', {
                good : obj.msjs.apellido.good,
                def : obj.msjs.apellido.def
            });
            //Tlf
            jstrap.onlyNumTlf(obj.fields[3]);
            validText(obj.fields[3], 'text', {
                good : obj.msjs.tlf.good,
                def : obj.msjs.tlf.def
            });
            //N documento
            jstrap.maxLenghtDoc($(obj.fields[2]).prev());
            jstrap.onlyNumDoc(obj.fields[2]);
            validText(obj.fields[2], 'documento', {
                good : obj.msjs.nDoc.good,
                def : obj.msjs.nDoc.def
            });            
            //Mensaje
            validText(obj.fields[6], 'text', {
                good : obj.msjs.mensaje.good,
                def : obj.msjs.mensaje.def
            });           
            //Mail
            validText(obj.fields[4], 'email', {
                good : obj.msjs.email.good,
                def : obj.msjs.email.def
            });            
            //Submit
            $(obj.fields[8]).bind('click', function(e){
                e.preventDefault();
                var t = $(this),
                readys = $(obj.form + ' .ready'),
                fields = $(obj.form + ' .fields'),
                iptCaptcha = $(obj.fields[7]),
                validCaptcha = false;
                if( iptCaptcha.size()>0 ){
                 validCaptcha = jstrap.isEmpty(iptCaptcha.val());
                }else{
                  validCaptcha = true;  
                }
                //field Captcha clear
                 $('#recaptcha_table .response').remove();
                //if    
                if( (readys.size() >= obj.params.limit) &&
                validCaptcha == true ){
                    //submir
                    $(obj.form).submit();
                }else{
                    //errors
                    fields
                    .not('.ready, :disabled')
                    .removeClass('ready')
                    .parents('.block')
                    .find('.response')
                    .removeClass('def good')
                    .addClass('bad')
                    .text(obj.msjs.defecto);                     
                    //captcha
                    if(iptCaptcha.size()>0){
                        if( jstrap.isEmpty(iptCaptcha.val()) == false ){
                            $('#recaptcha_table .recaptcha_input_area')
                            .append('<span class="response bad">' + obj.msjs.defecto + '</span>');
                        }
                    }
                }          
            });            
        }
    };
    formContact.validFields({
     form : '#formContactApt',   
     fields : [
         '#fNameCT',
         '#fApellidosCT',
         '#fDocCT',
         '#fTlfCT',
         '#fMailCT',
         '#fSubjectCT',
         '#fMsjCT',
         '#recaptcha_response_field',
         '#MictaSendbtn'
     ],
     params : {
        minChar : 1,
        limit : 7
     },
     msjs : {
         defecto : 'Campo requerido.',
         nombre : {
             good : 'El nombre es correcto.',
             def : 'Ingrese nombre correcto.'
         },
         apellido : {
             good : 'El apellido es correcto.',
             def : 'Ingrese apellido correcto.'
         },
         nDoc : {
             good : 'El n# de documento es correcto.',
             def : 'Ingrese n# de documento correcto.'
         },
         tlf : {
             good : 'El n# de telefono es correcto.',
             def : 'Ingrese n# de telefono correcto.'
         },
         email : {
             good : 'El email es correcto.',
             def : 'Ingrese email correcto.'
         },
         mensaje : {
             good : 'El mensaje es correcto.',
             def : 'Ingrese mensaje correcto.'
         }
     }
    });
    jstrap.pasteMaxlength('#mensaje');
});

