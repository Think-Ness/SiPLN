<?php
$pdo=new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db','root',''); 
print_r(json_decode($pdo->query('SELECT data_json FROM mtb_capel_draft LIMIT 1')->fetchColumn(), true));
