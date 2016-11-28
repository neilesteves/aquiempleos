/*
mis procesos
*/
$(function(){
    
    var contenido = "#gridTableR";
    var url_membresias = "/empresa/mi-estado-cuenta/lista-membresias/";
    var url_avisos_membresia = "/empresa/mi-estado-cuenta/avisos-membresia/";
    var trigger_class = '.detaAvisoMem';
    var membresia = {
        getMembresias : function(a) {
            $(a).bind("click", function(e) {
                e.preventDefault();
                $(contenido).html("");
                $(contenido).addClass("loading");
                $.ajax({
                    type: "GET",
                    url: url_membresias,
                    dataType: "html",
                    success: function(html) {
                        $(contenido).removeClass("loading");
                        $(contenido).html(html);
                        //comment
                        membresia.getAvisosMembresia(trigger_class);
                        
                    }
                });
            });
        },
        getAvisosMembresia : function(a) {
            $(a).bind("click", function(e) {
                e.preventDefault();
                var idEmpMem = $(this).attr("rel");
                $(contenido).html("");
                $(contenido).addClass("loading");
                $.ajax({
                    type: "GET",
                    url: url_avisos_membresia,
                    data: {idEmpMem: idEmpMem},
                    dataType: "html",
                    success: function(html) {
                        $(contenido).removeClass("loading");
                        $(contenido).html(html);
                    }
                });
            });
        },

        start : function() {
            membresia.getMembresias('#bandTop');
            $('#bandTop').trigger('click',null);
            $('#bandTop').unbind();
        }
    };
    membresia.start();
});
