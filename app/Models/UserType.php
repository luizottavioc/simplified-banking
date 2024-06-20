<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    use HasFactory;

    protected $table = 'user_types';

    private $typeAdminName = 'admin';
    private $typeMerchantName = 'merchant';
    private $typeUsualName = 'usual';

    public function getAdminType() {
        $type = $this->where('type', $this->typeAdminName)->first();
        return $type;
    }

    public function getMerchantType() {
        $type = $this->where('type', $this->typeMerchantName)->first();
        return $type;
    }

    public function getUsualType() {
        $type = $this->where('type', $this->typeUsualName)->first();
        return $type;
    }
}
