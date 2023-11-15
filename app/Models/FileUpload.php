<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Common\ServiceExceptionFactory;

class FileUpload extends Model
{
    public static function uploadToAzureBlobStorage($file)
    {
        $connectionString = 'DefaultEndpointsProtocol=https;AccountName=testdatafiles;AccountKey=PQAl2b/n5+7tE+QomCei+CoObFhKAixB/Y1dBQdtDa2KmjHeQT/7g+PkEAc9rlD+ds2JqOaRBfPl+ASt1xaH+A==;EndpointSuffix=core.windows.net';

        $containerName = 'fortestdata';

        $connectionString = env('AZURE_STORAGE_CONNECTION_STRING');
        $containerName = env('AZURE_STORAGE_CONTAINER_NAME');

        try {
            $blobRestProxy = BlobRestProxy::createBlobService($connectionString);

            $blobName = uniqid() . '-' . $file->getClientOriginalName();

            $blobRestProxy->createBlockBlob($containerName, $blobName, fopen($file->getRealPath(), 'r'));

            // Construct the URL for the uploaded file
            $url = sprintf('%s/%s/%s', env('AZURE_STORAGE_URL'), $containerName, $blobName);

            // Save the file information to the database using Eloquent
            $uploadedFile = new FileUpload();
            $uploadedFile->filename = $blobName;
            $uploadedFile->url = $url;
            $uploadedFile->save();

            return $url; // Return the URL immediately after upload
        } catch (ServiceException $e) {
            // Log the Azure Storage exception for debugging
            \Log::error("Azure Storage Exception: " . $e->getMessage());
            return null;
        }
    }
}

