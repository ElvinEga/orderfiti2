<?php

namespace App\Http\Controllers\Admin;


use Exception;
use App\Models\Template;
use Illuminate\Http\Request;
use App\Services\TemplateService;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\TemplateRequest;
use App\Http\Resources\TemplateResource;

class TemplateController extends AdminController
{
    private TemplateService $templateService;

    public function __construct(TemplateService $template)
    {
        parent::__construct();
        $this->templateService = $template;
        $this->middleware(['permission:settings'])->only('store', 'update', 'destroy', 'show');
    }

    public function index(
        PaginateRequest $request
    ): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return TemplateResource::collection($this->templateService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }


    public function store(
        TemplateRequest $request
    ): \Illuminate\Http\Response | TemplateResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new TemplateResource($this->templateService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(
        Template $template
    ): \Illuminate\Http\Response | TemplateResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new TemplateResource($this->templateService->show($template));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(
        TemplateRequest $request,
        Template $template
    ): \Illuminate\Http\Response | TemplateResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new TemplateResource($this->templateService->update($request, $template));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(
        Template $template
    ): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $this->templateService->destroy($template);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function sortCategory(
        Request $request
    ) {
        try {
            $this->templateService->sortCategory($request);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
