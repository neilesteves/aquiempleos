var AquiEmpleos = function (opts) {
    var Errors = $('.errorMesajes');
    this.login = function () {
        var a = $('#loginP');
        a.click(function (e) {
            var urlReturn = $('#frmUserLogIn').attr('action');
            var emailEP = $('#emailPersonalizado').val();
            var passEP = $('#txtPasswordLogin').val();
            var tipoEP = $('#chkRemember').val();
            var tipo = $('#chkTipo').val();
            var hidAuthToken = $('#hidAuthToken');
            $.ajax({
                'url': '/auth/new-login-ajax/',
                'type': 'POST',
                'dataType': 'JSON',
                'data': {
                    'txtUser': emailEP,
                    'txtPasswordLogin': passEP,
                    'chkRemember': tipoEP,
                    'chkRemember' : tipoEP,
                            'tipo': tipo,
                    'hidAuthToken': hidAuthToken.val(),
                },
                'success': function (res) {
                    if (res.status == '1') {
                        window.location = urlReturn;
                    } else {
                        hidAuthToken.val(res.hashToken);
                        Errors.html(res.msg);
                    }
                },
                'error': function (res) {
                    hidAuthToken.val(res.hashToken);
                    Errors.html(res.msg);
                }
            });
        });
    }
    this.registro = function () {
        var r = $('#registro');
        r.click(function (e) {
            var txtName = $('#txtName').val();
            var txtFirstLastName = $('#txtFirstLastName').val();
            var txtSecondLastName = $('#txtSecondLastName').val();
            var txtBirthDay = $('#txtBirthDay').val();
            var txtEmail = $('#txtEmail').val();
            var pswd = $('#pswd').val();
            var pswd2 = $('#pswd2').val();
            var hidAuthToken = $('#auth_token');
            $.ajax({
                'url': '/registro/registro-rapido',
                'type': 'POST',
                'dataType': 'JSON',
                'data': {
                    'txtName': txtName,
                    'txtFirstLastName': txtFirstLastName,
                    'txtSecondLastName': txtSecondLastName,
                    'txtBirthDay': txtBirthDay,
                    'txtEmail': txtEmail,
                    'pswd': pswd,
                    'pswd2': pswd2,
                    'auth_token': hidAuthToken.val(),
                },
                'success': function (res) {
                    if (res.status == '1') {
                        window.location = '/registro/paso2';
                    } else {
                        hidAuthToken.val(res.hashToken);
                        Errors.html(res.message);
                    }
                },
                'error': function (res) {
                    hidAuthToken.val(res.hashToken);
                    Errors.html(res.message);
                }
            });

        });
    }
    this.loginglobal = function () {
        if (window.location.hash.indexOf('loginP') !== -1) {
            $("#modalLoginUser").modal();
        }

    }
    this.ubigeo = function () {

        var a = $('#fDepart');
        var C = $("[name='id_provincia']");
        a.change(function (e) {
            var id_ubigeo = $(this).val();
            if (a.hasClass('ubigeo')) {
                $.ajax({
                    'url': '/registro/obtener-token/',
                    'type': 'POST',
                    'dataType': 'JSON',
                    'success': function (res) {
                        console.log(res);
                        $.ajax({
                            'url': '/registro/filtrar-distritos/',
                            'type': 'POST',
                            'dataType': 'JSON',
                            'data': {
                                'id_ubigeo': id_ubigeo,
                                'csrfhash': res,
                            },
                            'success': function (res) {
                                C.children('option').remove();
                                C.append('<option value="0" label="Seleccione Ciudad">Seleccione Ciudad</option>');
                                $.each(res, function (i, v) {
                                    C.append('<option value=" ' + i + '" label=" ' + v + ' "> ' + v + '</option>');
                                });
                            },
                            'error': function (res) {
                                //  hidAuthToken.val(res.hashToken);
                                // Errors.html(res.msg);
                            }
                        });

                    },
                    'error': function (res) {
                        //  hidAuthToken.val(res.hashToken);
                        ///    Errors.html(res.msg);
                    }
                });
            }

        });
    }

};
function previwIMage()
{
    $("#txtLogo").change(function () {
        if (this.files && this.files[0])
        {
            //$(".text-gallito-img-upload").addClass("hideDiv");
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#divImage img').attr('src', e.target.result);
            }

            reader.readAsDataURL(this.files[0]);
        }
    });
}
var empleo = new AquiEmpleos();
empleo.login();
empleo.registro();
previwIMage()
empleo.loginglobal();
empleo.ubigeo();
var arrayFehasSeleccionadas = [];
var arrayFechasComprarar = [];
$(document).on("ready", inicio)
function inicio()
{
    $(".help-password a").on("click", abrirModalOlvideContra)
    validarRecuperarContrasenia();
    difinirCalendarios()
    $(".destaque-simple").on("click", function ()
    {
        var i=$(this);
        var diasWeb = $('input[name=tarifa]:checked').attr("data-diasweb");
        var diasDestaque = $('input[name=tarifa]:checked').attr("data-diasdestaque");
        var precio = $('input[name=tarifa]:checked').attr("data-pw");
        $("#radioPrecioDestaque").html(precio);
        $("#radioDiasPublicacion").html(diasWeb);
        $("#radioDiasDestaque").html(diasDestaque);
        $("#tipo_web").html(i.attr("data-descripcion"));
    })
    $(".btn-pagar-submit").on("click", function (event) {
        event.preventDefault();
        if ($("input[name='radioTipoPago']").is(':checked')) {
            $("#message-validacion").html("");
            if ($("input[name='radioTipoPago']:checked").val() == "credomatic") {
                var tarjeta = $("input[name='ccnumber']").val();
                if (tarjeta == "") {
                    $("#message-validacion").html("Ingrese un número de Tarjeta");
                    return;
                }
            }
            mostrarPreloadGeneral();
            $("#formEndP4Emp").submit();
        } else {
            $("#message-validacion").html("Seleccione un método de Pago");
            return;
        }
    });
}
var calendarioSeleccionados = []
function functiontofindIndexByKeyValue(arraytosearch, key, valuetosearch) {

    for (var i = 0; i < arraytosearch.length; i++) {

        if (arraytosearch[i][key] == valuetosearch) {
            return i;
        }
    }
    return null;
}
function eliminarElementoDelArray(elemento)
{
    for (var i = 0; i < arrayFechasComprarar.length; i++)
    {

        if (arrayFechasComprarar[i].id == elemento) {
            arrayFechasComprarar.splice(i, 1);  //removes 1 element at position i 
            break;
        }
    }
}
function buscarEnArrayById(id)
{
    for (var i = 0; i < arrayFechasComprarar.length; i++)
    {
        if (arrayFechasComprarar[i].id == id)
        {
            return true;
        }
    }
    return false
}
function estaSeleccionadoArray(array, fecha) {
    var band = false;
    for (var i = 0; i < array.length; i++)
    {
        var dia = array[i].dia;
        var mes = array[i].mes;
        var anio = array[i].anio;

        var anio_fecha = fecha.getUTCFullYear();
        var mes_fecha = fecha.getUTCMonth() + 1;
        var dia_fecha = fecha.getDate();

        //console.log(diaAnterior+"y"+dia)
        if (dia == dia_fecha && mes == mes_fecha && anio == anio_fecha)
        {
            band = true;
        }
    }
    return band;
}
function difinirCalendarios()
{

    if ($("#simple-select-min-max").length > 0)
    {
        $('#simple-select-min-max').multiDatesPicker({
            minDate: 0,
            onSelect: function (dateText, inst)
            {
                $("#contenedor-fechas-check").html("");
                var daySelected = inst.selectedDay
                var mesSelected = inst.selectedMonth + 1;
                var yearSelected = inst.selectedYear;
                //console.log(dateText)
                //console.log("mostraremos el array a comparar")
                var daysArray = $('#simple-select-min-max').get(0).multiDatesPicker.dates.picked;
                var dia_constante = 24 * 60 * 60 * 1000;


                if (daysArray.length == 1)
                {
                    var dia = daysArray[0].getDate()
                    var mes = (daysArray[0].getMonth() + 1)
                    var anio = daysArray[0].getFullYear()
                    var fechaAGuardar1 = new Date(anio, mes - 1, dia);
                    var fechaAGuardar2 = new Date(anio, mes - 1, dia);
                    var fechaAGuardar3 = new Date(anio, mes - 1, dia);
                    var daySelected = inst.selectedDay
                    fechaAGuardar1.setDate(daySelected);
                    fechaAGuardar2.setDate(fechaAGuardar2.getDate() + 1);
                    fechaAGuardar3.setDate(fechaAGuardar3.getDate() + 2);
                    arrayFechasComprarar.push({
                        id: fechaAGuardar1.getDate() + "-" + (fechaAGuardar1.getMonth() + 1) + "-" + fechaAGuardar1.getFullYear(),
                        dia: fechaAGuardar1.getDate(),
                        mes: fechaAGuardar1.getMonth() + 1,
                        anio: fechaAGuardar1.getFullYear(),
                        date: fechaAGuardar1
                    })
                    arrayFechasComprarar.push({
                        id: fechaAGuardar2.getDate() + "-" + (fechaAGuardar2.getMonth() + 1) + "-" + fechaAGuardar2.getFullYear(),
                        dia: fechaAGuardar2.getDate(),
                        mes: fechaAGuardar2.getMonth() + 1,
                        anio: fechaAGuardar2.getFullYear(),
                        date: fechaAGuardar2
                    })
                    arrayFechasComprarar.push({
                        id: fechaAGuardar3.getDate() + "-" + (fechaAGuardar3.getMonth() + 1) + "-" + fechaAGuardar3.getFullYear(),
                        dia: fechaAGuardar3.getDate(),
                        mes: fechaAGuardar3.getMonth() + 1,
                        anio: fechaAGuardar3.getFullYear(),
                        date: fechaAGuardar3
                    })



                } else
                {
                    //console.log("pintar mas de 2")

                    var banderaDiaActual = false;
                    var banderaDiaAnterior = 0;
                    var banderaDiaPosterior = 0;
                    for (var i = 0; i < arrayFechasComprarar.length; i++)
                    {
                        var dia = arrayFechasComprarar[i].dia;
                        var mes = arrayFechasComprarar[i].mes;
                        var anio = arrayFechasComprarar[i].anio;

                        if (daySelected == dia && mes == mesSelected && anio == yearSelected)
                        {

                            banderaDiaActual = true
                        }
                    }
                    if (banderaDiaActual)
                    {

                        // verifico si el día anterior ya a sido marcado
                        var rompeBloqueDiaAnterior = 0;
                        var banderaSeEncontro = 0;
                        var banderaSeEncontroAnterior2 = 0;
                        // Días anteriores
                        for (var j = 1; true; j++) {
                            var fechaactual = new Date(mesSelected + "/" + daySelected + "/" + yearSelected);
                            var tmp = new Date(fechaactual - (j * dia_constante));
                            if (estaSeleccionadoArray(arrayFechasComprarar, tmp)) {
                                banderaDiaAnterior++;
                                banderaSeEncontro = 1;
                            } else {
                                break;
                            }
                        }

                        if (banderaDiaAnterior == 2) {
                            banderaSeEncontroAnterior2 = 1;
                        }
                        console.log("dias anteriores: " + banderaDiaAnterior);

                        // aqui verifico los dias posteriores

                        var banderaSeEncontro = 0;
                        var banderaSeEncontroAnterior2 = 0;
                        for (var j = 1; true; j++) {
                            var fechaactual = new Date(mesSelected + "/" + daySelected + "/" + yearSelected);
                            var suma = fechaactual.getTime() + (j * dia_constante);
                            var tmp = new Date(suma);
                            console.log(tmp);
                            if (estaSeleccionadoArray(arrayFechasComprarar, tmp)) {
                                banderaDiaPosterior++;
                                banderaSeEncontro = 1;
                            } else {
                                break;
                            }
                        }
                        if (banderaDiaPosterior == 2) {
                            banderaSeEncontroAnterior2 = 1;
                        }
                        console.log("dias posteriores: " + banderaDiaPosterior);
                    }
                    if (banderaDiaAnterior == 0 && banderaDiaPosterior == 0)// si no estan agregare esos dias al array a pintar
                    {

                        //console.log("El dia seleccionado"+daySelected);
                        var fechaActual = new Date(yearSelected, mesSelected - 1, daySelected);
                        var suma = fechaActual.getTime() + (1 * dia_constante);
                        var fechaAnterior = new Date(suma);
                        var suma2 = fechaActual.getTime() + (2 * dia_constante);
                        var fechaPosterior = new Date(suma2);
                        var banderaGuardar1 = true;
                        var banderaGuardar2 = true;
                        var banderaGuardar3 = true;

                        if (estaSeleccionadoArray(arrayFechasComprarar, fechaActual)) {
                            banderaGuardar1 = false;
                        }
                        if (estaSeleccionadoArray(arrayFechasComprarar, fechaAnterior)) {
                            banderaGuardar2 = false;
                        }
                        if (estaSeleccionadoArray(arrayFechasComprarar, fechaPosterior)) {
                            banderaGuardar3 = false;
                        }

                        if (banderaGuardar1)// si no esta en el array lo guardo
                        {
                            fechaActual.setDate(daySelected);
                            var idABuscar = fechaActual.getDate() + "-" + (fechaActual.getMonth() + 1) + "-" + fechaActual.getFullYear();
                            var banderaGuardar = buscarEnArrayById(idABuscar)
                            if (!banderaGuardar)
                            {
                                arrayFechasComprarar.push({
                                    id: fechaActual.getDate() + "-" + (fechaActual.getMonth() + 1) + "-" + fechaActual.getFullYear(),
                                    dia: fechaActual.getDate(),
                                    mes: fechaActual.getMonth() + 1,
                                    anio: fechaActual.getFullYear(),
                                    date: fechaActual
                                })
                            }



                        }
                        if (banderaGuardar2)// si no esta en el array lo guardo
                        {
                            fechaAnterior.setDate(fechaAnterior.getDate());
                            var idABuscar = fechaAnterior.getDate() + "-" + (fechaAnterior.getMonth() + 1) + "-" + fechaAnterior.getFullYear();
                            var banderaGuardar = buscarEnArrayById(idABuscar)
                            if (!banderaGuardar)
                            {
                                arrayFechasComprarar.push({
                                    id: fechaAnterior.getDate() + "-" + (fechaAnterior.getMonth() + 1) + "-" + fechaAnterior.getFullYear(),
                                    dia: fechaAnterior.getDate(),
                                    mes: fechaAnterior.getMonth() + 1,
                                    anio: fechaAnterior.getFullYear(),
                                    date: fechaAnterior
                                })
                            }



                        }
                        if (banderaGuardar3)// si no esta en el array lo guardo
                        {
                            fechaPosterior.setDate(fechaPosterior.getDate());
                            var idABuscar = fechaPosterior.getDate() + "-" + (fechaPosterior.getMonth() + 1) + "-" + fechaPosterior.getFullYear();
                            var banderaGuardar = buscarEnArrayById(idABuscar)
                            if (!banderaGuardar)
                            {
                                arrayFechasComprarar.push({
                                    id: fechaPosterior.getDate() + "-" + (fechaPosterior.getMonth() + 1) + "-" + fechaPosterior.getFullYear(),
                                    dia: fechaPosterior.getDate(),
                                    mes: fechaPosterior.getMonth() + 1,
                                    anio: fechaPosterior.getFullYear(),
                                    date: fechaPosterior
                                })
                            }



                        }
                    }
                    //alert(banderaDiaAnterior)
                    if (banderaDiaAnterior > 2 || banderaDiaPosterior > 2)// si hay mayores a uno pinto solo uno
                    {
                        var fechaAGuardar = new Date(yearSelected, mesSelected - 1, daySelected);
                        fechaAGuardar.setDate(daySelected);
                        arrayFechasComprarar.push({
                            id: fechaAGuardar.getDate() + "-" + (fechaAGuardar.getMonth() + 1) + "-" + fechaAGuardar.getFullYear(),
                            dia: fechaAGuardar.getDate(),
                            mes: fechaAGuardar.getMonth() + 1,
                            anio: fechaAGuardar.getFullYear(),
                            date: fechaAGuardar
                        })



                    }
                    // si doy click a uno que ya esta marcado
                    if (banderaDiaActual)
                    {
                        //alert("estos son los valores")
                        //alert(banderaDiaAnterior)
                        if (banderaDiaAnterior == 2)
                        {


                            $('#simple-select-min-max').multiDatesPicker('removeDates', new Date(yearSelected, mesSelected - 1, daySelected));
                            var date1 = daySelected + "-" + mesSelected + "-" + yearSelected;

                            var fechaactual = new Date(mesSelected + "/" + daySelected + "/" + yearSelected);
                            var tmp = new Date(fechaactual - (1 * dia_constante));
                            $('#simple-select-min-max').multiDatesPicker('removeDates', new Date(tmp.getFullYear(), tmp.getUTCMonth(), tmp.getDate()));
                            var date2 = tmp.getDate() + "-" + (tmp.getUTCMonth() + 1) + "-" + tmp.getFullYear();

                            var fechaactual = new Date(mesSelected + "/" + daySelected + "/" + yearSelected);
                            var tmp = new Date(fechaactual - (2 * dia_constante));
                            $('#simple-select-min-max').multiDatesPicker('removeDates', new Date(tmp.getFullYear(), tmp.getUTCMonth(), tmp.getDate()));
                            var date3 = tmp.getDate() + "-" + (tmp.getUTCMonth() + 1) + "-" + tmp.getFullYear();



                            eliminarElementoDelArray(date1);
                            eliminarElementoDelArray(date2);
                            eliminarElementoDelArray(date3);

                        } else if (banderaDiaAnterior == 1)
                        {
                            $('#simple-select-min-max').multiDatesPicker('removeDates', new Date(yearSelected, mesSelected - 1, daySelected));

                            var fechaactual = new Date(mesSelected + "/" + daySelected + "/" + yearSelected);
                            var tmp = new Date(fechaactual - (1 * dia_constante));
                            $('#simple-select-min-max').multiDatesPicker('removeDates', new Date(tmp.getFullYear(), tmp.getUTCMonth(), tmp.getDate()));




                            var date1 = daySelected + "-" + mesSelected + "-" + yearSelected;
                            var date2 = tmp.getDate() + "-" + (tmp.getUTCMonth() + 1) + "-" + tmp.getFullYear();
                            console.log(date2);

                            eliminarElementoDelArray(date1);
                            eliminarElementoDelArray(date2);

                            console.log(arrayFechasComprarar);
                        }
                        if (banderaDiaPosterior == 2)
                        {

                            $('#simple-select-min-max').multiDatesPicker('removeDates', new Date(yearSelected, mesSelected - 1, daySelected));
                            var fechaactual = new Date(mesSelected + "/" + daySelected + "/" + yearSelected);
                            var suma = fechaactual.getTime() + (1 * dia_constante);
                            var tmp = new Date(suma);

                            $('#simple-select-min-max').multiDatesPicker('removeDates', new Date(tmp.getFullYear(), tmp.getUTCMonth(), tmp.getDate()));
                            var date2 = tmp.getDate() + "-" + (tmp.getUTCMonth() + 1) + "-" + tmp.getFullYear();

                            var fechaactual = new Date(mesSelected + "/" + daySelected + "/" + yearSelected);
                            var suma = fechaactual.getTime() + (2 * dia_constante);
                            var tmp = new Date(suma);

                            $('#simple-select-min-max').multiDatesPicker('removeDates', new Date(yearSelected, mesSelected - 1, daySelected + 2));
                            var date3 = tmp.getDate() + "-" + (tmp.getUTCMonth() + 1) + "-" + tmp.getFullYear();

                            var date1 = daySelected + "-" + mesSelected + "-" + yearSelected;

                            eliminarElementoDelArray(date1);
                            eliminarElementoDelArray(date2);
                            eliminarElementoDelArray(date3);
                            console.log(arrayFechasComprarar);

                        } else if (banderaDiaPosterior == 1)
                        {

                            $('#simple-select-min-max').multiDatesPicker('removeDates', new Date(yearSelected, mesSelected - 1, daySelected));
                            var fechaactual = new Date(mesSelected + "/" + daySelected + "/" + yearSelected);
                            var suma = fechaactual.getTime() + (1 * dia_constante);
                            var tmp = new Date(suma);
                            console.log(tmp);
                            $('#simple-select-min-max').multiDatesPicker('removeDates', new Date(tmp.getFullYear(), tmp.getUTCMonth(), tmp.getDate()));


                            var date1 = daySelected + "-" + mesSelected + "-" + yearSelected;
                            var date2 = tmp.getDate() + "-" + (tmp.getUTCMonth() + 1) + "-" + tmp.getFullYear();
                            console.log(date2);
                            eliminarElementoDelArray(date1);
                            eliminarElementoDelArray(date2);


                            console.log(arrayFechasComprarar);
                        }

                        if (banderaDiaPosterior > 2 || banderaDiaAnterior > 2)
                        {

                            $('#simple-select-min-max').multiDatesPicker('removeDates', new Date(yearSelected, mesSelected - 1, daySelected));
                            var date1 = daySelected + "-" + mesSelected + "-" + yearSelected;
                            //alert(date1)
                            eliminarElementoDelArray(date1);
                            eliminarElementoDelArray(date1);

                            console.log(arrayFechasComprarar);
                            //alert("que paso")
                        }
                    }
                }
                arrayFechasComprarar.sort(function (a, b) {
                    return a.dia - b.dia || a.mes - b.mes;
                });
                for (var i = 0; i < arrayFechasComprarar.length; i++)
                {
                    //console.log(arrayFechasComprarar[i])
                    var dia = arrayFechasComprarar[i].dia;
                    var mes = arrayFechasComprarar[i].mes;
                    var anio = arrayFechasComprarar[i].anio;
                    var today = dia + '-' + mes + '-' + anio;

                    $("#contenedor-fechas-check").append('<div class="row">' +
                            '<div class="checkbox">' +
                            '<label><input type="hidden" value="0|' + today + '" name="fecha_impreso[]" class="checkbox_hidden_date"/><input name="name_check_destacados" class="checkbox_date" type="checkbox" ><span class="span_date">' + today + '</span></label>' +
                            '</div>' +
                            '</div>');
                    $(".checkbox_date").off("click");
                    $(".checkbox_date").on("click", function ()
                    {
                        var dateChecbox = $(this).siblings("span").html();
                        if ($(this).is(':checked')) {
                            $(this).parent().find(".checkbox_hidden_date").val("1|" + dateChecbox);
                        } else {
                            $(this).parent().find(".checkbox_hidden_date").val("0|" + dateChecbox);
                        }
                    });

                }
                arrayFehasSeleccionadas = [];

                for (var i = 0; i < arrayFechasComprarar.length; i++)
                {
                    arrayFehasSeleccionadas.push(arrayFechasComprarar[i].date)
                }

                if (arrayFechasComprarar.length > 0)
                {
                    $("#totalDiasDestaque").html(0);
                    $('#simple-select-min-max').multiDatesPicker('removeDates', new Date(yearSelected, mesSelected - 1, daySelected));
                    $('#simple-select-min-max').multiDatesPicker("addDates", arrayFehasSeleccionadas);

                }

                $("#dias").html(arrayFehasSeleccionadas.length);
                $("#totalDiasPublicacion").html(arrayFehasSeleccionadas.length);


                validacionBoton();
                //armando el array para pintar
                //console.log(arrayFehasSeleccionadas)


            },
            beforeShowDay: function (date)
            {
                /*
                 console.log(this);
                 var fecha =new Date(date);                
                 var dd = fecha.getDate();
                 var mm = fecha.getMonth()+1; //January is 0!
                 
                 var yyyy = fecha.getFullYear();
                 if(dd<10){
                 dd='0'+dd
                 } 
                 if(mm<10){
                 mm='0'+mm
                 } 
                 var fecha = dd+'-'+mm+'-'+yyyy;
                 console.log(fecha)*/
                var daysArray = $('#simple-select-min-max').get(0).multiDatesPicker.dates.picked
                //console.log(daysArray.length);
                //console.log("probando el before");
                return [true, ""];
            }
        });
    }

}
function  pintar3Casillas()
{
    alert(parseInt($(this).html()));
}
function abrirModalOlvideContra(event)
{
    event.preventDefault();
    $("#modalLoginUser").modal("hide");
    $("#modalRecoverPassword").modal("show")
}

