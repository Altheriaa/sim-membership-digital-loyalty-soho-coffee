<?php

namespace App\Models;

use App\Role;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'role' => Role::class,
            'is_active' => 'boolean',
        ];
    }

    public function hasRole(Role ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Count the users that hold each role, keyed by the role value.
     *
     * @return array<string, int>
     */
    public static function countByRole(): array
    {
        return static::query()
            ->toBase()
            ->selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role')
            ->map(fn ($total): int => (int) $total)
            ->all();
    }

    /**
     * Limit the query to one role. A null role leaves the query untouched.
     *
     * @param  Builder<User>  $query
     */
    #[Scope]
    protected function ofRole(Builder $query, ?Role $role): void
    {
        $query->when($role, fn (Builder $query, Role $role) => $query->where('role', $role->value));
    }

    /**
     * Match a term against the name or the email, treating %, _ and ! in the term literally.
     *
     * @param  Builder<User>  $query
     */
    #[Scope]
    protected function matching(Builder $query, ?string $term): void
    {
        $query->when($term, function (Builder $query, string $term) {
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%';

            $query->where(function (Builder $query) use ($pattern) {
                $query->whereRaw("name like ? escape '!'", [$pattern])
                    ->orWhereRaw("email like ? escape '!'", [$pattern]);
            });
        });
    }
}
