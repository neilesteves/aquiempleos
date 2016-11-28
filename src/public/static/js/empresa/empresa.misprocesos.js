/*
mis procesos
 */
$(function(){
    var misprocesos = {
        verAnuncio : function(a){
            $(a).live("click",function(){
                var idverproceso = $(this).attr("href");
                var url = $(this).attr("rel");
                var contenido = "#content-"+idverproceso.substr(1,idverproceso.length);
                $(contenido).html("");
                $(contenido).addClass("loading");

                $(contenido).load(url,function(){
                    $(contenido).removeClass("loading");
                    misprocesos.scrollAviso();
                    //Paginado aviso preferencial
                    misprocesos.pagerAvisoMembresia('.aPagerAP');
                    //Tooltip
                    misprocesos._tooltipLive('.tooltipApt');
                    misprocesos._tooltipLive('.firstAP a');
                });
            });
        },
        verHistorial : function(a){
            $(a).live("click",function(){
                var idverproceso = $(this).attr("href");
                var idanuncio = $(this).attr("rel");
                var contenido = "#content-"+idverproceso.substr(1,idverproceso.length);
                $(contenido).html("");
                $(contenido).addClass("loading");

                $.ajax({
                    type: "POST",
                    url: "/empresa/mis-procesos/ver-historial",
                    data: "id="+idanuncio,
                    dataType: "html",
                    success: function(msg){
                        $(contenido).removeClass("loading");
                        $(contenido).html(msg);
                        misprocesos.scrollTb();
                    }
                });
            });
        },
        cerrarProceso : function(a){
            $(a).on("click",function(){
                var x = null, modal = null;
                x = $(this);
                modal = x.attr("href");
                $(modal + " a[href=#aceptar]").off("click");
                $(modal + " a[href=#aceptar]").on("click",function(){
                    window.location = x.attr("rel");
                });
            });
        },
        anclaExtender : function(a){
            $(a).bind("click",function(e){
                e.preventDefault();
                var t = $(this),
                idAW = t.attr("rel"),
                url = t.attr("href");
                //Revisar
                var text = t.text();
                if(!t.hasClass('noRequest')){
                    t.addClass('noRequest');
                    t.css({
                        'padding-left':'20px',
                        'text-decoration':'none',
                        'cursor':'default'
                    }).addClass('loading16').html('&nbsp;');
                    $.ajax({
                        'url' : '/empresa/mis-procesos/validar-extendido/',
                        'type' : 'POST',
                        'dataType' : 'JSON',
                        'data' : {
                            'id' : idAW
                        },
                        'success' : function(res){
                            t.removeClass('noRequest');
                            t.css({
                                'padding-left':'0',
                                'text-decoration':'underline',
                                'cursor':'pointer'
                            }).removeClass('loading16').html(text);
	                        	
                            $('body').attr('data-hash',res['hash']);
                            if (res['val'] == true) {
                                if(t.hasClass('pref')){
                                    location.href = url;
                                }else{
                                    location.href = url;
                                }
                                //$('#winHrefExt').click();
                            } else {
                            	if (res['cond'] != undefined) {
                                    $('#innerMain').parent().prepend('<div class="hide flash-message success" style="display: none;"><div class="msgsTime msgYTIE"><div class="contenidoFlashMessage msgRed mB10 r5">Este aviso no se puede ampliar hasta '+res['cond']+' días antes del Fin de Publicación.</div></div></div>');
                            	} else {
                                    $('#innerMain').parent().prepend('<div class="hide flash-message success" style="display: none;"><div class="msgsTime msgYTIE"><div class="contenidoFlashMessage msgRed mB10 r5">Este aviso tiene un proceso de ampliación pendiente: no puede ampliarse nuevamente.</div></div></div>');
                            	}
                                var mensajes = $('.flash-message'),
                                s = 'middle',
                                interval = '3000';
                                $.each(mensajes, function(k, v){
                                    var h2 = (1000 * k);
                                    if($(v).hasClass('errorout')){
                                        interval = '9000';
                                    }else{
                                        interval = interval;
                                    }
                                    setTimeout(function(){
                                        $(v).fadeIn(s, h2, function(){
                                            setTimeout(function(){
                                            	if(!$(v).hasClass('msgVisible')){
                                                    $(v).fadeOut(s);
                                                }
                                            	
                                                setTimeout(function(){
                                                    mensajes.remove();
                                                }, h2+ interval);
                                                
                                            } , h2 + interval);
                                        });
                                    },h2);
                                    
                                });
                            }
                        }
                    });
                }
            });
                   
            //    }),
            },
            oneClickExt : function(link){
                var linkA = $(link),
                count = 0;
                linkA.bind('click', function(event){
                    event.preventDefault();
                    count++;
                    var cnt = $('#cntExtendNotif'),
                    t = $(this),
                    url = t.attr('href');
                    if(count <= 1){
                        setTimeout(function(){
                            window.location = url;
                        },1000);                    
                    }
                    cnt.empty().css({
                        'width':'100%',
                        'height':'150px'
                    }).addClass('loading');                
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
                    heightL.addClass('srollModalAEmp').css({
                        'height': alto
                    });
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
                        cntData.css({
                            'height':'300px',
                            'width':'100%'
                        }).addClass('loading');
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

            },
            linkOneClick : function(linkBtn){
                var linkA = $(linkBtn);
                linkA.click(function(e){
                    e.preventDefault();
                    var t = $(this);
                    if(!(t.hasClass('linkInvalid'))){
                        location.href = t.attr('href');
                        t.addClass('linkInvalid');
                    }
                });
            }
        };
        misprocesos.verAnuncio("a[href=#winVerProceso]");
        misprocesos.verHistorial("a[href=#winVerHistorial]");
        misprocesos.cerrarProceso(".winModal");
        misprocesos.anclaExtender('.hrefExtend');
        misprocesos.oneClickExt('#btnIdentico');
        misprocesos.oneClickExt('#btnPersonali');
        misprocesos.linkOneClick('#inBtnYes');
    });
