<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationCategory extends Model
{
    protected $table = 'organization_categories' ;
    
    protected $fillable = ['name', 'emoji', 'description'];

    public function organizations()
    {
        return $this->hasMany(Organization::class);
    }
}
