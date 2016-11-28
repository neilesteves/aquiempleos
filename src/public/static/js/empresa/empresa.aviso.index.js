/*
 Empresa Aviso Index
 */
$(function() {
    var avisoIndex = {
        clickBlockProd: function() {
            var counter = 0;

            $(".choose-ads:not(.not-blocka)", ".products").on('click', function(e) {
                var _this = $(this),
                    btn = _this.find('.btn');

                if(btn.parent('form').length != 0){
                    btn.parent().submit();
                }else{
                    if (btn.hasClass("login_modal")) {
                        counter++;
                        if (counter === 1) {
                            //btn.trigger('click');
                            counter = 0;
                        }
                    } else {
                        //window.location.href = btn.attr('href');
                    }
                }
            });
        }
    };
    // init
    avisoIndex.clickBlockProd();
});