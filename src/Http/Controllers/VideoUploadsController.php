<?php

namespace Wink\Http\Controllers;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;


class VideoUploadsController
{
    private $FFMpeg;
    private $FFProbe;

    public function __construct(FFMpeg $FFMpeg, FFProbe $FFProbe)
    {
        $this->FFMpeg = $FFMpeg;
        $this->FFProbe = $FFProbe;
    }

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
        $thumbnail = $this->getThumbnail($video, 1);
        $thumbnailFile = $thumbnail->move(config('wink.video_storage_path'),  $video->hashName() . '.jpg');

        return response()->json([
            'url' => Storage::disk(config('wink.storage_disk'))->url($videoPath),
            'thumbnail' => Storage::disk(config('wink.storage_disk'))->url($thumbnailFile),
            'mime' => $video->getMimeType(),
        ]);
    }

    public function getThumbnail(UploadedFile $file, int $keySecond): File
    {
        $path = $file->path();

        $duration = max(1, (int)$this->FFProbe->format($path)->get('duration'));
        $keySecond = min($keySecond, $duration);
        $timeCode = TimeCode::fromSeconds($keySecond);

        /** @var \FFMpeg\Media\Video $video */
        $video = $this->FFMpeg->open($path);
        $frame = $video->frame($timeCode);

        $tempFile = tempnam('/tmp', 'thumb');
        $frame->save($tempFile);

        return new File($tempFile);
    }
}
