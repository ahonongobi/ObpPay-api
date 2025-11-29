<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'obp_id',
        'card_cvv',
        'avatar',
        'fcm_token',
        'role',
    ];

    

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $appends = ['avatar_url'];

    public function getAvatarUrlAttribute()
    {
        if (!$this->avatar) {
            return "https://i.postimg.cc/nhqCkh77/default-avatar-icon-of-social-media-user-vector.jpg";
        
        }

        return asset('storage/' . $this->avatar);
    }


    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transactions::class);
    }

    public function loanRequests()
    {
        return $this->hasMany(Loanrequests::class);
    }

    public function scoreEvents()
    {
        return $this->hasMany(UserScore::class);
    }

    public function kyc()
    {
        return $this->hasMany(Kyc::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {

            // --- PHONE AUTO POUR ADMIN ---
            if (empty($user->phone)) {
                $user->phone = 'admin-' . rand(100000, 999999);
            }

            // --- OBP-ID AUTO POUR ADMIN ---
            if (empty($user->obp_id)) {
                // Format exemple : OBP-A12345
                $user->obp_id = 'OBP-A' . rand(10000, 99999);
            }
        });
    }
}
