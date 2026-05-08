<?php

namespace App\Services;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;

class PathGeneratorService extends DefaultPathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media).'/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media).'/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media).'/responsive/';
    }

    protected function getBasePath(Media $media): string
    {
        $prefix = config('media-library.prefix', '');
        $model = md5($media->model_type.$media->model_id);

        if ($prefix !== '') {
            return $prefix.'/'.$model;
        }

        return $model;
    }
}
