//base location
var baseUri = $('base').attr('href').replace('/app/','');
$(function(){
    //autocompleta endereço
    $('#cep').live('keyup',function(e){
        if (e.shiftKey || e.ctrlKey || e.altKey) { // if shift, ctrl or alt keys held down 
            e.preventDefault();         // Prevent character input 
        } else { 
            var n = e.keyCode; 
            if (!((n == 8)              // backspace 
                || (n == 46)                // delete 
                //|| (n >= 35 && n <= 40)     // arrow keys/home/end 
                || (n >= 48 && n <= 57)     // numbers on keyboard 
                || (n >= 96 && n <= 105))   // number on keypad 
            ) { 
                e.preventDefault();     // Prevent character input 
                return false;
            } 
        }         
        //consulta CEP webservices
        var cep = $.trim($('#cep').val()).replace('_','');
        if(cep.length >= 9){
            $('#cep').blur();
            var cep = $.trim($('#cep').val());
            var url = baseUri+'/cep/getcep/';    
            $.post(url,{
                cep:cep
            },
            function (data) {
                if(data != -1){
                    data = $.parseJSON(data);
                    data = data.rs[0];
                    $('#endereco').val(data.endereco);
                    $('#bairro').val(data.bairro);
                    $('#cidade').val(data.cidade);
                    $('#uf').val(data.uf.toUpperCase());
                    $('#cep').removeClass('invalid');
                    $('#endernum').focus();
                }
                else{
                    $('#cep').addClass('invalid');    
                    $('#cep').focus();  
                }
            })             
        }
    })  
    
    $('#uf').live('change',function(){
        var cep = $.trim($('#cep').val());        
        if(cep == ""){
            var endereco = $.trim($('#endereco').val());
            endereco += ", " + $.trim($('#cidade').val());
            endereco += ", " + $.trim($('#uf').val());
            var url = baseUri+'/cep/getend/';   
            $.post(url,{
                endereco:endereco
            },
            function (data) {
                if(data != -1 && data != -3){
                    data = $.parseJSON(data);
                    data = data.rs[0];
                    $('#endereco').val(data.endereco);
                    $('#cep').val(data.cep);
                    $('#bairro').val(data.bairro);
                }
            })        
        }
    })
})
