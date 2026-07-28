<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=si_foreign_db', 'root', '');
$cases = $pdo->query("SELECT id FROM jobdesk_cases LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$steps = $pdo->query("SELECT * FROM jobdesk_case_steps LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
echo "Cases:\n"; print_r($cases);
echo "Steps:\n"; print_r($steps);
