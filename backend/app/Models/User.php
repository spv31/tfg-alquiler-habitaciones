<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
  /** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory, Notifiable, HasApiTokens, CanResetPassword, HasRoles;

  public function sendPasswordResetNotification($token)
  {
    $url = env('FRONTEND_URL') . "/reset-password?token=$token&email=" . urlencode($this->email);

    $this->notify(new ResetPasswordNotification($url));
  }

  /**
   * The attributes that are mass assignable.
   *
   * @var list<string>
   */
  protected $fillable = [
    'name',
    'email',
    'password',
    'user_type',
    'identifier',
    'role',
    'phone_number',
    'address',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var list<string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  public function properties()
  {
    return $this->hasMany(Property::class);
  }

  public function invitations()
  {
    return $this->hasMany(Invitation::class, 'owner_id');
  }

  public function rentals()
  {
    return $this->hasOne(PropertyTenant::class, 'tenant_id');
  }

  public function profileImage()
  {
    return $this->morphOne(Image::class, 'imageable');
  }

  public function getProfileImageUrlAttribute()
  {
    if (!$this->relationLoaded('profileImage')) {
      $this->load('profileImage');
    }

    return $this->profileImage
      ? route('image.user.show ', ['user' => $this->id, 'filename' => $this->profileImage->image_path])
      : null;
  }

  /**
   * Contract templates created by user
   * 
   * @return \Illuminate\Database\Eloquent\Relations\HasMany<ContractTemplate, User>
   */
  public function contractTemplates(): \Illuminate\Database\Eloquent\Relations\HasMany
  {
    return $this->hasMany(ContractTemplate::class);
  }

  public function contracts()
  {
    return $this->hasMany(Contract::class, 'tenant_id');
  }

  public function contract()
  {
    return $this->hasOne(Contract::class, 'tenant_id')
      ->whereIn('status', ['draft', 'signed_by_owner', 'active'])
      ->latestOfMany();
  }

  public function propertyTenant()   
  {
    return $this->hasOne(PropertyTenant::class, 'tenant_id');
  }
}
