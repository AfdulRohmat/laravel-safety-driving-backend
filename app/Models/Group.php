<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'created_by', // Assuming foreign key column name for createdBy
    ];

    /**
     * Get the user who created the group.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the members of the group.
     */
    public function members()
    {
        return $this->hasMany(GroupMember::class);
    }

    /**
     * Get the trips for the group.
     */
    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
}
