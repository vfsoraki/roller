<?php

namespace VFSoraki\Roller;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use VFSoraki\Roller\Exception\RollerException;

/**
 * Adds rolling capabilities to User
 */
trait RollerUser
{

    /**
     * Sync roles for this user.
     *
     * @param array|\Illuminate\Support\Collection $roleIds
     * @param null|array $resource
     *
     * @throws RollerException
     */
    private function syncRoles($roleIds, $resource=null)
    {
        $roleIds = Support::makeCollection($roleIds);

        // Query to remove all roles for current user
        $query = RRU::whereUserId($this->getKey());
        if ($resource) {
            $query->whereResourceType($resource['type'])->whereResourceId($resource['id']);
        }

        // Query to relate all roles to current user
        $data = [];
        foreach ($roleIds as $roleId) {
            $data[] = [
                'user_id' => $this->getKey(),
                'role_id' => $roleId,
                'resource_type' => $resource['type'],
                'resource_id' => $resource['id'],
            ];
        }

        // Execute queries in a transaction
        $table = (new RRU())->getTable();
        DB::transaction(function () use ($query, $table, $data) {
            $query->delete();
            DB::table($table)->insert($data);
        });
    }

    /**
     * Make an array or null value from provided resource.
     * This will be used for queries.
     *
     * @param string|\Illuminate\Database\Eloquent\Model|null $resource The resource instance, or its type if parameter is a string.
     * @return array|null
     * @throws RollerException
     */
    private function makeArrayResource($resource)
    {
        if (is_string($resource)) {
            if (class_exists($resource)) {
                $syncResource = [
                    'type' => $resource,
                    'id' => null,
                ];
            } else {
                throw new RollerException('Class does not exist: '.$resource);
            }
        } elseif ($resource instanceof Model) {
            $syncResource = [
                'type' => get_class($resource),
                'id' => $resource->getKey(),
            ];
        } elseif (is_null($resource)) {
            $syncResource = [
                'type' => null,
                'id' => null,
            ];
        } else {
            throw new RollerException('Provided resource is not a class or instance.');
        }
        return $syncResource;
    }


    /**
     * Add a role or list of roles to user. If a resource is also provided, the roles
     * are added to that resource domain only.
     * Resource can be a class name or Eloquent Model instance.
     *
     * @param string|array|\Illuminate\Support\Collection $roles A role or list of roles to add to user.
     * @param string|\Illuminate\Database\Eloquent\Model|null $resource The resource instance, or its type if parameter is a string.
     *
     * @return $this
     *
     * @throws RollerException
     */
    public function giveRoles($roles, $resource=null)
    {
        $this->syncRoles(
            Support::makeRoleIds($roles),
            $this->makeArrayResource($resource)
        );
        return $this;
    }

    /**
     * Alias of add_role
     *
     * @param string $role A role to add to user.
     * @param string|\Illuminate\Database\Eloquent\Model|null $resource The resource instance, or its type if parameter is a string.
     *
     * @return $this
     */
    public function giveRole($role, $resource=null)
    {
        return $this->giveRoles($role, $resource);
    }

    /**
     * Check if user has roles. If a resource is provided, roles are checked against that resource domain.
     * Resource can be a class name or Eloquent Model instance.
     *
     * @param string|array|\Illuminate\Support\Collection $roles A role or list of roles to add to user.
     * @param string|\Illuminate\Database\Eloquent\Model|null $resource The resource instance, or its type if parameter is a string.
     *
     * @return boolean
     *
     * @throws RollerException
     */
    public function hasRoles($roles, $resource=null)
    {
        $resource = $this->makeArrayResource($resource);
        $roles = Support::makeRoleIds($roles);

        // Normally check if user has role, regarding resource
        $rrus = RRU::whereUserId($this->getKey())
            ->whereResourceType($resource['type'])
            ->whereResourceId($resource['id'])
            ->whereIn('role_id', $roles)
            ->get();
        if ($rrus and $rrus->count() > 0) {
            return true;
        }

        // But if resource is provided, we should also check if user has that global role or not
        if (!is_null($resource['id'])) {
            // Check if user has role on resource type only
            $rrus = RRU::whereUserId($this->getKey())
                ->where(function ($query) use ($resource) {
                    $query->whereResourceType($resource['type']);
                    $query->orWhere('resource_type', null);
                })
                ->whereResourceId(null)
                ->whereIn('role_id', $roles)
                ->get();
            if ($rrus and $rrus->count() > 0) {
                return true;
            }
        } elseif (!is_null($resource['type'])) {
            // Check if user has global role only
            $rrus = RRU::whereUserId($this->getKey())
                ->whereResourceType(null)
                ->whereResourceId(null)
                ->whereIn('role_id', $roles)
                ->get();
            if ($rrus and $rrus->count() > 0) {
                return true;
            }
        }

        return false;
    }

    public function hasRole($role, $resource=null)
    {
        return $this->hasRoles($role, $resource);
    }

}
