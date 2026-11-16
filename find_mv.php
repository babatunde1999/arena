<?php
$c = file_get_contents('public/index.php');
$lines = explode("\n", $c);
foreach ($lines as $i => $l) {
    if (stripos($l, 'Market') !== false && stripos($l, 'Vendor') !== false) {
        echo ($i+1) . ': ' . $l . PHP_EOL;
        break;
    }
}
if (strpos($c, 'carousel-card') !== false) {
    echo "has carousel\n";
} else echo "no\n";
?>
