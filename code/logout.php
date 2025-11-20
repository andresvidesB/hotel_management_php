<?php
require __DIR__ . '/vendor/autoload.php';
use Src\Shared\Infrastructure\Services\AuthService;

AuthService::logout();
header('Location: login.php');
exit;