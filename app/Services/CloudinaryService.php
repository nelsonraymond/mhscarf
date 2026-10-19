<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class CloudinaryService
{
    private Cloudinary $cloudinary;

    public function __construct()
    {
        $url = config('services.cloudinary.url');

        if (! $url) {
            throw new RuntimeException(
                'CLOUDINARY_URL belum dikonfigurasi.'
            );
        }

        $this->cloudinary = new Cloudinary($url);
    }

    public function upload(
        UploadedFile $file,
        string $folder
    ): array {
        $result = $this->cloudinary
            ->uploadApi()
            ->upload($file->getRealPath(), [
                'folder' => $folder,
                'resource_type' => 'image',
            ]);

        return [
            'url' => $result['secure_url'],
            'public_id' => $result['public_id'],
        ];
    }

    public function delete(?string $publicId): void
    {
        if (! $publicId) {
            return;
        }

        $this->cloudinary
            ->uploadApi()
            ->destroy($publicId, [
                'resource_type' => 'image',
            ]);
    }
}