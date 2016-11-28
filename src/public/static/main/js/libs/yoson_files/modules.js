yOSON.AppSchema.modules= {
    postulante: {
        controllers: {
            "bolsas-de-trabajo": {
                allActions:function() {}
                ,
                actions: {
                    index:function() {
                        yOSON.AppCore.runModule("see_more_companies")
                    }
                    ,
                    byDefault:function() {}
                }
            }
            ,
            "avisos-sugeridos": {
                allActions:function() {
                    yOSON.AppCore.runModule("control_help_layout"),
                    yOSON.AppCore.runModule("manage_font_size", {
                        container: ".suggested_tabs_content"
                    }
                    )
                }
                ,
                actions: {
                    index:function() {
                        yOSON.AppCore.runModule("suggested_delete"),
                        yOSON.AppCore.runModule("suggested_hightlight_add")
                    }
                    ,
                    eliminados:function() {
                        yOSON.AppCore.runModule("suggested_delete"),
                        yOSON.AppCore.runModule("suggested_hightlight_add")
                    }
                    ,
                    favoritos:function() {
                        yOSON.AppCore.runModule("suggested_delete")
                    }
                    ,
                    byDefault:function() {}
                }
            }
            ,
            registro: {
                allActions:function() {}
                ,
                actions: {
                    paso3:function() {
                        var locations,
                        suggested;
                        yOSON.AppCore.runModule("salary_range_slider"),
                        suggested= {
                            txtField: "#txtSkillField", btnAdd: ".btn_add_skill", classItem: "step3_skills_selected_item", container: ".step3_skills_container", tagContainer: ".step3_skills_selected_container", closeOption: "close_skills", limitKey: "maxTags"
                        }
                        ,
                        yOSON.AppCore.runModule("suggested_add_option", {
                            txtSuggestedField: suggested.txtField, btnAddOption: suggested.btnAdd, classContainer: suggested.container, classTagContainer: suggested.tagContainer, classItem: suggested.classItem, classClose: suggested.closeOption, limitKey: suggested.limitKey, closestContainer: ".step3_skills_mini_form", tplTags: "#tplItemSkill", disabledEvents: !1
                        }
                        ),
                        yOSON.AppCore.runModule("suggested_autocomplete_options", {
                            txtSuggestedField: suggested.txtField, btnAddOption: suggested.btnAdd, classContainer: suggested.container, classTagContainer: suggested.tagContainer, classItem: suggested.classItem, classClose: suggested.closeOption, limitKey: suggested.limitKey, urlAjax: "/registro/filtrar-aptitudes", tplTags: "#tplItemSkill", closestContainer: ".step3_skills_mini_form", numLettersToStart: 0
                        }
                        ),
                        yOSON.AppCore.runModule("suggested_close_tag", {
                            txtSuggestedField: suggested.txtField, btnAddOption: suggested.btnAdd, classContainer: suggested.container, classTagContainer: suggested.tagContainer, classItem: suggested.classItem, limitKey: suggested.limitKey, classClose: "."+suggested.closeOption
                        }
                        ),
                        locations= {
                            txtField: "#txtUbicacion", btnAdd: "", classItem: "step3_locations_selected_item", container: ".step3_locations_container", tagContainer: ".step3_locations_selected_container", closeOption: "close_location", limitKey: "maxLocationsItems"
                        }
                        ,
                        yOSON.AppCore.runModule("suggested_add_option", {
                            txtSuggestedField: locations.txtField, btnAddOption: locations.btnAdd, classContainer: locations.container, classTagContainer: locations.tagContainer, classItem: locations.classItem, classClose: locations.closeOption, limitKey: locations.limitKey, closestContainer: ".step3_locations_mini_form", tplTags: "#tplItemLocation", disabledEvents: !0
                        }
                        ),
                        yOSON.AppCore.runModule("suggested_autocomplete_options", {
                            txtSuggestedField: locations.txtField, btnAddOption: locations.btnAdd, classContainer: locations.container, classTagContainer: locations.tagContainer, classItem: locations.classItem, classClose: locations.closeOption, limitKey: locations.limitKey, tplTags: "#tplItemLocation", closestContainer: ".step3_locations_mini_form", urlAjax: "/registro/filtrar-ubigeo/ubigeo", numLettersToStart: 2
                        }
                        ),
                        yOSON.AppCore.runModule("suggested_close_tag", {
                            txtSuggestedField: locations.txtField, btnAddOption: locations.btnAdd, classContainer: locations.container, classTagContainer: locations.tagContainer, classItem: locations.classItem, limitKey: locations.limitKey, classClose: "."+locations.closeOption
                        }
                        ),
                        yOSON.AppCore.runModule("hover_list_address"),
                        yOSON.AppCore.runModule("submit_form")
                    }
                    ,
                    paso2:function() {
                        yOSON.AppCore.runModule("selection_modal")
                    }
                    ,
                    byDefault:function() {}
                }
            }
            ,
            "mi-cuenta": {
                allActions:function() {
                    "perfil-publico"!==yOSON.action&&(yOSON.AppCore.runModule("control_help_layout"), yOSON.AppCore.runModule("slider_data_profile"), yOSON.AppCore.runModule("progressbar"), yOSON.AppCore.runModule("featured_notices_aside"), yOSON.AppCore.runModule("suggested_delete"), yOSON.AppCore.runModule("suggested_hightlight_add"), yOSON.AppCore.runModule("hover_account_items"), yOSON.AppCore.runModule("modal_welcome_information"))
                }
                ,
                actions: {
                    "perfil-publico":function() {
                        yOSON.AppCore.runModule("active_tooltip")
                    }
                    ,
                    "mis-datos-personales":function() {
                        yOSON.AppCore.runModule("save_upload"),
                        yOSON.AppCore.runModule("validate_my_personal_data_form"),
                        yOSON.AppCore.runModule("disable_rows_form", {
                            form: "#frmUserRegistration", checkbox: "#chkIncapacity", formValues: ["chkConadis", "selDisability"], inverse: !1
                        }
                        ),
                        yOSON.AppCore.runModule("disable_rows_form", {
                            form: "#frmUserRegistration", checkbox: "#chkConadis", formValues: ["txtconadisCode"], inverse: !1
                        }
                        )
                    }
                    ,
                    "mis-estudios":function() {
                        yOSON.AppCore.runModule("skill_save", {
                            frmSkill:"#frmUserEducation", tplForm:"#tplUserEducation", hidSkill:"hidStudy", rulesValidate: {
                                txtYearBegin: {
                                    number: !0
                                }
                                , txtYearEnd: {
                                    number: !0, lessThanEqual: "#txtYearBegin"
                                }
                                , selMonthEnd: {
                                    lessThanEqualMonth: ["#selMonthBegin", "#txtYearBegin", "#txtYearEnd"], currentMonth: ["#txtYearBegin", "#txtYearEnd"]
                                }
                                , txtInstitution: {
                                    autocompleteValidator: "#hidInstitution"
                                }
                            }
                            , autocomplete:[ {
                                txtValue: "#txtInstitution", txtIdValue: "#hidInstitution", urlAutocomplete: "/mi-cuenta/filtrar-institucion"
                            }
                            , {
                                txtValue: "#txtCareer", txtIdValue: "#hidCareer", urlAutocomplete: "/mi-cuenta/filtrar-carrera"
                            }
                            ]
                        }
                        ),
                        yOSON.AppCore.runModule("skill_edit", {
                            frmSkill: "#frmUserEducation", tplForm: "#tplUserEducation", formValues: ["txtInstitution", "txtCareer", "selMonthBegin", "txtYearBegin", "selMonthEnd", "txtYearEnd", "actualStudent", "selLevelStudy", "selStateStudy", "hidStudy", "hidCareer", "selStateStudyCombo", "selCountry"], urlAjaxEdit: "/mi-cuenta/get-data-estudios"
                        }
                        ),
                        yOSON.AppCore.runModule("skill_delete", {
                            urlAjaxDelete: "/mi-cuenta/borrar-estudio-ajax"
                        }
                        ),
                        yOSON.AppCore.runModule("disable_rows_form", {
                            form: "#frmUserEducation", checkbox: "#actualStudent", formValues: ["selMonthEnd", "txtYearEnd"]
                        }
                        ),
                        yOSON.AppCore.runModule("combos_depends", [ {
                            form: "#frmUserEducation", selParent: "#selLevelStudy", selChild: "#selStateStudy", urlAjax: "/mi-cuenta/filtrar-estado-estudio", paramAjax: "id_nivel_estudio", arrExceptions: [2, 3]
                        }
                        ]),
                        yOSON.AppCore.runModule("validate_by_level_study")
                    }
                    ,
                    "mis-otros-estudios":function() {
                        yOSON.AppCore.runModule("skill_save", {
                            frmSkill:"#frmOtherEducation", tplForm:"#tplOthersStudies", hidSkill:"hidOtherStudy", rulesValidate: {
                                txtOtherName: {
                                    alphabet: !1
                                }
                                , txtOtherInstitution: {
                                    alphabet: !1
                                }
                                , txtOtherYearBegins: {
                                    number: !0
                                }
                                , txtOtherYearEnd: {
                                    number: !0, lessThanEqual: "#txtOtherYearBegins"
                                }
                                , selOtherMonthEnd: {
                                    lessThanEqualMonth: ["#selOtherMonthBegins", "#txtOtherYearBegins", "#txtOtherYearEnd"], currentMonth: ["#txtOtherYearBegins", "#txtOtherYearEnd"]
                                }
                            }
                            , autocomplete:[ {
                                txtValue: "#txtOtherInstitution", txtIdValue: "#hidOtherInstitution", urlAutocomplete: "/mi-cuenta/filtrar-institucion"
                            }
                            ]
                        }
                        ),
                        yOSON.AppCore.runModule("skill_edit", {
                            frmSkill: "#frmOtherEducation", tplForm: "#tplOthersStudies", formValues: ["txtOtherName", "txtOtherInstitution", "selOtherType", "selOtherCountry", "selOtherMonthBegins", "txtOtherYearBegins", "selOtherMonthEnd", "txtOtherYearEnd", "actuallyStudying", "hidOtherInstitution", "hidOtherStudy"], urlAjaxEdit: "/mi-cuenta/get-data-otros-estudios"
                        }
                        ),
                        yOSON.AppCore.runModule("skill_delete", {
                            urlAjaxDelete: "/mi-cuenta/borrar-otro-estudio-ajax"
                        }
                        ),
                        yOSON.AppCore.runModule("disable_rows_form", {
                            form: "#frmOtherEducation", checkbox: "#actuallyStudying", formValues: ["selOtherMonthEnd", "txtOtherYearEnd"]
                        }
                        )
                    }
                    ,
                    "mis-programas":function() {
                        yOSON.AppCore.runModule("skill_save", {
                            frmSkill:"#frmUserPrograms", tplForm:"#tplUserPrograms", hidSkill:"hidPrograms", rulesValidate: {
                                txtProgram: {
                                    autocompleteValidator: "#selProgram"
                                }
                            }
                            , autocomplete:[ {
                                txtValue: "#txtProgram", txtIdValue: "#selProgram", urlAutocomplete: "/mi-cuenta/filtrar-programas"
                            }
                            ]
                        }
                        ),
                        yOSON.AppCore.runModule("skill_edit", {
                            frmSkill: "#frmUserPrograms", tplForm: "#tplUserPrograms", formValues: ["selProgram", "selLevel", "hidPrograms", "txtProgram"], urlAjaxEdit: "/mi-cuenta/get-data-programas"
                        }
                        ),
                        yOSON.AppCore.runModule("skill_delete", {
                            urlAjaxDelete: "/mi-cuenta/borrar-programa-ajax"
                        }
                        )
                    }
                    ,
                    "mi-ubicacion":function() {
                        yOSON.AppCore.runModule("validate_my_location"),
                        yOSON.AppCore.runModule("count_words", {
                            containerGlobal: ".skill_body", mensaje: "#txtPresentacion", maxNumber: ".max_nummber", initialMessage: "(Te quedan 300 caracteres)", maxCharacters: 300
                        }
                        ),
                        yOSON.AppCore.runModule("autocomplete_custom", {
                            context: "#frmLocation", txtValue: "#txtUbigeo", txtIdValue: "#txtIdUbigeo", urlAutocomplete: "/registro/filtrar-ubigeo/ubigeo"
                        }
                        ),
                        yOSON.AppCore.runModule("validate_country_ubication", {
                            txtUbicacion: "#txtUbigeo", txtIdUbicacion: "#txtIdUbigeo", container: ".skill_body"
                        }
                        )
                    }
                    ,
                    "mis-logros":function() {
                        yOSON.AppCore.runModule("skill_save", {
                            frmSkill:"#frmUserAchievements", tplForm:"#tplUserAchievements", hidSkill:"hidAchievements", rulesValidate: {
                                txtPrize: {
                                    alphNumeric: !0
                                }
                                , txtInstitution: {
                                    alphabet: !0
                                }
                                , txtDateAchievement: {
                                    number: !0
                                }
                                , selDate: {
                                    currentMonth: ["#txtDateAchievement", "#txtDateAchievement"]
                                }
                            }
                        }
                        ),
                        yOSON.AppCore.runModule("skill_edit", {
                            frmSkill: "#frmUserAchievements", tplForm: "#tplUserAchievements", formValues: ["txtPrize", "txtInstitution", "txtDateAchievement", "selDate", "txtDescription", "hidAchievements"], urlAjaxEdit: "/mi-cuenta/get-data-logros"
                        }
                        ),
                        yOSON.AppCore.runModule("skill_delete", {
                            urlAjaxDelete: "/mi-cuenta/borrar-logro-ajax"
                        }
                        )
                    }
                    ,
                    "cambio-de-clave":function() {
                        yOSON.AppCore.runModule("security_password"),
                        yOSON.AppCore.runModule("validate_change_password")
                    }
                    ,
                    "eliminar-cuenta":function() {
                        yOSON.AppCore.runModule("validation_password_reasons")
                    }
                    ,
                    "mis-alertas":function() {
                        yOSON.AppCore.runModule("validate_alerts")
                    }
                    ,
                    "mis-idiomas":function() {
                        yOSON.AppCore.runModule("skill_save", {
                            frmSkill:"#frmLanguages", tplForm:"#tplLanguages", hidSkill:"hidLanguage", rulesValidate: {
                                selLanguage: {
                                    alphabet: !0
                                }
                            }
                        }
                        ),
                        yOSON.AppCore.runModule("skill_edit", {
                            frmSkill: "#frmLanguages", formValues: ["selLanguage", "selLevelWritten", "selLevelOral", "hidLanguage"], urlAjaxEdit: "/mi-cuenta/get-data-idiomas", tplForm: "#tplLanguages"
                        }
                        ),
                        yOSON.AppCore.runModule("skill_delete", {
                            urlAjaxDelete: "/mi-cuenta/borrar-idioma-ajax"
                        }
                        )
                    }
                    ,
                    "mis-experiencias":function() {
                        yOSON.AppCore.runModule("count_words", {
                            containerGlobal: ".skill_body", mensaje: "#txaComments", maxNumber: ".max_nummber", initialMessage: "(Te quedan 500 caracteres)", maxCharacters: 1500
                        }
                        ),
                        yOSON.AppCore.runModule("skill_save", {
                            frmSkill:"#frmUserExperience", tplForm:"#tplUserExperience", hidSkill:"hidExperiences", rulesValidate: {
                                txtIndustry: {
                                    alphabet: !0
                                }
                                , txtYearBegin: {
                                    number: !0
                                }
                                , txtYearEnd: {
                                    number: !0, lessThanEqual: "#txtYearBegin"
                                }
                                , selMonthEnd: {
                                    lessThanEqualMonth: ["#selMonthBegin", "#txtYearBegin", "#txtYearEnd"], currentMonth: ["#txtYearBegin", "#txtYearEnd"]
                                }
                                , txtNameProjectType: {
                                    alphabet: !0
                                }
                                , txtBudgetProjectType: {
                                    number: !0
                                }
                            }
                            , autocomplete:[ {
                                txtValue: "#txtJob", txtIdValue: "#hidJob", urlAutocomplete: "/mi-cuenta/filtro-puestos"
                            }
                            ]
                        }
                        ),
                        yOSON.AppCore.runModule("skill_edit", {
                         
                            frmSkill: "#frmUserExperience", tplForm: "#tplUserExperience", formValues: ["chkInProgress", "hidExperiences", "hidJob", "selLevelArea", "selLevelJob", "selMonthBegin", "selMonthEnd", "selProjectType", "togglefields", "txaComments", "txtBudgetProjectType", "txtExperience", "txtIndustry", "txtJob", "txtNameProjectType", "txtYearBegin", "txtYearEnd"], urlAjaxEdit: "/mi-cuenta/get-data-experiencias"
                        }
                        ),
                        yOSON.AppCore.runModule("skill_delete", {
                            urlAjaxDelete: "/mi-cuenta/borrar-experiencia-ajax"
                        }
                        ),
                        yOSON.AppCore.runModule("toogle_class_form"),
                        yOSON.AppCore.runModule("disable_rows_form", {
                            form: "#frmUserExperience", checkbox: "#chkInProgress", formValues: ["selMonthEnd", "txtYearEnd"]
                        }
                        ),
                        yOSON.AppCore.runModule("combos_depends", [ {
                            form: "#frmUserExperience", selParent: "#selLevelArea", selChild: "#selLevelJob", urlAjax: "/mi-cuenta/filtrar-nivel-area", paramAjax: "id_area", arrExceptions: []
                        }
                        ])
                    }
                    ,
                    "mis-referencias":function() {
                        yOSON.AppCore.runModule("skill_save", {
                            frmSkill:"#frmReference", tplForm:"#tplUserReferences", hidSkill:"hidReference", isPercentActive:!1, rulesValidate: {
                                txtNameReference: {
                                    alphabet: !0
                                }
                                , txtTelephoneReferenceEmail: {
                                    nEmail: !0
                                }
                            }
                        }
                        ),
                        yOSON.AppCore.runModule("skill_edit", {
                            frmSkill: "#frmReference", tplForm: "#tplUserReferences", formValues: ["selCareReference", "txtNameReference", "txtPositionReference", "txtTelephoneReferenceOne", "txtTelephoneReferenceTwo", "txtTelephoneReferenceEmail", "hidReference"], urlAjaxEdit: "/mi-cuenta/get-referencias-ajax"
                        }
                        ),
                        yOSON.AppCore.runModule("skill_delete", {
                            isPercentActive: !1, urlAjaxDelete: "/mi-cuenta/eliminar-referencia"
                        }
                        ),
                        yOSON.AppCore.runModule("validate_my_references")
                    }
                    ,
                    "mis-hobbies":function() {
                        yOSON.AppCore.runModule("suggested_add_option", {
                            txtSuggestedField: "#txtHobbies", miniForm: ".hobbies_mini_form", btnAddOption: ""
                        }
                        ),
                        yOSON.AppCore.runModule("suggested_autocomplete_options", {
                            txtSuggestedField: "#txtHobbies", urlAjax: "/mi-cuenta/filtrar-hobbies", btnAddOption: ""
                        }
                        ),
                        yOSON.AppCore.runModule("suggested_close_tag"),
                        yOSON.AppCore.runModule("submit_form")
                    }
                    ,
                    privacidad:function() {
                        yOSON.AppCore.runModule("validate_privacity")
                    }
                    ,
                    "mi-perfil":function() {
                        yOSON.AppCore.runModule("accordion_filter_bar", {
                            title: ".accordion"
                        }
                        ),
                        yOSON.AppCore.runModule("active_tooltip")
                    }
                    ,
                    byDefault:function() {}
                }
            }
            ,
            home: {
                allActions:function() {}
                ,
                actions: {
                    index:function() {
                        yOSON.AppCore.runModule("banner_image_performance"),
                        yOSON.AppCore.runModule("banner_image_performance", {
                            banner: "#parallaxProfile", loadByScroll: !0, isImageTag: !1
                        }
                        ),
                        yOSON.AppCore.runModule("effect_parallax_home"),
                        yOSON.AppCore.runModule("slider_companies_logos"),
                        yOSON.AppCore.runModule("validate_e_planning_top"),
                        yOSON.AppCore.runModule("search_autocomplete")
                    }
                    ,
                    "mapa-sitio":function() {
                        yOSON.AppCore.runModule("site_map_tabs")
                    }
                    ,
                    byDefault:function() {}
                }
            }
            ,
            buscar: {
                allActions:function() {}
                ,
                actions: {
                    "busqueda-avanzada":function() {
                        var company,
                        ubication;
                        yOSON.AppCore.runModule("hide_show_options"),
                        yOSON.AppCore.runModule("busqueda_avanzada"),
                        yOSON.AppCore.runModule("hover_advanced_search_tags"),
                        ubication= {
                            txtField: "#txtUbigeo", btnAdd: "", container: "#advancedSearchSubCardThreeFieldsetUbigeo", tagContainer: "#advancedSearchSubCardThreeFieldsetUbigeoTag", classItem: "advanced_search_sub_card_three_ubigeo_tag_item", closeOption: "close_tag", limitKey: "maxUbigeoItems"
                        }
                        ,
                        yOSON.AppCore.runModule("suggested_add_option", {
                            txtSuggestedField: ubication.txtField, btnAddOption: ubication.btnAdd, classContainer: ubication.container, classTagContainer: ubication.tagContainer, classItem: ubication.classItem, classClose: ubication.closeOption, limitKey: ubication.limitKey, closestContainer: ".advanced_search_sub_card_three_label_ubigeo", tplTags: "#tplItemUbigeoTag", disabledEvents: !1
                        }
                        ),
                        yOSON.AppCore.runModule("suggested_autocomplete_options", {
                            txtSuggestedField: ubication.txtField, btnAddOption: ubication.btnAdd, classContainer: ubication.container, classTagContainer: ubication.tagContainer, classItem: ubication.classItem, classClose: ubication.closeOption, limitKey: ubication.limitKey, urlAjax: "/filter-ubicacion-aviso", tplTags: "#tplItemUbigeoTag", closestContainer: ".advanced_search_sub_card_three_label_ubigeo", numLettersToStart: 0
                        }
                        ),
                        yOSON.AppCore.runModule("suggested_close_tag", {
                            txtSuggestedField: ubication.txtField, btnAddOption: ubication.btnAdd, classContainer: ubication.container, classTagContainer: ubication.tagContainer, classItem: ubication.classItem, limitKey: ubication.limitKey, classClose: "."+ubication.closeOption
                        }
                        ),
                        company= {
                            txtField: "#txtCompany", btnAdd: "", container: "#advancedSearchSubCardThreeFieldsetCompany", tagContainer: "#advancedSearchSubCardThreeFieldsetCompanyTag", classItem: "advanced_search_sub_card_three_company_tag_item", closeOption: "close_tag", limitKey: "maxCompanyTags"
                        }
                        ,
                        yOSON.AppCore.runModule("suggested_add_option", {
                            txtSuggestedField: company.txtField, btnAddOption: company.btnAdd, classContainer: company.container, classTagContainer: company.tagContainer, classItem: company.classItem, classClose: company.closeOption, limitKey: company.limitKey, closestContainer: ".advanced_search_sub_card_three_label_company", tplTags: "#tplItemCompanyTag", disabledEvents: !1
                        }
                        ),
                        yOSON.AppCore.runModule("suggested_autocomplete_options", {
                            txtSuggestedField: company.txtField, btnAddOption: company.btnAdd, classContainer: company.container, classTagContainer: company.tagContainer, classItem: company.classItem, classClose: company.closeOption, limitKey: company.limitKey, urlAjax: "/search-company", tplTags: "#tplItemCompanyTag", closestContainer: ".advanced_search_sub_card_three_label_company", numLettersToStart: 2
                        }
                        ),
                        yOSON.AppCore.runModule("suggested_close_tag", {
                            txtSuggestedField: company.txtField, btnAddOption: company.btnAdd, classContainer: company.container, classTagContainer: company.tagContainer, classItem: company.classItem, limitKey: company.limitKey, classClose: "."+company.closeOption
                        }
                        )
                    }
                    ,
                    index:function() {
                        yOSON.AppCore.runModule("accordion_filter_bar", {
                            title: ".filters > li:nth-child(1)"
                        }
                        ),
                        yOSON.AppCore.runModule("close_tag"),
                        yOSON.AppCore.runModule("manage_font_size"),
                        yOSON.AppCore.runModule("modal_on_search_filter"),
                        yOSON.AppCore.runModule("url_redirect_search"),
                        yOSON.AppCore.runModule("see_more_options"),
                        yOSON.AppCore.runModule("search_change_view_mobile"),
                        yOSON.AppCore.runModule("enable_filter_search"),
                        yOSON.AppCore.runModule("modal_search_information")
                    }
                    ,
                    byDefault:function() {}
                }
            }
            ,
            aviso: {
                allActions:function() {}
                ,
                actions: {
                    ver:function() {
                        yOSON.AppCore.runModule("share_buttons"),
                        yOSON.AppCore.runModule("modal_share_email"),
                        yOSON.AppCore.runModule("modal_show_questions"),
                        yOSON.AppCore.runModule("featured_notices_aside"),
                        yOSON.AppCore.runModule("suggested_delete"),
                        yOSON.AppCore.runModule("suggested_hightlight_add"),
                        yOSON.AppCore.runModule("suggested_hightlight_add", {
                            context: ".job_description", callNewJob: !1
                        }
                        ),
                        yOSON.AppCore.runModule("suggested_hightlight_delete"),
                        yOSON.AppCore.runModule("load_map"),
                        yOSON.AppCore.runModule("show_aside_company"),
                        yOSON.AppCore.runModule("handling_company_video"),
                        yOSON.AppCore.runModule("put_height_body_bg")
                    }
                    ,
                    byDefault:function() {}
                }
            }
            ,
            postulaciones: {
                allActions:function() {}
                ,
                actions: {
                    index:function() {
                        yOSON.AppCore.runModule("show_applications_history"),
                        yOSON.AppCore.runModule("modal_close_applications")
                    }
                    ,
                    byDefault:function() {}
                }
            }
            ,
            notificaciones: {
                allActions:function() {}
                ,
                actions: {
                    index:function() {
                        yOSON.AppCore.runModule("show_conversation"),
                        yOSON.AppCore.runModule("count_words", {
                            readData: !0
                        }
                        ),
                        yOSON.AppCore.runModule("add_answer"),
                        yOSON.AppCore.runModule("mobile_view")
                    }
                    ,
                    byDefault:function() {}
                }
            }
            ,
            "perfil-destacado": {
                allActions:function() {}
                ,
                actions: {
                    paso2:function() {
                        yOSON.AppCore.runModule("featured_profile")
                    }
                    ,
                    byDefault:function() {}
                }
            }
            ,
            "comprar-perfil": {
                allActions:function() {}
                ,
                actions: {
                    "pago-efectivo":function() {
                        yOSON.AppCore.runModule("accordion_effect")
                    }
                    ,
                    byDefault:function() {}
                }
            }
            ,
            byDefault:function() {}
        }
        ,
        allControllers:function() {
            ("mi-cuenta"!==yOSON.controller||"perfil-publico"!==yOSON.action)&&(yOSON.AppCore.runModule("validate_modal_not_enough_information"), yOSON.AppCore.runModule("modal_not_enough_information"), yOSON.AppCore.runModule("validate_search_form"), yOSON.AppCore.runModule("disable_especial_characters"), yOSON.AppCore.runModule("autocomplete_custom", {
                context: "#modalNotEnoughInformation", txtValue: "#txtUbicacion", txtIdValue: "#txtIdUbicacion", urlAutocomplete: "/registro/filtrar-ubigeo/ubigeo"
            }
            ), yOSON.AppCore.runModule("validate_country_ubication", {
                txtUbicacion: "#txtUbicacion", txtIdUbicacion: "#txtIdUbicacion", container: "#frmNotEnoughInformation"
            }
            ))
        }
    }
    ,
    empresa: {
        controllers: {
            home: {
                allActions:function() {}
                ,
                actions: {
                    index:function() {
                        yOSON.AppCore.runModule("slider_companies_logos", {
                            sliderTcn: ".our_clients_slider"
                        }
                        ),
                        yOSON.AppCore.runModule("accordeon_our_plans"),
                        yOSON.AppCore.runModule("banner_image_performance"),
                        yOSON.AppCore.runModule("banner_image_performance", {
                            banner: "#bannerSlider"
                        }
                        ),
                        yOSON.AppCore.runModule("validate_e_planning_top"),
                        yOSON.AppCore.runModule("modal_contact")
                    }
                    ,
                    byDefault:function() {}
                }
            }
        }
        ,
        allControllers:function() {}
    }
    ,
    byDefault:function() {}
    ,
    allModules:function() {
        yOSON.AppCore.runModule("manipulateHTMLDom"),
        yOSON.AppCore.runModule("lazy_load"),
        yOSON.AppCore.runModule("placeholder_ie"),
        yOSON.AppCore.runModule("message_box"),
        yOSON.AppCore.runModule("modal_switcher"),
        yOSON.AppCore.runModule("plugins_pretty_select"),
        yOSON.AppCore.runModule("validate_forms"),
        yOSON.AppCore.runModule("modal_login_form"),
        yOSON.AppCore.runModule("modal_register_form"),
        yOSON.AppCore.runModule("modal_recover_password"),
        yOSON.AppCore.runModule("load_social_plugins"),
        yOSON.AppCore.runModule("cookie_web_settings"),
        yOSON.AppCore.runModule("mobile_toggle_menu"),
        yOSON.AppCore.runModule("fixed_menu"),
        yOSON.AppCore.runModule("tracking_factory"),
        yOSON.AppCore.runModule("e_planning")
    }
}

;