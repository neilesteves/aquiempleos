$( function() { 
//easing
jQuery.easing.jswing=jQuery.easing.swing;jQuery.extend(jQuery.easing,{def:"easeOutQuad",swing:function(e,f,a,h,g){return jQuery.easing[jQuery.easing.def](e,f,a,h,g)},easeInQuad:function(e,f,a,h,g){return h*(f/=g)*f+a},easeOutQuad:function(e,f,a,h,g){return -h*(f/=g)*(f-2)+a},easeInOutQuad:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f+a}return -h/2*((--f)*(f-2)-1)+a},easeInCubic:function(e,f,a,h,g){return h*(f/=g)*f*f+a},easeOutCubic:function(e,f,a,h,g){return h*((f=f/g-1)*f*f+1)+a},easeInOutCubic:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f+a}return h/2*((f-=2)*f*f+2)+a},easeInQuart:function(e,f,a,h,g){return h*(f/=g)*f*f*f+a},easeOutQuart:function(e,f,a,h,g){return -h*((f=f/g-1)*f*f*f-1)+a},easeInOutQuart:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f*f+a}return -h/2*((f-=2)*f*f*f-2)+a},easeInQuint:function(e,f,a,h,g){return h*(f/=g)*f*f*f*f+a},easeOutQuint:function(e,f,a,h,g){return h*((f=f/g-1)*f*f*f*f+1)+a},easeInOutQuint:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f*f*f+a}return h/2*((f-=2)*f*f*f*f+2)+a},easeInSine:function(e,f,a,h,g){return -h*Math.cos(f/g*(Math.PI/2))+h+a},easeOutSine:function(e,f,a,h,g){return h*Math.sin(f/g*(Math.PI/2))+a},easeInOutSine:function(e,f,a,h,g){return -h/2*(Math.cos(Math.PI*f/g)-1)+a},easeInExpo:function(e,f,a,h,g){return(f==0)?a:h*Math.pow(2,10*(f/g-1))+a},easeOutExpo:function(e,f,a,h,g){return(f==g)?a+h:h*(-Math.pow(2,-10*f/g)+1)+a},easeInOutExpo:function(e,f,a,h,g){if(f==0){return a}if(f==g){return a+h}if((f/=g/2)<1){return h/2*Math.pow(2,10*(f-1))+a}return h/2*(-Math.pow(2,-10*--f)+2)+a},easeInCirc:function(e,f,a,h,g){return -h*(Math.sqrt(1-(f/=g)*f)-1)+a},easeOutCirc:function(e,f,a,h,g){return h*Math.sqrt(1-(f=f/g-1)*f)+a},easeInOutCirc:function(e,f,a,h,g){if((f/=g/2)<1){return -h/2*(Math.sqrt(1-f*f)-1)+a}return h/2*(Math.sqrt(1-(f-=2)*f)+1)+a},easeInElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}if((h/=k)==1){return e+l}if(!j){j=k*0.3}if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}return -(g*Math.pow(2,10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j))+e},easeOutElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}if((h/=k)==1){return e+l}if(!j){j=k*0.3}if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}return g*Math.pow(2,-10*h)*Math.sin((h*k-i)*(2*Math.PI)/j)+l+e},easeInOutElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}if((h/=k/2)==2){return e+l}if(!j){j=k*(0.3*1.5)}if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}if(h<1){return -0.5*(g*Math.pow(2,10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j))+e}return g*Math.pow(2,-10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j)*0.5+l+e},easeInBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}return i*(f/=h)*f*((g+1)*f-g)+a},easeOutBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}return i*((f=f/h-1)*f*((g+1)*f+g)+1)+a},easeInOutBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}if((f/=h/2)<1){return i/2*(f*f*(((g*=(1.525))+1)*f-g))+a}return i/2*((f-=2)*f*(((g*=(1.525))+1)*f+g)+2)+a},easeInBounce:function(e,f,a,h,g){return h-jQuery.easing.easeOutBounce(e,g-f,0,h,g)+a},easeOutBounce:function(e,f,a,h,g){if((f/=g)<(1/2.75)){return h*(7.5625*f*f)+a}else{if(f<(2/2.75)){return h*(7.5625*(f-=(1.5/2.75))*f+0.75)+a}else{if(f<(2.5/2.75)){return h*(7.5625*(f-=(2.25/2.75))*f+0.9375)+a}else{return h*(7.5625*(f-=(2.625/2.75))*f+0.984375)+a}}}},easeInOutBounce:function(e,f,a,h,g){if(f<g/2){return jQuery.easing.easeInBounce(e,f*2,0,h,g)*0.5+a}return jQuery.easing.easeOutBounce(e,f*2-g,0,h,g)*0.5+h*0.5+a}});
//selectivr
var selectvr = function(){
    (function(j){function A(a){return a.replace(B,h).replace(C,function(a,d,b){for(var a=b.split(","),b=0,e=a.length;b<e;b++){var s=D(a[b].replace(E,h).replace(F,h))+o,l=[];a[b]=s.replace(G,function(a,b,c,d,e){if(b){if(l.length>0){var a=l,f,e=s.substring(0,e).replace(H,i);if(e==i||e.charAt(e.length-1)==o)e+="*";try{f=t(e)}catch(k){}if(f){e=0;for(c=f.length;e<c;e++){for(var d=f[e],h=d.className,j=0,m=a.length;j<m;j++){var g=a[j];if(!RegExp("(^|\\s)"+g.className+"(\\s|$)").test(d.className)&&g.b&&(g.b===!0||g.b(d)===!0))h=u(h,g.className,!0)}d.className=h}}l=[]}return b}else{if(b=c?I(c):!v||v.test(d)?{className:w(d),b:!0}:null)return l.push(b),"."+b.className;return a}})}return d+a.join(",")})}function I(a){var c=!0,d=w(a.slice(1)),b=a.substring(0,5)==":not(",e,f;b&&(a=a.slice(5,-1));var l=a.indexOf("(");l>-1&&(a=a.substring(0,l));if(a.charAt(0)==":")switch(a.slice(1)){case "root":c=function(a){return b?a!=p:a==p};break;case "target":if(m==8){c=function(a){function c(){var d=location.hash,e=d.slice(1);return b?d==i||a.id!=e:d!=i&&a.id==e}k(j,"hashchange",function(){g(a,d,c())});return c()};break}return!1;case "checked":c=function(a){J.test(a.type)&&k(a,"propertychange",function(){event.propertyName=="checked"&&g(a,d,a.checked!==b)});return a.checked!==b};break;case "disabled":b=!b;case "enabled":c=function(c){if(K.test(c.tagName))return k(c,"propertychange",function(){event.propertyName=="$disabled"&&g(c,d,c.a===b)}),q.push(c),c.a=c.disabled,c.disabled===b;return a==":enabled"?b:!b};break;case "focus":e="focus",f="blur";case "hover":e||(e="mouseenter",f="mouseleave");c=function(a){k(a,b?f:e,function(){g(a,d,!0)});k(a,b?e:f,function(){g(a,d,!1)});return b};break;default:if(!L.test(a))return!1}return{className:d,b:c}}function w(a){return M+"-"+(m==6&&N?O++:a.replace(P,function(a){return a.charCodeAt(0)}))}function D(a){return a.replace(x,h).replace(Q,o)}function g(a,c,d){var b=a.className,c=u(b,c,d);if(c!=b)a.className=c,a.parentNode.className+=i}function u(a,c,d){var b=RegExp("(^|\\s)"+c+"(\\s|$)"),e=b.test(a);return d?e?a:a+o+c:e?a.replace(b,h).replace(x,h):a}function k(a,c,d){a.attachEvent("on"+c,d)}function r(a,c){if(/^https?:\/\//i.test(a))return c.substring(0,c.indexOf("/",8))==a.substring(0,a.indexOf("/",8))?a:null;if(a.charAt(0)=="/")return c.substring(0,c.indexOf("/",8))+a;var d=c.split(/[?#]/)[0];a.charAt(0)!="?"&&d.charAt(d.length-1)!="/"&&(d=d.substring(0,d.lastIndexOf("/")+1));return d+a}function y(a){if(a)return n.open("GET",a,!1),n.send(),(n.status==200?n.responseText:i).replace(R,i).replace(S,function(c,d,b,e,f){return y(r(b||f,a))}).replace(T,function(c,d,b){d=d||i;return" url("+d+r(b,a)+d+") "});return i}function U(){var a,c;a=f.getElementsByTagName("BASE");for(var d=a.length>0?a[0].href:f.location.href,b=0;b<f.styleSheets.length;b++)if(c=f.styleSheets[b],c.href!=i&&(a=r(c.href,d)))c.cssText=A(y(a));q.length>0&&setInterval(function(){for(var a=0,c=q.length;a<c;a++){var b=q[a];if(b.disabled!==b.a)b.disabled?(b.disabled=!1,b.a=!0,b.disabled=!0):b.a=b.disabled}},250)}if(!/*@cc_on!@*/true){var f=document,p=f.documentElement,n=function(){if(j.XMLHttpRequest)return new XMLHttpRequest;try{return new ActiveXObject("Microsoft.XMLHTTP")}catch(a){return null}}(),m=/MSIE (\d+)/.exec(navigator.userAgent)[1];if(!(f.compatMode!="CSS1Compat"||m<6||m>8||!n)){var z={NW:"*.Dom.select",MooTools:"$$",DOMAssistant:"*.$",Prototype:"$$",YAHOO:"*.util.Selector.query",Sizzle:"*",jQuery:"*",dojo:"*.query"},t,q=[],O=0,N=!0,M="slvzr",R=/(\/\*[^*]*\*+([^\/][^*]*\*+)*\/)\s*/g,S=/@import\s*(?:(?:(?:url\(\s*(['"]?)(.*)\1)\s*\))|(?:(['"])(.*)\3))[^;]*;/g,T=/\burl\(\s*(["']?)(?!data:)([^"')]+)\1\s*\)/g,L=/^:(empty|(first|last|only|nth(-last)?)-(child|of-type))$/,B=/:(:first-(?:line|letter))/g,C=/(^|})\s*([^\{]*?[\[:][^{]+)/g,G=/([ +~>])|(:[a-z-]+(?:\(.*?\)+)?)|(\[.*?\])/g,H=/(:not\()?:(hover|enabled|disabled|focus|checked|target|active|visited|first-line|first-letter)\)?/g,P=/[^\w-]/g,K=/^(INPUT|SELECT|TEXTAREA|BUTTON)$/,
        J=/^(checkbox|radio)$/;
        if(m>6){ v = /[\$\^*]=([\'\"])\1/;}else{v = null;}
        var E=/([(\[+~])\s+/g;
        F=/\s+([)\]+~])/g,
        Q=/\s+/g,
        x=/^\s*((?:[\S\s]*\S)?)\s*$/,
        i="",o=" ",
        h="$1";
(function(a,c){function d(){try{p.doScroll("left")}catch(a){setTimeout(d,50);return}b("poll")}function b(d){if(!(d.type=="readystatechange"&&f.readyState!="complete")&&((d.type=="load"?a:f).detachEvent("on"+d.type,b,!1),!e&&(e=!0)))c.call(a,d.type||d)}var e=!1,g=!0;if(f.readyState=="complete")c.call(a,i);else{if(f.createEventObject&&p.doScroll){try{g=!a.frameElement}catch(h){}g&&d()}k(f,"readystatechange",b);k(a,"load",b)}})(j,function(){for(var a in z){var c,d,b=j;if(j[a]){for(c=z[a].replace("*",a).split(".");(d=c.shift())&&(b=b[d]););if(typeof b=="function"){t=b;U();break}}}})}}})(this);
};
//msgBox

var MsgBox = (function(){
    var slideOptions = {duration:500,easing:'easeOutCubic'},
        showBox = function(msg,style,timeShow){
            $('#msgResult').addClass(style).slideDown(slideOptions);
            $('#msgResultBox').html(msg);
            $('#msgResultClose').live('click',function(event){
                event.preventDefault();
                $('#msgResult').slideUp(slideOptions);
            });
            closeBoxTimer(timeShow);
         },
        closeBox = function(){
            if($('#msgResult').hasClass('none')){
                $('#msgResult').slideUp(slideOptions);                
            }
        },
        closeBoxTimer = function(miliseconds){
            setTimeout(closeBox, miliseconds);
        }   
    return{showBox:showBox,closeBox:closeBox}
})();

// administradores
var flagClose = true;
var Administradores = (function(){
   var  version,
        dom = {
            manageProcess : $('.manageProcess'),
            managerEdit : $('#managerEdit'),
            loadingAdmin : $('#loadingAdmin'),
            formAdmin: $('#formAdmin'),
            closeAdmin : $('#closeAdmin'),
            dataResutlAdmin : $('#dataResutlAdmin'),
            formAdminContent : $('#formAdminContent'),
            adminListTable : $('#adminListTable')
        },
        init = function(){
            showManager();
            closeManager();
            deleteItem();
        },
        showLoadingAdmin =  function(cond,callback){
            if(cond){
                dom.loadingAdmin.fadeIn('600');
            }else{
                dom.loadingAdmin.fadeOut('600',function(){
                    callback&&callback();
                });
            }
        },
        showManager = function(){
            dom.manageProcess.bind('click',function(event){
                event.preventDefault();
                var _this = $(this);
                scrollTop(20);
                dom.adminListTable.slideUp('slow',function(){
                    showLoadingAdmin(true);
                    baseUrl = _this.attr('href');
                    $.ajax({
                        url : baseUrl,
                        beforeSend: function(){
                            $('#dataResutlAdmin').remove();  
                        },
                        success : function(data){
                            dom.managerEdit.attr('data-anuncion-id',baseUrl);
                            if($.browser.msie){
                                version = $.browser.version;
                                if(version == '7.0' || version == '8.0'){
                                    selectvr();                            
                                }
                            }
                            showLoadingAdmin(false,function(){
                                dom.managerEdit.hide().append(data).slideDown('slow');
                                $("#areaId").live('change',function(){
                                    addProcess();
                                });
                                assignProcess();
                                pagination();
                                //deleteAdmin();

                            }); 
                            
                            
                        }
                    }); 
                });
                    
            });
        },
        closeManager = function(){
            dom.closeAdmin.live('click',function(event){
                event.preventDefault();
                if(flagClose){
                    dom.adminListTable.slideDown('slow');
                    if($.browser.msie){
                        if(version == '7.0'){
                            $("#msgResult").remove();
                            $('#formAdminContent').remove();
                        }
                    }
                    $('#dataResutlAdmin').slideUp({duration:1000,easing:'easeInOutBack',complete:function(){
                        $('#dataResutlAdmin').remove();
                    }});
                    $("#msgResult").fadeOut('slow');
                }
            });
        },
        addProcess = function(){
            var areaId,$options,
                adminId = $('#formAdmin').attr('admin-id');
                $options = '<option value="">Seleccione proceso</option>',
                areaId = $("#areaId").val(),
                onSelectMouseOver = function(){
                    $(this).css({'width':'auto', 'max-width':''});
                },
                onSelectMouseOut = function(){
                    $(this).css('max-width','345px');
                };
                if($.trim(areaId) != ""){
                    $.ajax({
                        url: '/empresa/administrador-procesos/procesos-no-asignados/administrador_id/'+adminId+'/area_id/'+areaId,
                        beforeSend : function(){
                            flagClose=false;
                            $('#anuncioId').html($options).attr('disabled','disabled');
                            $('#btnFormAdmin').attr('disabled','disabled');
                            $('#loading-process').removeClass('none');
                        },
                        success : function(data){
                            flagClose=true;
                            $('#loading-process').addClass('none');
                            if(data.length != 0){
                                $.each(data,function(index,value){
                                    $options += '<option value="'+data[index].id+'">'+data[index].puesto+'</option>';

                                });
                                //asign events if ie
                                /*if ($.browser.msie){                        
                                   $('#anuncioId').mouseover(onSelectMouseOver);
                                   $('#anuncioId').mouseout(onSelectMouseOut);
                                }*/
                            }
                            $('#anuncioId')
                                .html($options)
                                .removeAttr('disabled');
                            $('#btnFormAdmin')
                                .removeAttr('disabled');
                        }
                    });
                } else {
                    $('#anuncioId')
                                .html($options)
                                .removeAttr('disabled');
                }
        },
        assignProcess = function(){
            var formAdmin = $('#formAdmin'),
                formAdminAction = $('#formAdmin').attr('action');
            formAdmin.bind('submit',function(event){
                event.preventDefault();
                var estado,
                    anuncioId = $("#anuncioId").val();
                    if(anuncioId != "" && anuncioId != undefined){
                       $.ajax({
                            url : formAdminAction+'/anuncio_id/'+anuncioId,
                            beforeSend : function(){
                                flagClose=false;
                                $('#loading-assigProcess').removeClass('none');
                                $('#btnFormAdmin').attr('disabled','disabled');
                            },
                            success : function(data){
                                flagClose=true;
                                estado = data.estado;
                                $('#loading-assigProcess').addClass('none');

                                MsgBox.showBox(data.mensaje,'msgResult'+estado,5000);

                                $('#btnFormAdmin').removeAttr('disabled');

                                if(estado != 3 || estado != 2){
                                    refreshTable();
                                }
                                addProcess();
                            } 
                        }); 
                    }else{
                        MsgBox.showBox("No se puede Asignar proceso",'msgResultInter',5000); 
                    }
                
            });
        },
        refreshTable = function(urlPage){
            var wTable,hTable,
                adminId = $('#formAdmin').attr('admin-id');
                if(urlPage == "" || urlPage == undefined){
                    urlPage = '/empresa/administrador-procesos/listar/administrador_id/'+adminId+'/pagina/1';
                }
            $.ajax({
                dataType: 'html',
                url : urlPage,
                beforeSend : function(){
                        wTable = $('#tableDataGrid').width();
                        hTable = $('#tableDataGrid').height();
                        $('#layerOpacity').removeClass('none').width(wTable).height(hTable);
                },
                success : function(data){
                    $('#layerOpacity').addClass('none');
                    if(urlPage == ""){
                        $("#dataResutlAdmin").hide().fadeIn().html(data);
                    }else{
                        $("#tableDataGrid").hide().fadeIn().html(data);
                    }
                    if(version == '7.0' || version == '8.0'){
                        selectvr();                            
                    }
                    pagination();
                }
            });
        },
        deleteItem = function(){
            var $that,
                $thatTr;
            $('.abtnDelete').live('click',function(event){
                event.preventDefault();
                $that = $(this);
                $.ajax({
                    url : $(this).attr('href'),
                    beforeSend : function(){
                        flagClose=false;
                        $thatTr = $that.parents('tr');
                        $that.hide();
                        $that.parent().append('<div class="min-loading"></div>');
                        MsgBox.closeBox(); 
                    },
                    success : function(data){
                        flagClose=true;
                        if(data.estado == 1){
                            $thatTr.fadeOut({duration:1000,easing:'easeOutCubic',complete : function(){
                                $thatTr.remove();
                                if($("#tableDataGrid table tbody").children().length == 0){
                                    refreshTable();
                                }
                            }});
                            MsgBox.showBox(data.mensaje,'msgResultOk',1000);
                        }else{
                            $that.parent().find('div').remove();
                            $that.show();
                            MsgBox.showBox(data.mensaje,'msgResultFail',1000);
                        }
                        addProcess();
                    }
                });
            });
        },
        pagination = function(){
            var urlPage;
            $(".itemPag a").bind('click',function(event){
                event.preventDefault();
                urlPage = $(this).attr('href');
                if(urlPage!= ""){
                    refreshTable(urlPage);
                }
            });
            
        },
        scrollTop = function(position){
            $('html, body').animate({scrollTop:position});
        }
    return {init:init}
})();

Administradores.init();
    
});


