<?php

function Logger($msg){
 
    $data = date("d-m-y");
    $hora = date("H:i:s");

    //Nome do arquivo:
    $arquivo = "log/log_$data.txt";
    chmod($arquivo, 0777);
    chown($arquivo, 'www'); 
    
    //Texto a ser impresso no log:
    $texto = "[$hora]> $msg \n";
    
    
    $manipular = fopen("$arquivo", "a+b");
    fwrite($manipular, $texto);
    fclose($manipular);
 
}

Logger('Carregando Webhook');

$data = json_decode(file_get_contents('php://input'), true);

Logger('Retorno' . $data);

// Responder requisição com um json contendo "status": "200":
header('Content-Type: application/json');
echo json_encode(array('status' => 200));


