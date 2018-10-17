<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableInterface;

class ProjectUser extends Pivot implements AuditableInterface
{
    use Auditable;
    use SerializesDates;

    const ROLE_MANAGER = 'manager';
    const ROLE_CONTRIBUTOR = 'contributor';
    const ROLE_VIEWER = 'viewer';

    /**
     * Determines which roles a certain role can assign.
     *
     * The keys are the actual ProjectUser role and the array value is the list
     * of roles the actual role is allowed to share at.
     *
     * Ex. A viewer cannot share at any level, thus the empty array
     * Ex. A contributor can share at any level except manager level
     *
     * @var array
     */
    public static $roleAssignationAllowance = [
        self::ROLE_VIEWER => [],
        self::ROLE_CONTRIBUTOR => [
            self::ROLE_VIEWER,
            self::ROLE_CONTRIBUTOR
        ],
        self::ROLE_MANAGER => [
            self::ROLE_VIEWER,
            self::ROLE_CONTRIBUTOR,
            self::ROLE_MANAGER
        ],
    ];

    protected $visible = [
        'role',
        'created_at',
        'updated_at'
    ];
}
