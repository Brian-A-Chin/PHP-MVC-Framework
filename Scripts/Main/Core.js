class BaseClass {
    URL;
    URI;
    URLObject;
    CurrentPage;
    DELAY = 400;
    Purgatory = [];
    DelayClicks = 0
    DelayTimer = null;
    Account = null;
    PendingActions = [];
    VerificationMode = false;
    VerifyElements = {};
    ExceptionMode = false;
    ExportTypes = ['xlsx', 'pdf', 'csv', 'txt', 'json'];
    ExportNiceNames = ['Excel', 'PDF', 'CSV', 'TXT', 'JSON'];
    DefaultExportName = document.title;

    constructor( PostDirectory ) {
        this.PostDir = PostDirectory;
        this.BaseURL = window.location.href;
        this.URL = new URL(window.location.href);
        this.URLObject = this.URL.searchParams;
        this.URI = window.location.href.split(/[\/\?]+/);
        this.Account = this.URLObject.get('account');
    }

    static ThrowCriticalError( Data ){
        toastr.error('An error occurred');
        $(document).Toasts('create', {
            position:'bottomRight',
            delay:10000,
            autohide:true,
            title: '<span class="text-danger"><i class="fas fa-times-circle"></i></span> An error occurred',
            body: '<p>'+Data+'</p>'
        });
    }

    SetNiceError( Parameters ){
        if($('#niceError').length){
            $('#niceError').text(Parameters.Message);
        }
    }

    HandleAjaxResponse(Parameters){
        if (Parameters.Data.hasOwnProperty('PopupMsg')) {
            if (Parameters.Data.Result) {
                toastr.success(Parameters.Data.PopupMsg);
                /*$(document).Toasts('create', {
                    position:'topRight',
                    delay:5000,
                    autohide:true,
                    title: '<span class="text-success"><i class="far fa-check-circle"></i></span> Success',
                    body: Parameters.Data.PopupMsg
                });

                 */
            } else {
                toastr.error(Parameters.Data.PopupMsg);
                /*
                $(document).Toasts('create', {
                    position:'topRight',
                    delay:5000,
                    autohide:true,
                    title: '<span class="text-danger"><i class="fas fa-times-circle"></i></span> An error occurred',
                    body: Parameters.Data.PopupMsg
                });

                 */
            }
        }
        if (Parameters.Data.hasOwnProperty('Redirect') && Parameters.Data.hasOwnProperty('View')) {
            Parameters.Element.find('#template_inject_container').html(Parameters.Data.View)
            setTimeout(function () {
                window.location.href = Parameters.Data.Redirect;
            }, 1000);
        } else if (Parameters.Data.hasOwnProperty('View')) {
            Parameters.Element.find('#template_inject_container').html(Parameters.Data.View)
        } else if (Parameters.Data.hasOwnProperty('Redirect')) {
            var timeout = Parameters.Data.hasOwnProperty('Timeout') ? Parameters.Data.Timeout : 1000;
            setTimeout(function () {
                window.location.href = Parameters.Data.Redirect;
            }, timeout);
        } else if (Parameters.Data.hasOwnProperty('Reload')) {
            if(Parameters.Data.Reload) {
                setTimeout(function () {
                    window.location.reload();
                }, 1000);
            }
        }

        if (Parameters.Data.hasOwnProperty('Response')) {
            this.SetNiceError({
                Message: Parameters.Data.Response
            });
        }

        if (Parameters.Data.hasOwnProperty('Errors')) {
            var errors = Parameters.Data.Errors;
            if($.isArray(errors)){
                $.each(errors,function (key,value){
                    $(document).Toasts('create', {
                        position:'bottomRight',
                        delay:5000,
                        autohide:false,
                        title: '<span class="text-danger"><i class="fas fa-times-circle"></i></span> An error occurred',
                        body: '<p>'+value+'</p>'
                    });
                });
            }
        }
    }

    ajaxRequest(Parameters) {
        var postUrl = this.PostDir;

        if(Parameters.hasOwnProperty('Method')){
            postUrl += Parameters.Method;
        }else if(Parameters.hasOwnProperty('URL')){
            postUrl += Parameters.URL;
        }
        console.log(Parameters);
        let response = false;
        let aJaxParams = {
            type: "POST",
            url: postUrl,
            async: false,
            method: 'POST',
            data: Parameters.Data,
            success: function (Data) {
                $('.loader,.nested-loader').fadeOut();
                console.log(Data);
                try {
                    response = Parameters.Strict ? $.parseJSON(Data) : Data;
                } catch (e) {
                    console.log(e);
                    if (Data.trim().length > 0) {

                        BaseClass.ThrowCriticalError(Data);
                    }
                }
            },
            error: function (Data) {

                BaseClass.ThrowCriticalError(Data);
            }
        };

        if (Parameters.hasOwnProperty('UploadSupport')) {
            if (Parameters.UploadSupport) {

                aJaxParams.cache = false;
                aJaxParams.contentType = false;
                aJaxParams.processData = false;

            }
        }
        $.ajax(aJaxParams);
        if (response != false) {
            this.HandleAjaxResponse({
                Element: null,
                Data: response
            });
        }
        return response;
    }

    Logout(){
        window.location.href = '/account/login';
    }

    GetTemplate( Parameters ) {
        return this.ajaxRequest({
            Strict: false,
            Data: {
                GetTemplate: Parameters.TemplateName
            }
        });
    }

    FormIsFilled(params) {
        var result = true;
        $('.write-error').remove();
        $.each($('#' + params.target + ' .form-control'), function (k) {
            if (!$(this).hasClass('not-required')) {
                if ($.inArray($(this).attr("name"), params.exclude) === -1) {
                    if (this.value.length === 0 && $(this).attr("type") !== 'hidden') {
                        result = false;
                        if (params.highlight) {
                            $(this).css('border', '1px solid red');
                            $(this).after('<p class="write-error"><small class="text-danger">Required <i class="fa fa-level-up" aria-hidden="true"></i></small></p>')
                        }
                    } else {
                        $(this).css('border', '1px solid #ddd');
                    }
                }
            }
        });

        $.each($('.required-radio-options'), function () {
            if ($(this).attr('data-selected') === 'false') {
                result = false;
            }
        });

        return result
    }

    FormatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }




     GenerateKey(max) {
        var result = '';
        var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var charactersLength = characters.length;
        for (var i = 0; i < max; i++) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }
        return result;
    }

    NumberFormat(str) {
        return parseInt(str.replace(/[^\d]/g, ''));
    }

    ReplaceUnderscores(str) {
        return str.replace(/_/g, ' ');
    }

    PostRequest( target ) {
        let data = {};
        let $this = $(target);
        let originalText = $this.text();
        let values = $this.attr('data-values').split(',');
        let url = $this.attr('data-url');
        let i = 0;
        $.each($this.attr('data-keys').split(','),function(k,v){
            data[v] = values[i++];
        });
        let response = this.ajaxRequest({
            Strict: true,
            URL:url,
            Data : data
        });

        $this.text($this.attr('data-temp-txt'));
        setTimeout(function () {
            $this.text(originalText);
        }, 2000);
    }

   aJaxFormSubmit( form ){
        let $this = $(form);
        var ajaxParams = {
            Data: $this.serialize()
        };

        var actionAttr = $this.attr('action');
        var methodAttr = $this.attr('data-method');
        if (typeof actionAttr !== 'undefined' && actionAttr !== false) {
            ajaxParams['URL'] = actionAttr;
        } else if (typeof methodAttr !== 'undefined' && methodAttr !== false) {
            ajaxParams['Method'] = methodAttr;
        }




        try {
            let data = $.parseJSON((this.ajaxRequest(ajaxParams)));
            this.HandleAjaxResponse({
                Element:$this,
                Data:data
            });
        }catch (e) {
            console.log(e);
            BaseClass.ThrowCriticalError('Failed to parse JSON response. Data returned in incorrect format or page not found.');
        }

    }






}

