<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;

class UploadController extends Controller
{
    /**
     * Upload + optimize an image (used by the cover-image field and the
     * rich text editor's image button). Returns a public URL.
     */
    public function store(Request $request)
    {
        abort_unless($request->user()?->isAuthor(), 403);

        $request->validate([
            'image' => ['required', 'image', 'max:5120'], // 5MB
        ]);

        $file = $request->file('image');
        $filename = Str::uuid().'.webp';
        $path = 'uploads/'.date('Y/m');

        // Intervention Image v4: manager is built via usingDriver(), reading
        // is done with decode()/decodePath() (read() no longer exists), and
        // encoding goes through encodeUsingFormat() with a Format enum.
        $driverClass = extension_loaded('imagick') && ! extension_loaded('gd')
            ? ImagickDriver::class
            : GdDriver::class;

        $manager = ImageManager::usingDriver($driverClass);

        $encoded = $manager->decodePath($file->getRealPath())
            ->scaleDown(width: 1600)
            ->encodeUsingFormat(Format::WEBP, quality: 82);

        Storage::disk('public')->put("{$path}/{$filename}", (string) $encoded);

        return response()->json([
            'url' => Storage::disk('public')->url("{$path}/{$filename}"),
        ], 201);
    }
}
