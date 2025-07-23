<?php
ob_start();
require_once 'model/Database.php';
$output1 = ob_get_contents();
ob_end_clean();

ob_start();
require_once 'model/Article.php';
$output2 = ob_get_contents();
ob_end_clean();

echo "Database.php output length: " . strlen($output1) . "\n";
echo "Article.php output length: " . strlen($output2) . "\n";

if (strlen($output1) > 0) {
    echo "Database.php output: " . var_export($output1, true) . "\n";
}
if (strlen($output2) > 0) {
    echo "Article.php output: " . var_export($output2, true) . "\n";
}
