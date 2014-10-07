<?php

// Get NobelManager
require 'NobelManager.php';
$NobelManager = new NobelManager();

// Declare stars and nobel laureates
$nobelLaureates = array(
    array(
        'name' => 'Alice Munro',
        'resume' => 'alice.docx',
        'photo' => 'alice.jpg',
        'field' => 'Literature',
        'year_won' => '1996'
    ),
    array(
        'name' => 'Mo Yan',
        'resume' => 'mo.docx',
        'photo' => 'mo.jpg',
        'field' => 'Literature',
        'year_won' => '2014'
    )
);
$stars = array(
    array(
        'name' => 'Brad Pitt',
        'resume' => 'brad.docx',
        'photo' => 'brad.jpg',
        'most_pop_movie' => 'Fight Club'
    ),
    array(
        'name' => 'George Clooney',
        'resume' => 'george.docx',
        'photo' => 'george.jpg',
        'most_pop_movie' => 'Oceans Eleven'
    )
);

// Check that bucket exists
$nobelBucket = 'e90-problem-3-nobel';

$checkedBucket = $NobelManager->checkBucketExists($nobelBucket);

if($checkedBucket){
    // Upload to S3
    $nobelLaureates = $NobelManager->uploadNobels($nobelBucket, $nobelLaureates, 'nobels');
    $stars = $NobelManager->uploadNobels($nobelBucket, $stars, 'stars');
    // Add to SimpleDB
    $domain = 'nobels';
    $NobelManager->insertNobels($domain, $stars);
    $NobelManager->insertNobels($domain, $nobelLaureates);
}

var_dump($checkedBucket);



//$NobelManager->wipeBucket($nobelBucket, false);