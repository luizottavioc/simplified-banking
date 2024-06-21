<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    use HasFactory;

    protected $table = 'user_types';

    private $defaultTypeNames = [
        'admin' => 'admin',
        'teller' => 'teller',
        'merchant' => 'merchant',
        'usual' => 'usual',
    ];

    public function getAdminType()
    {
        $type = $this->where('type', $this->defaultTypeNames['admin'])->first();
        return $type;
    }

    public function getTellerType()
    {
        $type = $this->where('type', $this->defaultTypeNames['teller'])->first();
        return $type;
    }

    public function getMerchantType()
    {
        $type = $this->where('type', $this->defaultTypeNames['merchant'])->first();
        return $type;
    }

    public function getUsualType()
    {
        $type = $this->where('type', $this->defaultTypeNames['usual'])->first();
        return $type;
    }
}
