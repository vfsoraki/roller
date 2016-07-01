<?php

namespace VFSoraki\Roller;

/**
 * Adds rolling capabilities to a model
 */
trait RollerResource
{

    /**
     * Find users who have specified roles on this instance
     *
     * @param string|array|\Illuminate\Support\Collection $roles A role, or list of roles. Can be a string, array or Illuminate\Support\Collection instance.
     *
     * @return \Illuminate\Support\Collection A list of users
     */
    public function whoHasRoles($roles)
    {
        $roles = Support::makeRoleIds($roles);

        $rrus = RRU::whereResourceType(get_class($this))
            ->whereResourceId($this->getKey())
            ->whereIn('role_id', $roles)
            ->get();

        $userIds = $rrus->map(function ($rru) {
            return $rru->user_id;
        });

        $userModel = config('roller.model.user');
        $userModel = new $userModel;

        return $userModel->whereIn($userModel->getKeyName(), $userIds)->get();
    }

}
