<?php
$captcha = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 10);
$_SESSION['captcha'] = $captcha;
echo "<strong style='font-family:monospace; letter-spacing:3px;'>$captcha</strong>";
?>

