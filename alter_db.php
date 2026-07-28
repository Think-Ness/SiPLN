<?php $db = new PDO("mysql:host=127.0.0.1;dbname=si_foreign_db", "root", ""); $db->exec("ALTER TABLE jobdesk_master_process ADD COLUMN instansi_id INT NULL"); echo "Done\n";
