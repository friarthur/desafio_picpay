<?php 
$host = 'localhost';
$user = 'root';
$password = 'admin';
$dbse = 'desafio_pic';

$conn = new mysqli($host, $user, $password, $dbse);

if($conn->connect_error == 0){

}else{
    echo 'Não funcionou a conexão';
    return;
}
?>