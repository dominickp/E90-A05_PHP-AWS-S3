<?php
// Require AWS
require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\SimpleDb\SimpleDbClient;

// Instantiate the S3 client using your credential profile
$clientParams = array();
$s3Client = S3Client::factory($clientParams);

// Force longer execution
ini_set('max_execution_time', 300);


class NobelManager{

    protected $s3Client;

    protected $simpleDbClient;

    public function __construct()
    {
        $this->s3Client = $this->getS3Client();
        $this->simpleDbClient = $this->getSimpleDbClient();
    }

    // Wipe a bucket
    public function wipeBucket($bucketName, $deleteBucket)
    {
        $this->checkBucketExists($bucketName);

        $objects = $this->s3Client->getIterator('ListObjects', array('Bucket' => $bucketName));

        echo 'Keys retrieved!<hr>';
        $counter = 0;
        foreach ($objects as $object) {

            $result = $this->s3Client->deleteObject(array(
                'Bucket' => $bucketName,
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
        if($deleteBucket == true){
            $this->s3Client->deleteBucket(array('Bucket' => $bucketName));

            echo 'Bucket removed';
        }

        return true;
    }

    // Verify bucket exists
    public function checkBucketExists($inputBucket)
    {

        $listBuckets = $this->s3Client->listBuckets();

        //print_r($listBuckets);

        $exists = false;

        foreach($listBuckets['Buckets'] as $bucket){
            if($bucket['Name'] == $inputBucket){
                $exists = true;
                break;
            }
        }

        return $exists;
    }

    // Instantiate the S3 client using your credential profile
    public function getSimpleDbClient()
    {
        $clientParams = array('region'  => 'us-east-1');
        $simpleDbClient = SimpleDbClient::factory($clientParams);
        return $simpleDbClient;
    }

    protected function hyphenateSpaces($input)
    {
        $output = preg_replace('#[ -]+#', '-', $input);
        return $output;
    }

    protected function buildAttributes($person)
    {
        $attributes = array();

        foreach($person as $attribute => $value)
        {
            if($attribute == 'name') continue;

            $attribute = array('Name' => $attribute, 'Value' => $value, 'Replace' => true);
            array_push($attributes, $attribute);
        }
        return $attributes;
    }

    // Insert nobels into SimpleDB
    public function insertNobels($domain, $nobelArray)
    {
        $client = $this->simpleDbClient;

        // Make domain
        $client->createDomain(array('DomainName' => $domain));


        // Insert rows
        foreach($nobelArray as $person)
        {
            $client->putAttributes(array(
                'DomainName' => $domain,
                'ItemName'   => $this->hyphenateSpaces($person['name']),
                'Attributes' => $this->buildAttributes($person)
            ));
        }
    }

    // Instantiate the S3 client using your credential profile
    public function getS3Client()
    {
        $clientParams = array();
        $s3Client = S3Client::factory($clientParams);
        return $s3Client;
    }

    // Upload an array of nobels to S3
    public function uploadNobels($bucket, $nobelArray, $subDirectoryName)
    {
        foreach($nobelArray as &$person)
        {
            // Get file info
            $directoryToUpload = 'NobelUpload/';
            $absolutePhotoPath = realpath($directoryToUpload.$person['photo']);
            $absoluteResumePath = realpath($directoryToUpload.$person['resume']);
            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            // Upload photo
            $result = $this->s3Client->putObject(array(
                'Bucket' => $bucket,
                'Key'    => $subDirectoryName.'/images/'.$person['photo'],
                'SourceFile' => $absolutePhotoPath,
                'ACL'          => 'public-read'
            ));

            $person['image_path'] = $result['ObjectURL'];

            // Upload photo
            $result = $this->s3Client->putObject(array(
                'Bucket' => $bucket,
                'Key'    => $subDirectoryName.'/resumes/'.$person['resume'],
                'SourceFile' => $absoluteResumePath,
                'ACL'          => 'public-read'
            ));

            $person['resume_path'] = $result['ObjectURL'];
        }

        return $nobelArray;

    }

}

