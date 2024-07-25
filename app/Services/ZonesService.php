<?php

namespace App\Services;


use App\Http\Requests\ZonesRequest;
use App\Http\Requests\PaginateRequest;
use App\Models\Zones;
use App\Models\Branch;
use Exception;
use Illuminate\Support\Facades\Log;
use Smartisan\Settings\Facades\Settings;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;
use Dipokhalder\EnvEditor\EnvEditor;

class ZonesService
{
    protected array $zonesFilter = [
        'name',
        'size',
        'branch_id',
        'status'
    ];


    public $envService;

    public function __construct(EnvEditor $envEditor)
    {
        $this->envService = $envEditor;
    }

    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            return Zones::with('branch')->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->zonesFilter)) {
                        if ($key == "except") {
                            $explodes = explode('|', $request);
                            if (count($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('id', '!=', $explode);
                                }
                            }
                        } else {
                            if ($key == "branch_id") {
                                $query->where($key, $request);
                            } else {
                                $query->where($key, 'like', '%' . $request . '%');
                            }
                        }
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(ZonesRequest $request)
    {
        try {
            $branch      = Branch::find($request->branch_id);
            $branch_name = $branch ? $branch->name : "";

            $filename = Str::random(10) . '.png';
            $slug     = Str::slug($branch_name.'-'.$request->name);
            $url      = URL::to('/') . "/#/menu/" . $slug;
//
//            if (!File::exists(storage_path('app/public/qr_codes/'))) {
//                File::makeDirectory(storage_path('app/public/qr_codes/'));
//            }
//            QrCode::format('png')->size(200)->generate($url, storage_path('app/public/qr_codes/' . $filename));
            return Zones::create($request->validated() + ['qr_code' => 'storage/qr_codes/' . $filename, 'slug' => $slug]);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(ZonesRequest $request, Zones $zones)
    {
        try {
            $branch      = Branch::find($request->branch_id);
            $branch_name = $branch ? $branch->name : "";

            $filename = Str::random(10) . '.png';
            $slug     = Str::slug($branch_name.'-'.$request->name);
            $url      = URL::to('/') . "/#/menu/" . $slug;

//            if (!File::exists(storage_path('app/public/qr_codes/'))) {
//                File::makeDirectory(storage_path('app/public/qr_codes/'));
//            }

//            if(File::exists($zones->qr_code)){
//                File::delete($zones->qr_code);
//            }

//            QrCode::format('png')->size(200)->generate($url, 'storage/qr_codes/' . $filename);

            return tap($zones)->update($request->validated() + ['qr_code' => 'storage/qr_codes/' . $filename, 'slug' => $slug]);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(Zones $zones): void
    {
        try {
            if(File::exists($zones->qr_code) && !$this->envService->getValue('DEMO')){
                File::delete($zones->qr_code);
            }
            $zones->delete();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(Zones $zones): Zones
    {
        try {
            return $zones;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
