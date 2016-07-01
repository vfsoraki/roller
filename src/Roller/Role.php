<?php

namespace VFSoraki\Roller;

use Illuminate\Database\Eloquent\Model;

/**
 * Role model
 *
 * @property integer $id
 * @property string $name
 */
class Role extends Model
{

    protected $fillable = ['name'];

}
