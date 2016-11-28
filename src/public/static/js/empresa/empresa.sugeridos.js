/*
 Sugeridos.
 */
$( function() {

	var sugeridos = {
		verAnuncio : function(a){
           $(a).live("click",function(){
                var idverproceso = $(this).attr("href");
                var url = $(this).attr("rel");
                var contenido = "#content-"+idverproceso.substr(1,idverproceso.length);
                $(contenido).html("");
                $(contenido).addClass("loading");

                $(contenido).load(url,function(){
                    $(contenido).removeClass("loading");
                    sugeridos.scrollAviso();
                    //Paginado aviso preferencial
                    sugeridos.pagerAvisoMembresia('.aPagerAP');                    
                    //Tooltip
                    sugeridos._tooltipLive('.tooltipApt');
                    sugeridos._tooltipLive('.firstAP a');
                });
            });
        },
        scrollAviso : function(){
        	var heightL = $('#dataFormAddNADM .cntModalAEmp'),
        	heightcontent = heightL.height(),
        	alto = 480;     	
        	if(heightcontent > alto ){
        		heightL.addClass('srollModalAEmp').css({'height': alto});
        	}    	
        },
        pagerAvisoMembresia : function(liPager){
            var liPager = $(liPager).not('.normalItem');
            liPager.on('click', function(e){
                e.preventDefault();
                var t = $(this);                
                if(t.hasClass('readyItem')){                    
                    var dataAjax = t.attr('data-ajax'),
                    cntData = $('#cntLoadAAP');
                    cntData.children().addClass('hide');
                    cntData.css({'height':'300px','width':'100%'}).addClass('loading');
                    $.ajax({
                        type : 'POST',
                        url : dataAjax,
                        dataType : 'HTML',
                        success : function(xhr){
                            cntData.removeClass('loading').removeAttr('style');
                            cntData.children().removeClass('hide');
                            cntData.html(xhr);
                            liPager.addClass('readyItem').removeClass('currentItem');
                            t.addClass('currentItem').removeClass('readyItem');   
                         
                        }
                    });                                        
                }                
            });
        },
        _tooltipLive : function(classTooltip){
            var trigger = $(classTooltip);
            var arrTitle = [] ;	
            $.each(trigger, function(i,v){
                    $(v).attr('rel',i);
                    arrTitle.push($(v).attr('title'));
                    $(v).removeAttr('title');
            });	                     
                    
                trigger.mouseenter(function(e){
                        var body = $('body');
                        var t = $(this),
                        tHeight = t.innerHeight(),
                        pos = t.offset(),
                        posLeft = pos.left,
                        posTop = pos.top + tHeight;
                        var tool = '<div class="tooltipCnt" style="left:' + posLeft + 'px; top:' + posTop + 'px"><div class="cachitoT r2">' + arrTitle[parseInt(t.attr('rel'))] + '</div></div>';
                        body.append(tool);
                        
                        t.removeAttr('title');
                        //ie alt
                        //t.attr('alt','');
                        t.removeAttr('alt');
                        
                        //centrado cachito
                        var newTool = $('body > .tooltipCnt'),
                        wTool = newTool.innerWidth(); 
                        newTool.css('left',posLeft - (wTool/2) + (t.innerWidth()/2));
                }).mouseleave(function(){
                        var t = $(this);
                        //t.attr('title',arrTitle[parseInt(t.attr('rel'))]);
                        $('.tooltipCnt').remove();
                });
                    
        }
	};
	//init
    sugeridos.verAnuncio("a[href=#winVerProceso]");
});