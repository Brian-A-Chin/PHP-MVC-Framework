const Core = new Modals('/Controllers/AuthenticationController');
$(window).on("load", function () {
    function Init(){
        if(Core.ajaxRequest({
            Strict: true,
            Data : {
                VL:true
            }
        })){
            //Authenticated
        }
    }
    Init();

    function InsertTemplate( Template ){
        $('#template_inject_container').html( Template );
    }

    $(document).on('click','.GT',function(){
        InsertTemplate(Core.GetTemplate({
            TemplateName: this.id + '.twig'
        }));
    });




    $(document).on('input','.form-control',function(){
        var $field = $(this).closest('.form-group');
        if (this.value) {
            $field.addClass('field--not-empty');
        } else {
            $field.removeClass('field--not-empty');
        }
    });

    $(document).on("input", ".numeric_strict", function() {
        this.value = this.value.replace(/[^0-9]/g,'');
    });



});