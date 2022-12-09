<?php

namespace App\Lib\UploadFiles;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SpaceApi
{
    protected $S3Client;

    public function __construct()
    {
        $this->S3Client = new S3Client([
            'region' => $this->getRegion(),
            'version' => 'latest',
            'endpoint' => $this->getEndpoint(),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);
    }

    protected function isAllow($mime_type)
    {
        $allow_types = [
            '/^image\/.*/',
            '/^audio\/.*/',
            '/^video\/.*/',
            '/^application\/msword$/',
            '/^application\/vnd.openxmlformats-officedocument.*/',
            '/^application\/x-xz$/',
            '/^application\/zip$/',
            '/^application\/pdf$/',
            '/^application\/x-rar-compressed$/',
            '/^text\/plain$/',
        ];
        foreach ($allow_types as $type) {
            if (preg_match($type, $mime_type)) {
                return true;
            }
        }
        return false;
    }

    public function deletes(array $files)
    {
        $names = [];
        foreach ($files as $filename) {
            if (strpos($filename, 'http') === 0) {
                $names[] = trim(parse_url($filename, PHP_URL_PATH), '/');
            };
        }
        if (count($names) > 0) {
            try {
                $result = $this->S3Client->deleteObjects([
                    'Bucket' => $this->getBucket(),
                    'Delete' => [
                        'Objects' => array_map(function ($name) {
                            return [
                                'Key' => $name,
                            ];
                        }, $names),
                    ],
                ]);
                $deleted = $result->get('Deleted');
                return is_array($deleted) && count($deleted) > 0;
            } catch (Exception $e) {
            }
        }
        return false;
    }

    public function delete($filename)
    {
        $name = "";
        if (strpos($filename, 'http') === 0) {
            $name = trim(parse_url($filename, PHP_URL_PATH), '/');
        };
        if ($name) {
            try {
                $result = $this->S3Client->deleteObject([
                    'Bucket' => $this->getBucket(),
                    'Key' => $name
                ]);
            } catch (Exception $e) {
                return false;
            }
            try {
                $result = $this->S3Client->getObject([
                    'Bucket' => $this->getBucket(),
                    'Key'    => $name
                ]);
                return false;
            } catch (S3Exception $e) {
                return true;
            }
        }
        return false;
    }

    public function upload(UploadedFile $file, $folder = "")
    {
        $mime_type = $file->getClientMimeType();
        if ($this->isAllow($mime_type)) {
            $client_file_name = $file->getClientOriginalName();
            $client_name = $client_file_name;
            $key = ($folder ? $folder : $this->getFolder()) . '/' . $client_name;
            try {
                return $this->S3Client->putObject([
                    'Bucket' => $this->getBucket(),
                    'Key' => $key,
                    'Body' => file_get_contents($file->getRealPath()),
                    'ACL' => 'public-read',
                    'ContentType' => $mime_type,
                ])->get('ObjectURL');
            } catch (Exception $e) {
                return '';
            }
        }
        return '';
    }

    public function uploads(array $files, string $path = '/')
    {
        $result = [];
        foreach ($files as $key => $file) {
            if ($file instanceof UploadedFile) {
                $path .= '/'. $file->getClientOriginalName();
                $url = $this->upload($file, $path);
                $result[$key] = $url;
            } else {
                $result[$key] = '';
            }
        }
        return $result;
    }

    public function uploadBase64($file_content_base64, $file_name, $file_type, $key="")
    {
        if (!$key) {
            $key = $this->getFolder();
        }
        $key = $key . "/" . $file_name;
        try {
            return $this->S3Client->putObject([
                'Bucket' => $this->getBucket(),
                'Key' => $key,
                'Body' => $file_content_base64,
                'ACL' => 'public-read',
                'ContentType' => $file_type,
            ])->get('ObjectURL');
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function copyObject($sourceBucket, $sourceKeyname, $newKeyname = "", $targetBucket = "")
    {
        try {
            return $this->S3Client->copyObject([
                'CopySource' => "{$sourceBucket}/{$sourceKeyname}",
                'Bucket'     => $targetBucket ? $targetBucket : $this->getBucket(),
                'Key'        => $newKeyname ? $newKeyname : $sourceKeyname,
                'ACL' => 'public-read',
            ])->get('ObjectURL');
        } catch (Exception $e) {
            return false;
        }
    }

    protected function getEndpoint()
    {
        return env('AWS_ENDPOINT');
    }

    protected function getRegion()
    {
        return env('AWS_REGION');
    }

    protected function getFolder()
    {
        return env('AWS_FOLDER');
    }

    protected function getBucket()
    {
        return env('AWS_BUCKET');
    }
}