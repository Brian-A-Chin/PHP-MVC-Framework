$(window).on("load", function () {

    if(!$('#template-editor-container').length)
        return false;

    let TemplateData = {};
    let Path;
    hljs.configure({   // optionally configure hljs
        languages: ['html']
    });

    var QuillEditor = new Quill('#template-editor-container', {
        theme: 'snow',
        modules: {
            syntax: true,              // Include syntax module
            toolbar: [['code-block']]  // Include button in toolbar
        },
    });



    function LoadTemplate(){


        var data = Core.ajaxRequest({
            URL: 'GetTemplate',
            Strict: false,
            Data: {
                'Path': TemplateData.Path,
            }
        });

        QuillEditor.setText(data);


    }

    function SaveTemplate(){


        var ajaxData = Core.ajaxRequest({
            URL: 'SaveTemplate',
            Strict: true,
            Data: {
                'Path': TemplateData.Path,
                'Template' : QuillEditor.getText()
            }
        });

        if(ajaxData.Result){
            toastr.success(ajaxData.Response);
        }


    }

    function Setup(){
        $('.hide').show();
        $('#template-title').text(TemplateData.Name)
        $('#modify-date').text(TemplateData.Modified)

    }

    $(document).on('click','.load-template',function(event){
        var $this = $(this);
        $('.nested-loader').fadeIn('slow').promise().done(function () {
            Path = $this.attr('data-path');
            TemplateData = {
                Name: $this.text().trim(),
                Modified: $this.attr('data-modified'),
                Path: Path
            };
            Setup();
            LoadTemplate();
            console.log(TemplateData)
        });
    });

    $(document).on('click','#save',function(event) {
        SaveTemplate();
    });




});