<?php

namespace VFSoraki\Roller;

use Illuminate\Database\Eloquent\Model;

/**
 * Class RRU
 * Since the relationship of roles, users and resources are complex,
 * we should implement them ourselves.
 *
 * @property integer $user_id
 * @property integer $role_id
 * @property string $resource_type
 * @property integer $resource_id
 *
 * @package VFSoraki\Roller
 */
class RRU extends Model
{
    protected $table = 'rrus';
}