
yOSON.AppSchema.modules = {
	'postulante': {
		controllers: {
			'home': {
				actions: {
					'index': function () {
						yOSON.AppCore.runModule('viewmore_tooltip');
						//yOSON.AppCore.runModule('animate_logo_companies');
						yOSON.AppCore.runModule('lazy_load',[{ wrap : '.recent-ads' },{ wrap : '.list-overflow' }]);
						yOSON.AppCore.runModule('search_filters');
						yOSON.AppCore.runModule('lazy_facebook_load');
					},
					'byDefault': function () {

					}
				},
				allActions: function () {

				}
			},
			'buscar': {
				actions: {
					'index': function () {
						yOSON.AppCore.runModule('busqueda_principal');
						yOSON.AppCore.runModule('accordion_effect',[{divAction: '.aptitus-title'}]);
						yOSON.AppCore.runModule('open_close_view_more');
					},
					'busqueda-avanzada': function () {
						yOSON.AppCore.runModule('accordion_effect',[{context : '.list-career',
							divAction : '.title', classOpenAction : 'on'}]);
						yOSON.AppCore.runModule('open_close_view_more');
						yOSON.AppCore.runModule('validate_key',[
							{ txtInput: '#txtAdvanceSearch', type: 'all' }
						]);
					},
					'byDefault': function () {
					}
				},
				allActions: function () {
				}
			},
			'upc': {
				actions: {
					'index': function () {
						yOSON.AppCore.runModule('parsley_validation');
						yOSON.AppCore.runModule('accordion_effect',[{divAction: '.accordeon'}]);
					},
					'byDefault': function () {
					}
				},
				allActions: function () {

				}
			},
			'registro': {
				actions: {
					'paso1': function () {
						yOSON.AppCore.runModule('applicant_registration');
						yOSON.AppCore.runModule('validate_input_ajax',[
						{
							context	: '#frmPostulantRegistration',
							urlAjaxValidate: '/registro/validar-email/',
							txtInput: '#txtEmail',
							dataAjaxAttr: { email: 'value', rol : 'data-rol'},
							dataAjax: {modulo: 'postulante'}
						},
						{
							context	: '#frmPostulantRegistration',
							urlAjaxValidate: '/registro/validar-dni/',
							txtInput: '#txtDocument',
							dataAjaxAttr: { ndoc: 'value', idPost: 'data-rel' }
						}
						]);

						yOSON.AppCore.runModule('validate_daybitrh');
						yOSON.AppCore.runModule('location_job',{context: '#frmPostulantRegistration'});
						yOSON.AppCore.runModule('file_uploader', [{
							frm: 'frmPostulantRegistration',
							srcDefault: yOSON.statHost + '/images/profile-default.jpg',
							urlDelete: '/postulante/registro/eliminarfoto'
						}]);
						yOSON.AppCore.runModule('count_character_for_skills',{
							input: 'txtAboutForYou',
							maxCharacter: 750
						});
					},
					'paso1-modificar': function () {
						yOSON.AppCore.runModule('applicant_registration');
						yOSON.AppCore.runModule('validate_input_ajax',[
						{
							context	: '#frmPostulantRegistration',
							urlAjaxValidate: '/registro/validar-email/',
							txtInput: '#txtEmail',
							dataAjaxAttr: { email: 'value', rol : 'data-rol'},
							dataAjax: {modulo: 'postulante'}
						},
						{
							context	: '#frmPostulantRegistration',
							urlAjaxValidate: '/registro/validar-dni/',
							txtInput: '#txtDocument',
							dataAjaxAttr: { ndoc: 'value', idPost: 'data-rel' }
						}
						]);

						yOSON.AppCore.runModule('validate_daybitrh');
						yOSON.AppCore.runModule('location_job',{context: '#frmPostulantRegistration'});
						yOSON.AppCore.runModule('file_uploader', [{
							frm: 'frmPostulantRegistration',
							srcDefault: yOSON.statHost + '/images/profile-default.jpg',
							urlDelete: '/postulante/registro/eliminarfoto'
						}]);
						yOSON.AppCore.runModule('count_character_for_skills',{
							input: 'txtAboutForYou',
							maxCharacter: 750
						});
					},
					'paso2': function () {
						yOSON.AppCore.runModule('mini_validate', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF' },
								programs	: { context: '#programsF' }
							}
						});
						yOSON.AppCore.runModule('skill_add', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF', template: '#tplExperience' },
								estudio		: { context: '#studyF', template: '#tplStudy' },
								otroEstudio : { context: '#studyOtherF', template: '#tplStudyOther' },
								idioma		: { context: '#languagesF', template: '#tplLanguage', separate: 'nivel', validRepeat: true },
								programs	: { context: '#programsF', template: '#tplPrograms', separate: 'nivel', validRepeat: true }
							}
						});
						yOSON.AppCore.runModule('skill_edit', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF', separate: 'nivel' },
								programs	: { context: '#programsF', separate: 'nivel' }
							}
						});
						yOSON.AppCore.runModule('skill_remove', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF' },
								programs	: { context: '#programsF' }
							}
						});

						//Estudios
						yOSON.AppCore.runModule('study_options');
						yOSON.AppCore.runModule('combos_depends',[
							{ context: '#studyF', selParent: '_id_tipo_carrera', selChild: 'id_carrera',
								urlAjax: '/registro/filtrar-carrera/', paramAjax: 'id_tipo_carrera' },
							{ context: '#studyF', selParent: '_id_nivel_estudio', selChild: '_id_nivel_estudio_tipo',
								urlAjax: '/home/filtrar-tipo-estudio/', paramAjax: 'id_nivel_estudio', jsonDefault: false, arrExceptions: [0,1,2,3] }
						]);


						//All
						yOSON.AppCore.runModule('hide_skill_block',[
							{ context: '#experienceF', chkInput: '#chkExperience' },
							{ context: '#studyF', chkInput: '#chkStudy' }
						]);
						yOSON.AppCore.runModule('input_show_more', [{
							context: '#experienceF', inputTagName: '_lugar',
							option: [{name: '_tipo_proyecto'}, {name: '_nombre_proyecto'}, {name: '_costo_proyecto'}]
						},
						{
							context: '#experienceF', inputTagName: '_nivel_puesto',
							option: {'10' : [{name: '_otro_nivel_puesto'}]}
						},
						{
							context: '#experienceF', inputTagName: '_id_puesto',
							option: {'1292' : [{name: '_otro_puesto', title: 'first_title'}]}
						},
						{
							context: '#studyF', inputTagName: '_id_carrera',
							option: {'Otros' : [{name: '_otro_carrera', title: 'first_title'}]},
							label : true
						},
						{
							context: '#studyF', inputTagName: '_id_nivel_estudio_tipo',
							option: {'18' : [{name: '_colegiatura_numero'}]}
						}]);
						yOSON.AppCore.runModule('autocomplete_text', [{widthList: 430}]);


						yOSON.AppCore.runModule('update_date', [
							{selTag: '_inicio_ano', selDepend: '_fin_ano'},
							{selTag: '_inicio_mes', selDepend: '_fin_mes'}
						]);
						yOSON.AppCore.runModule('disable_combos',[
							{chkName: '_en_curso', disableds: ['_fin_mes','_fin_ano']}
						]);
						yOSON.AppCore.runModule('count_character_for_skills');

						yOSON.AppCore.runModule('accordion_effect',[{divAction: '.blue-title'}]);
						yOSON.AppCore.runModule('validate_skill_form');
						yOSON.AppCore.runModule('file_uploader', [{
							frm: 'frmSkills',
							wrapFile: '#cvF',
							customFil: {
								classesButton: 'btn btn-primary',
								classesInput: 'input-xmedium mR20',
								txtBtn: 'Subir',
								placeholder: '¿Cuentas con una versión de tu CV? ¡Actualizala!'
							},
							btnDelete: '#deleteCvP',
							divImage: '.wrap-controls',
							filLogo: '#pCV',
							isImage: false,
							messageSize: '256kb',
							messageType: 'El archivo debe ser DOC, DOCX o PDF.',
							regex: /\.(doc|docx|pdf)$/i,
							urlDelete: '/subir-cv/eliminar-cv',
							urlUpload: '/subir-cv',
						}]);
					},
					'paso3': function () {
						yOSON.AppCore.runModule('viewmore_tooltip');
					},
					'byDefault': function () {
					}
				},
				allActions: function () {

				}
			},
			'mi-cuenta': {
				actions: {
					'index': function () {
						yOSON.AppCore.runModule('bar_animation');
					},
					'mis-datos-personales': function () {
						yOSON.AppCore.runModule('applicant_registration');
						yOSON.AppCore.runModule('validate_input_ajax',[
						{
							context	: '#frmPostulantRegistration',
							urlAjaxValidate: '/registro/validar-email/',
							txtInput: '#txtEmail',
							dataAjaxAttr: { email: 'value', rol : 'data-rol'},
							dataAjax: {modulo: 'postulante'}
						},
						{
							context	: '#frmPostulantRegistration',
							urlAjaxValidate: '/registro/validar-dni/',
							txtInput: '#txtDocument',
							dataAjaxAttr: { ndoc: 'value', idPost: 'data-rel' }
						}
						]);

						yOSON.AppCore.runModule('validate_daybitrh');
						yOSON.AppCore.runModule('location_job',{context: '#frmPostulantRegistration'});
						yOSON.AppCore.runModule('file_uploader', [{
							frm: 'frmPostulantRegistration',
							srcDefault: yOSON.statHost + '/images/profile-default.jpg',
							urlDelete: '/postulante/registro/eliminarfoto'
						}]);
						yOSON.AppCore.runModule('count_character_for_skills',{
							input: 'txtAboutForYou',
							maxCharacter: 750
						});
					},
					'cambio-de-clave' : function () {
						yOSON.AppCore.runModule('applicant_registration', {
							frm					: '#frmChangePassword',
							txtPassword			: '#txtNewPassword',
							txtRepeatPassword	: '#txtRepeatPassword'
						});
					},
					'actualiza': function () {
						yOSON.AppCore.runModule('mini_validate', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF' },
								programs	: { context: '#programsF' }
							}
						});
						yOSON.AppCore.runModule('skill_add', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF', template: '#tplExperience' },
								estudio		: { context: '#studyF', template: '#tplStudy' },
								otroEstudio : { context: '#studyOtherF', template: '#tplStudyOther' },
								idioma		: { context: '#languagesF', template: '#tplLanguage', separate: 'nivel', validRepeat: true },
								programs	: { context: '#programsF', template: '#tplPrograms', separate: 'nivel', validRepeat: true }
							}
						});
						yOSON.AppCore.runModule('skill_edit', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF', separate: 'nivel' },
								programs	: { context: '#programsF', separate: 'nivel' }
							}
						});
						yOSON.AppCore.runModule('skill_remove', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF' },
								programs	: { context: '#programsF' }
							}
						});

						//Estudios
						yOSON.AppCore.runModule('study_options');
						yOSON.AppCore.runModule('combos_depends',[
							{ context: '#studyF', selParent: '_id_tipo_carrera', selChild: 'id_carrera',
								urlAjax: '/registro/filtrar-carrera/', paramAjax: 'id_tipo_carrera' },
							{ context: '#studyF', selParent: '_id_nivel_estudio', selChild: '_id_nivel_estudio_tipo',
								urlAjax: '/home/filtrar-tipo-estudio/', paramAjax: 'id_nivel_estudio', jsonDefault: false, arrExceptions: [0,1,2,3] }
						]);


						//All
						yOSON.AppCore.runModule('hide_skill_block',[
							{ context: '#experienceF', chkInput: '#chkExperience' },
							{ context: '#studyF', chkInput: '#chkStudy' }
						]);
						yOSON.AppCore.runModule('input_show_more', [{
							context: '#experienceF', inputTagName: '_lugar',
							option: [{name: '_tipo_proyecto'}, {name: '_nombre_proyecto'}, {name: '_costo_proyecto'}]
						},
						{
							context: '#experienceF', inputTagName: '_nivel_puesto',
							option: {'10' : [{name: '_otro_nivel_puesto'}]}
						},
						{
							context: '#experienceF', inputTagName: '_id_puesto',
							option: {'1292' : [{name: '_otro_puesto', title: 'first_title'}]}
						},
						{
							context: '#studyF', inputTagName: '_id_carrera',
							option: {'Otros' : [{name: '_otro_carrera', title: 'first_title'}]},
							label : true
						},
						{
							context: '#studyF', inputTagName: '_id_nivel_estudio_tipo',
							option: {'18' : [{name: '_colegiatura_numero'}]}
						}]);

						yOSON.AppCore.runModule('count_character_for_skills');
						yOSON.AppCore.runModule('autocomplete_text', [{widthList: 430}]);
						yOSON.AppCore.runModule('file_uploader', [{
							frm: 'frmUpdate',
							wrapFile: '#cvF',
							customFil: {
								classesButton: 'btn btn-primary',
								classesInput: 'input-xmedium mR20',
								txtBtn: 'Subir',
								placeholder: '¿Cuentas con una versión de tu CV? ¡Actualizala!'
							},
							btnDelete: '#deleteCvP',
							divImage: '.wrap-controls',
							filLogo: '#pCV',
							isImage: false,
							messageSize: '256kb',
							messageType: 'El archivo debe ser DOC, DOCX o PDF.',
							regex: /\.(doc|docx|pdf)$/i,
							urlDelete: '/subir-cv/eliminar-cv',
							urlUpload: '/subir-cv',
						}]);

						yOSON.AppCore.runModule('validate_skill_form');
						yOSON.AppCore.runModule('accordion_effect',[{divAction: '.blue-title'}]);
					},
					'mis-experiencias': function () {
						yOSON.AppCore.runModule('mini_validate', {
							"Perfil_postulante" : { experiencia : { context: '#experienceF', btn: '#btnExperience' }}
						});
						yOSON.AppCore.runModule('skill_add', {
							"Perfil_postulante" : { experiencia : { context: '#experienceF', template: '#tplExperience', btn: '#btnExperience' }}
						});
						yOSON.AppCore.runModule('skill_edit', {
							"Perfil_postulante" : { experiencia : { context: '#experienceF' }}
						});
						yOSON.AppCore.runModule('skill_remove', {
							"Perfil_postulante" : { experiencia : { context: '#experienceF' }}
						});

						yOSON.AppCore.runModule('hide_skill_block',[
							{ context: '#experienceF', chkInput: '#chkExperience' }
						]);
						yOSON.AppCore.runModule('input_show_more', [{
							context: '#experienceF', inputTagName: '_lugar',
							option: [{name: '_tipo_proyecto'}, {name: '_nombre_proyecto'}, {name: '_costo_proyecto'}]
						},
						{
							context: '#experienceF', inputTagName: '_nivel_puesto',
							option: {'10' : [{name: '_otro_nivel_puesto'}]}
						},
						{
							context: '#experienceF', inputTagName: '_id_puesto',
							option: {'1292' : [{name: '_otro_puesto', title: 'first_title'}]}
						}]);
						yOSON.AppCore.runModule('count_character_for_skills');
					},
					'mis-estudios' : function(){
						yOSON.AppCore.runModule('mini_validate', {
							"Perfil_postulante" : { estudio		: { context: '#studyF', btn: '#btnStudy' } }
						});
						yOSON.AppCore.runModule('skill_add', {
							"Perfil_postulante" : { estudio		: { context: '#studyF', template: '#tplStudy', btn: '#btnStudy' } }
						});
						yOSON.AppCore.runModule('skill_edit', {
							"Perfil_postulante" : { estudio		: { context: '#studyF' } }
						});
						yOSON.AppCore.runModule('skill_remove', {
							"Perfil_postulante" : { estudio		: { context: '#studyF' } }
						});

						yOSON.AppCore.runModule('study_options');
						yOSON.AppCore.runModule('autocomplete_text');
						yOSON.AppCore.runModule('combos_depends',[
							{ context: '#studyF', selParent: '_id_tipo_carrera', selChild: 'id_carrera',
								urlAjax: '/registro/filtrar-carrera/', paramAjax: 'id_tipo_carrera' },
							{ context: '#studyF', selParent: '_id_nivel_estudio', selChild: '_id_nivel_estudio_tipo',
								urlAjax: '/home/filtrar-tipo-estudio/', paramAjax: 'id_nivel_estudio', jsonDefault: false, arrExceptions: [0,1,2,3] }
						]);

						yOSON.AppCore.runModule('hide_skill_block',[
							{ context: '#studyF', chkInput: '#chkStudy' }
						]);
						yOSON.AppCore.runModule('input_show_more', [{
							context: '#studyF', inputTagName: '_id_carrera',
							option: {'Otros' : [{name: '_otro_carrera', title: 'first_title'}]},
							label : true
						},
						{
							context: '#studyF', inputTagName: '_id_nivel_estudio_tipo',
							option: {'18' : [{name: '_colegiatura_numero'}]}
						}]);
					},
					'mis-otros-estudios': function () {
						yOSON.AppCore.runModule('mini_validate', {
							"Perfil_postulante" : { otroEstudio : { context: '#studyOtherF', btn: '#btnOtherStudy' } }
						});
						yOSON.AppCore.runModule('skill_add', {
							"Perfil_postulante" : { otroEstudio : { context: '#studyOtherF', template: '#tplStudyOther', btn: '#btnOtherStudy' } }
						});
						yOSON.AppCore.runModule('skill_edit', {
							"Perfil_postulante" : { otroEstudio : { context: '#studyOtherF' } }
						});
						yOSON.AppCore.runModule('skill_remove', {
							"Perfil_postulante" : { otroEstudio : { context: '#studyOtherF' } }
						});
					},
					'mis-idiomas': function () {
						yOSON.AppCore.runModule('mini_validate', {
							"Perfil_postulante" : { idioma: { context: '#languagesF', btn: '#btnLanguage' } }
						});
						yOSON.AppCore.runModule('skill_add', {
							"Perfil_postulante" : { idioma: { context: '#languagesF', template: '#tplLanguage', btn: '#btnLanguage' , separate: 'nivel', validRepeat: true } }
						});
						yOSON.AppCore.runModule('skill_edit', {
							"Perfil_postulante" : { idioma: { context: '#languagesF', separate: 'nivel' } }
						});
						yOSON.AppCore.runModule('skill_remove', {
							"Perfil_postulante" : { idioma: { context: '#languagesF' } }
						});
					},
					'mis-programas': function () {
						yOSON.AppCore.runModule('mini_validate', {
							"Perfil_postulante" : { programs: { context: '#programsF', btn: '#btnPrograms' } }
						});
						yOSON.AppCore.runModule('skill_add', {
							"Perfil_postulante" : { programs: { context: '#programsF', template: '#tplPrograms', btn: '#btnPrograms' , separate: 'nivel', validRepeat: true } }
						});
						yOSON.AppCore.runModule('skill_edit', {
							"Perfil_postulante" : { programs: { context: '#programsF', separate: 'nivel' } }
						});
						yOSON.AppCore.runModule('skill_remove', {
							"Perfil_postulante" : { programs: { context: '#programsF' } }
						});
					},
					'mis-referencias': function () {
						yOSON.AppCore.runModule('mini_validate', {
							"Perfil_postulante" : { programs	: { context: '#referenceF', btn: '#btnReference' } }
						});
						yOSON.AppCore.runModule('skill_add', {
							"Perfil_postulante" : { programs	: { context: '#referenceF', template: '#tplReference', btn: '#btnReference', separate: 'es'} }
						});
						yOSON.AppCore.runModule('skill_edit', {
							"Perfil_postulante" : { programs	: { context: '#referenceF', separate: 'es' } }
						});
						yOSON.AppCore.runModule('skill_remove', {
							"Perfil_postulante" : { programs	: { context: '#referenceF' } }
						});
					},
					'byDefault': function () {
					}
				},
				allActions: function () {
					yOSON.AppCore.runModule('update_date', [
						{selTag: '_inicio_ano', selDepend: '_fin_ano'},
						{selTag: '_inicio_mes', selDepend: '_fin_mes'}
					]);
					yOSON.AppCore.runModule('disable_combos',[
						{chkName: '_en_curso', disableds: ['_fin_mes','_fin_ano']}
					]);
				}
			},
			'perfil-destacado': {
				actions: {
					'paso2': function () {
						yOSON.AppCore.runModule('modal_all', [{maxWidth: 540, maxHeight: 500}]);
					},
					'byDefault': function () {

					}
				},
				allActions: function () {
				}
			},
			'subir-cv': {
				actions: {
					'index': function () {
						yOSON.AppCore.runModule('file_uploader', [{
							frm: 'cvF',
							wrapFile: '#cvF',
							customFil: {
								classesButton: 'btn btn-primary',
								classesInput: 'input-xmedium mR20',
								txtBtn: 'Subir',
								placeholder: '¿Cuentas con una versión de tu CV? ¡Actualizala!'
							},
							btnDelete: '#deleteCvP',
							divImage: '.wrap-controls',
							filLogo: '#pCV',
							isImage: false,
							messageSize: '256kb',
							messageType: 'El archivo debe ser DOC, DOCX o PDF.',
							regex: /\.(doc|docx|pdf)$/i,
							urlDelete: '/subir-cv/eliminar-cv',
							urlUpload: '/subir-cv',
						}]);
					},
					'byDefault': function () {

					}
				},
				allActions: function () {
				}
			},
			'comprar-perfil': {
				actions: {
					'pago-efectivo' : function(){
						yOSON.AppCore.runModule('accordion_effect',[{context: '.accordion_affiliated_office', divIcon: 'span', contentOpen: '+', contentClose: '-', oneAtATime: true}]);
					},
					'byDefault': function () {
					}
				},
				allActions: function(){
				}
			},
			'bolsas-de-trabajo': {
				actions: {
					'index': function () {
						yOSON.AppCore.runModule('search_tcn');
					},
					'byDefault': function () {

					}
				},
				allActions: function () {
				}
			},
			byDefault: function () {},
			allActions: function () {}
		},
		byDefault: function () {},
		allControllers: function () {}
	},
	'empresa': {
		controllers: {
			'home': {
				actions: {
					'index': function () {
						yOSON.AppCore.runModule('modal_contact',{
							modal: '#divModalContact',
							frm: '#frmContactMemberShip',
							btnModal: '#btnSendMemberShip',
							urlAjax: '/home/envio-correo-membresia'
						});
					},
					'membresia-anual' : function () {
						yOSON.AppCore.runModule('link_all_block');
					},
					'seleccion': function () {
						yOSON.AppCore.runModule('modal_contact');
					},
					'byDefault': function () {
					}
				},
				allActions: function () {
				}
			},
			'mi-cuenta': {
				actions: {
					'datos-empresa': function () {
						yOSON.AppCore.runModule('company_registration');
						yOSON.AppCore.runModule('validate_input_ajax',[
						{
							context	: '#frmCompanyRegistration',
							urlAjaxValidate: '/registro/validar-email/',
							txtInput: '#txtEmail',
							dataAjaxAttr: { email: 'value'},
							dataAjax: {modulo: 'empresa'}
						},
						{
							context	: '#frmCompanyRegistration',
							urlAjaxValidate: '/empresa/registro-empresa/validar-nombrecomercial/',
							txtInput: '#txtNombreComercial',
							dataAjaxAttr: { ndoc: 'value', idEmp: 'rel' }
						},
						{
							context	: '#frmCompanyRegistration',
							urlAjaxValidate: '/empresa/registro-empresa/validar-razonsocial/',
							txtInput: '#txtRazonSocial',
							dataAjaxAttr: { ndoc: 'value', idEmp: 'rel' }
						},
						{
							context	: '#frmCompanyRegistration',
							urlAjaxValidate: '/empresa/registro-empresa/validar-ruc/',
							txtInput: '#txtRuc',
							dataAjaxAttr: { ndoc: 'value', idEmp: 'rel' }
						}
						]);
						yOSON.AppCore.runModule('location_job',{context: '#frmCompanyRegistration'});
						yOSON.AppCore.runModule('file_uploader', [{frm: 'frmCompanyRegistration'}]);
					},
					'cambio-clave' : function () {
						yOSON.AppCore.runModule('applicant_registration', {
							frm					: '#frmChangePassword',
							txtPassword			: '#txtNewPassword',
							txtRepeatPassword	: '#txtRepeatPassword'
						});
					},
					'membresias' : function () {
						yOSON.AppCore.runModule('modal_view_membership');
					},
					'byDefault': function () {
					}
				},
				allActions: function () {
				}
			},
			'registro-empresa': {
				actions: {
					'index': function () {
						yOSON.AppCore.runModule('company_registration');
						yOSON.AppCore.runModule('validate_input_ajax',[
						{
							context	: '#frmCompanyRegistration',
							urlAjaxValidate: '/registro/validar-email/',
							txtInput: '#txtEmail',
							dataAjaxAttr: { email: 'value'},
							dataAjax: {modulo: 'empresa'}
						},
						{
							context	: '#frmCompanyRegistration',
							urlAjaxValidate: '/empresa/registro-empresa/validar-nombrecomercial/',
							txtInput: '#txtNombreComercial',
							dataAjaxAttr: { ndoc: 'value', idEmp: 'rel' }
						},
						{
							context	: '#frmCompanyRegistration',
							urlAjaxValidate: '/empresa/registro-empresa/validar-razonsocial/',
							txtInput: '#txtRazonSocial',
							dataAjaxAttr: { ndoc: 'value', idEmp: 'rel' }
						},
						{
							context	: '#frmCompanyRegistration',
							urlAjaxValidate: '/empresa/registro-empresa/validar-ruc/',
							txtInput: '#txtRuc',
							dataAjaxAttr: { ndoc: 'value', idEmp: 'rel' }
						}
						]);
						yOSON.AppCore.runModule('location_job',{context: '#frmCompanyRegistration'});
						yOSON.AppCore.runModule('file_uploader', [{frm: 'frmCompanyRegistration'}]);
					},
					'byDefault': function () {
					}
				},
				allActions: function () {
				}
			},
			'buscador-aptitus': {
				actions: {
					'index': function () {
						yOSON.AppCore.runModule('busqueda_principal', [{url: '/empresa/buscador-aptitus', isGet: true}]);
						yOSON.AppCore.runModule('accordion_effect', [
							{divAction: '.aptitus-title'},
							{divAction: '.accord'}
						]);
						yOSON.AppCore.runModule('open_close_view_more');
						yOSON.AppCore.runModule('search_checked');
						yOSON.AppCore.runModule('my_search_save');
						yOSON.AppCore.runModule('my_search_delete');
						yOSON.AppCore.runModule('validate_key',[
							{ txtInput: '#txtNameSaveSearch', type: 'all' }
						]);
						yOSON.AppCore.runModule('show_tooltip');
						yOSON.AppCore.runModule('modal_all');
						yOSON.AppCore.runModule('autocomplete_tags');
					},
					'perfil-publico-emp': function () {
						yOSON.AppCore.runModule('modal_send_email');
						yOSON.AppCore.runModule('mini_validate',[{
							context: '#frmSendEmail', btn: '#btnSendEmail', scrollActive: false
						}]);
						yOSON.AppCore.runModule('count_character_for_skills', {input: 'mensajeCompartir', maxCharacter: 300});
						//yOSON.AppCore.runModule('modal_invitar_anuncio');
					},
					'perfil-publico-emp-solr': function () {
						yOSON.AppCore.runModule('modal_send_email');
						yOSON.AppCore.runModule('mini_validate',[{
							context: '#frmSendEmail', btn: '#btnSendEmail', scrollActive: false
						}]);
						yOSON.AppCore.runModule('count_character_for_skills', {input: 'mensajeCompartir', maxCharacter: 300});

						yOSON.AppCore.runModule('modal_invitar_anuncio');
					},
					'byDefault': function () {
					}
				},
				allActions: function () {
				}
			},
			'comprar-aviso': {
				actions: {
					'pago-efectivo' : function(){
						yOSON.AppCore.runModule('accordion_effect',[{context: '.accordion_affiliated_office', divIcon: 'span', contentOpen: '+', contentClose: '-', oneAtATime: true}]);
					},
					'byDefault': function () {
					}
				},
				allActions: function(){
				}
			},
			'aviso': {
				actions: {
					'editar2': function () {
						yOSON.AppCore.runModule('file_uploader', [{frm: 'frmPublishAd'}]);
						yOSON.AppCore.runModule('change_name_company');
						yOSON.AppCore.runModule('location_job');

						yOSON.AppCore.runModule('mini_validate', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF' },
								programs	: { context: '#programsF' },
								preguntas	: { context: '#preguntasF' }
							}
						});
						yOSON.AppCore.runModule('skill_add', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF', template: '#tplExperience' },
								estudio		: { context: '#studyF', template: '#tplStudy' },
								otroEstudio : { context: '#studyOtherF', template: '#tplStudyOther' },
								idioma		: { context: '#languagesF', template: '#tplLanguage', separate: 'nivel', validRepeat: true },
								programs	: { context: '#programsF', template: '#tplPrograms', separate: 'nivel', validRepeat: true },
								preguntas	: { context: '#preguntasF', template: '#tplQuestions' }
							}
						});
						yOSON.AppCore.runModule('skill_edit', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF', separate: 'nivel' },
								programs	: { context: '#programsF', separate: 'nivel' },
								preguntas	: { context: '#preguntasF' }
							}
						});
						yOSON.AppCore.runModule('skill_remove', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF' },
								programs	: { context: '#programsF' },
								preguntas	: { context: '#preguntasF' }
							}
						});

						//Estudios
						yOSON.AppCore.runModule('study_options_ad');
						yOSON.AppCore.runModule('combos_depends',[
							{ context: '#studyF', selParent: '_id_nivel_estudio', selChild: '_id_nivel_estudio_tipo',
								urlAjax: '/home/filtrar-tipo-estudio/', paramAjax: 'id_nivel_estudio', jsonDefault: false, arrExceptions: [0,1,2,3]},
							{ context: '#studyF', selParent: '_id_tipo_carrera', selChild: '_id_carrera',
								urlAjax: '/registro/filtrar-carrera/', paramAjax: 'id_tipo_carrera'}
						]);

						//All
						yOSON.AppCore.runModule('input_show_more', [{
							context: '#studyF', inputTagName: '_id_carrera',
							option: {'Otros' : [{name: '_otra_carrera', title: 'second_title'}]},
							label : true
						}]);

						yOSON.AppCore.runModule('validate_form_ad');
						yOSON.AppCore.runModule('validate_key',[
							{ txtInput: '#funciones', type: 'no_arroba' },
							{ txtInput: '#responsabilidades', type: 'no_arroba' },
							{ txtInput: '#preguntasF textarea', type: 'no_arroba' }
						]);
						yOSON.AppCore.runModule('accordion_effect',[{divAction: '.blue-title'}]);
					},
                                        'editar': function () {
						yOSON.AppCore.runModule('file_uploader', [{frm: 'frmPublishAd'}]);
						yOSON.AppCore.runModule('change_name_company');
						yOSON.AppCore.runModule('location_job');

						yOSON.AppCore.runModule('mini_validate', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF' },
								programs	: { context: '#programsF' },
								preguntas	: { context: '#preguntasF' }
							}
						});
						yOSON.AppCore.runModule('skill_add', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF', template: '#tplExperience' },
								estudio		: { context: '#studyF', template: '#tplStudy' },
								otroEstudio : { context: '#studyOtherF', template: '#tplStudyOther' },
								idioma		: { context: '#languagesF', template: '#tplLanguage', separate: 'nivel', validRepeat: true },
								programs	: { context: '#programsF', template: '#tplPrograms', separate: 'nivel', validRepeat: true },
								preguntas	: { context: '#preguntasF', template: '#tplQuestions' }
							}
						});
						yOSON.AppCore.runModule('skill_edit', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF', separate: 'nivel' },
								programs	: { context: '#programsF', separate: 'nivel' },
								preguntas	: { context: '#preguntasF' }
							}
						});
						yOSON.AppCore.runModule('skill_remove', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF' },
								programs	: { context: '#programsF' },
								preguntas	: { context: '#preguntasF' }
							}
						});

						//Estudios
						yOSON.AppCore.runModule('study_options_ad');
						yOSON.AppCore.runModule('combos_depends',[
							{ context: '#studyF', selParent: '_id_nivel_estudio', selChild: '_id_nivel_estudio_tipo',
								urlAjax: '/home/filtrar-tipo-estudio/', paramAjax: 'id_nivel_estudio', jsonDefault: false, arrExceptions: [0,1,2,3]},
							{ context: '#studyF', selParent: '_id_tipo_carrera', selChild: '_id_carrera',
								urlAjax: '/registro/filtrar-carrera/', paramAjax: 'id_tipo_carrera'},

							{ context: '#basicDataF', selParent: 'id_area', selChild: 'id_nivel_puesto', urlAjax: '/mi-cuenta/filtrar-nivel-area/', paramAjax: 'id_area'},
							//{ context: '#experienceF', selParent: 'managerExperiencia_0_id_area', selChild: 'managerExperiencia_0_id_nivel_puesto', urlAjax: '/mi-cuenta/filtrar-nivel-area/', paramAjax: 'id_area'}
						]);

						//All
						yOSON.AppCore.runModule('input_show_more', [{
							context: '#studyF', inputTagName: '_id_carrera',
							option: {'Otros' : [{name: '_otra_carrera', title: 'second_title'}]},
							label : true
						},{
							context: '#basicDataF', inputTagName: 'id_puesto',
							option: {'Otros' : [{name: 'nombre_puesto'}]}, divWrap: '.control-group',
							label : true
						}]);

						yOSON.AppCore.runModule('validate_form_ad');
						yOSON.AppCore.runModule('validate_key',[
							{ txtInput: '#funciones', type: 'no_arroba' },
							{ txtInput: '#responsabilidades', type: 'no_arroba' },
							{ txtInput: '#preguntasF textarea', type: 'no_arroba' }
						]);
						yOSON.AppCore.runModule('accordion_effect',[{divAction: '.blue-title'}]);
					},
					'byDefault': function () {
					}
				},
				allActions: function () {
				}
			},
			'publica-aviso': {
				actions: {
					'index': function () {
						yOSON.AppCore.runModule('file_uploader', [{frm: 'frmPublishAd'}]);
						yOSON.AppCore.runModule('change_name_company');
						yOSON.AppCore.runModule('location_job');

						yOSON.AppCore.runModule('mini_validate', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF' },
								programs	: { context: '#programsF' },
								preguntas	: { context: '#preguntasF' }
							}
						});
						yOSON.AppCore.runModule('skill_add', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF', template: '#tplExperience' },
								estudio		: { context: '#studyF', template: '#tplStudy' },
								otroEstudio : { context: '#studyOtherF', template: '#tplStudyOther' },
								idioma		: { context: '#languagesF', template: '#tplLanguage', separate: 'nivel', validRepeat: true },
								programs	: { context: '#programsF', template: '#tplPrograms', separate: 'nivel', validRepeat: true },
								preguntas	: { context: '#preguntasF', template: '#tplQuestions' }
							}
						});
						yOSON.AppCore.runModule('skill_edit', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF', separate: 'nivel' },
								programs	: { context: '#programsF', separate: 'nivel' },
								preguntas	: { context: '#preguntasF' }
							}
						});
						yOSON.AppCore.runModule('skill_remove', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF' },
								programs	: { context: '#programsF' },
								preguntas	: { context: '#preguntasF' }
							}
						});

						//Estudios
						yOSON.AppCore.runModule('study_options_ad');
						yOSON.AppCore.runModule('combos_depends',[
							{ context: '#studyF', selParent: '_id_nivel_estudio', selChild: '_id_nivel_estudio_tipo',
								urlAjax: '/home/filtrar-tipo-estudio/', paramAjax: 'id_nivel_estudio', jsonDefault: false, arrExceptions: [0,1,2,3]},
							{ context: '#studyF', selParent: '_id_tipo_carrera', selChild: '_id_carrera',
								urlAjax: '/registro/filtrar-carrera/', paramAjax: 'id_tipo_carrera'},

							{ context: '#basicDataF', selParent: 'id_area', selChild: 'id_nivel_puesto', urlAjax: '/mi-cuenta/filtrar-nivel-area/', paramAjax: 'id_area'},
							//{ context: '#experienceF', selParent: 'managerExperiencia_0_id_area', selChild: 'managerExperiencia_0_id_nivel_puesto', urlAjax: '/mi-cuenta/filtrar-nivel-area/', paramAjax: 'id_area'}
						]);

						//All
						yOSON.AppCore.runModule('input_show_more', [{
							context: '#studyF', inputTagName: '_id_carrera',
							option: {'Otros' : [{name: '_otra_carrera', title: 'second_title'}]},
							label : true
						},{
							context: '#basicDataF', inputTagName: 'id_puesto',
							option: {'Otros' : [{name: 'nombre_puesto'}]}, divWrap: '.control-group',
							label : true
						}]);

						yOSON.AppCore.runModule('validate_form_ad');
						yOSON.AppCore.runModule('validate_key',[
							{ txtInput: '#funciones', type: 'no_arroba' },
							{ txtInput: '#responsabilidades', type: 'no_arroba' },
							{ txtInput: '#preguntasF textarea', type: 'no_arroba' }
						]);
						yOSON.AppCore.runModule('accordion_effect',[{divAction: '.blue-title'}]);
					},
					'paso3': function () {
						yOSON.AppCore.runModule('printed_detailed_notice');
					},
					'paso4': function () {
						yOSON.AppCore.runModule('publish_ads_pay');
						yOSON.AppCore.runModule('featured_profile',{
							frm : '#formEndP4Emp',
							isHideInputs : false
						});
						yOSON.AppCore.runModule('modal_all', [{maxWidth: 540, maxHeight: 500}]);
						yOSON.AppCore.runModule('manipulate_payment_ad');

					},
					'byDefault': function () {
					}
				},
				allActions: function () {
				}
			},
			'publica-aviso-destacado': {
				actions: {
					'paso2': function () {
						yOSON.AppCore.runModule('file_uploader', [{frm: 'frmPublishAd'}]);
						yOSON.AppCore.runModule('change_name_company');
						yOSON.AppCore.runModule('location_job');

						yOSON.AppCore.runModule('mini_validate', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF' },
								programs	: { context: '#programsF' },
								preguntas	: { context: '#preguntasF' }
							}
						});
						yOSON.AppCore.runModule('skill_add', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF', template: '#tplExperience' },
								estudio		: { context: '#studyF', template: '#tplStudy' },
								otroEstudio : { context: '#studyOtherF', template: '#tplStudyOther' },
								idioma		: { context: '#languagesF', template: '#tplLanguage', separate: 'nivel', validRepeat: true },
								programs	: { context: '#programsF', template: '#tplPrograms', separate: 'nivel', validRepeat: true },
								preguntas	: { context: '#preguntasF', template: '#tplQuestions' }
							}
						});
						yOSON.AppCore.runModule('skill_edit', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF', separate: 'nivel' },
								programs	: { context: '#programsF', separate: 'nivel' },
								preguntas	: { context: '#preguntasF' }
							}
						});
						yOSON.AppCore.runModule('skill_remove', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF' },
								programs	: { context: '#programsF' },
								preguntas	: { context: '#preguntasF' }
							}
						});

						//Estudios
						yOSON.AppCore.runModule('study_options_ad');
						yOSON.AppCore.runModule('combos_depends',[
							{ context: '#studyF', selParent: '_id_nivel_estudio', selChild: '_id_nivel_estudio_tipo',
								urlAjax: '/home/filtrar-tipo-estudio/', paramAjax: 'id_nivel_estudio', jsonDefault: false, arrExceptions: [0,1,2,3]},
							{ context: '#studyF', selParent: '_id_tipo_carrera', selChild: '_id_carrera',
								urlAjax: '/registro/filtrar-carrera/', paramAjax: 'id_tipo_carrera'}
						]);

						//All
						yOSON.AppCore.runModule('input_show_more', [{
							context: '#studyF', inputTagName: '_id_carrera',
							option: {'Otros' : [{name: '_otra_carrera', title: 'second_title'}]},
							label : true
						}]);

						yOSON.AppCore.runModule('validate_form_ad');
						yOSON.AppCore.runModule('validate_key',[
							{ txtInput: '#funciones', type: 'no_arroba' },
							{ txtInput: '#responsabilidades', type: 'no_arroba' },
							{ txtInput: '#preguntasF textarea', type: 'no_arroba' }
						]);
						yOSON.AppCore.runModule('accordion_effect',[{divAction: '.blue-title'}]);
					},
					'paso3': function () {
						yOSON.AppCore.runModule('publish_ads_pay');
						yOSON.AppCore.runModule('featured_profile',{
							frm : '#formEndP4Emp',
							isHideInputs : false
						});
						yOSON.AppCore.runModule('modal_all', [{maxWidth: 540, maxHeight: 500}]);
					},
					'byDefault': function () {
					}
				},
				allActions: function () {
				}
			},
			'publica-aviso-preferencial': {
				actions: {
					'paso2': function () {
						yOSON.AppCore.runModule('file_uploader', [{frm: 'frmPublishAd'}]);
						yOSON.AppCore.runModule('change_name_company');
						yOSON.AppCore.runModule('location_job');

						yOSON.AppCore.runModule('mini_validate', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF' },
								programs	: { context: '#programsF' },
								preguntas	: { context: '#preguntasF' }
							}
						});
						yOSON.AppCore.runModule('skill_add', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF', template: '#tplExperience' },
								estudio		: { context: '#studyF', template: '#tplStudy' },
								otroEstudio : { context: '#studyOtherF', template: '#tplStudyOther' },
								idioma		: { context: '#languagesF', template: '#tplLanguage', separate: 'nivel', validRepeat: true },
								programs	: { context: '#programsF', template: '#tplPrograms', separate: 'nivel', validRepeat: true },
								preguntas	: { context: '#preguntasF', template: '#tplQuestions' }
							}
						});
						yOSON.AppCore.runModule('skill_edit', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF', separate: 'nivel' },
								programs	: { context: '#programsF', separate: 'nivel' },
								preguntas	: { context: '#preguntasF' }
							}
						});
						yOSON.AppCore.runModule('skill_remove', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF' },
								programs	: { context: '#programsF' },
								preguntas	: { context: '#preguntasF' }
							}
						});

						//Estudios
						yOSON.AppCore.runModule('study_options_ad');
						yOSON.AppCore.runModule('combos_depends',[
							{ context: '#studyF', selParent: '_id_nivel_estudio', selChild: '_id_nivel_estudio_tipo',
								urlAjax: '/home/filtrar-tipo-estudio/', paramAjax: 'id_nivel_estudio', jsonDefault: false, arrExceptions: [0,1,2,3]},
							{ context: '#studyF', selParent: '_id_tipo_carrera', selChild: '_id_carrera',
								urlAjax: '/registro/filtrar-carrera/', paramAjax: 'id_tipo_carrera'}
						]);

						//All
						yOSON.AppCore.runModule('input_show_more', [{
							context: '#studyF', inputTagName: '_id_carrera',
							option: {'Otros' : [{name: '_otra_carrera', title: 'second_title'}]},
							label : true
						}]);
						yOSON.AppCore.runModule('btn_actions_preferencial_ad');
						yOSON.AppCore.runModule('unique_click', [
							{btn: '#btnClone'}, {btn: '#btnDelete'}
						]);
						yOSON.AppCore.runModule('validate_form_ad');
						yOSON.AppCore.runModule('validate_key',[
							{ txtInput: '#funciones', type: 'no_arroba' },
							{ txtInput: '#responsabilidades', type: 'no_arroba' },
							{ txtInput: '#preguntasF textarea', type: 'no_arroba' }
						]);
						yOSON.AppCore.runModule('accordion_effect',[{divAction: '.blue-title'}]);
					},
					'paso4': function () {
						yOSON.AppCore.runModule('publish_ads_pay');
						yOSON.AppCore.runModule('featured_profile',{
							frm : '#formEndP4Emp',
							isHideInputs : false
						});
						yOSON.AppCore.runModule('modal_all', [{maxWidth: 540, maxHeight: 500}]);
						yOSON.AppCore.runModule('manipulate_payment_ad');
					},
					'byDefault': function () {
					}
				},
				allActions: function () {
				}
			},
			'mis-procesos': {
				actions: {
					'ver-proceso': function () {
						yOSON.AppCore.runModule('accordion_effect', [
							{divAction: '.aptitus-title'},
							{divAction: '.accord'}
						]);
						yOSON.AppCore.runModule('open_close_view_more');
						yOSON.AppCore.runModule('search_checked');
						yOSON.AppCore.runModule('message_new_charts');
					},
					'estadisticas': function () {
						yOSON.AppCore.runModule('google_charts');
					},
					'byDefault': function () {
					}
				},
				allActions: function () {
				}
			},
			'bolsa-cvs': {
				actions: {
						'index': function () {
							yOSON.AppCore.runModule('accordion_effect', [
									{divAction: '.aptitus-title'},
									{divAction: '.accord'}
							]);
							yOSON.AppCore.runModule('open_close_view_more');
							yOSON.AppCore.runModule('search_checked');
						},
						'byDefault': function () {
						}
				},
				allActions: function () {
				}
			},
			'comprar-membresia-anual': {
				actions: {
					'paso1': function () {
						yOSON.AppCore.runModule('modal_all', [{maxWidth: 540, maxHeight: 500}]);
					},
					'pago-efectivo' : function(){
						yOSON.AppCore.runModule('accordion_effect',[{context: '.accordion_affiliated_office', divIcon: 'span', contentOpen: '+', contentClose: '-', oneAtATime: true}]);
					},
					'byDefault': function () {

					}
				},
				allActions: function () {
				}
			},
			'activar-membresia': {
				actions: {
					'index': function () {
					},
					'byDefault': function () {
					}
				},
				allActions: function () {
				}
			},
			byDefault: function () {},
			allActions: function () {}
		},
		byDefault: function () {},
		allControllers: function () {}
	},
	'admin': {
		controllers: {
			'registro-empresa': {
				actions: {
					'index': function () {
						yOSON.AppCore.runModule('company_registration');
						yOSON.AppCore.runModule('validate_input_ajax',[
						{
							context	: '#frmCompanyRegistration',
							urlAjaxValidate: '/registro/validar-email/',
							txtInput: '#txtEmail',
							dataAjaxAttr: { email: 'value'},
							dataAjax: {modulo: 'empresa'}
						},
						{
							context	: '#frmCompanyRegistration',
							urlAjaxValidate: '/empresa/registro-empresa/validar-nombrecomercial/',
							txtInput: '#txtNombreComercial',
							dataAjaxAttr: { ndoc: 'value', idEmp: 'rel' }
						},
						{
							context	: '#frmCompanyRegistration',
							urlAjaxValidate: '/empresa/registro-empresa/validar-razonsocial/',
							txtInput: '#txtRazonSocial',
							dataAjaxAttr: { ndoc: 'value', idEmp: 'rel' }
						},
						{
							context	: '#frmCompanyRegistration',
							urlAjaxValidate: '/empresa/registro-empresa/validar-ruc/',
							txtInput: '#txtRuc',
							dataAjaxAttr: { ndoc: 'value', idEmp: 'rel' }
						}
						]);
						yOSON.AppCore.runModule('location_job',{context: '#frmCompanyRegistration'});
						yOSON.AppCore.runModule('file_uploader', [{frm: 'frmCompanyRegistration', urlDelete: '/admin/mi-cuenta-empresa/eliminar-foto/'}]);
					},
					'byDefault': function () {
					}
				},
				allActions: function () {

				}
			},
			'mi-cuenta-empresa': {
				actions: {
					'cambio-clave' : function () {
						yOSON.AppCore.runModule('applicant_registration', {
							frm					: '#frmChangePassword',
							txtPassword			: '#txtNewPassword',
							txtRepeatPassword	: '#txtRepeatPassword'
						});
					},
					'datos-empresa': function () {
						yOSON.AppCore.runModule('company_registration');
						yOSON.AppCore.runModule('validate_input_ajax',[
						{
							context	: '#frmCompanyRegistration',
							urlAjaxValidate: '/empresa/registro-empresa/validar-nombrecomercial/',
							txtInput: '#txtNombreComercial',
							dataAjaxAttr: { ndoc: 'value', idEmp: 'rel' }
						},
						{
							context	: '#frmCompanyRegistration',
							urlAjaxValidate: '/empresa/registro-empresa/validar-razonsocial/',
							txtInput: '#txtRazonSocial',
							dataAjaxAttr: { ndoc: 'value', idEmp: 'rel' }
						},
						{
							context	: '#frmCompanyRegistration',
							urlAjaxValidate: '/empresa/registro-empresa/validar-ruc/',
							txtInput: '#txtRuc',
							dataAjaxAttr: { ndoc: 'value', idEmp: 'rel' }
						}
						]);
						yOSON.AppCore.runModule('location_job',{context: '#frmCompanyRegistration'});
						yOSON.AppCore.runModule('file_uploader', [{frm: 'frmCompanyRegistration', urlDelete: '/admin/mi-cuenta-empresa/eliminar-foto/'}]);
					},
					'byDefault': function () {
					}
				},
				allActions: function () {

				}
			},
			'membresia-empresa': {
				actions: {
					'index': function () {
						yOSON.AppCore.runModule('company_membership');
					},
					'byDefault': function () {
					}
				},
				allActions: function () {

				}
			},
			'mi-cuenta': {
				actions: {
					'cambio-de-clave' : function () {
						yOSON.AppCore.runModule('applicant_registration', {
							frm					: '#frmChangePassword',
							txtPassword			: '#txtNewPassword',
							txtRepeatPassword	: '#txtRepeatPassword'
						});
					},
					'mis-datos-personales' : function () {
						yOSON.AppCore.runModule('applicant_registration');
						yOSON.AppCore.runModule('validate_input_ajax',[
						{
							context	: '#frmPostulantRegistration',
							urlAjaxValidate: '/registro/validar-email/',
							txtInput: '#txtEmail',
							dataAjaxAttr: { email: 'value', rol : 'data-rol'},
							dataAjax: {modulo: 'postulante'}
						},
						{
							context	: '#frmPostulantRegistration',
							urlAjaxValidate: '/registro/validar-dni/',
							txtInput: '#txtDocument',
							dataAjaxAttr: { ndoc: 'value', idPost: 'data-rel' }
						}
						]);

						yOSON.AppCore.runModule('validate_daybitrh');
						yOSON.AppCore.runModule('location_job',{context: '#frmPostulantRegistration'});
						yOSON.AppCore.runModule('count_character_for_skills',{
							input: 'txtAboutForYou',
							maxCharacter: 750
						});
					},
					'mis-experiencias': function () {
						yOSON.AppCore.runModule('mini_validate', {
							"Perfil_postulante" : { experiencia : { context: '#experienceF', btn: '#btnExperience' }}
						});
						yOSON.AppCore.runModule('skill_add', {
							"Perfil_postulante" : { experiencia : { context: '#experienceF', template: '#tplExperience', btn: '#btnExperience' }}
						});
						yOSON.AppCore.runModule('skill_edit', {
							"Perfil_postulante" : { experiencia : { context: '#experienceF' }}
						});
						yOSON.AppCore.runModule('skill_remove', {
							"Perfil_postulante" : { experiencia : { context: '#experienceF' }}
						});

						yOSON.AppCore.runModule('hide_skill_block',[
							{ context: '#experienceF', chkInput: '#chkExperience' }
						]);
						yOSON.AppCore.runModule('input_show_more', [{
							context: '#experienceF', inputTagName: '_lugar',
							option: [{name: '_tipo_proyecto'}, {name: '_nombre_proyecto'}, {name: '_costo_proyecto'}]
						},
						{
							context: '#experienceF', inputTagName: '_nivel_puesto',
							option: {'10' : [{name: '_otro_nivel_puesto'}]}
						},
						{
							context: '#experienceF', inputTagName: '_id_puesto',
							option: {'1292' : [{name: '_otro_puesto', title: 'first_title'}]}
						}]);
						yOSON.AppCore.runModule('count_character_for_skills');
					},
					'mis-estudios' : function(){
						yOSON.AppCore.runModule('mini_validate', {
							"Perfil_postulante" : { estudio		: { context: '#studyF', btn: '#btnStudy' } }
						});
						yOSON.AppCore.runModule('skill_add', {
							"Perfil_postulante" : { estudio		: { context: '#studyF', template: '#tplStudy', btn: '#btnStudy' } }
						});
						yOSON.AppCore.runModule('skill_edit', {
							"Perfil_postulante" : { estudio		: { context: '#studyF' } }
						});
						yOSON.AppCore.runModule('skill_remove', {
							"Perfil_postulante" : { estudio		: { context: '#studyF' } }
						});

						yOSON.AppCore.runModule('study_options');
						yOSON.AppCore.runModule('combos_depends',[
							{ context: '#studyF', selParent: '_id_tipo_carrera', selChild: 'id_carrera',
								urlAjax: '/registro/filtrar-carrera/', paramAjax: 'id_tipo_carrera' },
							{ context: '#studyF', selParent: '_id_nivel_estudio', selChild: '_id_nivel_estudio_tipo',
								urlAjax: '/home/filtrar-tipo-estudio/', paramAjax: 'id_nivel_estudio', jsonDefault: false, arrExceptions: [0,1,2,3] }
						]);

						yOSON.AppCore.runModule('hide_skill_block',[
							{ context: '#studyF', chkInput: '#chkStudy' }
						]);
						yOSON.AppCore.runModule('input_show_more', [{
							context: '#studyF', inputTagName: '_id_carrera',
							option: {'Otros' : [{name: '_otro_carrera', title: 'first_title'}]},
							label : true
						},
						{
							context: '#studyF', inputTagName: '_id_nivel_estudio_tipo',
							option: {'18' : [{name: '_colegiatura_numero'}]}
						}]);

						yOSON.AppCore.runModule('autocomplete_text');
					},
					'mis-otros-estudios': function () {
						yOSON.AppCore.runModule('mini_validate', {
							"Perfil_postulante" : { otroEstudio : { context: '#studyOtherF', btn: '#btnOtherStudy' } }
						});
						yOSON.AppCore.runModule('skill_add', {
							"Perfil_postulante" : { otroEstudio : { context: '#studyOtherF', template: '#tplStudyOther', btn: '#btnOtherStudy' } }
						});
						yOSON.AppCore.runModule('skill_edit', {
							"Perfil_postulante" : { otroEstudio : { context: '#studyOtherF' } }
						});
						yOSON.AppCore.runModule('skill_remove', {
							"Perfil_postulante" : { otroEstudio : { context: '#studyOtherF' } }
						});
					},
					'mis-idiomas': function () {
						yOSON.AppCore.runModule('mini_validate', {
							"Perfil_postulante" : { idioma: { context: '#languagesF', btn: '#btnLanguage' } }
						});
						yOSON.AppCore.runModule('skill_add', {
							"Perfil_postulante" : { idioma: { context: '#languagesF', template: '#tplLanguage', btn: '#btnLanguage' , separate: 'nivel', validRepeat: true } }
						});
						yOSON.AppCore.runModule('skill_edit', {
							"Perfil_postulante" : { idioma: { context: '#languagesF', separate: 'nivel' } }
						});
						yOSON.AppCore.runModule('skill_remove', {
							"Perfil_postulante" : { idioma: { context: '#languagesF' } }
						});
					},
					'mis-programas': function () {
						yOSON.AppCore.runModule('mini_validate', {
							"Perfil_postulante" : { programs: { context: '#programsF', btn: '#btnPrograms' } }
						});
						yOSON.AppCore.runModule('skill_add', {
							"Perfil_postulante" : { programs: { context: '#programsF', template: '#tplPrograms', btn: '#btnPrograms' , separate: 'nivel', validRepeat: true } }
						});
						yOSON.AppCore.runModule('skill_edit', {
							"Perfil_postulante" : { programs: { context: '#programsF', separate: 'nivel' } }
						});
						yOSON.AppCore.runModule('skill_remove', {
							"Perfil_postulante" : { programs: { context: '#programsF' } }
						});
					},
					'mis-referencias': function () {
						yOSON.AppCore.runModule('mini_validate', {
							"Perfil_postulante" : { programs	: { context: '#referenceF', btn: '#btnReference'  } }
						});
						yOSON.AppCore.runModule('skill_add', {
							"Perfil_postulante" : { programs	: { context: '#referenceF', template: '#tplReference', btn: '#btnReference', separate: 'es'} }
						});
						yOSON.AppCore.runModule('skill_edit', {
							"Perfil_postulante" : { programs	: { context: '#referenceF', separate: 'es' } }
						});
						yOSON.AppCore.runModule('skill_remove', {
							"Perfil_postulante" : { programs	: { context: '#referenceF' } }
						});
					},
					'byDefault': function () {
					}
				},
				allActions: function () {
					yOSON.AppCore.runModule('update_date', [
						{selTag: '_inicio_ano', selDepend: '_fin_ano'},
						{selTag: '_inicio_mes', selDepend: '_fin_mes'}
					]);
					yOSON.AppCore.runModule('disable_combos',[
						{chkName: '_en_curso', disableds: ['_fin_mes','_fin_ano']}
					]);
				}
			},
			'publicar-aviso': {
				actions: {
					'paso2': function () {
						yOSON.AppCore.runModule('file_uploader', [{frm: 'frmPublishAd'}]);
						yOSON.AppCore.runModule('change_name_company');
						yOSON.AppCore.runModule('location_job');

						yOSON.AppCore.runModule('mini_validate', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF' },
								programs	: { context: '#programsF' },
								preguntas	: { context: '#preguntasF' }
							}
						});
						yOSON.AppCore.runModule('skill_add', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF', template: '#tplExperience' },
								estudio		: { context: '#studyF', template: '#tplStudy' },
								otroEstudio : { context: '#studyOtherF', template: '#tplStudyOther' },
								idioma		: { context: '#languagesF', template: '#tplLanguage', separate: 'nivel', validRepeat: true },
								programs	: { context: '#programsF', template: '#tplPrograms', separate: 'nivel', validRepeat: true },
								preguntas	: { context: '#preguntasF', template: '#tplQuestions' }
							}
						});
						yOSON.AppCore.runModule('skill_edit', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF', separate: 'nivel' },
								programs	: { context: '#programsF', separate: 'nivel' },
								preguntas	: { context: '#preguntasF' }
							}
						});
						yOSON.AppCore.runModule('skill_remove', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF' },
								programs	: { context: '#programsF' },
								preguntas	: { context: '#preguntasF' }
							}
						});

						//Estudios
						yOSON.AppCore.runModule('study_options_ad');
						yOSON.AppCore.runModule('combos_depends',[
							{ context: '#studyF', selParent: '_id_nivel_estudio', selChild: '_id_nivel_estudio_tipo',
								urlAjax: '/home/filtrar-tipo-estudio/', paramAjax: 'id_nivel_estudio', jsonDefault: false, arrExceptions: [0,1,2,3]},
							{ context: '#studyF', selParent: '_id_tipo_carrera', selChild: '_id_carrera',
								urlAjax: '/registro/filtrar-carrera/', paramAjax: 'id_tipo_carrera'}
						]);

						//All
						yOSON.AppCore.runModule('input_show_more', [{
							context: '#studyF', inputTagName: '_id_carrera',
							option: {'Otros' : [{name: '_otra_carrera', title: 'second_title'}]},
							label : true
						}]);

						yOSON.AppCore.runModule('validate_form_ad');
						yOSON.AppCore.runModule('validate_key',[
							{ txtInput: '#funciones', type: 'no_arroba' },
							{ txtInput: '#responsabilidades', type: 'no_arroba' },
							{ txtInput: '#preguntasF textarea', type: 'no_arroba' }
						]);
						yOSON.AppCore.runModule('accordion_effect',[{divAction: '.blue-title'}]);
					},
					'byDefault': function () {
					}
				},
				allActions: function () {
				}
			},
			'publicar-aviso-preferencial': {
				actions: {
					'paso2': function () {
						yOSON.AppCore.runModule('file_uploader', [{frm: 'frmPublishAd'}]);
						yOSON.AppCore.runModule('change_name_company');
						yOSON.AppCore.runModule('location_job');

						yOSON.AppCore.runModule('mini_validate', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF' },
								programs	: { context: '#programsF' },
								preguntas	: { context: '#preguntasF' }
							}
						});
						yOSON.AppCore.runModule('skill_add', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF', template: '#tplExperience' },
								estudio		: { context: '#studyF', template: '#tplStudy' },
								otroEstudio : { context: '#studyOtherF', template: '#tplStudyOther' },
								idioma		: { context: '#languagesF', template: '#tplLanguage', separate: 'nivel', validRepeat: true },
								programs	: { context: '#programsF', template: '#tplPrograms', separate: 'nivel', validRepeat: true },
								preguntas	: { context: '#preguntasF', template: '#tplQuestions' }
							}
						});
						yOSON.AppCore.runModule('skill_edit', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF', separate: 'nivel' },
								programs	: { context: '#programsF', separate: 'nivel' },
								preguntas	: { context: '#preguntasF' }
							}
						});
						yOSON.AppCore.runModule('skill_remove', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF' },
								programs	: { context: '#programsF' },
								preguntas	: { context: '#preguntasF' }
							}
						});

						//Estudios
						yOSON.AppCore.runModule('study_options_ad');
						yOSON.AppCore.runModule('combos_depends',[
							{ context: '#studyF', selParent: '_id_nivel_estudio', selChild: '_id_nivel_estudio_tipo',
								urlAjax: '/home/filtrar-tipo-estudio/', paramAjax: 'id_nivel_estudio', jsonDefault: false, arrExceptions: [0,1,2,3]},
							{ context: '#studyF', selParent: '_id_tipo_carrera', selChild: '_id_carrera',
								urlAjax: '/registro/filtrar-carrera/', paramAjax: 'id_tipo_carrera'}
						]);

						//All
						yOSON.AppCore.runModule('input_show_more', [{
							context: '#studyF', inputTagName: '_id_carrera',
							option: {'Otros' : [{name: '_otra_carrera', title: 'second_title'}]},
							label : true
						}]);

						yOSON.AppCore.runModule('validate_form_ad');
						yOSON.AppCore.runModule('validate_key',[
							{ txtInput: '#funciones', type: 'no_arroba' },
							{ txtInput: '#responsabilidades', type: 'no_arroba' },
							{ txtInput: '#preguntasF textarea', type: 'no_arroba' }
						]);
						yOSON.AppCore.runModule('accordion_effect',[{divAction: '.blue-title'}]);
					},
					'byDefault': function () {
					}
				},
				allActions: function () {
				}
			},
			'aviso': {
				actions: {
					'editar': function () {
						yOSON.AppCore.runModule('file_uploader', [{frm: 'frmPublishAd'}]);
						yOSON.AppCore.runModule('change_name_company');
						yOSON.AppCore.runModule('location_job');

						yOSON.AppCore.runModule('mini_validate', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF' },
								programs	: { context: '#programsF' },
								preguntas	: { context: '#preguntasF' }
							}
						});
						yOSON.AppCore.runModule('skill_add', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF', template: '#tplExperience' },
								estudio		: { context: '#studyF', template: '#tplStudy' },
								otroEstudio : { context: '#studyOtherF', template: '#tplStudyOther' },
								idioma		: { context: '#languagesF', template: '#tplLanguage', separate: 'nivel', validRepeat: true },
								programs	: { context: '#programsF', template: '#tplPrograms', separate: 'nivel', validRepeat: true },
								preguntas	: { context: '#preguntasF', template: '#tplQuestions' }
							}
						});
						yOSON.AppCore.runModule('skill_edit', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF', separate: 'nivel' },
								programs	: { context: '#programsF', separate: 'nivel' },
								preguntas	: { context: '#preguntasF' }
							}
						});
						yOSON.AppCore.runModule('skill_remove', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF' },
								programs	: { context: '#programsF' },
								preguntas	: { context: '#preguntasF' }
							}
						});

						//Estudios
						yOSON.AppCore.runModule('study_options_ad');
						yOSON.AppCore.runModule('combos_depends',[
							{ context: '#studyF', selParent: '_id_nivel_estudio', selChild: '_id_nivel_estudio_tipo',
								urlAjax: '/home/filtrar-tipo-estudio/', paramAjax: 'id_nivel_estudio', jsonDefault: false, arrExceptions: [0,1,2,3]},
							{ context: '#studyF', selParent: '_id_tipo_carrera', selChild: '_id_carrera',
								urlAjax: '/registro/filtrar-carrera/', paramAjax: 'id_tipo_carrera'},
							{ context: '#basicDataF', selParent: 'id_area', selChild: 'id_nivel_puesto', urlAjax: '/mi-cuenta/filtrar-nivel-area/', paramAjax: 'id_area'},
						]);

						//All
						yOSON.AppCore.runModule('input_show_more', [{
							context: '#studyF', inputTagName: '_id_carrera',
							option: {'Otros' : [{name: '_otra_carrera', title: 'second_title'}]},
							label : true
						},{
							context: '#basicDataF', inputTagName: 'id_puesto',
							option: {'Otros' : [{name: 'nombre_puesto'}]}, divWrap: '.control-group',
							label : true
						}]);

						yOSON.AppCore.runModule('validate_form_ad');
						yOSON.AppCore.runModule('validate_key',[
							{ txtInput: '#funciones', type: 'no_arroba' },
							{ txtInput: '#responsabilidades', type: 'no_arroba' },
							{ txtInput: '#preguntasF textarea', type: 'no_arroba' }
						]);
						yOSON.AppCore.runModule('accordion_effect',[{divAction: '.blue-title'}]);
					},
					'byDefault': function () {
					}
				},
				allActions: function () {
				}
			},
			'aviso-preferencial': {
				actions: {
					'editar': function () {
						yOSON.AppCore.runModule('file_uploader', [{frm: 'frmPublishAd'}]);
						yOSON.AppCore.runModule('change_name_company');
						yOSON.AppCore.runModule('location_job');

						yOSON.AppCore.runModule('mini_validate', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF' },
								programs	: { context: '#programsF' },
								preguntas	: { context: '#preguntasF' }
							}
						});
						yOSON.AppCore.runModule('skill_add', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF', template: '#tplExperience' },
								estudio		: { context: '#studyF', template: '#tplStudy' },
								otroEstudio : { context: '#studyOtherF', template: '#tplStudyOther' },
								idioma		: { context: '#languagesF', template: '#tplLanguage', separate: 'nivel', validRepeat: true },
								programs	: { context: '#programsF', template: '#tplPrograms', separate: 'nivel', validRepeat: true },
								preguntas	: { context: '#preguntasF', template: '#tplQuestions' }
							}
						});
						yOSON.AppCore.runModule('skill_edit', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF', separate: 'nivel' },
								programs	: { context: '#programsF', separate: 'nivel' },
								preguntas	: { context: '#preguntasF' }
							}
						});
						yOSON.AppCore.runModule('skill_remove', {
							"Perfil_postulante" : {
								experiencia : { context: '#experienceF' },
								estudio		: { context: '#studyF' },
								otroEstudio : { context: '#studyOtherF' },
								idioma		: { context: '#languagesF' },
								programs	: { context: '#programsF' },
								preguntas	: { context: '#preguntasF' }
							}
						});

						//Estudios
						yOSON.AppCore.runModule('study_options_ad');
						yOSON.AppCore.runModule('combos_depends',[
							{ context: '#studyF', selParent: '_id_nivel_estudio', selChild: '_id_nivel_estudio_tipo',
								urlAjax: '/home/filtrar-tipo-estudio/', paramAjax: 'id_nivel_estudio', jsonDefault: false, arrExceptions: [0,1,2,3]},
							{ context: '#studyF', selParent: '_id_tipo_carrera', selChild: '_id_carrera',
								urlAjax: '/registro/filtrar-carrera/', paramAjax: 'id_tipo_carrera'}
						]);

						//All
						yOSON.AppCore.runModule('input_show_more', [{
							context: '#studyF', inputTagName: '_id_carrera',
							option: {'Otros' : [{name: '_otra_carrera', title: 'second_title'}]},
							label : true
						}]);

						yOSON.AppCore.runModule('validate_form_ad');
						yOSON.AppCore.runModule('validate_key',[
							{ txtInput: '#funciones', type: 'no_arroba' },
							{ txtInput: '#responsabilidades', type: 'no_arroba' },
							{ txtInput: '#preguntasF textarea', type: 'no_arroba' }
						]);
						yOSON.AppCore.runModule('accordion_effect',[{divAction: '.blue-title'}]);
					},
					'byDefault': function () {
					}
				},
				allActions: function () {
				}
			},
			'gestion': {
				actions: {
					'cambio-clave' : function () {
						yOSON.AppCore.runModule('applicant_registration', {
							frm					: '#frmChangePassword',
							txtPassword			: '#txtNewPassword',
							txtRepeatPassword	: '#txtRepeatPassword'
						});
					},
					'avisos': function () {
						yOSON.AppCore.runModule('plugin_switcher');
					},
					'callcenter' : function () {
						yOSON.AppCore.runModule('callcenter_search_email');
					},
					'byDefault': function () {
					}
				},
				allActions: function () {

				}
			},
			byDefault: function () {},
			allActions: function () {}
		},
		byDefault: function () {},
		allControllers: function () {}
	},

	byDefault: function () {},

	allModules: function (oMCA) {
		yOSON.AppCore.runModule('window_modal');
		yOSON.AppCore.runModule('modal_login');
		yOSON.AppCore.runModule('modal_register');
		yOSON.AppCore.runModule('forgot_password');
		yOSON.AppCore.runModule('placeholder_ie');
		yOSON.AppCore.runModule('validate_key',[
			{ txtInput: '.number', type: 'number' },
			{ txtInput: '.decimal', type: 'decimal' },
			{ txtInput: '.onlytext', type: 'text' }
		]);
		yOSON.AppCore.runModule('e_planning');
	}
};
