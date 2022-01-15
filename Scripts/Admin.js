
$(window).on("load", function () {
    var PermissionParents = {};
    var Permissions = [];
    
    $(document).on('click','.tile',function(event){
        if(event.target.nodeName === 'DIV') {
            if (this.hasAttribute('data-action')) {
                Core.URLObject.set('action', $(this).attr('data-action'));
                //window.location.href = decodeURIComponent(url);
            }
        }
    });


    function SetPermissions(SetCountsOnly){
        PermissionParents = {};
        Permissions = [];
        var lastParent = null;
        $('.permission').each(function(){
            var parent = $(this).attr('data-parent');
            var permissionName = $(this).attr('data-value');
            var $counter = $('#'+parent+'-section');

            if(lastParent == null || lastParent != parent){
                $counter.find('span').text('0');
                lastParent = parent;
            }

            if($(this).is(':checked')){
                if(!SetCountsOnly){
                    Permissions.push(permissionName);
                    if(this.hasAttribute('data-parent')) {
                        if (!PermissionParents.hasOwnProperty(parent)) {
                            PermissionParents[parent] = 1;
                        } else {
                            PermissionParents[parent] = PermissionParents[parent] + 1;
                        }
                    }
                }
                $counter.find('span').eq(0).text( parseInt($counter.find('span').eq(0).text()) + 1 );
            }
            $counter.find('span').eq(1).text( parseInt($counter.find('span').eq(1).text()) + 1 );


        });
    }
    SetPermissions();

    $(document).on('click','.permission',function(event) {
        SetPermissions();
    });

    $(document).on('click','.set-permissions',function(event) {
        SetPermissions();
        if(Permissions.length > 0){
            var permissionSet = $.merge(Permissions,Object.keys(PermissionParents));
            var permissionID = $('#pid').val();
            var permissionName = $('#permission-name').val();
            var ajax = Core.ajaxRequest({
                Strict: true,
                URL:'ManagePermissionGroup',
                Data : {
                    CreatePermissions:permissionID==undefined,
                    Permissions:permissionSet,
                    PermissionGroupID:permissionID,
                    Name:permissionName
                }
            });
        }else{
            toastr.error("You must select at least 1 allowable action.")
        }

    });



});