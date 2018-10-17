<?php

namespace App\Models\Behaviors;

use App\Models\User;
use Laravel\Scout\Searchable;
use TeamTNT\TNTSearch\TNTSearch;

trait SearchableByUser
{
    use Searchable {
        searchableAs as traitSearchableAs;
        search as traitSearch;
    }

    public static $userIdSearchContext;

    public static function setSearchableAsUserContext(User $user)
    {
        self::$userIdSearchContext = $user->id;
    }

    public function searchableAs()
    {
        return $this->traitSearchableAs() . '.u' . self::$userIdSearchContext;
    }

    public static function searchForUser($query, User $user)
    {
        static::setSearchableAsUserContext($user);

        $tableName = (new static)->getTable();

        return static::traitSearch($query, function (TNTSearch $tntsearch, $query, $options) use ($user, $tableName) {
            $tntsearch->selectIndex(sprintf('%s.u%s.index', $tableName, $user->id));

            return $tntsearch->search($query);
        });
    }
}