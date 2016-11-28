$(document).ready(function () {

    $('body').attr('id', 'acnclaBody');
    $('.proList').hide();
    var dataIdOriginal = $("#btn-calcular").attr("data-id");

    $("#contenedor-fechas-check").on("click", ".checkbox_date", function ()
    {
        var tDestacados = parseInt($('input[name=name_check_destacados]:checked').length);
        $("#totalDiasDestaque").html(tDestacados);
    });

    $('input:radio[name="tarifa"]').change(function () {
        if ($(this).is(':checked')) {
            $('.proList').hide();
            $('.list-' + $(this).data("value")).show();
        }
    });
    $('[name="path_foto"]').on("click", function () {

//          var val = $("#pathArchivo");

        //val.focus();
    });
    $(".destaque-simple").on("click", function () {
        var imput = $(this);
        //console.log(imput.html());
        var valorRadioButton = $('input[name=tarifa]:checked').val();
        var precioWeb = $('input[name=tarifa]:checked').attr('data-pw');
        var data = $('input[name=tarifa]:checked').attr('data-web');
        var precioImpreso = $("input[name='precio_ai']").val();
        $("input[name='id_tarifa']").val(valorRadioButton);
        $("input[name='precio_aw']").val(precioWeb);
//        tipo_web
        // $('#tipo_web').html(imput.html());
        // $("input[name='precio_ai']").val(0);
        //$ 37.70
        //   var precio1 = parseFloat($("#radioPrecioDestaque").html());
        //  var precio2 = parseFloat($("#montoImpresoDestacado").html());
        var total = parseFloat(precioWeb) + parseFloat(precioImpreso);

        var val = data;
        var basePath = window.location.protocol + "//" + window.location.host + "/empresa/publica-aviso/cacular-con-iva-ajax/monto/" + total
        if (val == 'true') {
            $.ajax({
                url: basePath,
                type: 'GET',
                async: true,
                dataType: "json",
                success: function (data) {
                    ///  console.log(data)
                    //   console.log()
                    ocultarPreloadGeneral()
                    //   $("html,body").animate({scrollTop: $("#anuncioWeb").offset().top}, 1500);
                    $("#montoTotalAjax").html(data["data"].Total);
                    $("#id_iva").html(data["data"].iva);
                    $("#id_monto_subTotal").html(data["data"].subTotal);
                }
            });
        }


    })
    $("#path_foto").on("change", function ()
    {
        $(".input-image-file").val($("#path_foto").val())
    })

    $("#btn-calcular").on("click", function () {


        var basePath = window.location.protocol + "//" + window.location.host + "/empresa/publica-aviso/cacular-impreso-web-ajax?"
        $("input[name='id_tarifa']").val(dataIdOriginal)

        var dp;
        if ($('#check_dp').is(':checked')) {
            dp = true;
        } else {
            dp = false;
        }
        var dh;
        if ($('#check_dh').is(':checked')) {
            dh = true;
        } else {
            dh = false;
        }
        var estilo = $("input[name='estilo']:checked").val();
        var color = $("input[name='color']:checked").val();
        var fondo = $("input[name='Fondo']:checked").val();
        var texto = $("#texto").val();
        var tdiasPublicacion = parseInt($("#dias").html());
        var tDestacados = parseInt($('input[name=name_check_destacados]:checked').length);
        var foto;
        if ($("#path_foto").val() == "")
        {

            foto = false;
        } else
        {
            var archivo = $("#path_foto").val();
            var extensiones = archivo.substring(archivo.lastIndexOf("."));
            if (extensiones != ".jpeg" && extensiones != ".jpg" && extensiones != ".png")
            {
                $("html,body").animate({scrollTop: $("#headingOne").offset().top}, 1500);
                $('#errorImagen').html('No es una extencion valida');
                //alert("El archivo de tipo " + extensiones + " no es válido");
                return  false;
            }
            foto = true;
        }
        $("#continuar").attr('data-impreso', 'true');
        $("#continuar").removeClass("opacity");
        $("#continuar").attr("disabled", false);
        $("#continuar").attr("class", 'buttom-gray-w');
        mostrarPreloadGeneral()
        $.ajax({
            url: basePath + 'IdTarifa=' + dataIdOriginal + "&dp=" + dp + "&dh=" + dh + "&estilo=" + estilo + "&color=" + color + "&Fondo=" + fondo + "&texto=" + texto + "&tdias=" + tdiasPublicacion + "&tdestacados=" + tDestacados + "&foto=" + foto,
            type: 'GET',
            async: true,
            dataType: "json",
            success: function (data) {
                //  console.log(data["data"].Total)
                $("#montoImpresoDestacado").html(data["data"].Total);
                $("input[name=precio_ai]").val(data["data"].Total);
                $("#diasTotalesImpreso").html($("#totalDiasPublicacion").html());
                $("#diasDestaqueImpreso").html($("#totalDiasDestaque").html());
                $("input[name='precio_ai']").val(data["data"].Total);
                var precio1 = parseFloat($("#radioPrecioDestaque").html());
                var precio2 = parseFloat($("#montoImpresoDestacado").html());
                var total = precio1 + precio2;
                var basePath = window.location.protocol + "//" + window.location.host + "/empresa/publica-aviso/cacular-ajax/monto/" + total

                $.ajax({
                    url: basePath,
                    type: 'GET',
                    async: true,
                    dataType: "json",
                    success: function (data) {
                        ocultarPreloadGeneral();
                        $("html,body").animate({scrollTop: $("#anclaImpreso").offset().top}, 1500);
                        $("#montoTotalAjax").html(data["data"].Total);
                        $("#id_iva").html(data["data"].iva);
                        //  $(".destaque-simple").attr('data-web', '');
                        $("#id_monto_subTotal").html(data["data"].subTotal);
                    }
                });


            }
        });



    })
    $("#btn-limpiar").on("click", function () {
        var valorRadioButton = $('input[name=tarifa]:checked').val();
        $("input[name='id_tarifa']").val(valorRadioButton)
        $("[name='estilo']").attr('checked', false);
        $("[name='estilo']").attr('checked', false);
        $("[name='Fondo']").attr('checked', false);
        $("[name='color']").attr('checked', false);
        $("#continuar").removeClass("opacity");
        $("#continuar").attr("disabled", false);
        $("#continuar").attr("class", 'buttom-gray-w');
        $("[name='texto']").val('');
        $("#pathArchivo").val('');
        $("[name='path_foto']").val('');
        $("[name='MAX_FILE_SIZE']").val('');
        $("#contenedor-fechas-check").html('');
        $("#btn-calcular").attr('disabled', true);
        $("#btn-calcular").attr('class', 'opacity');
        var pw = $("input[name='precio_aw']").val();
        $("input[name='precio_ai']").val('0');
        $("#diasTotalesImpreso").html('0');
        $("#diasDestaqueImpreso").html('0');
        $("#montoImpresoDestacado").html('0');
        var pi = 0;
        var total = parseFloat(pw) + parseFloat(pi);
        calcular(total);
    })
    $("input[name='estilo']").on("click", function () {
        var e = $(this);
        $("#EstiloAviso").html($('label[for="estilo-' + e.val() + '"]').text());
        validacionBoton();
    })
    $("input[name='Fondo']").on("click", function () {
        var e = $(this);
        $("#fondoAviso").html($('label[for="Fondo-' + e.val() + '"]').text());
        validacionBoton();
    })
    $("input[name='color']").on("click", function () {
        var e = $(this);
        $("#ColorAviso").html($('label[for="color-' + e.val() + '"]').text());
        validacionBoton();
    })
    $("#texto").on("change", function () {
        validacionBoton();
    });
});
function calcular(total) {
    var basePath = window.location.protocol + "//" + window.location.host + "/empresa/publica-aviso/cacular-con-iva-ajax/monto/" + total
    var data = $('input[name=tarifa]:checked').attr('data-web');
    if (data == 'true') {
        $.ajax({
            url: basePath,
            type: 'GET',
            async: true,
            dataType: "json",
            success: function (data) {
                ocultarPreloadGeneral()
                $("#montoTotalAjax").html(data["data"].Total);
                $("#id_iva").html(data["data"].iva);
                $("#id_monto_subTotal").html(data["data"].subTotal);
            }
        });
    }
}
function validacionBoton() {
    var estilo = $("input[name='estilo']:checked").length
    var fondo = $("input[name='Fondo']:checked").length
    var color = $("input[name='color']:checked").length
    var texto = $("#texto").val();
    var dias = $("#dias").html();
    if (estilo === 0 || fondo === 0 || color === 0 || texto === "" || dias === "0") {
        $("#btn-calcular").attr("disabled", true);
        $("#btn-calcular").addClass("opacity");
        $("#continuar").attr('disabled', true);
        $("#continuar").attr('class', 'opacity');
    } else {


        $("#btn-calcular").removeClass("opacity");
        $("#btn-calcular").attr("disabled", false);
    }
}