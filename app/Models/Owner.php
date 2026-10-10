<?php
// app/Models/Owner.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    protected $table = 'owners';
    protected $primaryKey = 'owner_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public function pets()
    {
        return $this->hasMany(Pet::class, 'owner_id', 'owner_id');
    }
}
