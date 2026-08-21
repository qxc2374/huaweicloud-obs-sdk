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
 * This sample demonstrates how to upload multiparts to OBS
 * using the OBS SDK for PHP.
 */
require __DIR__ . '/bootstrap.php';

use QianXiong\ObsClient;
use QianXiong\ObsException;






/*
 * Constructs a obs client instance with your account for accessing OBS
 */

try
{
	printf("Create a new bucket for demo\n\n");
	$obsClient -> createBucket(['Bucket' => $bucketName]);
	
	/*
	 * Step 1: initiate multipart upload
	 */
	printf("Step 1: initiate multipart upload\n\n");
	
	$resp = $obsClient -> initiateMultipartUpload(['Bucket'=>$bucketName,
			'Key'=>$objectKey]);
	
	$uploadId = $resp['UploadId'];
	/*
	 * Step 2: upload a part
	 */
	printf("Step 2: upload a part\n\n");
	$resp = $obsClient->uploadPart([
			'Bucket'=>$bucketName,
			'Key' => $objectKey,
			'UploadId'=>$uploadId,
			'PartNumber'=>1,
			'Body' => 'Hello OBS'
	]);
	
	$etag = $resp['ETag'];
	
	/*
	 * Step 3: complete multipart upload
	 */
	printf("Step 3: complete multipart upload\n\n");
	$obsClient->completeMultipartUpload([
			'Bucket'=>$bucketName,
			'Key'=>$objectKey,
			'UploadId'=>$uploadId,
			'Parts'=>[
					['PartNumber'=>1,'ETag'=>$etag]
			],
	]);
	
	
} catch ( ObsException $e ) {
	echo 'Response Code:' . $e->getStatusCode () . PHP_EOL;
	echo 'Error Message:' . $e->getExceptionMessage () . PHP_EOL;
	echo 'Error Code:' . $e->getExceptionCode () . PHP_EOL;
	echo 'Request ID:' . $e->getRequestId () . PHP_EOL;
	echo 'Exception Type:' . $e->getExceptionType () . PHP_EOL;
} finally{
	$obsClient->close ();
}