<?php
// Require AWS
require 'vendor/autoload.php';

use Aws\S3\S3Client;

// Instantiate the S3 client using your credential profile
$clientParams = array();
$s3Client = S3Client::factory($clientParams);

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

// Destination
$destinationBucket = 'dpelusotestbucket';
$directoryToUpload = 'ExampleBucket/';
$checkDestinationBucket = checkBucketExists($s3Client, $destinationBucket);

if($checkDestinationBucket === true){
    // Check if directory exists
    if(!file_exists($directoryToUpload)) throw new \Exception('Directory "'.$directoryToUploadd.'" not found');
    // Get all files within that directory, removing . and .. directories
    $files = array_diff(scandir($directoryToUpload), array('..', '.'));
    print_r($files);
    // Upload files
    foreach($files as $file)
    {
        $absolutePath = realpath($directoryToUpload.$file);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $result = $s3Client->putObject(array(
            'Bucket'       => $destinationBucket,
            'Key'          => $file,
            'SourceFile'   => $absolutePath,
            'ContentType'  => finfo_file($finfo, $directoryToUpload.$file),
            'ACL'          => 'public-read',
            'StorageClass' => 'REDUCED_REDUNDANCY',
            'Metadata'     => array()
        ));

        echo $result['ObjectURL'].'<hr>';
    }
} else {
    throw new \Exception('Bucket "'.$destinationBucket.'" not found');
}


echo 'No errors?';