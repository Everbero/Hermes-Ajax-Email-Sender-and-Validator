<?php
$recipient_email    = "tresxweb@gmail.com"; //recipiente
$from_email         = "hermes@sstelematica.com"; //remetente.


if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
	die('Sorry Request must be Ajax POST'); //sai do código
}

if($_POST){
    
    $sender_name 	= filter_var($_POST["name"], FILTER_SANITIZE_STRING); //capture sender name
    $sender_email 	= filter_var($_POST["email"], FILTER_SANITIZE_STRING); //capture sender email
    // $country_code   = filter_var($_POST["ddd"], FILTER_SANITIZE_NUMBER_INT);
    $phone_number   = filter_var($_POST["telefone"], FILTER_SANITIZE_NUMBER_INT);
    $subject        = "Nova mensagem em \"sstelematica.com.br\" de " . $sender_name;

    if( isset($_POST['message']) ){
         $message       = filter_var($_POST["message"], FILTER_SANITIZE_STRING); //capture message
    }else{
        $message = "Esta pessoa solicitou uma apresentação";
    }

    

    $attachments = $_FILES['file_attach'];
    
	
    //php validation
    if(strlen($sender_name)<4){ // If length is less than 4 it will output JSON error.
        print json_encode(array('type'=>'error', 'text' => 'Seu nome é muito curto ou está vazio!'));
        exit;
    }
    if(!filter_var($sender_email, FILTER_VALIDATE_EMAIL)){ //email validation
        print json_encode(array('type'=>'error', 'text' => 'Por favor digite um endereço de email válido!'));
        exit;
    }
    // if(!filter_var($country_code, FILTER_VALIDATE_INT)){ //check for valid numbers in country code field
    //     $output = json_encode(array('type'=>'error', 'text' => 'Enter only digits in country code'));
    //     exit;
    // }
    if(!filter_var($phone_number, FILTER_SANITIZE_NUMBER_FLOAT)){ //check for valid numbers in phone number field
        print json_encode(array('type'=>'error', 'text' => 'Digite apenas números no campo telefone.'));
        exit;
    }
    if(strlen($subject)<3){ //check emtpy subject
        print json_encode(array('type'=>'error', 'text' => 'Assunto é um campo obrigatório.'));
        exit;
    }
    if(strlen($message)<3){ //check emtpy message
        print json_encode(array('type'=>'error', 'text' => 'Sua mensagem é muito curto, por favor digite mais alguma coisa.'));
        exit;
    }

    
    $file_count = count($attachments['name']); //count total files attached
    $boundary = md5("3xweb.org"); 
            
    if($file_count > 0){ //if attachment exists
        //header
        $headers = "MIME-Version: 1.0\r\n"; 
        $headers .= "From:".$from_email."\r\n"; 
        $headers .= "Reply-To: ".$sender_email."" . "\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary = $boundary\r\n\r\n"; 
        
        //message text
        $body = "--$boundary\r\n";
        $body .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n"; 
        $body .= chunk_split(base64_encode("Nome: " . $sender_name . "\nTelefone: " . $phone_number . "\nMensagem: " . $message)); 

        //attachments
        for ($x = 0; $x < $file_count; $x++){       
            if(!empty($attachments['name'][$x])){
                
                if($attachments['error'][$x]>0) //exit script and output error if we encounter any
                {
                    $mymsg = array( 
                    1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini", 
                    2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form", 
                    3=>"The uploaded file was only partially uploaded", 
                    4=>"No file was uploaded", 
                    6=>"Missing a temporary folder" ); 
                    print  json_encode( array('type'=>'error',$mymsg[$attachments['error'][$x]]) ); 
					exit;
                }
                
                //get file info
                $file_name = $attachments['name'][$x];
                $file_size = $attachments['size'][$x];
                $file_type = $attachments['type'][$x];
                
                //read file 
                $handle = fopen($attachments['tmp_name'][$x], "r");
                $content = fread($handle, $file_size);
                fclose($handle);
                $encoded_content = chunk_split(base64_encode($content)); //split into smaller chunks (RFC 2045)
                
                $body .= "--$boundary\r\n";
                $body .="Content-Type: $file_type; name=".$file_name."\r\n";
                $body .="Content-Disposition: attachment; filename=".$file_name."\r\n";
                $body .="Content-Transfer-Encoding: base64\r\n";
                $body .="X-Attachment-Id: ".rand(1000,99999)."\r\n\r\n"; 
                $body .= $encoded_content; 
            }
        }

    }else{ //send plain email otherwise
       $headers = "From:".$from_email."\r\n".
        "Reply-To: ".$sender_email. "\n" .
        "X-Mailer: PHP/" . phpversion();
        $body = "Nome: " . $sender_name . "\n Telefone: " . $phone_number . "\n Mensagem: " . $message;
    }
        
    $sentMail = mail($recipient_email, $subject, $body, $headers);
    if($sentMail) //output success or failure messages
    {       
        print json_encode(array('type'=>'done', 'text' => 'Sua mensagem foi recebida, obrigado.'));
		exit;
    }else{
        print json_encode(array('type'=>'error', 'text' => 'Could not send mail! Please check your PHP mail configuration.'));  
		exit;
    }
}
?>