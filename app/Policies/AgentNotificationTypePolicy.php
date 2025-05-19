<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AgentNotificationType;
use Illuminate\Auth\Access\HandlesAuthorization;

class AgentNotificationTypePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_agent::notification::type');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AgentNotificationType $agentNotificationType): bool
    {
        return $user->can('view_agent::notification::type');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_agent::notification::type');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AgentNotificationType $agentNotificationType): bool
    {
        return $user->can('update_agent::notification::type');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AgentNotificationType $agentNotificationType): bool
    {
        return $user->can('delete_agent::notification::type');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_agent::notification::type');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, AgentNotificationType $agentNotificationType): bool
    {
        return $user->can('force_delete_agent::notification::type');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_agent::notification::type');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, AgentNotificationType $agentNotificationType): bool
    {
        return $user->can('restore_agent::notification::type');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_agent::notification::type');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, AgentNotificationType $agentNotificationType): bool
    {
        return $user->can('replicate_agent::notification::type');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_agent::notification::type');
    }
}
