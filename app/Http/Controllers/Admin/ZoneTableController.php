<?php

namespace App\Http\Controllers\Admin;


use Exception;
use App\Models\Zones;
//use App\Exports\ZonesExport;
use App\Services\ZonesService;
//use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\ZonesRequest;
use App\Http\Resources\ZonesResource;

class ZoneTableController extends AdminController
{
    private ZonesService $zonesService;

    public function __construct(ZonesService $diningTable)
    {
        parent::__construct();
        $this->zonesService = $diningTable;
//        $this->middleware(['permission:dining-tables'])->only('export');
        $this->middleware(['permission:dining_tables_create'])->only('store');
        $this->middleware(['permission:dining_tables_edit'])->only('update');
        $this->middleware(['permission:dining_tables_delete'])->only('destroy');
        $this->middleware(['permission:dining_tables_show'])->only('show');
    }

    public function index(PaginateRequest $request): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return ZonesResource::collection($this->zonesService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }


    public function store(
        ZonesRequest $request
    ): \Illuminate\Http\Response | ZonesResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new ZonesResource($this->zonesService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(
        Zones $diningTable
    ): \Illuminate\Http\Response | ZonesResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new ZonesResource($this->zonesService->show($diningTable));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(
        ZonesRequest $request,
        Zones $diningTable
    ): \Illuminate\Http\Response | ZonesResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new ZonesResource($this->zonesService->update($request, $diningTable));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(
        Zones $diningTable
    ): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $this->zonesService->destroy($diningTable);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

//    public function export(PaginateRequest $request): \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
//    {
//        try {
//            return Excel::download(new ZonesExport($this->zonesService, $request), 'Dining-Table.xlsx');
//        } catch (Exception $exception) {
//            return response(['status' => false, 'message' => $exception->getMessage()], 422);
//        }
//    }
}
