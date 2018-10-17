<?php

namespace App\Functional\Models;

use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\Team;
use App\Models\User;
use App\Services\Projects\ProjectManager;
use App\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UserTest extends TestCase
{
    use DatabaseMigrations;

    public function testIsNotSuperAdmin()
    {
        /** @var User $user */
        $user = User::make();

        $this->assertFalse($user->isSuperAdmin());
    }

    public function testIsSuperAdmin()
    {
        /** @var User $user */
        $user = User::make();

        $user->permissions = [User::PERMISSION_USER_TEAM_MANAGEMENT];

        $this->assertTrue($user->isSuperAdmin());
    }

    /**
     * @group RV-42
     * @dataProvider updateAllFieldsDataProvider
     */
    public function testIsAllowedToUpdateAllFields(string $role, array $fieldsToUpdate, bool $expected)
    {
        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        /** @var Team $team */
        $team = factory(Team::class)->create();
        /** @var User $user */
        $user = factory(User::class)->create();

        $project = $projectManager->create($team, $user, [
            'name' => 'My weird project'
        ])
            ->shareWith($user, $role);

        $this->assertEquals($expected, $user->isAllowedToUpdateAllFields($fieldsToUpdate, $project));
    }

    public function updateAllFieldsDataProvider()
    {
        $tests = [
            [ProjectUser::ROLE_VIEWER, [], false],
            [ProjectUser::ROLE_VIEWER, ['description'], false],
            [ProjectUser::ROLE_VIEWER, ['name'], false],
            [ProjectUser::ROLE_VIEWER, ['name', 'description'], false],
            [ProjectUser::ROLE_CONTRIBUTOR, [], true],
            [ProjectUser::ROLE_CONTRIBUTOR, ['description'], true],
            [ProjectUser::ROLE_CONTRIBUTOR, ['name'], false],
            [ProjectUser::ROLE_CONTRIBUTOR, ['name', 'description'], false],
            [ProjectUser::ROLE_MANAGER, [], true],
            [ProjectUser::ROLE_MANAGER, ['description'], true],
            [ProjectUser::ROLE_MANAGER, ['name'], true],
            [ProjectUser::ROLE_MANAGER, ['name', 'description'], true],
        ];

        return $this->nameDataProviderTests($tests);
    }

    /**
     * @group RV-42
     */
    public function testIsAllowedToUpdateFieldWillBeDeniedForContributor()
    {
        /** @var ProjectManager $projectManager */
        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        /** @var Team $team */
        $team = factory(Team::class)->create();
        /** @var User $user */
        $user = factory(User::class)->create();

        $project = $projectManager->create($team, $user, [
            'name' => 'My weird project'
        ]);

        $project->shareWith($user, ProjectUser::ROLE_CONTRIBUTOR);

        $this->assertFalse($user->isAllowedToUpdateField('name', $project));
    }

    /**
     * @group RV-42
     */
    public function testIsAllowedToUpdateFieldWillBeDeniedForViewer()
    {
        /** @var ProjectManager $projectManager */
        $projectManager = app(\App\Services\Projects\ProjectManager::class);

        /** @var Team $team */
        $team = factory(Team::class)->create();
        /** @var User $user */
        $user = factory(User::class)->create();

        $project = $projectManager->create($team, $user, [
            'name' => 'My weird project'
        ]);

        $project->shareWith($user, ProjectUser::ROLE_VIEWER);

        $this->assertFalse($user->isAllowedToUpdateField('description', $project));
    }

    /**
     * @group RV-42
     * @dataProvider canShareAtRoleLevelDataProvider
     */
    public function testCanShareAtRoleLevel(string $actualRole, string $requestedRole, bool $expected)
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Project $project */
        $project = factory(Project::class)->create();

        $project->shareWith($user, $actualRole);

        $this->assertEquals($expected, $user->canShareAtRoleLevel($requestedRole, $project));
    }

    /**
     * @group RV-42
     * @dataProvider canShareAtRoleLevelDataProvider
     */
    public function testCanUnshareAtRoleLevel(string $actualRole, string $requestedRole, bool $expected)
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var User $targetUsr */
        $targetUser = factory(User::class)->create();

        /** @var Project $project */
        $project = factory(Project::class)->create();

        $project->shareWith($user, $actualRole);
        $project->shareWith($targetUser, $requestedRole);

        $this->assertEquals($expected, $user->canUnshareAtRoleLevel($targetUser, $project));
    }

    public function canShareAtRoleLevelDataProvider()
    {
        $tests = [
            [ProjectUser::ROLE_VIEWER, ProjectUser::ROLE_VIEWER, false],
            [ProjectUser::ROLE_VIEWER, ProjectUser::ROLE_CONTRIBUTOR, false],
            [ProjectUser::ROLE_VIEWER, ProjectUser::ROLE_MANAGER, false],
            [ProjectUser::ROLE_CONTRIBUTOR, ProjectUser::ROLE_VIEWER, true],
            [ProjectUser::ROLE_CONTRIBUTOR, ProjectUser::ROLE_CONTRIBUTOR, true],
            [ProjectUser::ROLE_CONTRIBUTOR, ProjectUser::ROLE_MANAGER, false],
            [ProjectUser::ROLE_MANAGER, ProjectUser::ROLE_VIEWER, true],
            [ProjectUser::ROLE_MANAGER, ProjectUser::ROLE_CONTRIBUTOR, true],
            [ProjectUser::ROLE_MANAGER, ProjectUser::ROLE_MANAGER, true],
        ];

        return $this->nameDataProviderTests($tests);
    }
}
