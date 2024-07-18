<?php

namespace App\Http\Controllers\Table;


use App\Http\Controllers\Controller;
use App\Models\FrontendZones;
use Exception;
use App\Services\ZonesService;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\ZonesResource;
use App\Models\Zones;

class ZonesController extends Controller
{
    private ZonesService $zonesService;

    public function __construct(ZonesService $zones)
    {
        $this->zonesService = $zones;
    }

    public function index(PaginateRequest $request) : \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ZonesResource::collection($this->zonesService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(FrontendZones $frontendZones): ZonesResource|\Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new ZonesResource($frontendZones);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
