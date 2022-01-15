
$(window).on("load", function () {
    $(document).on('click','.tile',function(event){
        if(event.target.nodeName === 'DIV') {
            if (this.hasAttribute('data-action')) {
                var account = Core.URLObject.get('account');
                var action = $(this).attr('data-action')
                window.location.href = action+'?account='+account;
            }
        }
    });

});