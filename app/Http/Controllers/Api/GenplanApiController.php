<?php

namespace App\Http\Controllers\Api;

use App\Domain\Genplan\GenplanPublicQuery;
use App\Http\Controllers\Controller;
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
    public function overview(GenplanPublicQuery $query): JsonResource
    {
        $genplan = $query->overview();

        abort_if(! $genplan, 404);

        return new GenplanOverviewResource($genplan);
    }

    public function quarter(string $quarter, GenplanPublicQuery $query): JsonResource
    {
        $record = $query->quarter($quarter);

        abort_if(! $record, 404);

        return new QuarterResource($record);
    }

    public function plots(string $quarter, GenplanPublicQuery $query): AnonymousResourceCollection
    {
        $record = $query->quarter($quarter);

        abort_if(! $record, 404);

        return PlotResource::collection($query->plots($record));
    }

    public function infrastructure(Request $request, GenplanPublicQuery $query): AnonymousResourceCollection
    {
        $validated = $request->validate(['mode' => ['nullable', 'in:2d,3d']]);

        return InfrastructurePointResource::collection($query->infrastructure($validated['mode'] ?? null));
    }

    public function surroundings(GenplanPublicQuery $query): AnonymousResourceCollection
    {
        return SurroundingPlaceResource::collection($query->surroundings());
    }
}
