$(function(){ 
    $('.edit').live('click',function(){
        $('input').removeAttr('disabled');
        $('select').removeAttr('disabled');
        $('radio').removeAttr('disabled');
        $('checkbox').removeAttr('disabled');
        $('.save').show();
        $(this).html('<i class="glyphicon glyphicon-log-out"></i> Cancelar');
        $(this).removeClass('edit').addClass('cancel');
        $('input:first').focus();
    })
    $('.cancel').live('click',function(){
        $('input').attr('disabled','disabled');
        $('select').attr('disabled','disabled');
        $('radio').attr('disabled','disabled');
        $('checkbox').attr('disabled','disabled');
        $('.save').hide();
        $('input').removeClass('invalid');
        $(this).html('<i class="glyphicon glyphicon-edit"></i> Editar');
        $(this).removeClass('cancel').addClass('edit');
        $('#f-update').get(0).reset();
    })
   
    
    $('.remove').live('click',function(e){
        e.preventDefault();
        var id = $(this).attr('id');
        var url = baseuri + '/usuario/remove/'+id+'/'
        $('#myModal #url-user-remove').attr('href',url);
        $('#myModal').modal('show');
    })
    
    $('.user-save').live('click',function(e){
        e.preventDefault();
        $('#f-user').submit();
    })
     $('.user-cancel').live('click',function(e){
         var url = baseuri + '/usuario/';
         window.location = url;
     })
})