<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Employee
 */
class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'department_team_id',
        'position_id',
        'is_kam',
        'is_bdm'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->hasOne(DepartmentTeam::class,'id','department_team_id');
    }

    public function position()
    {
        return $this->hasOne(Position::class,'id','position_id');
    }
}
