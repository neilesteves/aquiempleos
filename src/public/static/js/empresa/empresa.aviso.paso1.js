/*
 Empresa Modal Cuenta
 */
$( function() {
    var mUrl =  urls.mediaUrl,
    bUrl = urls.siteUrl,
    flagLinkPaso2 = false,
    sol = 'S/.',
    urlDataFlag = 0,
    cacheStatic = urls.staticCache ;
    if(cacheStatic == undefined){
    	cacheStatic = '';
    }else{
    	cacheStatic = cacheStatic;
    }
    var msgs = {
        paso1Plan : 'Debe seleccionar un plan para continuar.',
        paso1BError : 'Debe seleccionar el medio de publicación y tamaño del aviso.',
        paso1CError : 'Debe iniciar sesión para continuar.',
        paso2BError : 'La combinación seleccionada no existe.'
    };
    var avisoEmp = {
        clickBlockF : function(block1, block2, block3, block4) {
            //click bloque 2
            var blockB = $('.topTitP2, .cntBodyP2', block2),
            btnClickB = $('.iBtn2S2M', block2);
            blockB.bind('click', function(e) {
                e.preventDefault();
                btnClickB.trigger('click');
                e.stopPropagation();
            });
            //click bloque 3
            var blockC = $('.topTitP2, .cntBodyP2', block3),
            btnClickC = $('.iBtn3S2M', block3);
            blockC.bind('click', function(e) {
                e.preventDefault();
                btnClickC.trigger('click');
                e.stopPropagation();
            });
            //click bloque 4
            var blockD = $('.topTitP2, .cntBodyP2', block4),
            btnClickD = $('.iBtn4S2M', block4);
            blockD.bind('click', function(e) {
                e.preventDefault();
                btnClickD.trigger('click');
                e.stopPropagation();
            });
        },
        paso1Emp : function() {
            var sections = $('#sectionsEM, #innerSectionEmp'),
            section1 = $('#section1EM'),
            section2 = $('#section2EM'),
            section3 = $('#section3EM'),
            spped = 'middle',
            tStep1 = $('#continP1'),
            tStep2 = $('.continP2'),
            title = $('#countWordEM'),
            titlePos = title.offset().top,
            backSection2 = $('#backEmpP1'),
            primOptP1 = $('#primOptP1');
            var btnBackX = $('#backEmpXF');
            if(sections.hasClass('openSection3')) {
                $('html, body').animate({
                    scrollTop:titlePos
                }, spped);
                section1.animate({
                    'top':'-480px'
                },spped);
                section2.animate({
                    'top':'-600px'
                },spped, function() {
                    sections.animate({
                        'height':'400px'
                    },spped);
                });
                section3.animate({
                    'top':'0'
                },spped);
            }

            tStep1.click( function(e) {
                e.preventDefault();
                _funP2(titlePos, spped, section1, sections, section2);

            });
            function _funP2(titlePos, spped, section1, sections, section2) {
				
		title.html('Elija su tipo de Aviso Econ&oacute;mico :');
		
                $('html, body').animate({
                    scrollTop:titlePos
                }, spped);
                section1.animate({
                    'top':'-480px'
                },spped, function() {
                    sections.animate({
                        'height':'600px'
                    },spped);
                });
                section2.animate({
                    'top':'0'
                },spped);
				
            }

            tStep2.click( function(e) {
                e.preventDefault();

                if($(this).data('type')){
                    var btnNextEmpP1 = $('#nextEmpP1'),
                    textTraking = btnNextEmpP1.attr('onclick'),
                    newTextTraking = textTraking.replace(/(?!_track)_[a-z]+(?=')/ig, "_" + $(this).data('type'));
                    btnNextEmpP1.attr('onclick', newTextTraking);
                }

                $('html, body').animate({
                    scrollTop:titlePos
                }, spped);
                var t = $(this),
                dataS = t.attr('rel'),
                objJson = eval('(' + dataS + ')');
                title.html('Elija su estrategia para el medio impreso : <span class="bold">Aviso ' + objJson.words + '</span>');
                section2.animate({
                    'top':'-600px'
                },spped, function() {
                    sections.animate({
                        'height':'400px'
                    },spped);
                });
                section3.animate({
                    'top':'0'
                },spped);
                $('#optP1Emp1').attr('rel', objJson.id1);
                $('#optP1Emp2').attr('rel', objJson.id2);
                $('#optP1Emp3').attr('rel', objJson.id3);
                $('#priceRelp1').text(objJson.precio1);
                $('#priceRelp2').text(objJson.precio2);
                $('#priceRelp3').text(objJson.precio3);

                btnBackX.css('display','none');
				
            });
            backSection2.click( function(e) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop:titlePos
                }, spped);
                //title.html('Elija su tipo de Aviso Econ&oacute;mico : ');
                title.html('');
                section2.animate({
                    'top':'0'
                },spped, function() {
                    sections.animate({
                        'height':'600px'
                    },spped);
                });
                section3.animate({
                    'top':'1030px'
                },spped);
				
                btnBackX.css('display','block');
				
            });
            primOptP1.click( function(e) {
                e.preventDefault();
                var t = $(this),
                dataM = t.attr('rel'),
                objMJson = eval('(' + dataM + ')');
                if(t.hasClass('loginPrimP1')) {
                    t.addClass('winModal');
                    $('#hideLoginReg').remove();
                    $('#hideRegisterRReg').remove();
                    $('#fRegisterWMH').append('<input id="hideLoginReg" name="id_tarifa" type="hidden" value="' + objMJson.id + '"/>');
                    $('#formResgiRap').append('<input id="hideRegisterRReg" name="id_tarifa" type="hidden" value="' + objMJson.id + '"/>');
                } else {
                    var urlP2 = t.attr('href');
                    window.location = urlP2;
                }
            });
            var nameRadio = $('input[name="optP1Emp"]'),
            nextId = $('#nextEmpP1'),
            form = $('#formEmpP1'),
            errorP1 = $('#msgErrorEmpP1'),
            sendP1B3 = $('#idProductoLg');
            nameRadio.change( function() {
                var t = $(this);
                
                if (t.data('type')) {
                    var btnNextEmpP1 = $('#nextEmpP1'),
                        textTraking = btnNextEmpP1.attr('onclick'),
                        newTextTraking = textTraking.replace(/(?!')\w+(?='])/ig, t.data('type'));
                    btnNextEmpP1.attr('onclick', newTextTraking);
                };

                nextId.attr('rel',t.attr('rel'));

                if(nextId.hasClass('loginEmp')) {
                    nextId.addClass('winModal');
                    $('#hideLoginReg').remove();
                    $('#hideRegisterRReg').remove();
                    $('#fRegisterWMH').append('<input id="hideLoginReg" name="id_tarifa" type="hidden" value="' + t.attr('rel') + '"/>');
                    $('#formResgiRap').append('<input id="hideRegisterRReg" name="id_tarifa" type="hidden" value="' + t.attr('rel') + '"/>');
                }

                if(sendP1B3.size() == 1) {
                    $('#hideLoginRegS3').remove();
                    form.append('<input id="hideLoginRegS3" name="id_tarifa" type="hidden" value="' + t.attr('rel') + '"/>');
                }
            });
            nextId.click( function(e) {
                e.preventDefault();
                var t = $(this);
                if(t.attr('rel')) {
                    errorP1.text('');
                    // Si no esta loegado no hace submit
                    if(!(t.hasClass('loginEmp'))) {
                        form.submit();
                    }
                } else {
                    errorP1.text(msgs.paso1Plan);
                }
            });
        },
        showEjm : function(linkEjm){
            var linkEjmA = $(linkEjm);
            linkEjmA.click(function(){
                var t = $(this),
                cnt = $('#ejmsAviso .cntImgAvisoEPA');
                                
                cnt.addClass('loading').html('<img src="' + urls.mediaUrl  + '/images/empresa/avisos/' + $.trim(t.attr('rel')) + '" alt="Aviso" />');
                                
            });
        },
        paso1EmpAP : function() {

            var sections = $('#sectionsEM, #innerSectionEmp'),
            sectionW = $('#sectionsEM'),
            section1 = $('#section2AP'),
            section2 = $('#section3AP'),
            spped = 'middle',
            tStep1 = $('.continueP1C'),
            tStep2 = $('.continP2'),
            title = $('#countWordEM'),
            titlePos = title.offset().top,
            backSection2 = $('#backEmpP2AP');

            var url = document.location.href,
            domainSplit = url.split('/'),
            params = domainSplit.length;
                    
            if(domainSplit[params-1] == '4') {
                _funP2(titlePos, spped, section1, sections, section2);
            }

            tStep1.on('click', function(e) {
                e.preventDefault();
                _funP2(titlePos, spped, section1, sections, section2);
            });

            var btnBackX = $('#backEmpXF');
            function _funP2(titlePos, spped, section1, sections, section2) {

                title.html('Elija su estrategia para el medio impreso: <strong>Aviso Preferencial</strong>');

                $('html, body').animate({
                    scrollTop:titlePos
                }, spped);
                section1.animate({
                    'top':'-820px'
                },spped, function() {
                    sections.animate({
                        'height':'820px'
                    },spped);
                });
                section2.animate({
                    'top':'0'
                },spped);
                            
                btnBackX.css('display','none');
            }

            backSection2.on('click', function(e) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop:titlePos
                }, spped);
                title.html('Elija su tipo de Aviso Preferencial : ');
                section1.animate({
                    'top':'0'
                },spped, function() {
                    sections.animate({
                        'height':'780px'
                    },spped);
                });
                section2.animate({
                    'top':'780px'
                },spped);
                            
                //Retornando Flag a 0
                urlDataFlag = 0;
                            
                btnBackX.css('display','block');
            });
                       
        },
        captureUrl : function(){
            var url = document.location.href,
            domain = url.lastIndexOf(document.location.host) + document.location.host.length + 1,
            params = url.substring(domain),
            splitUrl = params.split('/'),
            flagProduct = 0,
            arrPos = [];                
            if( params.indexOf('producto') != '-1' ){
                $.each(splitUrl, function(i,v){
                    if(v == 'producto'){
                        flagProduct = 1;
                        arrPos.push(i);
                    }
                });
                var tipoAdv = splitUrl[arrPos[0] + 1];

                $('#avispPref' + parseInt(tipoAdv)).trigger('click');
            }                                                
            //Clikeando el dato del precio
            if( params.indexOf('precio') != '-1' ){
                $.each(splitUrl, function(ind,vector){
                    if(vector == 'precio1' ||
                        vector == 'precio2' || 
                        vector == 'precio3'){
                        //Flag a de Url para el click en el Ajax
                        urlDataFlag = 1;
                    }
                });
            } else{
                //reset Flag Url
                urlDataFlag = 0;
            }
        },
        _autoClickUrl : function(){
            var url = document.location.href,
            domain = url.lastIndexOf(document.location.host) + document.location.host.length + 1,
            params = url.substring(domain),
            splitUrl = params.split('/'),
            arrPrice = [];
            //Clikeando el dato del precio
            $.each(splitUrl, function(ind,vector){
                if(vector == 'precio1' ||
                    vector == 'precio2' || 
                    vector == 'precio3'){
                    arrPrice.push(ind);

                }
            });
            if( arrPrice.length > 0 ){
                var strPrice = ((splitUrl[arrPrice[0]]).split(''))[6],
                tipoPrice = splitUrl[parseInt(arrPrice[0]) + 1],
                itemClick = (parseInt(strPrice)-1)*4 + parseInt(tipoPrice);
                //Click    
                $('#tablePriceAPA .priceTrTd').eq((parseInt(strPrice)-1)*4 + parseInt(tipoPrice)).trigger('click');
            }                                
        },
        addParamForm : function(link){
            $(link).on('click', function(e){
                var t = $(this),
                dataS = t.attr('rel'),
                objJson = eval('(' + dataS + ')'),                    
                iptReturn = $('#fRegisterWMH #return_registro'),
                iptReturn2 = $('#formResgiRap #return_registro');
                iptReturn.val(objJson.idProd);
                iptReturn2.val(objJson.idProd);
            });
        },
        markTipo : function(){
            var nameTipo = $('input[name="inputTAP"]'),
            namePlan = $('input[name="labelTAP"]'),
            tdPricePlan = $('.priceTrTd'),
            cntIndX = $('#tipoRadioCheck'),
            cntIndY = $('#planRadioCheck'),
            indX = '-1',
            indY = '-1',
            tableCnt = $('#tablePriceAPA');               
            cntIndX.val(indX);
            cntIndY.val(indY);
               
            $.each(tdPricePlan, function(i,v){
                $(tdPricePlan[i]).attr({
                    'posicion-x':$(tdPricePlan[i]).position().left, 
                    'posicion-y' : $(tdPricePlan[i]).position().top
                }); 
            });
                              
            nameTipo.live('change', function(){
                var t = $(this);                    
                cntIndX.val(nameTipo.index(this));                    
                //_marcador();
                avisoEmp._marcador(cntIndX, cntIndY, indX, indY, tdPricePlan, tableCnt);
            });
            namePlan.live('change', function(){
                var t = $(this);                    
                cntIndY.val(namePlan.index(this)); 
                //_marcador();
                avisoEmp._marcador(cntIndX, cntIndY, indX, indY, tdPricePlan, tableCnt);
            });
                
        },
        clickMarkTipo : function(divCnt){
            var divLink = $(divCnt);
            divLink.bind('click', function(e){
                e.preventDefault();                  
                    
                var t = $(this);
                $('#msgErrorEmpP1').removeClass('good bad').text('');
                if(t.hasClass('activeTd')){
                                            
                    var tableCnt = $('#tablePriceAPA'),
                    tdPricePlan = $('.priceTrTd'),
                    cntIndX = $('#tipoRadioCheck'),
                    cntIndY = $('#planRadioCheck'),
                    indX = '-1',
                    indY = '-1';
                   
                    cntIndX.val(t.attr('valor-x'));
                    cntIndY.val(t.attr('valor-y'));   
                    //inputs
                    var radiosTop = $('input[name="inputTAP"]');
                    radiosTop.removeAttr('checked');
                    radiosTop.eq(t.attr('valor-x')).attr('checked','checked');
                    var radiosLeft = $('input[name="labelTAP"]');
                    radiosLeft.removeAttr('checked');
                    radiosLeft.eq(t.attr('valor-y')).attr('checked','checked');                    
                    
                    //nuevos
                    var inputAvs = $('input[name="radioAvsPref"]');
                    inputAvs.removeAttr('checked');
                    t.find('.cntIptRadio').children('.radioAvs').attr('checked','checked');
                    
                    //Marcador
                    avisoEmp._marcador(cntIndX, cntIndY, indX, indY, tdPricePlan, tableCnt);                        
                }
                    
                });
            },
            changeRadioTd : function(){
                //nuevos
                var inputAvs = $('input[name="radioAvsPref"]');
                inputAvs.bind('click', function(){
                    var t = $(this);
                	setTimeout(function(){
                		inputAvs.removeAttr('checked');
                    	t.attr('checked','checked');                  		
                	},0);                 	
                });         	
            },
            _marcador : function(cntIndX, cntIndY, indX, indY, tdPricePlan, tableCnt){

            //reset Link Siguiente
            $('#nextEmpP1AP').attr('data-href', '#');

            var valTipo = cntIndX.val(),
            valPlan = cntIndY.val(),
            tdCurrent = parseInt(valTipo) + (4 * parseInt(valPlan));
            if(valTipo != indX && valPlan != indY){
                var tdC = tdPricePlan.eq(tdCurrent);
                tableCnt.animate({
                    'background-position-x':parseInt(tdC.attr('posicion-x')) + 15,
                    'background-position-y':tdC.attr('posicion-y')
                }, 'fast', function(){
                    $('.priceTrTd').removeClass('activeTipo');
                    tdC.addClass('activeTipo');                            
                });
                        
                //tarifa
                var tarifaW = $('#tarifaId'),
                valTarifa = tarifaW.val().split(','),
                tamanio = tarifaW.attr('arr-tamanio'),
                nextLink = $('#nextEmpP1AP'),
                urlNext = nextLink.attr('rel'),
                //tarifaNew = (parseInt(valTipo) * parseInt(tamanio)) + parseInt(valPlan);
                tarifaNew = (parseInt(valTipo) * 3) + parseInt(valPlan);
                        
                //Url para el siguiente paso
                var urlPS = '';
                if (yOSON.modulo == 'empresa') {
                    urlPS = bUrl + '/empresa/publica-aviso-preferencial/paso2/tarifa/' + valTarifa[tarifaNew] + '/';
                } else {
                    urlPS = bUrl + '/admin/publicar-aviso-preferencial/paso2/tarifa/' + valTarifa[tarifaNew] + '/';
                }
                //chekeando extendido y republica
                var url = document.location.href,
                domain = url.lastIndexOf(document.location.host) + document.location.host.length + 1,
                params = url.substring(domain),
                splitUrl = params.split('/'),
                dataUrlExtend;
                if(params.indexOf('extiende/')!='-1'){
                	dataUrlExtend = 'extiende/' + splitUrl[splitUrl.length-1] + '/';
                }else if(params.indexOf('republica/')!='-1'){
                	dataUrlExtend = 'republica/' + splitUrl[splitUrl.length-1] + '/';
                }else{
                	dataUrlExtend = '';
                }
                //armando url
                nextLink.attr('data-href', urlPS + dataUrlExtend);   
                
                //Para el Login
                $('#fRegisterWMH input#return_registro').val(urlPS + dataUrlExtend);
                //Para el registro rapido
                $('#formResgiRap input#return_registro').val(urlPS + dataUrlExtend);
                
                //Flag
                flagLinkPaso2 = true;
                        
                //ver aviso
                var cntInfo = $('#infoSelectD'), 
                cntPreview = $('#viewPCAP'),
                planP = $('#planSizeAdv'),
                sizeP = $('#tamanioSizeAdv'),
                imgP = $('#imgAvisoPA1'),
                typoI = $('#typePCAP'),
                precioI = $('#pricePCAP'),
                planI = $('#planPCAP');
                planP.html(tdC.attr('data-plan'));
                sizeP.html(tdC.attr('data-size'));
                imgP.html('<img src="'+ mUrl +'/images/empresa/preferenciales/vistaprevia/previa' + tdC.attr('data-plan') + '.jpg?' + cacheStatic + '" alt="Aviso Preferencial" />');
                        
                if(tdC.attr('data-precio') == '---'){
                    planI.html('');
                    typoI.html('');
                    precioI.html('');
                    //muestra info anterior
                    cntInfo.slideUp('fast');
                }else{                        
                    planI.html(tdC.attr('data-typo'));
                    typoI.html(tdC.attr('data-plan'));
                    precioI.html(tdC.attr('data-precio'));
                    //muestra info anterior
                    cntInfo.slideDown('fast');
                }
            }
                              
        },
        linkPaso2AP : function(link){
            var erroTxt = $('#msgErrorEmpP1');
            $(link).on('click', function(e){
                e.preventDefault();
                var t = $(this),
                tdPrecios = $('#tablePriceAPA .activeTipo');
                erroTxt.text('');
                    
                //Revisar nodeName
                if(t.get(0).nodeName == 'DIV'){
                    //No esta logeado
                    erroTxt.text(msgs.paso1CError);
                }else{
                        
                    if( t.attr('data-href') != '#' && flagLinkPaso2 == true ){
                        erroTxt.text('');
                        //Verificando que no sea precio 0 '-1 ó ---'
                        if(tdPrecios.attr('data-precio') == '---'){
                            erroTxt.text(msgs.paso2BError);
                        }else{
                            //redireccion
                            document.location.href = t.attr('data-href');
                            t.attr('disabled','disabled');
                        }
                    }else{
                        erroTxt.text(msgs.paso1BError);
                    }                        
                        
                }
                    
            });
        },
        clickBlockAP : function(block1, block2, block3, block4) {
            //click bloque 1
            var blockA = $('.topTitP2, .cntBodyP2', block1),
            btnClick = $('.iBtn1S2MAP', block1);
            blockA.bind('click', function(e) {
                e.preventDefault();
                btnClick.trigger('click');
                e.stopPropagation();
            });
            //click bloque 2
            var blockB = $('.topTitP2, .cntBodyP2', block2),
            btnClickB = $('.iBtn2S2MAP', block2);
            blockB.bind('click', function(e) {
                e.preventDefault();
                btnClickB.trigger('click');
                e.stopPropagation();
            });
            //click bloque 3
            var blockC = $('.topTitP2, .cntBodyP2', block3),
            btnClickC = $('.iBtn3S2MAP', block3);
            blockC.bind('click', function(e) {
                e.preventDefault();
                btnClickC.trigger('click');
                e.stopPropagation();
            });
            //click bloque 4
            var blockD = $('.topTitP2, .cntBodyP2', block4),
            btnClickD = $('.iBtn4S2MAP', block4);
            blockD.bind('click', function(e) {
                e.preventDefault();
                btnClickD.trigger('click');
                e.stopPropagation();
            });                    
        },   
        requestData : function(classRequest){
            var btnResquest = $(classRequest),
            cntTable = $('#tablePriceAPA'),
            iTable = $('#tablePriceAPA');
                
            var url = document.location.href,
            domainSplit = url.split('/'),
            params = domainSplit.length;

            if(domainSplit[params-1] == '4') {
                var obj = domainSplit[params-3];
                _compilaData(obj);  
                    
            }else{
                //Otro valor no ejecuta nada
                var mask = $('#maskGrid');
                mask.remove('');                     
            }                             

            btnResquest.on('click', function(e){
                e.preventDefault();
                    
                var t = $(this),
                data = t.attr('rel'),
                obj = eval('(' + data + ')');
                //Reset
                avisoEmp._resetData();
                    
                //Compila Data
                _compilaData(obj.idProd);
                    
            });
                
            //_construc
            function _compilaData(obj){

                cntTable.append('<div id="maskGrid" class="loading"></div>');
                //Ajax
                $.ajax({
                    type : 'post',
                    url : '/empresa/publica-aviso-preferencial/grilla-precios/',                    
                    data : {
                        'idProd' : obj
                    },
                    success : function(xhr){                            
                        if(xhr){                            
                            var objRequest = eval('(' + xhr + ')'),
                            objId = objRequest.id,
                            arrSize = objId.length,
                            objPlan = objRequest.plan,
                            objPrecio1 = objRequest.precio1,
                            objPrecio2 = objRequest.precio2,
                            objPrecio3 = objRequest.precio3,
                            objMaximoAvisos = objRequest.maximo_avisos,
                            objTamanio = objRequest.tamano_centimetro,
                            objPath = objRequest.path;
                                
                            //objId = array de precios
                            $('#tarifaId').val(objId).attr('arr-tamanio', arrSize);

                            //Limpiando mascara
                            var mask = $('#maskGrid');
                            mask.fadeIn('slow', function(){
                                mask.remove();
                            });

                            var i=1;
                            for (i=1;i<=arrSize;i++)
                            {
                                //radio hidden
                                $('#radioTipoAP' + i +'').removeClass('hide')
                                .parent().parent().removeClass('hide');
                                //precios
                                if(objPrecio1[i-1] == undefined || objPrecio1[i-1] == '-1.00'){
                                    objPrecio1[i-1] = '---',
                                    sol = '';                                
                                }else{
                                    sol = 'S/.';
                                } 
                                $('#priceA' + i +'').text(sol + ' ' + objPrecio1[i-1])
                                .parent().attr({
                                    'data-precio': objPrecio1[i-1], 
                                    'data-maximo': objMaximoAvisos[i-1],
                                    'data-plan': objPlan[i-1],
                                    'data-size': objTamanio[i-1],
                                    'data-typo' : 'Web + APTiTUS impreso',
                                    'valor-x' : i-1,
                                    'valor-y' : '0'
                                }).addClass('activeTd');

                                if(objPrecio2[i-1] == undefined || objPrecio2[i-1] == '-1.00'){
                                    objPrecio2[i-1] = '---',
                                    sol = '';
                                }else{
                                    sol = 'S/.';
                                }  
                                $('#priceB' + i +'').text(sol + ' ' + objPrecio2[i-1])
                                .parent().attr({
                                    'data-precio': objPrecio2[i-1], 
                                    'data-maximo': objMaximoAvisos[i-1],
                                    'data-plan': objPlan[i-1],
                                    'data-size': objTamanio[i-1],
                                    'data-typo' : 'Web + El Talán impreso',
                                    'valor-x' : i-1,
                                    'valor-y' : '1'

                                }).addClass('activeTd');

                                if(objPrecio3[i-1] == undefined || objPrecio3[i-1] == '-1.00'){
                                    objPrecio3[i-1] = '---',
                                    sol = '';
                                }else{
                                    sol = 'S/.';
                                }  
                                $('#priceC' + i +'').text(sol + ' ' + objPrecio3[i-1])
                                .parent().attr({
                                    'data-precio': objPrecio3[i-1], 
                                    'data-maximo': objMaximoAvisos[i-1],
                                    'data-plan': objPlan[i-1],
                                    'data-size': objTamanio[i-1],
                                    'data-typo' : 'Web + APTiTUS + El Talán impresos',
                                    'valor-x' : i-1,
                                    'valor-y' : '2'
                                }).addClass('activeTd');

                                //Plan
                                $('#tipo' + i +'AP').text(objPlan[i-1]);
                                //Tamanio
                                $('#medidaTAP' + i +'').text(objTamanio[i-1] + 'cm');  
                                //Img
                                $('#imgAPD' + i +'').html('<img src="' + mUrl + 
                                    '/images/empresa/preferenciales/aviso' + 
                                    objPlan[i-1] + '.jpg" alt="aviso ' + 
                                    objPlan[i-1] + '"/>');  
                                    //Nuevos
                                    //Plan
                                    $('#puestoNumA' + i +'').text(objMaximoAvisos[i-1] + ' puestos');
                                    $('#puestoNumB' + i +'').text(objMaximoAvisos[i-1] + ' puestos');
                                    $('#puestoNumC' + i +'').text(objMaximoAvisos[i-1] + ' puestos');
                                    //Plan
                                    $('#planAvsA' + i +'').text(objPlan[i-1]);
                                    $('#planAvsB' + i +'').text(objPlan[i-1]);
                                    $('#planAvsC' + i +'').text(objPlan[i-1]);
                                    //size
                                    $('#sizeAvsA' + i +'').text(objTamanio[i-1]);
                                    $('#sizeAvsB' + i +'').text(objTamanio[i-1]);
                                    $('#sizeAvsC' + i +'').text(objTamanio[i-1]);   
                                    //Img
                                    $('#imgAvsA' + i +'').html('<img src="' + mUrl + '/images/empresa/preferenciales/aviso' + objPlan[i-1] + '.jpg" />');
                                    $('#imgAvsB' + i +'').html('<img src="' + mUrl + '/images/empresa/preferenciales/aviso' + objPlan[i-1] + '.jpg" />');
                                    $('#imgAvsC' + i +'').html('<img src="' + mUrl + '/images/empresa/preferenciales/aviso' + objPlan[i-1] + '.jpg" />');
                                    //href
                                    //$('#hrefAvsA' + i +'')
                                    $('input[name="radioAvsPref"]').unbind();
                                    avisoEmp.changeRadioTd();
                                }

                            //Funcion Click
                            $('.activeTd').unbind();
                            avisoEmp.clickMarkTipo('.activeTd');
                                
                            //Activando Item por Url
                            if( urlDataFlag == 1 ){
                                avisoEmp._autoClickUrl();
                            }else{
                                //urlDataFlag = 0
                                //Primer Item seleccionador por defecto
                                $('.activeTd').eq(0).trigger('click');
                            }

                        }
                    },
                    error : function(xhrError){
                        var mask = $('#maskGrid');
                        mask.removeClass('loading').html('<div class="error" style="padding:100px;' + 
                            'font-size:16px; text-align:center">' +
                            'Actualize y, vuelva a intentarlo.</div>');
                    }
                });
            }
        },
        _resetData : function(){
            //reset data grid
            //data
            var sizeData = 4;
            var i=1;
            for (i=1;i<=(sizeData);i++)
            {
                $('#priceA' + i +'').text('');
                $('#priceB' + i +'').text('');
                $('#priceC' + i +'').text('');
                $('#tipo' + i +'AP').html('&nbsp;');
                $('#medidaTAP' + i +'').html('&nbsp;');
                $('#imgAPD' + i +'').html('');
                //radio hidden
                $('#radioTipoAP' + i +'').addClass('hide')
                .parent().parent().addClass('hide');
            }
            //bg Table Reset
            $('#tablePriceAPA').css('background-position','430px 70px');
            var arrPrice = $('.priceTrTd');
            //arrPrice.removeClass('activeTd');
                
            $.each(arrPrice, function(i,v){
                $(arrPrice).eq(i).removeClass('activeTipo').removeAttr('data-precio');
                $(arrPrice).eq(i).removeClass('activeTd');
            });
            //inputs radio
            $('#inputRadio1AP, #inputRadio2AP, #inputRadio3AP').removeAttr('checked');
            $('#radioTipoAP1, #radioTipoAP2, #radioTipoAP3, #radioTipoAP4').removeAttr('checked');
            $('#tipoRadioCheck').val('-1');
            $('#planRadioCheck').val('-1');
            //msj Error
            $('#msgErrorEmpP1').text('');
            //reset info
            $('#infoSelectD').addClass('hide').removeAttr('style');
            $('#typePCAP, #planPCAP, #pricePCAP').text('');
            //Reset btn Urls Siguiente Paso
            $('#nextEmpP1AP').attr('data-href','#');
            $('#fRegisterWMH #return_registro').val('/empresa/publica-aviso-preferencial/paso2');
            $('#formResgiRap #return_registro').val('/empresa/publica-aviso-preferencial/paso2');
        },           
        showEjmAP : function(linkEjmAP){
            var linkEjmB = $(linkEjmAP);
            linkEjmB.click(function(){
                var t = $(this),
                obj = eval('(' + t.attr('rel') + ')'),
                cntWindow = $('#ejmsAviso'),
                cnt = cntWindow.find('.cntImgAvisoEPA'),
                sizeAV = (obj.avisos).length,
                anchoW = (215*sizeAV) + (sizeAV*10) + 60;
                cnt.css({
                    'text-align':'left',
                    'width':'100%',
                    'height':'380px'
                });
                cntWindow.css({
                    'margin-top':'-190px',
                    'margin-left':'-'+anchoW/2+'px',
                    'width':anchoW,
                    'height':'380px'
                });
                cnt.html('<h3 class="titleAPLR font28AP">'+ obj.tipo +'</h3><div id="cntImgAvisoAP"></div>');
                var cntImgs = $('#cntImgAvisoAP'),
                i;
                for( i=0; i < sizeAV; i++ ){
                    cntImgs.append(''+
                        '<div class="imgSectionAPTY loading left"><div class="imgSAPTY">' + 
                        '<img width="215" height="271" src="' + mUrl  + '/images/empresa/preferenciales/ejemplos/ejemplo' + (obj.avisos)[i] + '.jpg?'+ cacheStatic + '" alt="Aviso" />' +
                        '</div><div class="infoSectionAPTY"><span class="text1PTY">' + (obj.avisos)[i] + '</span> <span class="text2PTY">(' + (obj.tamanios)[i] + ')</span></div>' +
                        '</div>'
                        );  
                }
            });
        }
    };
    // init
    avisoEmp.paso1Emp();
    avisoEmp.clickBlockF('#iBlock1S2M', '#iBlock2S2M', '#iBlock3S2M', '#iBlock4S2M');               
    avisoEmp.showEjm('.viewShowEjm');
    avisoEmp.linkPaso2AP('#nextEmpP1AP');
    //Aviso preferencial
    avisoEmp.clickBlockAP('#iBlock1S2MAP', '#iBlock2S2MAP', '#iBlock3S2MAP', '#iBlock4S2MAP');        
    avisoEmp.paso1EmpAP();
    avisoEmp.requestData('.continueP1C');
    avisoEmp.addParamForm('.continNAPS');
    avisoEmp.markTipo();
    avisoEmp.showEjmAP('.viewShowEjmAP');
    //captura Url
    avisoEmp.captureUrl();


});