<?php
$default_branch = 'mk';
$host = $_SERVER['HTTP_HOST'];
header("Location: http://$host" . DIRECTORY_SEPARATOR . $default_branch);
exit;

