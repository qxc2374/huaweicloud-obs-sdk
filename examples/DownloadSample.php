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
 * This sample demonstrates how to download an object
 * from OBS in different ways using the OBS SDK for PHP.
 */
require __DIR__ . '/bootstrap.php';

use QianXiong\ObsClient;
use QianXiong\ObsException;






/*
 * Constructs a obs client instance with your account for accessing OBS
 */

try
{
	/*
	 * Create bucket
	 */
	printf("Create a new bucket for demo\n\n");
	$obsClient -> createBucket(['Bucket' => $bucketName]);
	
	/*
	 * Upload an object to your bucket
	 */
	printf("Uploading a new object to QianXiong\n\n");
	$content = "abcdefghijklmnopqrstuvwxyz\n\t0123456789011234567890\n";
	$obsClient -> putObject(['Bucket' => $bucketName, 'Key' => $objectKey, 'Body' => $content]);
	
	/*
	 * Download the object as an inputstream and display it directly
	 */
	printf("Downloading an object\n");
	$resp = $obsClient -> getObject(['Bucket' => $bucketName, 'Key' => $objectKey]);
	printf("\t%s\n\n", $resp['Body']);
	
	
	/*
	 * Download the object to a file
	 */
	printf("Downloading an object to local file\n");
	$resp = $obsClient -> getObject(['Bucket' => $bucketName, 'Key' => $objectKey, 'SaveAsFile' => $localFilePath]);
	printf("\tSaveAsFile:%s\n\n", $resp['SaveAsFile']);
	
	
	printf("Deleting object %s \n\n", $objectKey);
	$obsClient -> deleteObject(['Bucket' => $bucketName, 'Key' => $objectKey]);
	
	
} catch ( ObsException $e ) {
	echo 'Response Code:' . $e->getStatusCode () . PHP_EOL;
	echo 'Error Message:' . $e->getExceptionMessage () . PHP_EOL;
	echo 'Error Code:' . $e->getExceptionCode () . PHP_EOL;
	echo 'Request ID:' . $e->getRequestId () . PHP_EOL;
	echo 'Exception Type:' . $e->getExceptionType () . PHP_EOL;
} finally{
	$obsClient->close ();
}