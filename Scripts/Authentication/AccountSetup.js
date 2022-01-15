const Core = new BaseClass('/Controllers/AuthenticationController');
$(window).on("load", function () {
    var errors = false;
    var total_steps = 0;
    var current_step = 1;
    var imgExts = ['png', 'jpg', 'jpeg', 'tiff']
    var allowedExts = ['png', 'jpg', 'jpeg', 'pdf', 'tiff', 'docx', 'doc', 'zip'];
    var max_file_size = 3000000 //3MB
    var maxSize = Math.round((max_file_size / 1024) / 1024) + "mb";
    var orginal_image;
    var file_extension;
    var file_size;
    if($('.aJaxForm').length) {
            disableBtn();
            document.querySelector(".aJaxForm").addEventListener("submit", function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                if(!errors) {
                    Core.aJaxFormSubmit($(this));
                }
            }, false);
    }
    function init(){
        $('.phase').each(function(){
            total_steps+=1;
            $(this).attr("id",total_steps);
            if(total_steps == 1){ $(this).fadeIn(); }
        });
        $('#totalSteps').text(total_steps);
        $('.navigation-container').html('<div class="center-btn"><button  type="button" class="btn btn-outline-danger propagate back-btn" data-action="bck"  value="true">Go back</button><button  type="button" class="btn btn-outline-primary propagate continue-btn" data-action="fwd"  value="true">Continue</button></div>');
        if (total_steps == 1) {
            let submitBtn = $('.continue-btn');
            submitBtn.attr("type","Submit");
            submitBtn.text("Submit");
        }
        $("#1").find('.back-btn').text('Exit');
    }
    init();

    function disableBtn() {
        let $this = $("#" + current_step + " .continue-btn");
        $this.addClass("disabled")
        $this.attr("disabled", "disabled")
    }

    function enableBtn() {
        let $this = $("#" + current_step + " .continue-btn")
        $this.removeClass("disabled")
        $this.removeAttr("disabled")
    }

    $(document).on("click", ".propagate", function(event) {
        errors = false;
        if ((current_step +1) == total_steps) {
            let submitBtn = $("#"+(current_step +1)).find('.continue-btn');
            submitBtn.attr("type","Submit");
            submitBtn.text("Submit");
        }
        var action = $(this).attr("data-action");
        if (action == "fwd") {
            $("#" + current_step + " .required").each(function () {
                $(this).removeAttr('style');
                $(this).closest('div').removeAttr('style');
                if ($(this).hasClass("length-requirement")) {
                    if ( this.value.length > $(this).attr("data-max") || this.value.length < $(this).attr("data-min") ){
                        errors = true
                        $(this).css("border", "1px solid red");
                    }
                }else if ($(this).attr('type') === 'file') {
                    errors = true;
                    $(this).closest('div').css("border", "1px solid red");
                }else if(this.value.length == 0){
                    errors = true;
                    $(this).closest('div').css("border", "1px solid red");
                }
            });
            if(errors){
                toastr.error("Please correct the fields highlighted in red before proceeding.");
            }else if((current_step + 1) <= total_steps){
                $("#" + current_step).hide()
                current_step = current_step + 1;
                $("#currentStep").text(current_step)
                setTimeout(function() {
                    $(".loading").fadeOut()
                }, 100)
                $(".loading").show()
                $("#" + current_step).show()

            }
        }else if($(this).text().trim()==='Exit') {
            window.location.href = '../';
        }else{
            errors = false;
            $("#" + current_step).hide()
            current_step = current_step - 1;
            $("#currentStep").text(current_step)
            setTimeout(function() {
                $(".loading").fadeOut()
            }, 100)
            $(".loading").show()
            $("#" + current_step).show()
        }

    });

    function previewImage(input, showInThis) {
        if ($.inArray(file_extension, imgExts) > -1) {
            $("." + showInThis + "_text").text("")
            orginal_image = $('.' + showInThis).attr('src')
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('.' + showInThis).attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        } else {
            $('.' + showInThis).attr('src', orginal_image)
            $("." + showInThis + "_text").text("Attached: " + input.name + "." + file_extension)
        }
    }

    $(".preview").change(function() {
        file_extension = this.files[0].name.substr((this.files[0].name.lastIndexOf('.') + 1)).toLowerCase();
        file_size = this.files[0].size;
        if ($.inArray(file_extension, allowedExts) > -1) {
            if (file_size <= max_file_size) {
                var showInThis = $(this).attr("data-here");
                previewImage(this, showInThis);
                $(this).closest('div').removeAttr('style');
                $(this).removeClass('required');
            } else {
                toastr.error("The file you choose is to large. The maximum file size allowed is " + maxSize);
                this.value = "";
            }
        } else {
            toastr.error("The file you choose is not allowed. Only " + allowedExts.join(", ") + " file types are allowed.")
            this.value = "";
        }
    });

    $(document).on("input", ".numeric_strict", function() {
        this.value = this.value.replace(/[^0-9\.]/g,'');
    });

    $(document).on("input", ".alpha_strict", function() {
        this.value = this.value.replace(/[^a-zA-Z\ ]/g,'');
    });

    $(document).on("input", ".length-requirement", function() {
        var current_length = this.value.length;
        var min_length = parseInt( $(this).attr("data-min") );
        var max_length = parseInt( $(this).attr("data-max") );
        $(this).next('span').remove();
        if(current_length < min_length){
            $(this).after('<span class="text-danger"><small>This should be at least '+min_length+' character(s) long</small></span>');
        }else if(current_length > max_length){
            $(this).after('<span class="text-danger"><small>This should be a maximum of '+min_length+' character(s) long</small></span>');
        }
    });

    $(document).on('input','.form-control',function(){
        var $field = $(this).closest('.form-group');
        if (this.value) {
            $field.addClass('field--not-empty');
        } else {
            $field.removeClass('field--not-empty');
        }
    });


});