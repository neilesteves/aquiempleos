var empleo = new AquiEmpleos();
$('#datetimepicker4').datetimepicker({
    format: 'DD/MM/YYYY'
});
empleo.login();
empleo.registro();
empleo.autocomplit();
empleo.buscar();
empleo.buscarAreas();
empleo.ingresarFacebook();
empleo.loginglobal();
empleo.registroGloval();
