<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\DesktopClientCreateRequest;
use App\Http\Controllers\Controller;
use App\Models\DesktopClient;
use Carbon\Carbon;
use Dingo\Api\Http\Response;
use Illuminate\Http\UploadedFile;

class DesktopClientController extends Controller
{
    public function index()
    {
        $versions = DesktopClient::query()
            ->latest()
            ->get();

        return response()->json($versions);
    }

    public function store(DesktopClientCreateRequest $request)
    {
        /** @var UploadedFile $file */
        $file = $request->file('application');
        $disk = config('pillar.storage.desktop_clients.disk');

        $path = \Storage::disk($disk)
            ->putFileAs(config('pillar.storage.desktop_clients.upload_dir'), $file, sprintf('pillar-science-%s.%s', Carbon::now()->format('Y-m-d-H-i-s'), $file->getClientOriginalExtension()));

        /** @var DesktopClient $dc */
        $dc = DesktopClient::create([
            'disk' => $disk,
            'path' => $path,
            'size' => $file->getSize()
        ]);

        return response()->json($dc, Response::HTTP_CREATED);
    }

    public function download()
    {
        /** @var DesktopClient $dc */
        $dc = DesktopClient::query()
            ->latest()
            ->first();

        if (!$dc) {
            return response()->json(null, \Illuminate\Http\Response::HTTP_NO_CONTENT);
        }

        return \Storage::disk($dc->disk)->download($dc->path);
    }

    public function latest()
    {
        $dc = DesktopClient::query()
            ->latest()
            ->first();

        return $dc;
    }
}