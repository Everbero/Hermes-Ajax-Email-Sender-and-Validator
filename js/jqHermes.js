        var allowed_file_size = "1048576";
        var allowed_files = ['image/png', 'image/gif', 'image/jpeg', 'image/pjpeg', 'application/pdf'];
        var border_color = "#C2C2C2"; //cor padrão da borda do formulário

        $(document).on('submit','form',function(e) {
            e.preventDefault(); //impede que o formulário seja enviado pelo comportamento padrão
            proceed = true;
            
            $("#contact_results").html('<div class="alert alert-warning bounceIn animated" role="alert"><i class="fa fa-cog fa-spin fa-1x fa-fw"></i><span class="sr-only">Loading...</span>Validando dados</div>');

            //validação de campos simples
            $($(this).find("input[data-required=true], textarea[data-required=true]")).each(function(){
                    if(!$.trim($(this).val())){ //se este campo estiver vazio
                        $(this).css('border-color','red'); //muda a cor da borda para vermelho 
                        proceed = false; //retorna mensagem de erro
                    }
                    //valida campo email
                    var email_reg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/; 
                    if($(this).attr("type")=="email" && !email_reg.test($.trim($(this).val()))){
                        $(this).css('border-color','red'); //muda a cor da borda para vermelho  
                        proceed = false; //retorna mensagem de erro             
                    }   
            }).on("input", function(){ //muda a cor da borda de volta
                 $(this).css('border-color', border_color);
            });
            
            //verifica o tamanho e tipo do arquivo antes do upload
            if(window.File && window.FileReader && window.FileList && window.Blob){
                var total_files_size = 0;
                $(this.elements['file_attach[]'].files).each(function(i, ifile){
                    if(ifile.value !== ""){ //continua se existem arquivos
                        if(allowed_files.indexOf(ifile.type) === -1){ //verifica se o tipo de arquivo é suportado
                            alert( ifile.name + " este formato de arquivo não é suportado!");
                            proceed = false;
                        }
                     total_files_size = total_files_size + ifile.size; //soma o tamanho do arquivo com o tamanho total
                    }
                }); 
               if(total_files_size > allowed_file_size){ 
                    alert( "Certifique-se de que seu arquivo tem menos de 1 MB!");
                    proceed = false;
                }
            }
            
            //se tudo estiver ok, continua o envio pelo AJX
            if(proceed){ 
                var post_url = $(this).attr("action"); //pega o URL da ação do form
                var request_method = $(this).attr("method"); //pega o método GET/POST
                var form_data = new FormData(this); //Cria o objeto FormData
                
                $.ajax({ //disparo via ajax
                    url : post_url,
                    type: request_method,
                    data : form_data,
                    dataType : "json",
                    contentType: false,
                    cache: false,
                    processData:false
                }).done(function(res){ //retorana a mensagem "json" do servidor quando concluído
                    if(res.type == "error"){
                        $("#contact_results").html('<div class="alert alert-danger bounceIn animated" role="alert">   '+ res.text +"</div>");

                        setTimeout( function(){
                            $("#contact_results").html('<div class="alert alert-danger bounceOut animated" role="alert">');
                        }, 3000 );
                    }
                    
                    if(res.type == "done"){
                        $("#contact_results").html('<div class="alert alert-success bounceIn animated" role="alert">'+ res.text +"</div>");

                        setTimeout( function(){
                            $("#contact_results").html('<div class="alert alert-success bounceOut animated" role="alert">');
                        }, 3000 );
                    }
                });
            }
        });

        


