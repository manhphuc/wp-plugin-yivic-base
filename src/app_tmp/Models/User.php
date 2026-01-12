<?php

declare(strict_types=1);

namespace Yivic_Base\App\Models;

use Yivic_Base\App\Support\Passport\Has_Api_Tokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * User model for the table `wp_users` of WordPress
 * @property bigint $ID
 * @property string $user_login
 * @property string $user_nicename
 * @property string $user_email
 * @property string $user_url
 * @property string $user_registered
 * @property string $user_activation_key
 * @property int $user_status
 * @property string $display_name
 * @property string $remember_token
 * @property tinyint $spam
 *
 * @package Yivic_Base\App\Models
 */
class User extends Authenticatable {
	use Has_Api_Tokens;
	use HasFactory;
	use Notifiable;

	protected $hidden = [
		'user_pass',
		'user_activation_key',
	];

	protected $fillable = [
		'ID',
		'user_login',
		'user_pass',
		'user_nicename',
		'user_email',
		'user_url',
		'user_registered',
		'user_activation_key',
		'user_status',
		'display_name',
		'spam',
		'remember_token',
	];

	protected $primaryKey = 'ID';
}