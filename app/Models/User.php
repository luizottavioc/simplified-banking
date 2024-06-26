<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'cpf',
        'cnpj',
        'password',
        'user_type_id',
        'wallet',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'wallet' => 'integer',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
        ];
    }

    public function userType(): BelongsTo
    {
        return $this->belongsTo(UserType::class, 'user_type_id');
    }

    public function getUserById($id): User|null
    {
        return $this->find($id);
    }

    public function registerUser(array $userData): User
    {
        $userData['password'] = bcrypt($userData['password']);
        $user = $this->create($userData);

        return $user;
    }

    public function incrementUserWallet(int $idUser, int $value): User
    {
        $user = $this->getUserById($idUser);
        $user->wallet = $user->wallet + $value;
        $user->save();

        return $user;
    }

    public function decrementUserWallet(int $idUser, int $value): User
    {
        $user = $this->getUserById($idUser);
        $user->wallet = $user->wallet - $value;
        $user->save();

        return $user;
    }
}
