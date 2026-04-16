<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'username', 'email', 'password', 'module', 'force_password_reset', 'student_id', 'faculty_id', 'registrar_id', 'applicant_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function registrar()
    {
        return $this->belongsTo(Registrar::class);
    }

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    public function accountProfile()
    {
        return $this->hasOne(UserAccountProfile::class);
    }

    public function accountStatus()
    {
        return $this->hasOne(UserAccountStatus::class);
    }

    public function notificationDeliveries()
    {
        return $this->hasMany(NotificationDelivery::class);
    }

    public function activeNotificationDeliveries()
    {
        return $this->hasMany(NotificationDelivery::class)
            ->whereNull('dismissed_at');
    }

    public function updatedRooms()
    {
        return $this->hasMany(Room::class, 'updated_by_user_id');
    }

    public function roomCourseAssignments()
    {
        return $this->hasMany(RoomCourseAssignment::class, 'assigned_by_user_id');
    }
}
