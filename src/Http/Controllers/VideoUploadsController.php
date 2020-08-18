<?php

namespace Wink\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Pbmedia\LaravelFFMpeg\FFMpegFacade as FFMpeg;


class VideoUploadsController
{

    /**
     * Upload a new video.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload()
    {
        $video = request()->video;
        $videoPath = $video->store(config('wink.video_storage_path'), [
                'disk' => config('wink.storage_disk'),
                'visibility' => 'public',
            ]
        );
        $thumbnailPath = config('wink.video_storage_path') . '/' . $video->hashName() . '_thumb.jpg';
        FFMpeg::fromDisk(config('wink.storage_disk'))
            ->open($videoPath)
            ->getFrameFromSeconds(1)
            ->export()
            ->toDisk(config('wink.storage_disk'))
            ->save($thumbnailPath);

        return response()->json([
            'url' => asset(Storage::disk(config('wink.storage_disk'))->url($videoPath)),
            'thumbnail' => asset(Storage::disk(config('wink.storage_disk'))->url($thumbnailPath)),
            'mime' => $video->getMimeType(),
        ]);
    }
}
