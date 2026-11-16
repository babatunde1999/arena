<?php
$c = file_get_contents('public/index.php');
$lines = explode("\n", $c);
for ($i = 720; $i <= 800; $i++) {
    echo ($i+1) . ': ' . $lines[$i] . PHP_EOL;
}
?>
