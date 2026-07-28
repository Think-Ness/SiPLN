<?php
$headers = get_headers('https://docs.google.com/spreadsheets/d/11hrUFG-sSfEuuKyOdJFiHXTEbPNpqiVXWTvRQahNdIU/export?format=csv');
print_r($headers);