function validarRecuperarContrasenia()
{


    $("#frmRecoverPassword").validate({
        rules: {
            txtEmailForgot: {
                required: true
            }
        },
        highlight: function (element) {



            $(element).addClass('error-validacion');

//                    $("#txtNumDocumento").siblings("div").children("div").css("display","block");

        },
        unhighlight: function (element) {
            if ($(element).attr('type') === 'checkbox') {
                $(element).siblings('span').removeClass('error');
            } else if ($(element).attr('type') === 'radio') {
                $(element).parent().addClass('spanEpass');
                $(element).parent().siblings().addClass('spanEpass');
                $(element).parent().parent().removeClass('error');
            } else {
                $(element).removeClass('error-validacion');
                $(".contenedor-msj-error").fadeOut();
//                    $("#txtNumDocumento").siblings("div").children("div").css("display","none");
            }

        },
        errorPlacement: function (error, element) {
            $(this).addClass("error-validacion");
            $(".contenedor-msj-error").fadeIn();
        },
        submitHandler: function (form)
        {
            var basePath = window.location.protocol + "//" + window.location.host + "/";
            var rol = $("#rol").val();
            var correo = $("#txtEmailForgot").val();
            $.ajax({
                url: basePath + "auth/new-recuperar-clave/",
                type: 'POST',
                async: true,
                dataType: "json",
                data: {txtEmailForgot: correo, rol: rol},
                success: function (data) {
                    console.log("error")
                    console.log(data);
                    if (data.status == 0)
                    {
                        $(".contenedor-msj-ajax").html('<div class="contenedor-msj-error-2" ><i class="fa fa-exclamation-triangle" aria-hidden="true"></i><span>' + data.msg + '</span></div>');
                    } else
                    {
                        $(form).submit();
                    }

                }
            });
        }

    }
    );
}
