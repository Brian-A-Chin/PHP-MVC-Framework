
$(window).on("load", function () {
    $(document).on('click','.tile',function(event){
        if(event.target.nodeName === 'DIV') {
            if (this.hasAttribute('data-action')) {
                Core.URLObject.set('action', $(this).attr('data-action'));
                //window.location.href = decodeURIComponent(url);
            }
        }
    });




});