/*
 Registro Empresa Aviso Paso 4
 */
$( function() {
	var msgs = {
		mDelete : {
			success : '¡Se eliminó satisfactoriamente!',
			error : '¡Se produjo un error al eliminar.!',
			prog : 'Eliminando...',
			def : 'Está seguro que desea eliminar?',
			expe : 'Está seguro que desea eliminar?, también se eliminaran tus referencias relacionadas.'
		}
	};
	var formP4 = {
    verAnuncio : function(a){
       $(a).bind('click',function(){
            var idverproceso = $(this).attr('href');
            var url = $(this).attr('rel');
            var contenido = '#content-'+idverproceso.substr(1,idverproceso.length);
            $(contenido).html('');
            $(contenido).addClass('loading');

            $(contenido).load(url,function(){
                $(contenido).removeClass('loading');
                 formP4.scrollAviso(); 
            });
        });
    },
    scrollTb : function(){
    	var heightL = $('#overflowProHist'),
    	tableH = $('#dataProcesosHistorial');        	
    	if(parseInt(tableH.height()) > parseInt(heightL.height()) - 20 ){
    		heightL.addClass('overTableHS');
    	}    	
    },
    scrollAviso : function(){
    	var heightL = $('#dataFormAddNADM .cntModalAEmp'),
    	heightcontent = heightL.height(),
    	alto = 480;     	
    	if(heightcontent > alto ){
    		heightL.addClass('srollModalAEmp').css({'height': alto});
    	}    	
    },       
		sumaTotal : function(){
			var checks = $('.checkEmpP4'),
			total = $('#priceTotP4'),
			numTotal = (parseFloat(total.text())*100); 	//2 decimales		
			checks.bind('change', function(){
				var t = $(this),
				cantid = (parseFloat(t.attr('rel')))*100;					
				if(t.prop('checked')){
					numTotal = numTotal + cantid;
					total.text((numTotal/100).toFixed(2)); 						
				}else{
					numTotal = numTotal - cantid;
					total.text((numTotal/100).toFixed(2)); 					
				}
			});			
		},
		ejmExtracargos : function(clickEjm){
			var clickEjmA = $(clickEjm);
			clickEjmA.bind('click', function(){
				var t = $(this);
				imgData = t.attr('rel'); 
				cntImg = $('#cntExtracargos');
				cntImg.addClass('loading').html('<img src="' + imgData + '"/>');	
			});				
		}            				
	};
	// init
	//paso 4
	formP4.verAnuncio("a[href=#winVerProceso]");
	formP4.sumaTotal();
	formP4.ejmExtracargos('.imgExtraCargos');

});