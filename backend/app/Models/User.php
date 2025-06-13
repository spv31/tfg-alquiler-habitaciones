<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
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
    'stripe_account_id',
    'stripe_customer_id'
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
      'stripe_account_id' => 'string',
      'stripe_customer_id'=>'string',
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

  public function getProfileImageFilenameAttribute(): ?string
  {
    if (!$this->relationLoaded('profileImage')) {
      $this->load('profileImage');
    }

    return $this->profileImage?->image_path;
  }

  public function getProfileImageUrlAttribute(): ?string
  {
    $filename = $this->profile_image_filename;
    return $filename
      ? route('image.user.show', ['user' => $this->id, 'filename' => $filename])
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

  public function utilityBills()
  {
    return $this->hasMany(UtilityBill::class, 'owner_id');
  }

  /**
   * Bill shares for which you are responsible as tenant
   */
  public function billShares()
  {
    return $this->hasMany(BillShare::class, 'tenant_id');
  }

  /**
   * Payments made per user / tenant   
   */
  public function payments()
  {
    return $this->hasManyThrough(
      Payment::class,
      BillShare::class,
      'tenant_id',
      'bill_share_id',
      'id',
      'id'
    );
  }

  /**
   * Rentpayments made per tenant
   *
   * @return void
   */
  public function rentPayments()
  {
    return $this->hasManyThrough(
      RentPayment::class,
      Contract::class,
      'tenant_id',      
      'contract_id',   
      'id',
      'id'
    );
  }
}
