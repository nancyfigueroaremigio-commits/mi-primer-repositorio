<?php
session_start();
$a = rand(1,9);
$b = rand(1,9);
$_SESSION['captcha'] = $a + $b;
echo "¿Cuánto es $a + $b ?";
?>