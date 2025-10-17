<?php
// src/phpapp/public/index.php

// Start output buffering to capture all status messages
ob_start();

$results = [
    'database' => [],
    'aws_s3' => [],
    'aws_secrets' => [],
    'system' => []
];

// Database Connection Test
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
               getenv('DB_HOST') ?: 'mysql',
               getenv('DB_PORT') ?: '3306',
               getenv('DB_NAME') ?: 'mysqldb');

$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: 'password';

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $results['database'][] = ['status' => 'success', 'message' => 'Database connection successful'];
} catch (Throwable $t) {
    http_response_code(500);
    $results['database'][] = ['status' => 'error', 'message' => 'Database connection failed: ' . $t->getMessage()];
}

// AWS SDK Setup
require __DIR__ . '/../vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\SecretsManager\SecretsManagerClient;

// S3 Client Test
try {
    $s3 = new S3Client([
        'version' => '2006-03-01',
        'region'  => 'us-east-1',
        'endpoint' => getenv('AWS_ENDPOINT') ?: 'http://localstack:4566',
        'use_path_style_endpoint' => true,
        'credentials' => ['key' => 'test', 'secret' => 'test'],
    ]);
    
    $results['aws_s3'][] = ['status' => 'success', 'message' => 'S3 Client initialized successfully'];
    
    // Test S3Client - check for 'storage' bucket and create if it doesn't exist
    $bucketName = 'storage';
    
    // Check if bucket exists
    $bucketExists = $s3->doesBucketExist($bucketName);
    
    if ($bucketExists) {
        $results['aws_s3'][] = ['status' => 'info', 'message' => "S3 Bucket '$bucketName' already exists"];
    } else {
        $results['aws_s3'][] = ['status' => 'warning', 'message' => "S3 Bucket '$bucketName' does not exist, creating..."];
        
        // Create the bucket
        $result = $s3->createBucket([
            'Bucket' => $bucketName,
        ]);
        
        // Wait for bucket to be created
        $s3->waitUntil('BucketExists', [
            'Bucket' => $bucketName,
        ]);
        
        $results['aws_s3'][] = ['status' => 'success', 'message' => "S3 Bucket '$bucketName' created successfully"];
    }
    
    $results['aws_s3'][] = ['status' => 'success', 'message' => 'S3 test completed successfully'];
} catch (Throwable $t) {
    $results['aws_s3'][] = ['status' => 'error', 'message' => 'S3 Error: ' . $t->getMessage()];
}

// Secrets Manager Test
try {
    $sm = new SecretsManagerClient([
        'version' => '2017-10-17',
        'region'  => 'us-east-1',
        'endpoint' => getenv('AWS_ENDPOINT') ?: 'http://localstack:4566',
        'credentials' => ['key' => 'test', 'secret' => 'test'],
    ]);
    
    $results['aws_secrets'][] = ['status' => 'success', 'message' => 'Secrets Manager Client initialized successfully'];
} catch (Throwable $t) {
    $results['aws_secrets'][] = ['status' => 'error', 'message' => 'Secrets Manager Error: ' . $t->getMessage()];
}

// System Status
$results['system'][] = ['status' => 'success', 'message' => 'PHP Application is running'];
$results['system'][] = ['status' => 'info', 'message' => 'PHP Version: ' . phpversion()];
$results['system'][] = ['status' => 'info', 'message' => 'Server Time: ' . date('Y-m-d H:i:s T')];

// Clear output buffer
ob_end_clean();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Application Status</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Healthworks PHP Application</h1>
            <p>System Status Dashboard</p>
        </div>
        
        <div class="content">
            <div class="section database">
                <h2>
                    <span class="section-icon">DB</span>
                    Database Connection
                </h2>
                <ul class="status-list">
                    <?php foreach ($results['database'] as $item): ?>
                        <li class="status-item status-<?= $item['status'] ?>">
                            <span class="status-badge"></span>
                            <?= htmlspecialchars($item['message']) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="section aws-s3">
                <h2>
                    <span class="section-icon">S3</span>
                    AWS S3 Storage
                </h2>
                <ul class="status-list">
                    <?php foreach ($results['aws_s3'] as $item): ?>
                        <li class="status-item status-<?= $item['status'] ?>">
                            <span class="status-badge"></span>
                            <?= htmlspecialchars($item['message']) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="section aws-secrets">
                <h2>
                    <span class="section-icon">SM</span>
                    AWS Secrets Manager
                </h2>
                <ul class="status-list">
                    <?php foreach ($results['aws_secrets'] as $item): ?>
                        <li class="status-item status-<?= $item['status'] ?>">
                            <span class="status-badge"></span>
                            <?= htmlspecialchars($item['message']) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="section system">
                <h2>
                    <span class="section-icon">SYS</span>
                    System Information
                </h2>
                <ul class="status-list">
                    <?php foreach ($results['system'] as $item): ?>
                        <li class="status-item status-<?= $item['status'] ?>">
                            <span class="status-badge"></span>
                            <?= htmlspecialchars($item['message']) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; 2025 Healthworks PHP Application. Last updated: <?= date('Y-m-d H:i:s T') ?></p>
        </div>
    </div>
</body>
</html>