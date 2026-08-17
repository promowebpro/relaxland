<?php

namespace App\Http\Controllers\Api;

use App\Domain\Genplan\GenplanMode;
use App\Domain\Genplan\GenplanPublicQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\PlotListRequest;
use App\Http\Resources\GenplanOverviewResource;
use App\Http\Resources\InfrastructurePointResource;
use App\Http\Resources\PlotResource;
use App\Http\Resources\QuarterResource;
use App\Http\Resources\SurroundingPlaceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class GenplanApiController extends Controller
{
    public function overview(Request $request, GenplanPublicQuery $query): JsonResource
    {
        $mode = $this->mode($request);
        $genplan = $query->overview($mode);

        abort_if(! $genplan, 404);

        return new GenplanOverviewResource($genplan);
    }

    public function quarter(string $quarter, Request $request, GenplanPublicQuery $query): JsonResource
    {
        $record = $query->quarter($quarter, $this->mode($request));

        abort_if(! $record, 404);

        return new QuarterResource($record);
    }

    public function plots(string $quarter, PlotListRequest $request, GenplanPublicQuery $query): AnonymousResourceCollection
    {
        $mode = $request->mode();
        $record = $query->quarter($quarter, $mode);

        abort_if(! $record, 404);

        return PlotResource::collection($query->plots($record, $mode, $request->filters()));
    }

    public function infrastructure(Request $request, GenplanPublicQuery $query): AnonymousResourceCollection
    {
        return InfrastructurePointResource::collection($query->infrastructure($this->mode($request)));
    }

    public function surroundings(GenplanPublicQuery $query): AnonymousResourceCollection
    {
        return SurroundingPlaceResource::collection($query->surroundings());
    }

    private function mode(Request $request): GenplanMode
    {
        $validated = $request->validate(['mode' => ['nullable', 'in:2d,3d']]);

        return isset($validated['mode'])
            ? GenplanMode::from($validated['mode'])
            : GenplanMode::default();
    }
}
