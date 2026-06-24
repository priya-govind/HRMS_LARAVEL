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
 * @property int $id
 * @property string $accessory_type_name
 * @property int $accessory_type_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessoryTypes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessoryTypes newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessoryTypes onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessoryTypes query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessoryTypes whereAccessoryTypeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessoryTypes whereAccessoryTypeStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessoryTypes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessoryTypes whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessoryTypes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessoryTypes whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessoryTypes withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccessoryTypes withoutTrashed()
 */
	class AccessoryTypes extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $log_name
 * @property string $description
 * @property string|null $subject_type
 * @property string|null $event
 * @property int|null $subject_id
 * @property string|null $causer_type
 * @property int|null $causer_id
 * @property string|null $properties
 * @property string|null $batch_uuid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activitylog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activitylog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activitylog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activitylog whereBatchUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activitylog whereCauserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activitylog whereCauserType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activitylog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activitylog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activitylog whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activitylog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activitylog whereLogName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activitylog whereProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activitylog whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activitylog whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Activitylog whereUpdatedAt($value)
 */
	class Activitylog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $attribute_name
 * @property int $attribute_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AssetAttributeOptions> $options
 * @property-read int|null $options_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttribute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttribute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttribute onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttribute query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttribute whereAttributeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttribute whereAttributeStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttribute whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttribute whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttribute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttribute whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttribute withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttribute withoutTrashed()
 */
	class AssetAttribute extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $attribute_id
 * @property string $attribute_options
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttributeOptions newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttributeOptions newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttributeOptions onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttributeOptions query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttributeOptions whereAttributeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttributeOptions whereAttributeOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttributeOptions whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttributeOptions whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttributeOptions whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttributeOptions whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttributeOptions withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetAttributeOptions withoutTrashed()
 */
	class AssetAttributeOptions extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $asset_id
 * @property int $attribute_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\AssetAttribute $attribute
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetConfiguration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetConfiguration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetConfiguration query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetConfiguration whereAssetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetConfiguration whereAttributeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetConfiguration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetConfiguration whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetConfiguration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetConfiguration whereUpdatedAt($value)
 */
	class AssetConfiguration extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $item_name
 * @property string $item_type
 * @property int $item_category
 * @property int $item_brand
 * @property string $serial_number
 * @property string $status
 * @property string|null $purchased_amount
 * @property string|null $purchased_date
 * @property string|null $expiry_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ItemAttributeRelation> $ItemConfigurationValues
 * @property-read int|null $item_configuration_values_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AssignAsset> $assignments
 * @property-read int|null $assignments_count
 * @property-read mixed $item_category_name
 * @property-read \App\Models\Brands $itemBrand
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetItems newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetItems newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetItems query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetItems whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetItems whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetItems whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetItems whereItemBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetItems whereItemCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetItems whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetItems whereItemType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetItems wherePurchasedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetItems wherePurchasedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetItems whereSerialNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetItems whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetItems whereUpdatedAt($value)
 */
	class AssetItems extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $asset_type_name
 * @property int $asset_type_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AssetConfiguration> $configurations
 * @property-read int|null $configurations_count
 * @property-read mixed $attributes_with_values
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetTypes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetTypes newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetTypes onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetTypes query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetTypes whereAssetTypeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetTypes whereAssetTypeStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetTypes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetTypes whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetTypes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetTypes whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetTypes withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssetTypes withoutTrashed()
 */
	class AssetTypes extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $asset_item_id
 * @property int $employee_id
 * @property string|null $assigned_at
 * @property string|null $returned_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AssetItems|null $assetItems
 * @property-read \App\Models\User $employee
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssignAsset newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssignAsset newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssignAsset query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssignAsset whereAssetItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssignAsset whereAssignedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssignAsset whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssignAsset whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssignAsset whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssignAsset whereReturnedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssignAsset whereUpdatedAt($value)
 */
	class AssignAsset extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $emp_id
 * @property \Illuminate\Support\Carbon $chkinDate
 * @property \Illuminate\Support\Carbon|null $chkoutDate
 * @property string|null $work_duration
 * @property int|null $working_mode
 * @property string|null $comments
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $timesheet_slot
 * @property string|null $early_checkout_reason
 * @property string|null $late_checkout_reason used when user didn't checkout on current day
 * @property-read \App\Models\User $employee
 * @property-read \App\Models\User $user
 * @property-read \App\Models\WorkingMode|null $workingMode
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereChkinDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereChkoutDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereEarlyCheckoutReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereLateCheckoutReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereTimesheetSlot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereWorkDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereWorkingMode($value)
 */
	class Attendance extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BillType query()
 */
	class BillType extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $employee_name
 * @property string $employee_code
 * @property string $birth_date
 * @property string|null $last_alerted_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BirthdayCalendar newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BirthdayCalendar newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BirthdayCalendar query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BirthdayCalendar whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BirthdayCalendar whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BirthdayCalendar whereEmployeeCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BirthdayCalendar whereEmployeeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BirthdayCalendar whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BirthdayCalendar whereLastAlertedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BirthdayCalendar whereUpdatedAt($value)
 */
	class BirthdayCalendar extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $bot_name
 * @property int $parent_id
 * @property string $command
 * @property int $is_active
 * @property int $support_access
 * @property int $order_by
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $service_name
 * @property string|null $service_method
 * @property array<array-key, mixed>|null $required_fields
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RoleBotPermissions> $RoleBotPermissionss
 * @property-read int|null $role_bot_permissionss_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, BotMenu> $bot_children
 * @property-read int|null $bot_children_count
 * @property-read BotMenu|null $bot_parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $bot_permissions
 * @property-read int|null $bot_permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RoleBotPermissions> $rolePermissions
 * @property-read int|null $role_permissions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotMenu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotMenu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotMenu query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotMenu whereBotName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotMenu whereCommand($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotMenu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotMenu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotMenu whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotMenu whereOrderBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotMenu whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotMenu whereRequiredFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotMenu whereServiceMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotMenu whereServiceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotMenu whereSupportAccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotMenu whereUpdatedAt($value)
 */
	class BotMenu extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $brand_name
 * @property int $brand_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brands newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brands newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brands onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brands query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brands whereBrandName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brands whereBrandStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brands whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brands whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brands whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brands whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brands withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brands withoutTrashed()
 */
	class Brands extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $category_name
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string $url_link
 * @property int $parent_id
 * @property int $is_active_cat
 * @property int $order_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $support_access
 * @property int $allow_bot
 * @property string|null $bot_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Category> $children
 * @property-read int|null $children_count
 * @property-read Category|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RoleCategoryPermission> $roleCategoryPermissions
 * @property-read int|null $role_category_permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RoleCategoryPermission> $rolePermissions
 * @property-read int|null $role_permissions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereAllowBot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereBotName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIsActiveCat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereOrderBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereSupportAccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUrlLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withoutTrashed()
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $sender_id
 * @property int $receiver_id
 * @property string|null $message
 * @property int|null $forwarded_from
 * @property int|null $reply_to
 * @property string|null $attachment_path
 * @property int $is_read
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $receiver
 * @property-read Chat|null $repliedMessage
 * @property-read \App\Models\User $sender
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereAttachmentPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereForwardedFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereReceiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereReplyTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Chat whereUpdatedAt($value)
 */
	class Chat extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $patterns
 * @property string|null $examples
 * @property string|null $roles_allowed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotIntent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotIntent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotIntent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotIntent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotIntent whereExamples($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotIntent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotIntent whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotIntent wherePatterns($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotIntent whereRolesAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotIntent whereUpdatedAt($value)
 */
	class ChatbotIntent extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $chatbot_session_id
 * @property string $sender
 * @property string $content
 * @property string|null $extras
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed>|null $request_payload
 * @property array<array-key, mixed>|null $response_payload
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotMessage whereChatbotSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotMessage whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotMessage whereExtras($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotMessage whereRequestPayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotMessage whereResponsePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotMessage whereSender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotMessage whereUpdatedAt($value)
 */
	class ChatbotMessage extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $chatbot_intent_id
 * @property string $template
 * @property string|null $variables
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotResponse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotResponse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotResponse query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotResponse whereChatbotIntentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotResponse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotResponse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotResponse whereTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotResponse whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotResponse whereVariables($value)
 */
	class ChatbotResponse extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $status
 * @property string|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSession query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSession whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSession whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSession whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSession whereUserId($value)
 */
	class ChatbotSession extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $component_type_name
 * @property int $component_type_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComponentTypes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComponentTypes newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComponentTypes onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComponentTypes query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComponentTypes whereComponentTypeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComponentTypes whereComponentTypeStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComponentTypes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComponentTypes whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComponentTypes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComponentTypes whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComponentTypes withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ComponentTypes withoutTrashed()
 */
	class ComponentTypes extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $certification
 * @property string $cer_image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpCertification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpCertification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpCertification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpCertification whereCerImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpCertification whereCertification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpCertification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpCertification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpCertification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpCertification whereUserId($value)
 */
	class EmpCertification extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $image_path
 * @property string $emp_qualification
 * @property string $emp_marks
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpDocs newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpDocs newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpDocs query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpDocs whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpDocs whereEmpMarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpDocs whereEmpQualification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpDocs whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpDocs whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpDocs whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpDocs whereUserId($value)
 */
	class EmpDocs extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string|null $company_name
 * @property string $yr_experience
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpExperience newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpExperience newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpExperience query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpExperience whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpExperience whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpExperience whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpExperience whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpExperience whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmpExperience whereYrExperience($value)
 */
	class EmpExperience extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $expense_type_name
 * @property int $expense_type_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseItems newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseItems newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseItems query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseItems whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseItems whereExpenseTypeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseItems whereExpenseTypeStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseItems whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseItems whereUpdatedAt($value)
 */
	class ExpenseItems extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $asset_name
 * @property int $asset_type
 * @property int $asset_brand
 * @property string $serial_number
 * @property string $asset_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Brands|null $AssetBrand
 * @property-read \App\Models\ItemType|null $AssetType
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InventoryAssignment> $assignments
 * @property-read int|null $assignments_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereAssetBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereAssetName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereAssetStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereAssetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereSerialNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereUpdatedAt($value)
 */
	class Inventory extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $inventory_id
 * @property int $employee_id
 * @property string|null $assigned_at
 * @property string|null $returned_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $employee
 * @property-read \App\Models\Inventory $inventory
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryAssignment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryAssignment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryAssignment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryAssignment whereAssignedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryAssignment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryAssignment whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryAssignment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryAssignment whereInventoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryAssignment whereReturnedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryAssignment whereUpdatedAt($value)
 */
	class InventoryAssignment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $item_id
 * @property int $attribute_id
 * @property int $option_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AssetAttribute $attribute
 * @property-read \App\Models\AssetAttributeOptions $option
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemAttributeRelation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemAttributeRelation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemAttributeRelation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemAttributeRelation whereAttributeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemAttributeRelation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemAttributeRelation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemAttributeRelation whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemAttributeRelation whereOptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemAttributeRelation whereUpdatedAt($value)
 */
	class ItemAttributeRelation extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $item_type_name
 * @property int $item_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemType whereItemStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemType whereItemTypeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemType whereUpdatedAt($value)
 */
	class ItemType extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $emp_id
 * @property string $from_dt
 * @property string|null $to_dt
 * @property int $leave_type 1 = Leave, 2 = Permission
 * @property string|null $from_time
 * @property string|null $to_time
 * @property string|null $reason
 * @property int|null $approved_by
 * @property int $leave_status 0=waiting for approval,1=approved,2-rejected,3-cancelled by Employee.
 * @property string|null $reason_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $emp_name
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leaveinfo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leaveinfo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leaveinfo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leaveinfo whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leaveinfo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leaveinfo whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leaveinfo whereFromDt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leaveinfo whereFromTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leaveinfo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leaveinfo whereLeaveStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leaveinfo whereLeaveType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leaveinfo whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leaveinfo whereReasonStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leaveinfo whereToDt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leaveinfo whereToTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leaveinfo whereUpdatedAt($value)
 */
	class Leaveinfo extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $transaction_type
 * @property string $trans_amount
 * @property string $transaction_date
 * @property string|null $remarks
 * @property string $available_amt
 * @property string|null $payment_type
 * @property string|null $bill_refer
 * @property int $is_deleted
 * @property int $last_entry
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TransactionAudit> $auditLogs
 * @property-read int|null $audit_logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MonthlyExpenseItems> $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyExpense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyExpense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyExpense query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyExpense whereAvailableAmt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyExpense whereBillRefer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyExpense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyExpense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyExpense whereIsDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyExpense whereLastEntry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyExpense wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyExpense whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyExpense whereTransAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyExpense whereTransactionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyExpense whereTransactionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyExpense whereUpdatedAt($value)
 */
	class MonthlyExpense extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $expense_id
 * @property int $expense_item_id
 * @property string $exp_amount
 * @property int $is_deleted
 * @property-read \App\Models\ExpenseItems $expenseItem
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyExpenseItems newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyExpenseItems newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyExpenseItems query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyExpenseItems whereExpAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyExpenseItems whereExpenseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyExpenseItems whereExpenseItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyExpenseItems whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MonthlyExpenseItems whereIsDeleted($value)
 */
	class MonthlyExpenseItems extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $sender_id
 * @property string $sender_name
 * @property int $receiver_id
 * @property string|null $receiver_name
 * @property string $subject
 * @property string $message
 * @property int $is_read
 * @property string $notify_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereNotifyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereReceiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereReceiverName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereSenderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUpdatedAt($value)
 */
	class Notification extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $notify_type
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifyType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifyType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifyType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifyType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotifyType whereNotifyType($value)
 */
	class NotifyType extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $task_name
 * @property int $project_id
 * @property int $module_id
 * @property string $endDate
 * @property string $task_desc
 * @property int $task_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $created_by
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PMTasksAssign> $assignedEmployees
 * @property-read int|null $assigned_employees_count
 * @property-read \App\Models\User|null $creator
 * @property-read mixed $assigned_employee_names
 * @property-read \App\Models\ProjectModule $modules
 * @property-read \App\Models\ProjectStatus|null $pm_task_status
 * @property-read \App\Models\Projects $project
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PMTasks newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PMTasks newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PMTasks query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PMTasks whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PMTasks whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PMTasks whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PMTasks whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PMTasks whereModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PMTasks whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PMTasks whereTaskDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PMTasks whereTaskName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PMTasks whereTaskStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PMTasks whereUpdatedAt($value)
 */
	class PMTasks extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $task_id
 * @property int $employee_id
 * @property int $emp_task_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ProjectStatus|null $empStatus
 * @property-read \App\Models\User $employee
 * @property-read \App\Models\PMTasks $task
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PMTasksAssign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PMTasksAssign newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PMTasksAssign query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PMTasksAssign whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PMTasksAssign whereEmpTaskStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PMTasksAssign whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PMTasksAssign whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PMTasksAssign whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PMTasksAssign whereUpdatedAt($value)
 */
	class PMTasksAssign extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $permission_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $order_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Roles> $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereOrderBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission wherePermissionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission withoutTrashed()
 */
	class Permission extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $emp_id
 * @property int $assigned_by
 * @property string $module_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermitModule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermitModule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermitModule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermitModule whereAssignedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermitModule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermitModule whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermitModule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermitModule whereModuleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PermitModule whereUpdatedAt($value)
 */
	class PermitModule extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $ticket_type_id
 * @property string $problem_type
 * @property int $problem_type_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProblemType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProblemType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProblemType onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProblemType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProblemType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProblemType whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProblemType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProblemType whereProblemType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProblemType whereProblemTypeActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProblemType whereTicketTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProblemType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProblemType withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProblemType withoutTrashed()
 */
	class ProblemType extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $module_name
 * @property int $proj_id
 * @property string $desc
 * @property-read \App\Models\Projects $projects
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectModule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectModule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectModule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectModule whereDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectModule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectModule whereModuleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectModule whereProjId($value)
 */
	class ProjectModule extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $proj_id
 * @property int $module_id
 * @property int $emp_id
 * @property-read \App\Models\ProjectModule $module
 * @property-read \App\Models\Projects $projects
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectModuleAssign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectModuleAssign newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectModuleAssign query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectModuleAssign whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectModuleAssign whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectModuleAssign whereModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectModuleAssign whereProjId($value)
 */
	class ProjectModuleAssign extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $proj_status_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $emp_set_status
 * @property int $task_set_status
 * @property int $proj_set_status
 * @property int $ticket_set_status
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectStatus onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectStatus whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectStatus whereEmpSetStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectStatus whereProjSetStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectStatus whereProjStatusName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectStatus whereTaskSetStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectStatus whereTicketSetStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectStatus whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectStatus withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectStatus withoutTrashed()
 */
	class ProjectStatus extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $proj_typ_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectType onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectType whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectType whereProjTypName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectType withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectType withoutTrashed()
 */
	class ProjectType extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $proj_type
 * @property string $proj_name
 * @property string|null $proj_desc
 * @property string|null $start_date
 * @property string|null $end_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $proj_status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProjectModuleAssign> $assignments
 * @property-read int|null $assignments_count
 * @property-read \App\Models\ProjectType|null $projtype
 * @property-read \App\Models\ProjectStatus $status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tasks> $tasks
 * @property-read int|null $tasks_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projects newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projects newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projects onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projects query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projects whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projects whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projects whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projects whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projects whereProjDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projects whereProjName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projects whereProjStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projects whereProjType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projects whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projects whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projects withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projects withoutTrashed()
 */
	class Projects extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $punch_date
 * @property string $employee_name
 * @property string $employee_code
 * @property string $team_type
 * @property string $status
 * @property string|null $checkin_time
 * @property string|null $checkout_time
 * @property string|null $duration
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PunchAttendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PunchAttendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PunchAttendance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PunchAttendance whereCheckinTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PunchAttendance whereCheckoutTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PunchAttendance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PunchAttendance whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PunchAttendance whereEmployeeCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PunchAttendance whereEmployeeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PunchAttendance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PunchAttendance wherePunchDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PunchAttendance whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PunchAttendance whereTeamType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PunchAttendance whereUpdatedAt($value)
 */
	class PunchAttendance extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $ticket_type_id
 * @property int|null $problem_type_id
 * @property string $ticket_name
 * @property string|null $ticket_desc
 * @property int $ticket_raised_by
 * @property int $ticket_solved_by
 * @property int $ticket_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TicketAssignMembers> $AssignedTicketMembers
 * @property-read int|null $assigned_ticket_members_count
 * @property-read \App\Models\User|null $TicketOwner
 * @property-read \App\Models\ProblemType|null $problemType
 * @property-read \App\Models\ProjectStatus|null $ticketStatus
 * @property-read \App\Models\TicketType|null $ticketType
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaiseTicket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaiseTicket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaiseTicket query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaiseTicket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaiseTicket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaiseTicket whereProblemTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaiseTicket whereTicketDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaiseTicket whereTicketName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaiseTicket whereTicketRaisedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaiseTicket whereTicketSolvedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaiseTicket whereTicketStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaiseTicket whereTicketTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RaiseTicket whereUpdatedAt($value)
 */
	class RaiseTicket extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $task_id
 * @property int $emp_id
 * @property int $team_id
 * @property int $reopen_type
 * @property int $ctrl_status
 * @property int $task_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReopenedTask newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReopenedTask newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReopenedTask query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReopenedTask whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReopenedTask whereCtrlStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReopenedTask whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReopenedTask whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReopenedTask whereReopenType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReopenedTask whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReopenedTask whereTaskStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReopenedTask whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReopenedTask whereUpdatedAt($value)
 */
	class ReopenedTask extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $roles_id
 * @property int $bot_id
 * @property int $permission_id
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read \App\Models\BotMenu $bot_menu
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleBotPermissions newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleBotPermissions newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleBotPermissions query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleBotPermissions whereBotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleBotPermissions whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleBotPermissions whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleBotPermissions wherePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleBotPermissions whereRolesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleBotPermissions whereUpdatedAt($value)
 */
	class RoleBotPermissions extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $roles_id
 * @property int $category_id
 * @property int $permission_id
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read \App\Models\Category $category
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleCategoryPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleCategoryPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleCategoryPermission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleCategoryPermission whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleCategoryPermission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleCategoryPermission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleCategoryPermission wherePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleCategoryPermission whereRolesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleCategoryPermission whereUpdatedAt($value)
 */
	class RoleCategoryPermission extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RolePageUserPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RolePageUserPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RolePageUserPermission query()
 */
	class RolePageUserPermission extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $role_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RoleCategoryPermission> $categoryPermissions
 * @property-read int|null $category_permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RoleBotPermissions> $roleBotmenuPermissions
 * @property-read int|null $role_botmenu_permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RoleCategoryPermission> $roleCategoryPermissions
 * @property-read int|null $role_category_permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Roles newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Roles newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Roles query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Roles whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Roles whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Roles whereRoleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Roles whereUpdatedAt($value)
 */
	class Roles extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $license_type_name
 * @property int $license_type_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SoftwareLicenses newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SoftwareLicenses newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SoftwareLicenses onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SoftwareLicenses query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SoftwareLicenses whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SoftwareLicenses whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SoftwareLicenses whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SoftwareLicenses whereLicenseTypeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SoftwareLicenses whereLicenseTypeStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SoftwareLicenses whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SoftwareLicenses withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SoftwareLicenses withoutTrashed()
 */
	class SoftwareLicenses extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $task_id
 * @property int $team_id
 * @property int $employee_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $task_info
 * @property int $emp_task_status
 * @property string|null $comments
 * @property int $ctrl_status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TaskAssignEmp> $assignedEmployees
 * @property-read int|null $assigned_employees_count
 * @property-read \App\Models\User|null $employee
 * @property-read \App\Models\Projects|null $project
 * @property-read \App\Models\ProjectStatus|null $status
 * @property-read \App\Models\Tasks|null $task
 * @property-read \App\Models\Teams|null $team
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignEmp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignEmp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignEmp onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignEmp query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignEmp whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignEmp whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignEmp whereCtrlStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignEmp whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignEmp whereEmpTaskStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignEmp whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignEmp whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignEmp whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignEmp whereTaskInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignEmp whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignEmp whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignEmp withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignEmp withoutTrashed()
 */
	class TaskAssignEmp extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $task_id
 * @property int $team_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TeamMembers> $teamMembers
 * @property-read int|null $team_members_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignTeam newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignTeam newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignTeam query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignTeam whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignTeam whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignTeam whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignTeam whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignTeam whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskAssignTeam whereUpdatedAt($value)
 */
	class TaskAssignTeam extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $task_id
 * @property int $uploaded_by
 * @property string $original_name
 * @property string $stored_name
 * @property string $mime_type
 * @property int $size
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskFileUploads newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskFileUploads newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskFileUploads query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskFileUploads whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskFileUploads whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskFileUploads whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskFileUploads whereOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskFileUploads whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskFileUploads whereStoredName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskFileUploads whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskFileUploads whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskFileUploads whereUploadedBy($value)
 */
	class TaskFileUploads extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $task_id
 * @property int $team_id
 * @property int $team_status
 * @property string|null $comments
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ProjectStatus|null $status
 * @property-read \App\Models\Tasks|null $task
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskStatusTeam newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskStatusTeam newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskStatusTeam query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskStatusTeam whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskStatusTeam whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskStatusTeam whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskStatusTeam whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskStatusTeam whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskStatusTeam whereTeamStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaskStatusTeam whereUpdatedAt($value)
 */
	class TaskStatusTeam extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $task_name
 * @property int $proj_typ_id
 * @property int $proj_id
 * @property string $startDate
 * @property string $endDate
 * @property string $team_typ_id
 * @property int $task_status
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $comments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskAssignEmp> $assignedEmployees
 * @property-read int|null $assigned_employees_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $assignedMembers
 * @property-read int|null $assigned_members_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskAssignEmp> $assignedNormalEmployees
 * @property-read int|null $assigned_normal_employees_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Teams> $assignedTeams
 * @property-read int|null $assigned_teams_count
 * @property-read \App\Models\TaskAssignEmp $empTaskStatus
 * @property-read \App\Models\TaskAssignEmp|null $myAssignedInfo
 * @property-read \App\Models\Projects|null $project
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $reporting_employees
 * @property-read int|null $reporting_employees_count
 * @property-read \App\Models\ProjectStatus|null $status
 * @property-read \App\Models\User|null $task_owner
 * @property-read \App\Models\User|null $task_user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskStatusTeam> $teamStatus
 * @property-read int|null $team_status_count
 * @property-read \App\Models\TaskStatusTeam|null $teamTaskStatus
 * @property-read \App\Models\TaskStatusTeam|null $teamTaskStatus_TL
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereProjId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereProjTypId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereTaskName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereTaskStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereTeamTypId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tasks withoutTrashed()
 */
	class Tasks extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $team_id
 * @property int $emp_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $proj_type
 * @property int $ctrl_status
 * @property-read mixed $basic_info
 * @property-read mixed $basic_info_role
 * @property-read \App\Models\ProjectType|null $projType
 * @property-read \App\Models\Teams|null $team
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembers newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembers newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembers query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembers whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembers whereCtrlStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembers whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembers whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembers whereProjType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembers whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamMembers whereUpdatedAt($value)
 */
	class TeamMembers extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $team_typ_name
 * @property string|null $team_color
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $pm_id
 * @property-read \App\Models\User|null $reportingPerson
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Teams> $teams
 * @property-read int|null $teams_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType wherePmId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType whereTeamColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType whereTeamTypName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType withoutTrashed()
 */
	class TeamType extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $team_type
 * @property string $team_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $proj_type
 * @property-read mixed $members_info
 * @property-read mixed $members_info_role
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $members
 * @property-read int|null $members_count
 * @property-read \App\Models\ProjectType|null $projType
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tasks> $tasks
 * @property-read int|null $tasks_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TeamMembers> $teamMembers
 * @property-read int|null $team_members_count
 * @property-read \App\Models\TeamType|null $teamType
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teams newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teams newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teams onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teams query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teams whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teams whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teams whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teams whereProjType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teams whereTeamName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teams whereTeamType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teams whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teams withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teams withoutTrashed()
 */
	class Teams extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $ticket_id
 * @property int $owner_id
 * @property int $assign_mem_id
 * @property string|null $assign_comments comments posted will assigning
 * @property string|null $reply_to posted when completing
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAssignMembers newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAssignMembers newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAssignMembers query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAssignMembers whereAssignComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAssignMembers whereAssignMemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAssignMembers whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAssignMembers whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAssignMembers whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAssignMembers whereReplyTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAssignMembers whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketAssignMembers whereUpdatedAt($value)
 */
	class TicketAssignMembers extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $ticket_type
 * @property int $ticket_type_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketType onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketType whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketType whereTicketType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketType whereTicketTypeActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketType withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TicketType withoutTrashed()
 */
	class TicketType extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $emp_id
 * @property string $create_dt
 * @property string $day
 * @property string $from_time
 * @property string $to_time
 * @property int $project_id
 * @property int|null $module_id
 * @property int|null $task_id
 * @property string|null $custom_task
 * @property int|null $duration
 * @property string|null $comments
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Projects $Projects
 * @property-read \App\Models\User $employee
 * @property-read \App\Models\ProjectModule|null $module
 * @property-read \App\Models\Projects $project
 * @property-read \App\Models\PMTasks|null $task
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet whereCreateDt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet whereCustomTask($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet whereDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet whereFromTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet whereModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet whereToTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet whereUpdatedAt($value)
 */
	class Timesheet extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $emp_id
 * @property string $create_dt
 * @property string $day
 * @property string $from_time
 * @property string $to_time
 * @property int $project_id
 * @property string $module
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Projects $Projects
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet_bkup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet_bkup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet_bkup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet_bkup whereCreateDt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet_bkup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet_bkup whereDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet_bkup whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet_bkup whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet_bkup whereFromTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet_bkup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet_bkup whereModule($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet_bkup whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet_bkup whereToTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timesheet_bkup whereUpdatedAt($value)
 */
	class Timesheet_bkup extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $transaction_type
 * @property string $amount
 * @property string $transaction_date
 * @property string $available_amt
 * @property string|null $payment_type
 * @property string|null $bill_refer
 * @property string|null $remarks
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $is_deleted
 * @property int $last_entry
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TransactionAudit> $auditLogs
 * @property-read int|null $audit_logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TransactionItem> $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereAvailableAmt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereBillRefer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereIsDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereLastEntry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereTransactionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereTransactionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereUpdatedAt($value)
 */
	class Transaction extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $transaction_id
 * @property string $action
 * @property array<array-key, mixed> $original_data
 * @property int $edited_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Transaction $transaction
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionAudit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionAudit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionAudit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionAudit whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionAudit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionAudit whereEditedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionAudit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionAudit whereOriginalData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionAudit whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionAudit whereUpdatedAt($value)
 */
	class TransactionAudit extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $transaction_id
 * @property int $expense_item_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ExpenseItems $expenseItem
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionItem whereExpenseItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionItem whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionItem whereUpdatedAt($value)
 */
	class TransactionItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone_number
 * @property string|null $dob
 * @property string|null $preferred_name
 * @property string|null $emergency_contact_name
 * @property string|null $emergency_contact_number
 * @property string|null $job_designation
 * @property string|null $doj
 * @property string|null $aadhar_no
 * @property string|null $pan_no
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $gender
 * @property string|null $image
 * @property string|null $address
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $emp_status
 * @property string $emp_type
 * @property int $team_type
 * @property int $support_access
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InventoryAssignment> $InventoryAssignments
 * @property-read int|null $inventory_assignments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attendance> $attendances
 * @property-read int|null $attendances_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $blockedUsers
 * @property-read int|null $blocked_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmpCertification> $empCertify
 * @property-read int|null $emp_certify_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmpDocs> $empDocs
 * @property-read int|null $emp_docs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmpExperience> $empExperience
 * @property-read int|null $emp_experience_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $mutedUsers
 * @property-read int|null $muted_users_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Roles> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tasks> $task_members
 * @property-read int|null $task_members_count
 * @property-read \App\Models\TeamType|null $teamType
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TeamMembers> $team_members
 * @property-read int|null $team_members_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Teams> $teams
 * @property-read int|null $teams_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAadharNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDob($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDoj($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmergencyContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmergencyContactNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmpStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmpType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereJobDesignation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePanNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePreferredName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereSupportAccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTeamType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $work_mode_name
 * @property int $mode_status
 * @property string $color
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkingMode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkingMode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkingMode query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkingMode whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkingMode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkingMode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkingMode whereModeStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkingMode whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkingMode whereWorkModeName($value)
 */
	class WorkingMode extends \Eloquent {}
}

