$(function() {
    var bs = {
    backSpace : function() {
                    $(document).live("keyup keydown", function(event){
                        if (event.keyCode==8) {
                            $("#backToPerfil").click();
                            event.preventDefault(); 
                        }
                    });
                }
    };
    bs.backSpace();
});