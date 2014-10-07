<?php
// Require AWS
require 'vendor/autoload.php';

use Aws\S3\S3Client;

// Instantiate the S3 client using your credential profile
$clientParams = array();
$s3Client = S3Client::factory($clientParams);

// Get all buckets to test
$buckets = $s3Client->listBuckets();

// Dump all buckets to test
echo '<pre>';
print_r($buckets);
echo '</pre>';

echo 'No errors?';