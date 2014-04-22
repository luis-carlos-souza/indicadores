$(function(){  
    $('.btn-pwd').live('click',function(e){
        e.preventDefault();
        var id = $(this).attr('id');
        var url = baseuri + '/usuarios/pwd/'+id+'/'
        $('#myModal #url-afo-remove').attr('href',url);
        $('#myModal').modal('show');
    })

})
