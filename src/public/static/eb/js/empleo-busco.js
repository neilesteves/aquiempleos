jQuery.validator.addMethod("emailPersonalizado", function (value, element) {
    return this.optional(element) || /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i.test(value);
}, "Letters only please");
"use strict";

function _classCallCheck(instance, Constructor) {
    if (!(instance instanceof Constructor)) {
        throw new TypeError("Cannot call a class as a function");
    }
}

var PostulacionEmpleobusco = function PostulacionEmpleobusco() {
    _classCallCheck(this, PostulacionEmpleobusco);

    $(".content-table-cell").hover(function () {
        //$(this).find(".content-table-cell").addClass("noHidden");
        //$(this).find(".content-table-cell-interno").addClass("noHidden");
        $(this).find(".img-empresa").addClass("translateImgEmpresa");
    }, function () {
        //$(this).find(".content-table-cell").removeClass("noHidden");
        //$(this).find(".content-table-cell-interno").removeClass("noHidden");
        $(this).find(".img-empresa").removeClass("translateImgEmpresa");
    });
};

var obj = new PostulacionEmpleobusco();
var AquiEmpleos = function (opts) {
    var Errors = $('.errorMesajes');

    this.login = function () {
        var a = $('#loginP');
        var form1 = $("#frmUserLogIn");
        var pasword = $("#txtPasswordLogin");

        form1.submit(function (e) {
            e.preventDefault();
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
                    'chkRemember': tipoEP,
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
        $("#txtPasswordLogin").keydown(function (event) {
            if (event.which == 13) {
                $("#frmUserLogIn").submit();
            }
        });
        $("#loginP").click(function (event) {
            $("#frmUserLogIn").submit();

        });

    }
    this.registro = function () {
        var r = $('#registroP');
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
    this.autocomplit = function () {

        var urlGet = "/home/filtrar-avisos/";
        var options = {
            url: urlGet,
            getValue: "mostrar",
            list: {
                match: {
                    enabled: false
                },
                maxNumberOfElements: 10,
            },
            ajaxSettings: {
                'type': 'POST',
                dataType: "json",
                data: {
                    value: 'pr' // $(this).val()
                }
            },
            listLocation: "items",
            template: {
                type: "custom",
                method: function (val, data) {
                    return "<a href='" + data.id + "' >" + val + "</a>";
                }
            }
        };
        $(document).keypress(function (e) {
            if (e.which == 13) {
                e.preventDefault();
                var ubicacion = $('#pais').val();
                var puesto = $('#q').val();
                var slugUbicacion = '';
                var slugPuesto = '';
                if (ubicacion != 'none') {
                    //slugUbicacion = '/ubicacion/' + ubicacion
                    slugUbicacion = ubicacion;
                }
                if (puesto != '') {
                    slugPuesto = '/q/' + puesto
                }
                var relf = $('#pais').val();
                window.location = '/' + slugUbicacion + '/buscar' + slugPuesto;
            }
        });
        $("#q").easyAutocomplete(options);
    }
    this.buscar = function () {
        $("#buscar").click(function (e) {
            e.preventDefault();
            var ubicacion = $('#pais').val();
            var puesto = $('#q').val();
            var slugUbicacion = '';
            var slugPuesto = '';
            if (ubicacion != 'none') {
                //slugUbicacion = '/ubicacion/' + ubicacion
                slugUbicacion = ubicacion;
            }
            if (puesto != '') {
                slugPuesto = '/q/' + puesto
            }
            var relf = $('#pais').val();
            window.location = '/' + slugUbicacion + '/buscar' + slugPuesto;
        });
    }

    this.buscarAreas = function () {
        $('.buscar-area').click(function (e) {
            e.preventDefault();
            var ubicacion = $('#pais').val();
            var area = $(this).attr('data-src');
            window.location = '/' + ubicacion + area;
        });
    }
    this.ingresarFacebook = function () {
        $('.ingresar-facebook').click(function (e) {
            e.preventDefault();
            var src = $('.ingresar-facebook').attr('data-src');
            window.location = src;
        });
    }
    this.loginglobal = function () {
        if (window.location.hash.indexOf('loginP') !== -1) {
            $("#modalLoginUser").modal();
        }
         if (window.location.hash.indexOf('modalLoginUser') !== -1) {
            $("#modalLoginUser").modal();
        }
    }
    this.registroGloval = function () {
        if (window.location.hash.indexOf('modalRegisterUser') !== -1) {
            $("#modalRegisterUser").modal();
        }
    }
};

$(document).on("ready", inicio)
function inicio()
{
    $(".help-password a").on("click", abrirModalOlvideContra)
    validarRecuperarContrasenia();
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
