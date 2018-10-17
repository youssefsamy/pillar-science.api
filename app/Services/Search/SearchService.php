<?php

namespace App\Services\Search;

use App\Models\Dataset;
use App\Models\Protocol;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class SearchService
{
    public static function mapResults(Model $result): array {
        return [
            'resource' => $result,
            'type' => (new \ReflectionClass($result))->getShortName()
        ];
    }

    public function search($query, User $user = null, $limit = null): array
    {
        $results = [];

        if ($user) {
            $datasets = $this->searchDatasets($query, $user)->map(__CLASS__.'::mapResults')->toArray();
            $protocols = $this->searchProtocols($query, $user)->map(__CLASS__.'::mapResults')->toArray();

            $results = array_merge($results, $datasets, $protocols);
        }

        return array_slice($results, 0, $limit);
    }

    /**
     * @param $query
     * @return Collection
     */
    public function searchDatasets($query, User $user): Collection
    {
        return Dataset::searchForUser($query, $user)->get();
    }

    public function searchProtocols($query, User $user): Collection
    {
        return Protocol::searchForUser($query, $user)->get();
    }
}