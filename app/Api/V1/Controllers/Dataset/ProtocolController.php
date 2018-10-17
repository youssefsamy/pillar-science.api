<?php

namespace App\Api\V1\Controllers\Dataset;

use App\Http\Controllers\Controller;
use App\Models\Dataset;
use App\Models\Protocol;
use App\Models\User;
use Dingo\Api\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProtocolController extends Controller
{
    public function availableAutocomplete(Dataset $dataset, Request $request)
    {
        /** @var User $user */
        $user = \Auth::user();

        $protocols = Protocol::limit(5)
            ->where('name', 'like', '%' . $request->get('query', '') .'%')
            ->where('user_id', $user->id)
            ->whereNotIn('id', $dataset->protocols->pluck('id'))
            ->get();

        return response()->json($protocols);
    }

    /**
     * @param Dataset $dataset
     * @param Protocol $protocol
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Dataset $dataset, Protocol $protocol)
    {
        $this->authorize('view', $protocol);

        /** @var Collection $ancestors */
        $ancestors = $dataset->ancestors;

        $ancestors->add($dataset);

        /** @var Dataset $datasetOwningTheProtocol */
        $datasetOwningTheProtocol = null;
        foreach ($ancestors as $d) {
            if ($d->protocols()->find($protocol->id)) {
                $datasetOwningTheProtocol = $d;
            }
        }

        // Avoid recursion
        $protocol->dataset = $datasetOwningTheProtocol
            ->makeHidden('protocols');

        // $protocol->makeVisible('dataset.author');

        return response()->json($protocol);
    }

    /**
     * @param Dataset $dataset
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Dataset $dataset, Request $request)
    {
        $this->authorize('store', [Protocol::class, $dataset]);

        $protocol = Protocol::make($request->all());

        $protocol->user()->associate(\Auth::user());

        $dataset->protocols()->save($protocol);

        return response()->json($protocol, Response::HTTP_CREATED);
    }

    /**
     * Attaching a protocol to a dataset
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Dataset $dataset, Protocol $protocol)
    {
        $this->authorize('attach', [$protocol, $dataset]);

        $dataset->protocols()->save($protocol);

        return response()->json($protocol);
    }

    /**
     * @param Dataset $dataset
     * @param Protocol $protocol
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Dataset $dataset, Protocol $protocol)
    {
        $this->authorize('detach', [$protocol, $dataset]);

        $dataset->protocols()->detach($protocol);

        return response()->json();
    }
}
