yOSON.AppCore.addModule("effect_parallax_home",function(Sb){var afterCatchDom,catchDom,dom,events,initialize,st,suscribeEvents;return dom={},st={parallaxContainer:".parallax_container",btnShowPlanFeatured:"#btnShowPlanFeatured",featuredProfileHome:".featured_profile",shineLine:".shine_line",iconDiamond:".icon_diamond",diamondBox:".diamond_box"},catchDom=function(){dom.parallaxContainer=$(st.parallaxContainer),dom.featuredProfileHome=$(st.featuredProfileHome),dom.btnShowPlanFeatured=$(st.btnShowPlanFeatured,dom.featuredProfileHome),dom.shineLine=$(st.shineLine,dom.featuredProfileHome),dom.iconDiamond=$(st.iconDiamond,dom.featuredProfileHome),dom.diamondBox=$(st.diamondBox,dom.featuredProfileHome)},afterCatchDom=function(){isInternetExplorer()||device.mobile()||device.tablet()||(dom.parallaxContainer.addClass("is_active"),$.stellar({horizontalScrolling:!1,responsive:!0}))},suscribeEvents=function(){var ie8,ie9;ie8=!1,ie9=!1,isInternetExplorer()&&(ie8="8.0"===browser.version,ie9="9.0"===browser.version),ie8||ie9||device.mobile()||device.tablet()||(dom.btnShowPlanFeatured.on("mouseenter",events.showShinningDiamond),dom.btnShowPlanFeatured.on("mouseleave",events.hideShinningDiamond))},events={showShinningDiamond:function(){dom.shineLine.addClass("bounceIn"),dom.iconDiamond.addClass("shadowPulseDiamond"),dom.diamondBox.removeClass("slideInUpDiamond"),dom.diamondBox.addClass("slideInDownDiamond")},hideShinningDiamond:function(){dom.shineLine.removeClass("bounceIn"),dom.iconDiamond.removeClass("shadowPulseDiamond"),dom.diamondBox.removeClass("slideInDownDiamond"),dom.diamondBox.addClass("slideInUpDiamond")}},initialize=function(oP){$.extend(st,oP),catchDom(),afterCatchDom(),suscribeEvents()},{init:initialize}},["js/libs/jquery.stellar/jquery.stellar.min.js","js/libs/jquery.device.js"]),yOSON.AppCore.addModule("search_autocomplete",function(Sb){var afterCatchDom,catchDom,dom,functions,initialize,st;return dom={},st={txtDescription:"#txtDescription",urlSkills:"/home/filtrar-avisos"},catchDom=function(){dom.frmPrincipal=$(st.frmPrincipal),dom.txtDescription=$(st.txtDescription)},afterCatchDom=function(){yOSON.isAutocompleteActive&&dom.txtDescription.custom_autocomplete({urlAutocomplete:st.urlSkills,numLettersToStart:0,needToken:!1,fnPrepareData:functions.fnPrepareData,fnAfterUpdateText:functions.fnSelectSkill})},functions={fnSelectSkill:function(value,id){window.location=id},fnPrepareData:function(token,value){var isMobile;return isMobile=$(window).width()<=yOSON.utils.getBreakPointMobile(),{csrfhash:token,value:value,mobile:isMobile}}},initialize=function(oP){$.extend(st,oP),catchDom(),afterCatchDom()},{init:initialize}},["js/libs/jquery.autocomplete.custom.js"]);