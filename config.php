<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 't1');
define('DB_PASSWORD', 't1');
define('DB_NAME', 't1');

// Дерекқормен байланыс орнату
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Байланысты тексеру
if($conn === false){
    die("ҚАТЕ: Дерекқормен байланысу мүмкін емес. " . mysqli_connect_error());
}

// UTF-8 кодировкасын орнату
mysqli_set_charset($conn, "utf8");
?> 