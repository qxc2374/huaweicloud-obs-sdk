<?php

/**
 * Copyright 2019 Huawei Technologies Co.,Ltd.
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use
 * this file except in compliance with the License.  You may obtain a copy of the
 * License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed
 * under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR
 * CONDITIONS OF ANY KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations under the License.
 *
 */

/**
 * This sample demonstrates how to post object under specified bucket from
 * OBS using the OBS SDK for PHP.
 */
require __DIR__ . '/bootstrap.php';

use QianXiong\ObsClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;






/*
 * Constructs a obs client instance with your account for accessing OBS
 */

/*
 * Create bucket
 */
printf("Create a new bucket for demo\n\n");
$obsClient -> createBucket(['Bucket' => $bucketName]);


/*
 * Create sample file
 */

createSampleFile($sampleFilePath);

/*
 * Claim a post object request
 */
$formParams = [];
if (strcasecmp($signature, 'obs') === 0) {
    $formParams['x-obs-acl'] = ObsClient::AclPublicRead;
} else {
    $formParams['acl'] = ObsClient::AclPublicRead;
}
$formParams['content-type'] = 'text/plain';

$res = $obsClient -> createPostSignature(['Bucket' => $bucketName, 'Key' => $objectKey, 'Expires' => 3600, 'FormParams' => $formParams]);

$formParams['key'] = $objectKey;
$formParams['policy'] = $res['Policy'];

if (strcasecmp($signature, 'obs') === 0) {
    $formParams['Accesskeyid'] = $accessKey;
} else {
    $formParams['AWSAccesskeyid'] = $accessKey;
}

$formParams['signature'] = $res['Signature'];


printf("Creating object in browser-based post way\n\n");
$boundary = '9431149156168';

$buffers = [];
$contentLength = 0;

/*
 * Construct form data
 */
$buffer = [];
$first = true;
foreach ($formParams as $key => $val){
    if(!$first){
        $buffer[] = "\r\n";
    }else{
        $first = false;
    }
    
    $buffer[] = "--";
    $buffer[] = $boundary;
    $buffer[] = "\r\n";
    $buffer[] = "Content-Disposition: form-data; name=\"";
    $buffer[] = strval($key);
    $buffer[] = "\"\r\n\r\n";
    $buffer[] = strval($val);
}

$buffer = implode('', $buffer);
$contentLength += strlen($buffer);
$buffers[] = $buffer;

/*
 * Construct file description
 */
$buffer = [];

$buffer[] = "\r\n";
$buffer[] = "--";
$buffer[] = $boundary;
$buffer[] = "\r\n";
$buffer[] = "Content-Disposition: form-data; name=\"file\"; filename=\"";
$buffer[] = "myfile";
$buffer[] = "\"\r\n";
$buffer[] = "Content-Type: text/plain";
$buffer[] = "\r\n\r\n";

$buffer = implode('', $buffer);
$contentLength += strlen($buffer);
$buffers[] = $buffer;

/*
 * Construct file data
 */
$buffer = [];

$fp = fopen($sampleFilePath, 'r');
if($fp){
    while(!feof($fp)){
        $buffer[] = fgetc($fp);
    }
    fclose($fp);
}

$buffer = implode('', $buffer);
$contentLength += strlen($buffer);
$buffers[] = $buffer;

/*
 * Contruct end data
 */
$buffer = [];
$buffer[] = "\r\n--";
$buffer[] = $boundary;
$buffer[] = "--\r\n";

$buffer = implode('', $buffer);
$contentLength += strlen($buffer);
$buffers[] = $buffer;


$httpClient = new Client(['verify' => $sslVerify]);
$host = parse_url($endpoint)['host'];
$host = $bucketName . '.' . $host;
$url = 'https://' . $host . ':443';
$headers = ['Content-Length' => strval($contentLength), 'Content-Type' => 'multipart/form-data; boundary=' . $boundary];

try{
    $response = $httpClient -> request('POST', $url, ['body' => implode('', $buffers), 'headers'=> $headers]);
    
    printf('Post object successfully!');
    $response -> getBody()-> close();
}catch (ClientException $ex){
    printf('Exception message:%s', $ex ->getMessage());
}


if(file_exists($sampleFilePath)){
    unlink($sampleFilePath);
}

function createSampleFile($filePath)
{
    if(file_exists($filePath)){
        return;
    }
    $filePath = iconv('UTF-8', 'GBK', $filePath);
    if(is_string($filePath) && $filePath !== '')
    {
        $fp = null;
        $dir = dirname($filePath);
        try{
            if(!is_dir($dir))
            {
                mkdir($dir,0755,true);
            }
            
            if(($fp = fopen($filePath, 'w+')))
            {
                fwrite($fp, uniqid() . "\n");
                fwrite($fp, uniqid() . "\n");
            }
        }finally{
            if($fp){
                fclose($fp);
            }
        }
    }
}
