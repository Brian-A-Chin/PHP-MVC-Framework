$(window).on("load", function () {

    //Listen for logout request
    document.querySelector(".acct-logout").addEventListener("click", function (e) {
        Core.Logout();
    }, false);

});