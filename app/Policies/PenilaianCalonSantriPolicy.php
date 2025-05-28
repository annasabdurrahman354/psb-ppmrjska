<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PenilaianCalonSantri;
use Illuminate\Auth\Access\HandlesAuthorization;

class PenilaianCalonSantriPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_penilaian::calon::santri');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PenilaianCalonSantri $penilaianCalonSantri): bool
    {
        return $user->can('view_penilaian::calon::santri');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_penilaian::calon::santri');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PenilaianCalonSantri $penilaianCalonSantri): bool
    {
        return $user->can('update_penilaian::calon::santri');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PenilaianCalonSantri $penilaianCalonSantri): bool
    {
        return $user->can('delete_penilaian::calon::santri');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_penilaian::calon::santri');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, PenilaianCalonSantri $penilaianCalonSantri): bool
    {
        return $user->can('force_delete_penilaian::calon::santri');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_penilaian::calon::santri');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, PenilaianCalonSantri $penilaianCalonSantri): bool
    {
        return $user->can('restore_penilaian::calon::santri');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_penilaian::calon::santri');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, PenilaianCalonSantri $penilaianCalonSantri): bool
    {
        return $user->can('replicate_penilaian::calon::santri');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_penilaian::calon::santri');
    }
}
