$(window).on("load", function () {


    $('ul.nav-sidebar a').filter(function() {
        var rgx = new RegExp($(this).attr("href"), "gi");
        return Core.BaseURL.match(rgx);
    }).addClass('active');

    $('ul.nav-treeview a').filter(function() {
        var rgx = new RegExp($(this).attr("href"), "gi");
        return Core.BaseURL.match(rgx);
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');

    $('.verify').each(function(){
        Core.VerifyElements[this.id] = this.value;
    });

    $(document).on('click','.postRequest',function(event){
        $('.loader').fadeIn('slow').promise().done(function() {
            Core.PostRequest(event.target);
        });
    });

    $(document).on('submit','.aJaxForm',function(event){
        event.preventDefault();
        $('.loader').fadeIn('slow').promise().done(function() {

            if($(event.target).hasClass('requireVerification')){
                var id = $(event.target).attr('data-verify-id');
                var $target = $('#'+id);
                if(Core.VerifyElements[id] != $target.val()){
                    Core.ToggleVerificationMode({
                        Target:$target.val(),
                        Type:$target.attr('type').toLowerCase() === 'email' ? 'email' : 'phone',
                        CheckUnique:true,
                    });
                    return;
                }
            }
            Core.aJaxFormSubmit(event.target);

        });

    });

    $(document).on("input", ".counted", function (e) {
        var max = $(this).attr('data-max');
        var len = this.value.length;
        var left = max - this.value.length;
        $('#countedText').text(left);
        if (len > max) {
            $('#countedText').css('color', 'red');
            $('#mainModal-action').prop('disabled', 'disabled');
        } else {
            $('#countedText').removeAttr('style');
            $('#mainModal-action').removeAttr('disabled');
        }
    });

    $(document).on('input', '.filter', function (event) {

        var filter = this.value;
        $('#' + $(this).attr("data-filter-target") + ' .filterable').each(function () {
            if ($(this).text().search(new RegExp(filter, "i")) < 0) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });

    });

    $(document).on('click','#dialog-confirm',function(event){
        console.log(Core.PendingActions)
        $('.loader').fadeIn('slow').promise().done(function() {
            $('.loader').fadeIn();
            $.each(Core.PendingActions, function (key, value) {
                let $target = $('[data-pid="'+value+'"]');
                $target.addClass($target.attr('data-confirm'));
                $target.removeClass('confirm');
                $target.trigger('click');
            });
        });
        Core.HideDialog(false);
    });

    $(document).on('click','.dialog-close',function(event){
        Core.HideDialog(false);
        Core.PendingActions = [];
    });

    $(document).on('click','.confirm',function(event){

        let ID = Core.GenerateKey(6);
        $(this).attr('data-pid', ID);
        Core.PendingActions.push(ID);
        Core.ShowDialog(false);

    });

    $(document).on('click','#mainModal-action',function(event){
        if(Core.VerificationMode){
            $('.loader').fadeIn('slow').promise().done(function() {
                if (Core.ajaxRequest({
                    URL: 'VerifyClientCode',
                    Strict: true,
                    Data: {
                        'ClientCode': $("input[type='text'][name='VerificationCode']").val(),
                    }
                }).Result) {
                    toastr.success('Thank You');
                    let $this = $('#'+$('.content-wrapper .requireVerification').attr('id'));
                    if($this.prop("tagName") === 'FORM'){
                        $('#VerificationCode').val($("input[type='text'][name='VerificationCode']").val());
                        $('#mainModal').modal('hide');
                        $this.removeClass('requireVerification');
                        $this.submit();
                    }
                } else {
                    toastr.error('Invalid Code');
                }
            });
        }
    });

    $("#mainModal").on("hidden.bs.modal", function () {
        Core.VerificationMode = false;
    });

    $(document).on('keyup','.verify',function(event) {
        $('form').addClass('requireVerification');
        $('form').attr('data-verify-id',this.id);
    });



    $(document).on('click','.upload-btn',function(event) {
        if(FileAttachmentsList.length > 0){
            var formData = new FormData($(this).closest('form')[0]);
            var ajaxRequest = Core.ajaxRequest({
                UploadSupport: true,
                Strict: true,
                URL:$(this).closest('form').attr('action'),
                Data: formData
            });

            var showErrors = false;
            var hasErrorList = ajaxRequest.hasOwnProperty('ErrorList');
            var hasNoticeList = ajaxRequest.hasOwnProperty('NoticeList');
            $('#upload-errors').empty();
            if(hasNoticeList && ajaxRequest.Result){
                toastr.warning('Notice. Some files were modified.');
                showErrors = true;
            }else if(hasErrorList && ajaxRequest.Result && ajaxRequest.ErrorList.length == 0){
                toastr.success('Upload Successful');
            }else if(hasErrorList && ajaxRequest.ErrorList.length > 0 && !ajaxRequest.Result){
                toastr.error('Upload Failed');
                showErrors = true;
            }else {
                toastr.error('Upload Failed');
            }
            if(hasErrorList && showErrors){
                $.each(ajaxRequest.ErrorList, function(key,value){
                    $('#upload-errors').append('<p class="text-danger">'+value+'</p>');
                });
            }
            if(hasNoticeList && showErrors){
                $.each(ajaxRequest.NoticeList, function(key,value){
                    $('#upload-errors').append('<p class="text-danger">'+value+'</p>');
                });
            }
        }else{
            toastr.error('No files selected');
        }



    });

    $(document).on('click', '.link', function (event) {
        if (($.inArray(event.target.nodeName, $(this).attr('data-type').split(',')) !== -1 && event.originalEvent !== undefined) || (event.originalEvent == undefined && $(this).hasClass('auto-direct'))) {
            if (this.hasAttribute('data-url')) {
                var target = $(this).attr('data-url');
                if (this.hasAttribute('new-window')) {
                    window.open(target, '_blank');
                } else {
                    window.location.href = target;
                }
            }
        } else {
            console.log('Rejected:' + event.target.nodeName + ' not in ' + $(this).attr('data-type').split(','))
        }
    });

});