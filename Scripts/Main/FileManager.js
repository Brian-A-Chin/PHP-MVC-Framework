var FileAttachmentsList = [];
$(window).on("load", function() {
    var LoadingGIF = '/Assets/Icons/loader-small.gif';

    String.prototype.hashCode = function() {
        var hash = 0;
        if (this.length == 0) {
            return hash;
        }
        for (var i = 0; i < this.length; i++) {
            var char = this.charCodeAt(i);
            hash = ((hash<<5)-hash)+char;
            hash = hash & hash; // Convert to 32bit integer
        }
        return hash;
    }


    function SetPreview(params) {
        var file = params.FileData;
        var src = URL.createObjectURL(file);

        var file_extension = file.name.substr((file.name.lastIndexOf('.') + 1)).toLowerCase();
        console.log('ext->'+file_extension);
        $('#'+params.ID).attr("src",src);
    }





    $(document).on("change", "#attachments", function(e) {

        var files = e.target.files;
        for (var i = 0, f; f = files[i]; i++) {
            var name = this.files[i].name;
            var ID = Core.GenerateKey(7);
            SetPreview({
                ID:ID,
                FileData:files[i]
            });
            var src = URL.createObjectURL(files[i]);
            var file_size = Core.FormatBytes(this.files[i].size);
            FileAttachmentsList.push(name);
            $("#attachmentList").val(FileAttachmentsList)
            $("#attachmentsPreview").append('<li class="clickable"> <img id="'+ID+'" src="'+src+'" width="30" height="20"/>' + name + '(' + file_size + ')<i class="far fa-times-circle attachmentx" id="' + name + '"></i></li>')

        }

    });



    $(document).on('click', '.attachmentx', function() {
        if ($.inArray(this.id, FileAttachmentsList) != -1) {
            FileAttachmentsList.splice(FileAttachmentsList.indexOf(this.id), 1);

            $(this).closest("li").remove();
            $("#attachmentList").val(FileAttachmentsList)
        }
        console.log(FileAttachmentsList)
    });









});