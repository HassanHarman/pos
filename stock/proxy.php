<?php
if (!isset($target) || $target === '') {
    $target = basename($_SERVER['SCRIPT_NAME']);
}

$portal = isset($portal) ? $portal : 'stock';
$root = __DIR__ . '/../main';
chdir($root);
require $root . '/' . $target;
