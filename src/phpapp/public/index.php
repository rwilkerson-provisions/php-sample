<?php
// src/phpapp/public/index.php
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
               getenv('DB_HOST') ?: 'mysql',
               getenv('DB_PORT') ?: '3306',
               getenv('DB_NAME') ?: 'mysqldb');

$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: 'password';

try {
  $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
  echo "DB OK\n";
} catch (Throwable $t) {
  http_response_code(500);
  echo "DB ERROR: " . $t->getMessage() . "\n";
}

// S3 sample (LocalStack)
require __DIR__ . '/../vendor/autoload.php'; // if you add composer + aws/aws-sdk-php

use Aws\S3\S3Client;
$s3 = new S3Client([
  'version' => '2006-03-01',
  'region'  => 'us-east-1',
  'endpoint' => getenv('AWS_ENDPOINT') ?: 'http://localstack:4566',
  'use_path_style_endpoint' => true, // simpler with LocalStack
  'credentials' => ['key' => 'test', 'secret' => 'test'],
]);

// Secrets sample (LocalStack)
use Aws\SecretsManager\SecretsManagerClient;
$sm = new SecretsManagerClient([
  'version' => '2017-10-17',
  'region'  => 'us-east-1',
  'endpoint' => getenv('AWS_ENDPOINT') ?: 'http://localstack:4566',
  'credentials' => ['key' => 'test', 'secret' => 'test'],
]);

echo "PHP up\n";