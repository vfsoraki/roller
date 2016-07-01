<?php

namespace VFSoraki\Roller;

use Illuminate\Database\Eloquent\Model;

/**
 * Resource model
 */
class Resource extends Model
{

  /**
   * Resource-Role relationship
   */
  public function roles()
  {
    $this->belongsToMany(Role::class);
  }

}
