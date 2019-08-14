<?php

namespace Wink\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Pbmedia\LaravelFFMpeg\FFMpeg;


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
        $thumbnail = FFMpeg::fromDisk(config('wink.storage_disk'))
            ->open($videoPath)
            ->getFrameFromSeconds(1)
            ->export()
            ->toDisk(config('wink.video_storage_path'))
            ->save($video->hashName() . '_thumb.jpg');

        return response()->json([
            'url' => Storage::disk(config('wink.storage_disk'))->url($videoPath),
            'thumbnail' => Storage::disk(config('wink.storage_disk'))->url($thumbnail->getFile()),
            'mime' => $video->getMimeType(),
        ]);
    }
}
