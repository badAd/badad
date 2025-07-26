<?php

if (!isset($_GET['f'])) {
  exit();
} else {
  $filename = $_GET['f'];
}

$file = file_get_contents($filename);

echo $file;

?>
