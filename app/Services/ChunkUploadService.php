<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Pion\Laravel\ChunkUpload\Exceptions\UploadFailedException;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\AbstractHandler;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class ChunkUploadService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * @throws UploadMissingFileException
     * @throws UploadFailedException
     */
    public function handle(Request $request): \Illuminate\Http\JsonResponse
    {

        // create the file receiver
        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));

        // check if the upload is success, throw exception or return response you need
        if ($receiver->isUploaded() === false) {
            throw new UploadMissingFileException;
        }

        // receive the file
        $save = $receiver->receive();

        // check if the upload has finished (in chunk mode it will send smaller files)
        if ($save->isFinished()) {
            // save the file and return any response you need, current example uses `move` function. If you are
            // not using move, you need to manually delete the file by unlink($save->getFile()->getPathname())
            return $this->saveFile($save->getFile());
        }

        // we are in chunk mode, lets send the current progress
        /** @var AbstractHandler $handler */
        $handler = $save->handler();

        return response()->json([
            'done' => $handler->getPercentageDone(),
        ]);
    }

    protected function saveFile(UploadedFile $file): \Illuminate\Http\JsonResponse
    {
        $fileName = $this->createFilename($file);
        // Group files by mime type
        $mime = str_replace('/', '-', $file->getMimeType());
        // Group files by the date (week
        $dateFolder = date('Y-m-W');

        // Build the file path
        $filePath  = "upload/{$mime}/{$dateFolder}/";
        $finalPath = storage_path('app/public/'.$filePath);

        // move the file name
        $file->move($finalPath, $fileName);

        return response()->json([
            'url'       => asset("storage/{$filePath}{$fileName}"),
            'name'      => $fileName,
            'mime_type' => $mime,
        ]);
    }

    /**
     * Create unique filename for uploaded file
     */
    protected function createFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename  = str_replace('.'.$extension, '', $file->getClientOriginalName()); // Filename without extension

        // Add timestamp hash to name of the file
        $filename .= '_'.md5(time()).'.'.$extension;

        return $filename;
    }
}
