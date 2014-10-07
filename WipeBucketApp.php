<?php
// Require AWS
require 'vendor/autoload.php';

use Aws\S3\S3Client;

// Instantiate the S3 client using your credential profile
$clientParams = array();
$s3Client = S3Client::factory($clientParams);

// Force longer execution
ini_set('max_execution_time', 300);

// Verify bucket exists
function checkBucketExists($s3Client, $inputBucket)
{

    $listBuckets = $s3Client->listBuckets();

    //print_r($listBuckets);

    $exists = false;

    foreach($listBuckets['Buckets'] as $bucket){
        if($bucket['Name'] == $inputBucket){
            $exists = true;
            break;
        }
    }

    return $exists;
};

$inputBucket = 'dpelusotestbucket';

if(checkBucketExists($s3Client, $inputBucket)){
    $objects = $s3Client->getIterator('ListObjects', array('Bucket' => $inputBucket));

    echo 'Keys retrieved!<hr>';
    $counter = 0;
    foreach ($objects as $object) {

        $result = $s3Client->deleteObject(array(
            'Bucket' => $inputBucket,
            'Key'    => $object['Key']
        ));

        if($result['DeleteMarker'] == true){
            echo $object['Key'] . " Deleted<hr>";
        } else {
            //echo $object['Key'] . " could not be deleted!<hr>";
        }
        $counter++;

    }

    echo $counter.' objects deleted!';

    // Removing bucket
    $s3Client->deleteBucket(array('Bucket' => $inputBucket));

    echo 'Bucket removed';

} else {
    throw new \Exception('Bucket "'.$destinationBucket.'" not found');
}
