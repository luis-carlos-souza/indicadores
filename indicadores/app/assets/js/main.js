if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
    var msViewportStyle = document.createElement("style")
    msViewportStyle.appendChild(
        document.createTextNode(
            "@-ms-viewport{width:auto!important}"
            )
        )
    document.getElementsByTagName("head")[0].appendChild(msViewportStyle)
}

var baseuri = $('base').attr('href').replace('/app/','');    
$(function(){  
    //hide elementos
    $('.hider').hide();
    //disabled elementos
    $('.disabler').attr('disabled','disabled');
    //tootips
    $('.tip').tooltip();
    $('.tip-l').tooltip({
        placement:'left'
    });
    $('.tip-r').tooltip({
        placement:'right'
    });
    $('.tip-b').tooltip({
        placement:'bottom'
    });    
    //auto dropdown
    $('.js-activated').dropdownHover().dropdown(); 
    //btn back history
    $('.btn-back').live('click',function(e){
        e.preventDefault();
        window.history.back();
    })

})
