<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Cabinet;
use App\Models\Message;
use App\Services\MessageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class UserDeletionService
{
    public function __construct(
        private readonly MessageService $messageService
    ) {}

    /**
     * Delete user with all related data and notify other users.
     */
    public function deleteUserWithCascade(User $user): array
    {
        $deletedData = [
            'user' => $user,
            'cabinets' => [],
            'notified_users' => [],
        ];

        DB::transaction(function () use ($user, &$deletedData) {
            // Get all cabinets owned by the user
            $ownedCabinets = $user->ownedCabinets()->get();
            $deletedData['cabinets'] = $ownedCabinets->toArray();

            // Get all users who will be affected by cabinet deletion
            $affectedUsers = collect();
            foreach ($ownedCabinets as $cabinet) {
                $cabinetUsers = $cabinet->users()->where('user_id', '!=', $user->id)->get();
                $affectedUsers = $affectedUsers->merge($cabinetUsers);
            }

            // Send notifications to affected users before deletion
            $this->notifyUsersAboutCabinetDeletion($ownedCabinets, $affectedUsers->unique('id'));

            // Delete all owned cabinets first (this will cascade delete cabinet_user relationships)
            foreach ($ownedCabinets as $cabinet) {
                $cabinet->delete();
            }

            // Remove user from all remaining cabinets where they are not owner
            $user->cabinets()->detach();

            // Force delete any remaining cabinet_user relationships (safety net)
            \DB::table('cabinet_user')->where('user_id', $user->id)->delete();

            // Delete user messages
            $user->userMessages()->delete();

            // Delete audit logs related to this user
            $user->auditLogs()->delete();

            // Finally delete the user
            $user->delete();

            $deletedData['notified_users'] = $affectedUsers->unique('id')->toArray();
        });

        return $deletedData;
    }

    /**
     * Notify users about cabinet deletion.
     */
    private function notifyUsersAboutCabinetDeletion($cabinets, $users): void
    {
        if ($users->isEmpty()) {
            return;
        }

        foreach ($cabinets as $cabinet) {
            $message = "Кабинет '{$cabinet->name}' был удален, так как его владелец был удален из системы.";
            
            try {
                // Send system message to all users of the cabinet
                $this->messageService->sendPersistent(
                    'warning',
                    $message,
                    $users->pluck('id')->toArray(),
                    'Удаление кабинета'
                );

                Log::info("Notified users about cabinet deletion", [
                    'cabinet_id' => $cabinet->id,
                    'cabinet_name' => $cabinet->name,
                    'affected_users' => $users->pluck('id')->toArray(),
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to notify users about cabinet deletion", [
                    'cabinet_id' => $cabinet->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Get summary of what will be deleted.
     */
    public function getDeletionSummary(User $user): array
    {
        $ownedCabinets = $user->ownedCabinets()->get();
        $affectedUsers = collect();

        foreach ($ownedCabinets as $cabinet) {
            $cabinetUsers = $cabinet->users()->where('user_id', '!=', $user->id)->get();
            $affectedUsers = $affectedUsers->merge($cabinetUsers);
        }

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
            ],
            'owned_cabinets' => $ownedCabinets->map(function ($cabinet) {
                return [
                    'id' => $cabinet->id,
                    'name' => $cabinet->name,
                    'description' => $cabinet->description,
                ];
            }),
            'affected_users' => $affectedUsers->unique('id')->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                ];
            }),
            'total_cabinets' => $ownedCabinets->count(),
            'total_affected_users' => $affectedUsers->unique('id')->count(),
        ];
    }
}
