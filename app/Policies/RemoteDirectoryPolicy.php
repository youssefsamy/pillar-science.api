<?php

namespace App\Policies;

use App\Api\V1\Requests\DesktopClientRequest;
use App\Models\RemoteDirectory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Http\Request;

class RemoteDirectoryPolicy
{
    use HandlesAuthorization;
    /**
     * @var Request
     */
    private $request;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct(DesktopClientRequest $request)
    {
        $this->request = $request;
    }

    /**
     * User can view a remote directory if he is part of the team the
     * remote directory was attached to.
     *
     * @param User $user
     * @param RemoteDirectory $device
     * @return bool
     */
    public function viewDataset(User $user, RemoteDirectory $device)
    {
        return $this->deviceBelongsToUser($device, $user);
    }

    /**
     * User can update a remote directory if he is part of the team the
     * remote directory was attached to.
     *
     * @param User $user
     * @param RemoteDirectory $device
     */
    public function updateDataset(User $user, RemoteDirectory $device)
    {
        $requestedDevice = $this->request->getRemoteDirectory();

        if (!$this->validateDevices($device, $requestedDevice)) {
            return false;
        }

        return $this->deviceBelongsToUser($device, $user);
    }

    /**
     * We must verify that the root dataset device_id matches
     * the device bound in the request.
     *
     * @param User $user
     * @param RemoteDirectory $device The device associated to the dataset
     * to which we want to upload
     */
    public function uploadDataset(User $user, RemoteDirectory $device)
    {
        $requestedDevice = $this->request->getRemoteDirectory();

        if (!$this->validateDevices($device, $requestedDevice)) {
            return false;
        }

        return $this->deviceBelongsToUser($device, $user);
    }

    /**
     * We must verify that the root dataset device_id matches
     * the device bound in the request.
     *
     * @param User $user
     * @param RemoteDirectory $device
     */
    public function destroyDataset(User $user, RemoteDirectory $device)
    {
        $requestedDevice = $this->request->getRemoteDirectory();

        if (!$this->validateDevices($device, $requestedDevice)) {
            return false;
        }

        return $this->deviceBelongsToUser($device, $user);
    }

    public function moveDataset(User $user, RemoteDirectory $device)
    {
        $requestedDevice = $this->request->getRemoteDirectory();

        if (!$this->validateDevices($device, $requestedDevice)) {
            return false;
        }

        return $this->deviceBelongsToUser($device, $user);
    }

    public function mapDataset(User $user, RemoteDirectory $device)
    {
        return $this->deviceBelongsToUser($device, $user);
    }

    public function addMetadataDataset(User $user, RemoteDirectory $device)
    {
        return $this->deviceBelongsToUser($device, $user);
    }

    public function addProtocolDataset(User $user, RemoteDirectory $device)
    {
        return $this->deviceBelongsToUser($device, $user);
    }

    /**
     * Validates that the device is not empty and that the device
     * matches the requested device
     *
     * @param RemoteDirectory $device
     * @param RemoteDirectory $otherDevice
     * @return bool
     */
    protected function validateDevices(RemoteDirectory $device, RemoteDirectory $otherDevice = null)
    {
        if ($otherDevice === null) {
            return false;
        }

        return $device->id === $otherDevice->id;
    }

    /**
     * Validates that $device is bound to the $user through a team
     *
     * @param RemoteDirectory $device
     * @param User $user
     * @return bool
     */
    protected function deviceBelongsToUser(RemoteDirectory $device, User $user)
    {
        return $user->teams->contains(function ($team) use ($device) {
            return $team->id === $device->team->id;
        });
    }
}
