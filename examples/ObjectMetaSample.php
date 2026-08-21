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
 * This sample demonstrates how to set/get self-defined metadata for object
 * on OBS using the OBS SDK for PHP.
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
	 * Create object
	 */
	$content = 'Hello OBS';
	
	/*
	 * Setting self-defined metadata
	 */
	
	$metadata = [];
	
	$metadata['meta1'] = 'value1';
	$metadata['meta2'] = 'value2';
	$obsClient -> putObject(['Bucket' => $bucketName, 'Key' => $objectKey, 'Body' => $content, 'Metadata' => $metadata]);
	
	printf("Create object %s successfully!\n\n", $objectKey);
	
	/*
	 * Get object metadata
	 */
	$resp = $obsClient -> getObjectMetadata(['Bucket' => $bucketName, 'Key' => $objectKey]);
	printf("Getting object metadata:\n");
	foreach ($resp['Metadata'] as $key => $value){
		printf("\t%s=%s\n", $key, $value);
	}
	
	/*
	 * Delete object
	 */
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