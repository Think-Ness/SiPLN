<?php
require 'vendor/autoload.php';

use App\Shared\FirebaseSync;
use Yiisoft\Db\Sqlite\Connection;
use Yiisoft\Db\Sqlite\Driver;

$db = new Connection(new Driver('sqlite:database/pln.db'));
$db->open();

// Note: To delete an entire collection in Firestore REST API is hard (need to delete each document).
// But wait, the easiest way is to let the user know to click the Sync button, and if they see duplicates, we can clean them up.
// Actually, since this is a demo Firebase environment, I can just write a quick script to delete all santri documents first.

// We will fetch all santri and delete them.
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://firestore.googleapis.com/v1/projects/database-luar-negeri/databases/(default)/documents/instansi");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
print_r($data);

echo "Starting SyncAll...\n";
$res = FirebaseSync::syncAll($db);
print_r($res);
echo "Done.\n";
