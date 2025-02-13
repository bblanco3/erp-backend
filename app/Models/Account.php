<?php
    
    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\MorphMany;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;
    use Laravel\Sanctum\HasApiTokens;
    use Illuminate\Support\Facades\Hash;

    class Account extends Authenticatable
    {
        use HasApiTokens, HasFactory, Notifiable;

        /**
         * The connection name for the model.
         *
         * @var string
         */
        protected $connection = 'master';

        /**
         * The database table used by the model.
         *
         * @var string
         */
        protected $table = 'accounts';

        /**
         * The attributes that are mass assignable.
         *
         * @var array<int, string>
         */
        protected $fillable = [
            'name',
            'email',
            'password',
            'default_user_id',
            'is_active',
            'is_deleted',
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
            'is_active' => 'boolean',
        ];

        /**
         * Get the connection resolver instance.
         *
         * @return \Illuminate\Database\ConnectionResolverInterface
         */
        public static function resolveConnection($connection = null)
        {
            return static::$resolver->connection('master');
        }

        /**
         * Get all users associated with this account.
         * One account can have multiple users.
         * 
         * @return \Illuminate\Database\Eloquent\Relations\HasMany
         */
        public function get_users(): HasMany
        {
            return $this->hasMany(User::class);
        }

        /**
         * Get the current default user associated with this account.
         * 
         * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
         */
        public function get_current_user(): BelongsTo
        {
            return $this->belongsTo(User::class, 'default_user_id');
        }

        /**
         * Get the tenant through the current user.
         */
        public function tenant()
        {
            return $this->get_current_user()->tenant();
        }

        /**
         * Get the full information about the account including related data.
         * 
         * @return array
         */
        public function getFullInfoAttribute(): array
        {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'current_user' => $this->get_current_user(),
                'users' => $this->get_users(),
                'is_active' => $this->is_active,
            ];
        }

        public function default_user()
        {
            return $this->get_users()->where('id', $this->default_user_id)->first();
        }

        public function get_default_user_with_tenant()
        {
            return $this->get_users()
                ->with('tenant')
                ->where('is_default', true)
                ->first();
        }

        /**
         * Get all tenant IDs associated with this account's users
         */
        public function get_tenant_list()
        {
            return $this->get_users()
                ->select('tenant_id')
                ->distinct()
                ->pluck('tenant_id');
        }

        /**
         * Get all contacts associated with this account.
         */
        public function get_contacts(): MorphMany
        {
            return $this->morphMany(Contact::class, 'contactable');
        }

        /**
         * Get all active contacts associated with this account.
         */
        public function get_active_contacts(): MorphMany
        {
            return $this->morphMany(Contact::class, 'contactable')
                ->where('is_active', true)
                ->where('is_deleted', false);
        }
    }
