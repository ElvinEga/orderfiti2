<?php

namespace App\Services;


use App\Models\DefaultAccess;
use App\Models\Item;
use Exception;
use Illuminate\Support\Str;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Libraries\QueryExceptionLibrary;
use App\Http\Requests\TemplateRequest;

class TemplateService
{
    protected $itemCateFilter = [
        'name',
        'template_id',
        'status'
    ];

    protected $exceptFilter = [
        'excepts'
    ];

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

            return Template::with('media')->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->itemCateFilter)) {
                        $query->where($key, 'like', '%' . $request . '%');
                    }

                    if (in_array($key, $this->exceptFilter)) {
                        $explodes = explode('|', $request);
                        if (is_array($explodes)) {
                            foreach ($explodes as $explode) {
                                $query->where('id', '!=', $explode);
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
    public function store(TemplateRequest $request): array
    {
        try {
            $templateId = $request->input('template_id');
            $branchId = 0;

            if (!$request->has('branch_id')) {
                $branch_id = auth()->user()->branch_id;

                if ($branch_id != 0) {
                    $branchId = $branch_id;
                } else {
                    $defaultAccess = DefaultAccess::where(['user_id' => auth()->user()->id])->first();
                    $default_id = $defaultAccess->default_id;
                    $branchId = $default_id;
                }
            }

            // Find the template
            $template = Template::findOrFail($templateId);

            // Get the template items
            $templateItems = $template->templateItems;

            // Begin a database transaction
            DB::beginTransaction();

            $newItems = [];

            // Create new items based on template items
            foreach ($templateItems as $templateItem) {
                $newItem = new Item();

                // Copy relevant fields from template item to new item
                $newItem->name = $templateItem->name;
                $newItem->item_category_id = $templateItem->item_category_id;
                $newItem->branch_id = $branchId;
                $newItem->slug = $templateItem->slug;
                $newItem->description = $templateItem->description;
                $newItem->price = $templateItem->price;
                $newItem->status = $templateItem->status;
                $newItem->is_featured = $templateItem->is_featured;
                $newItem->item_type = $templateItem->item_type;
                $newItem->order = $templateItem->order;
                // Add any other fields that need to be copied

                $newItem->save();

                $this->copyMedia($templateItem, $newItem);


                $newItems[] = $newItem;

                // If there are any relations or additional data to copy, do it here
                // For example, if items have media:
//                 $newItem->addMedia($templateItem->getFirstMedia('item-media')->getPath())
//                         ->preservingOriginal()
//                         ->toMediaCollection('item');
            }

            // Commit the transaction
            DB::commit();

            return $newItems;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    private function copyMedia($sourceItem, $destinationItem): void
    {
        $media = DB::table('media')
            ->where('model_type', 'App\Models\Item')
            ->where('model_id', $sourceItem->id)
            ->where('collection_name', 'item')
            ->get();

        foreach ($media as $mediaItem) {
            $newMediaId = DB::table('media')->insertGetId([
                'model_type' => 'App\Models\Item',
                'model_id' => $destinationItem->id,
                'uuid' => Str::uuid(),
                'collection_name' => 'item',
                'name' => $mediaItem->name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);


        }
    }

    /**
     * @throws Exception
     */
    public function update(TemplateRequest $request, Template $itemCategory): Template
    {
        try {
            $itemCategory->update($request->validated() );
            if ($request->image) {
                $itemCategory->clearMediaCollection('item-category');
                $itemCategory->addMediaFromRequest('image')->toMediaCollection('item-category');
            }
            return $itemCategory;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(Template $itemCategory)
    {
        try {
            $checkItem = $itemCategory->items->whereNull('deleted_at');
            if (!blank($checkItem)) {
                $itemCategory->delete();
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                $itemCategory->delete();
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(Template $itemCategory)
    {
        try {
            return $itemCategory;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function sortCategory(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                foreach ($request->category_id as $index => $id) {
                    Template::where('id', $id)->update(['sort' => $index + 1]);
                }
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
