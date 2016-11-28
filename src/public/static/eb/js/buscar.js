$( function() {

	var d = {
        options: 'input[type="radio"], input[type="checkbox"]',
        modal: ".search_modal",
        search: ".btn_filter_search",  
        containetOptions: ".filter_bar",        
        section: ".filters",    

        frmSearch: "#frmSearchPage",        
        txtSearch: ".txt_search",     

        optionsCk: ".checkN:checked",
        txtPais: "#pais",
    },
    fn;

	var buscar = {

		init: function(){			
			fn = this;
			$(d.options).on("change", this.eInitSearch);
			//$(d.search).on("click", this.eInitSearch);

		},

		eSearchBegins: function(e) {
			$(d.modal).show();
        },

        eInitSearch: function(e) {

			var section, skeleton;

			section = $(d.section, d.containetOptions);
			skeleton = fn.createSqueleton(section);
			structure = fn.buildStructure(skeleton, $(d.optionsCk));
			searchText = fn.cleanText($(d.txtSearch).val());
			baseUrl = fn.createBaseURL(APP.baseHost, searchText);
			urlGenerated = fn.createFinalUrl(structure, baseUrl);

			//alert(baseUrl + " *-* " + urlGenerated);
            window.location = urlGenerated;
        },

        createSqueleton: function(sections) {
            var skeleton;
            return skeleton = {}, 
            	sections.each(function() {
	                var key;
	                key = $(this).data("key"),
	                skeleton[key] = []	                
            	}), 
            	skeleton
        },
        buildStructure: function(skeleton, options) {        	

            return options.each(function() {
                var key, value;
                key = $(this).closest(d.section).data("key"),
                value = $(this).data("value")

                if(key != undefined) {skeleton[key].push(value)}
            }), skeleton
        },
        createBaseURL: function(host, searchText) {
            var searchURL;
            var _pais = $(d.txtPais).val();
            return searchURL = "", "" !== searchText && (searchURL = "q/" + searchText + "/"), host + "/"+ _pais +"/buscar/" + searchURL
        },
        createFinalUrl: function(structure, url) {
            return $.each(structure, function(id, val) {
                val.length > 0 && (url += id + "/" + val.join("--") + "/")
            }), url
        },
        cleanText: function(searchText) {
            var value;
            return value = $.trim(searchText), "" !== value && (value = value.replace(/-+/g, " "), value = value.replace(/_+/g, " "), value = value.replace(/\.+/g, ""), value = value.replace(/\s/g, "+"), value = value.replace(/,+/g, ""), value = value.replace(/\%+/g, " ")), value
        },

		search: function(msg){
			alert(msg)
		},

	};

	buscar.init();

});