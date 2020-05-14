<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RecordingsController extends Controller
{

    const RECORDING_FILE_EXT = ".ogg";

    /**
     * Displays the list of audio recordings.
     * @return \Illuminate\View\View
     */
    public function showRecordingsPage()
    {
        $recordingsPath = app('path') . '/../public/recordings/';

        $this->checkDevMode($recordingsPath);

        $audioFiles = new Collection();
        $filesInDirectory = array_diff(scandir($recordingsPath), array('.', '..'));

        foreach ($filesInDirectory as $file) {
            $audioFiles->add(new \SplFileInfo($recordingsPath . $file));
        }

        return view('_pages.recordings')->with('recordings', $audioFiles);
    }

    /**
     * Downloads the audio recording file.
     * @param string $filename The filename (without the file extension)
     * @return bool|BinaryFileResponse
     */
    public function downloadAudioFile($filename)
    {
        $filePath = app('path') . '/../public/recordings/' . $filename . self::RECORDING_FILE_EXT;
        if (file_exists($filePath)) {
            return response()->download($filePath);
        }
        return response()->isNotFound();
    }

    /**
     * Deletes an audio recording file.
     * @param string $filename The filename (without the file extension)
     * @return bool|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory|BinaryFileResponse
     */
    public function deleteAudioFile($filename)
    {
        $filePath = app('path') . '/../public/recordings/' . $filename . self::RECORDING_FILE_EXT;
        if (file_exists($filePath)) {
            if (!unlink($filePath)) {
                return response()->isServerError();
            }
            return response(null, 204);
        }
        return response()->isNotFound();
    }

    /**
     * Checks and automatically handles adding dummy audio recordings in development mode.
     * @param string $path
     * @return void
     */
    private function checkDevMode($path)
    {
        if ((env('APP_ENV') == 'local') && !file_exists($path)) {
            // @todo - Copy in some example files here!!
            mkdir($path);
            shell_exec("cp -r " . app('path') . '/resources/dev/dummy_recordings/' . " $path");
        }
    }

}
