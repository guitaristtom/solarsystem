<?php
$loader = require_once __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../app/Application.php';

$app['solarsystem.import']->run($_POST);