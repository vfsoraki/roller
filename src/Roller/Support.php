<?php

namespace VFSoraki\Roller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use VFSoraki\Roller\Exception\RollerException;

/**
 * Utility functions for Roller
 */
class Support
{

    /**
     * Make a collection from a string, array or Collection
     *
     * @param string|array|\Illuminate\Support\Collection $items The items
     *
     * @return Collection The collection
     *
     * @throws RollerException
     */
    public static function makeCollection($items)
    {
        if (is_string($items)) {
            return new Collection([$items]);
        }

        if (is_array($items)) {
            return new Collection($items);
        }

        if ($items instanceof Collection) {
            return $items;
        }

        throw new RollerException('Can not make collection from items');
    }

    /**
     * Make a collection of role ids from provided roles
     * 
     * @param string|array|\Illuminate\Support\Collection $roles A role or list of roles to add to user.
     * 
     * @return \Illuminate\Support\Collection
     * 
     * @throws RollerException
     */
    public static function makeRoleIds($roles)
    {
        $roles = Support::makeCollection($roles);
        return $roles->map(
            function ($role) {
                if (is_string($role)) {
                    try {
                        $role = Role::whereName($role)->firstOrFail();
                    } catch (ModelNotFoundException $e) {
                        $role = Role::create(['name' => $role]);
                    }
                } elseif (!$role instanceof Role) {
                    throw new RollerException('Provided role is not a string or Role instance');
                }

                return $role->id;
            }
        );
    }

}
