# Video Thumbnails

OpenDXP can transcode uploaded videos into web-ready formats and extract still images at any point in time. 
Transcoding runs asynchronously via the message queue — the result is not immediately available after the first request.

> [!NOTE]
> **Requires ffmpeg**  
> All transcoding features depend on ffmpeg being installed on the server.  
> See [Additional Tools Installation](../../23_Installation_and_Upgrade/03_System_Setup_and_Hosting/06_Additional_Tools_Installation.md) for setup instructions.

---

## Transformations
A video thumbnail configuration is a named pipeline of transformations applied in sequence. Each transformation maps to an ffmpeg filter or option.

| Transformation      | Description                                                                               | Option / Filter                                                                     |
|---------------------|-------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------|
| Resize              | Scale to exact dimensions, ignoring aspect ratio                                          | [`-s WxH`](https://ffmpeg.org/ffmpeg.html#Video-Options)                            |
| Scale by Width      | Scale proportionally to a target width. Optionally skip if the video is already smaller.  | [`-vf scale`](https://ffmpeg.org/ffmpeg-filters.html#scale-1)                       |
| Scale by Height     | Scale proportionally to a target height. Optionally skip if the video is already smaller. | [`-vf scale`](https://ffmpeg.org/ffmpeg-filters.html#scale-1)                       |
| Cut                 | Trim the video to a start time and duration                                               | [`-ss` and `-t`](https://ffmpeg.org/ffmpeg.html#Main-options)                       |
| Set Framerate       | Convert to a constant frame rate by duplicating or dropping frames                        | [`-vf fps`](https://ffmpeg.org/ffmpeg-filters.html#fps-1)                           |
| Color Channel Mixer | Adjust color channels — useful for effects like grayscale or sepia                        | [`-vf colorchannelmixer`](https://ffmpeg.org/ffmpeg-filters.html#colorchannelmixer) |
| Mute                | Remove the audio stream entirely                                                          | [`-an`](https://ffmpeg.org/ffmpeg.html#Audio-Options)                               |

### Force Resize

The **Scale by Width** and **Scale by Height** transformations include a **Force Resize** option (enabled by default).

When **Force Resize is on**, the video is always scaled to the configured dimension — even if the source is smaller, which causes upscaling.  
When **Force Resize is off**, the transformation is skipped if the source video is already smaller than the target. 

This prevents quality loss from upscaling, which is especially relevant for vertical or low-resolution uploads.

---

## Working with Video Thumbnails in Code

### Image Snapshots

You can extract a still image from any video, optionally combined with an image thumbnail configuration for resizing.

```php
$asset = Asset::getById(123);

if ($asset instanceof Asset\Video) {
    // Preview image using a named image thumbnail config
    echo $asset->getImageThumbnail('myThumbnail');

    // Snapshot at a specific time offset (in seconds)
    echo $asset->getImageThumbnail(['width' => 250], 10);
}
```

### Video Transcoding

Transcoding is asynchronous. The first call to `getThumbnail()` dispatches a conversion job to the message queue. Subsequent calls return the current status until the job is complete.

```php
$asset = Asset::getById(123);

if ($asset instanceof Asset\Video) {
    $thumbnail = $asset->getThumbnail('myVideoThumbnail');

    echo match ($thumbnail['status']) {
        'finished'   => 'Ready: ' . $thumbnail['formats']['mp4'],
        'inprogress' => 'Transcoding in progress — check back shortly.',
        'error'      => 'Transcoding failed. Check the logs for details.',
        default      => 'Queued.',
    };
}
```

The `formats` array contains storage paths for each generated format, for example:

```php
[
    'mp4'  => '/videos/123/video-thumb__123__myVideoThumbnail/clip.mp4',
    'webm' => '/videos/123/video-thumb__123__myVideoThumbnail/clip.webm',
]
```

---

## Adaptive Bitrate Streaming (MPEG-DASH)

When a thumbnail configuration contains **media segments** (multiple bitrate variants), OpenDXP generates an additional `.mpd` manifest file alongside the regular formats. 
This enables adaptive bitrate streaming via MPEG-DASH — the player automatically selects the appropriate quality level based on the viewer's available bandwidth.

To play `.mpd` streams in all browsers, include a DASH player such as [dash.js](https://github.com/Dash-Industry-Forum/dash.js).

The `opendxp_video` Twig tag handles source selection automatically:

```twig
{{ opendxp_video(asset, {
    thumbnail: 'myVideoThumbnail'
}) }}
```

Rendered output (example):

```html
<video controls preload="auto" class="opendxp_video">
    <source type="video/mp4" src="/videos/955/video-thumb__955__myVideoThumbnail/clip.mp4">
    <source type="application/dash+xml" src="/videos/955/video-thumb__955__myVideoThumbnail/clip.mpd">
</video>
```

When a `.mpd` source is present, a DASH-capable player will prefer it and adapt quality dynamically. Browsers without DASH support fall back to the `mp4` source.

---

## Related

- [Video Editable](../../03_Documents/01_Editables/38_Video.md)
- [Additional Tools Installation](../../23_Installation_and_Upgrade/03_System_Setup_and_Hosting/06_Additional_Tools_Installation.md)