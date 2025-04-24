<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contract newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contract newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contract query()
 */
	class Contract extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContractTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContractTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContractTemplate query()
 */
	class ContractTemplate extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $imageable_id
 * @property string $imageable_type
 * @property string $image_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $imageable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereImageableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereImageableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image whereUpdatedAt($value)
 */
	class Image extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $email
 * @property string $token
 * @property int $rentable_id
 * @property string $rentable_type
 * @property int $owner_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $owner
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $rentable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation expireOld()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereRentableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereRentableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereUpdatedAt($value)
 */
	class Invitation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $address
 * @property string $cadastral_reference
 * @property string $description
 * @property string $rental_type
 * @property string $status
 * @property int $total_rooms
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PropertyDetail|null $details
 * @property-read mixed $images_url
 * @property-read string $main_image_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Image> $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invitation> $invitations
 * @property-read int|null $invitations_count
 * @property-read \App\Models\User $owner
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Room> $rooms
 * @property-read int|null $rooms_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PropertyTenant> $tenants
 * @property-read int|null $tenants_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereCadastralReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereRentalType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereTotalRooms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereUserId($value)
 */
	class Property extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $property_id
 * @property string|null $purchase_price
 * @property int|null $is_financed
 * @property string|null $mortgage_cost
 * @property string|null $purchase_taxes
 * @property string|null $renovation_cost
 * @property string|null $furniture_cost
 * @property string|null $purchase_date
 * @property string|null $estimated_value
 * @property string|null $annual_insurance_cost
 * @property string|null $annual_property_tax
 * @property string|null $annual_community_fees
 * @property string|null $annual_waste_tax
 * @property string|null $income_tax_percentage
 * @property string|null $annual_repair_percentage
 * @property string|null $rental_price
 * @property string|null $property_size
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Property $property
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyDetail whereAnnualCommunityFees($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyDetail whereAnnualInsuranceCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyDetail whereAnnualPropertyTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyDetail whereAnnualRepairPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyDetail whereAnnualWasteTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyDetail whereEstimatedValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyDetail whereFurnitureCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyDetail whereIncomeTaxPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyDetail whereIsFinanced($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyDetail whereMortgageCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyDetail wherePropertyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyDetail wherePropertySize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyDetail wherePurchaseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyDetail wherePurchasePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyDetail wherePurchaseTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyDetail whereRenovationCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyDetail whereRentalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyDetail whereUpdatedAt($value)
 */
	class PropertyDetail extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $rentable_id
 * @property string $rentable_type
 * @property int $tenant_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $rentable
 * @property-read \App\Models\User $tenant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyTenant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyTenant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyTenant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyTenant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyTenant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyTenant whereRentableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyTenant whereRentableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyTenant whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PropertyTenant whereUpdatedAt($value)
 */
	class PropertyTenant extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $property_id
 * @property int $room_number
 * @property string $description
 * @property string $rental_price
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed|null $images_url
 * @property-read string $main_image_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Image> $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invitation> $invitations
 * @property-read int|null $invitations_count
 * @property-read \App\Models\Property $property
 * @property-read \App\Models\PropertyTenant|null $tenant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room wherePropertyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereRentalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereRoomNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereUpdatedAt($value)
 */
	class Room extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $user_type
 * @property string $identifier
 * @property string $role
 * @property string $phone_number
 * @property string $address
 * @property-read mixed $profile_image_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invitation> $invitations
 * @property-read int|null $invitations_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Image|null $profileImage
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Property> $properties
 * @property-read int|null $properties_count
 * @property-read \App\Models\PropertyTenant|null $rentals
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUserType($value)
 */
	class User extends \Eloquent {}
}

