<?php
$targetFolder = 'D:\01. Project\04. Website\pln\berkas/berkas/Osman_Mohammad_Basyir_OSM26/';
var_dump(is_dir($targetFolder));
$files = scandir($targetFolder);
print_r($files);
