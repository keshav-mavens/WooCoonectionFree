(function ($) {
    "use strict";
    /* ------------------------------------------------------------------------- *
     * COMMON VARIABLES
     * ------------------------------------------------------------------------- */
    var $wn = $(window),
    $document = $(document),
    $body = $('body');
    $(function () { 
    });
}(jQuery));


//On click of search button...
function searchUserEmail(){
    var getSearchData = document.getElementById("datasearch").value;
    var getCurrentUrl = window.location.href.split('?')[0] ;
    var getFinalUrl = getCurrentUrl+'?page=wooconnection-authentication-admin&searchdata='+getSearchData;
    window.location = getFinalUrl;
}