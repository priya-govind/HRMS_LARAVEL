<?php

namespace App\Models;


// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Roles;
use App\Models\EmpDocs;
use Illuminate\Database\Eloquent\SoftDeletes;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'address',
        'image',
        'emp_status',
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
    public function hasPermission($category_id, $permission_id)
{
    // Replace this with your actual permission logic
    // For example, check if the user has a role or permission stored in DB

    // Example logic (customize as needed):
    return Category::SecureUser($category_id, $permission_id);
}

    public function roles()
    {
        return $this->belongsToMany(Roles::class, 'roles_user', 'user_id', 'roles_id');
    }
    

public function hasRole($role)
{
    return $this->roles->contains('name', $role);
}

public function empDocs()
{
    return $this->hasMany(EmpDocs::class, 'user_id', 'id'); // Define foreign and local keys properly.
}
public function empCertify()
{
    return $this->hasMany(EmpCertification::class, 'user_id', 'id'); // Define foreign and local keys properly.
}
public function empExperience()
{
    return $this->hasMany(EmpExperience::class, 'user_id', 'id'); // Define foreign and local keys properly.
}
public function task_members()
{
    return $this->belongsToMany(Tasks::class, 'task_assign_emp', 'employee_id', 'task_id');
}
public function team_members(){
    return $this->hasMany(TeamMembers::class, 'emp_id', 'id');  
}
public function teams() {
    return $this->belongsToMany(Teams::class, 'team_members', 'user_id', 'team_id');
}
public function attendances()
{
    return $this->hasMany(Attendance::class, 'emp_id', 'id');
}
public function teamType(){
       return $this->belongsTo(TeamType::class, 'team_type', 'id');
}
public function mutedUsers(){
        return $this->belongsToMany(User::class, 'muted_users', 'user_id', 'muted_user_id')->withTimestamps();
}
public function blockedUsers(){
    return $this->belongsToMany(User::class, 'blocked_users', 'user_id', 'blocked_user_id');
}
 public function InventoryAssignments() {
        return $this->hasMany(InventoryAssignment::class);
    }

}
