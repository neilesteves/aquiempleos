$(document).on("ready",function(){
  
   		
  
   
	iniciarSlider();
	iniciarSliderEmpresas();
	limitarAreas();
	$("#id_ver_mas").on("click",mostrarAreas);
	$("#id_ver_menos").on("click",function(){
		
		$('.img-zoom-in:gt(9)').addClass('hide');
		$("#id_ver_mas").removeClass('hide');
		$("#id_ver_menos").addClass('hide');
		$( ".areaOculta" ).addClass( "hidden" );
	});
	$(".levantar-modal-ingresar").on("click",abrirModalLogin)
	$(".levantar-modal-ingresar").on("click",abrirModalRegister)

});
function abrirModalLogin(){
	$("#modalLoginUser").modal("show");
}
function abrirModalRegister(){
	$("#modalRegisterUser").modal("show");
}
function iniciarSlider(){
	var owl = $("#owl-home-postulante");

	      owl.owlCarousel({
	      navigation : true,
	      lazyLoad : true,	
	      items : 1, //10 items above 1000px browser width
	      itemsDesktop : [1200,1], //5 items between 1000px and 901px
	      itemsDesktopSmall : [993,1], // 3 items betweem 900px and 601px
	      itemsTablet: [596,1], //2 items between 600 and 0;
	      itemsMobile : false // itemsMobile disabled - inherit from itemsTablet option
	      
	      });

	      // Custom Navigation Events
	      $(".arrow-next").click(function(){
	        owl.trigger('owl.next');
	      })
	      $(".arrow-prev").click(function(){
	        owl.trigger('owl.prev');
	      })
	      $(".stop").click(function(){
	        owl.trigger('owl.stop');
	      })
	      $(".item").fadeIn("slow").show();
}
function iniciarSliderEmpresas(){
	var owl = $("#owl-home-empresas");

	      owl.owlCarousel({
	      navigation : true,
	      lazyLoad : true,	
	      items : 4, //10 items above 1000px browser width
	      itemsDesktop : [1200,4], //5 items between 1000px and 901px
	      itemsDesktopSmall : [993,2], // 3 items betweem 900px and 601px
	      itemsTablet: [596,2], //2 items between 600 and 0;
	      itemsMobile : false // itemsMobile disabled - inherit from itemsTablet option
	      
	      });

	      // Custom Navigation Events
	      $(".arrow-next").click(function(){
	        owl.trigger('owl.next');
	      })
	      $(".arrow-prev").click(function(){
	        owl.trigger('owl.prev');
	      })
	      $(".stop").click(function(){
	        owl.trigger('owl.stop');
	      })
	      $(".item").hide();
	      $(".item").fadeIn("slow").show();
	      
	       
}

function limitarAreas(){
	var cantidad_areas=$(".area-postular").find(".img-zoom-in").length;
	if(cantidad_areas>10){
		$('.img-zoom-in:gt(9)').addClass('hide');
		$('.img-zoom-in:gt(9)').addClass('slideTogglecss');
	}
	else{
		$(".ver-mas").addClass('hide');
	}
}
function mostrarAreas(){
	$('.img-zoom-in').removeClass('hide');
	$("#id_ver_mas").addClass('hide');
	$("#id_ver_menos").removeClass('hide');
	$( ".areaOculta" ).removeClass( "hidden" );
}