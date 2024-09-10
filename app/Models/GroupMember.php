<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role',
        'group_id',
    ];

    /**
     * Get the user associated with the group member.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the group that this member belongs to.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
