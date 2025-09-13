<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Events\OwnershipTransferred;
use App\Events\UserInvited;
use App\Events\UserRemoved;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\InviteUserRequest;
use App\Http\Resources\CabinetUserResource;
use App\Models\Cabinet;
use App\Models\User;
use App\Services\CabinetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class CabinetUserController extends Controller
{
    public function __construct(
        private readonly CabinetService $cabinetService
    ) {}

    /**
     * Invite user to cabinet.
     */
    public function invite(InviteUserRequest $request, Cabinet $cabinet): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if user is owner of this cabinet
            if ($cabinet->owner_id !== $user->id) {
                return response()->json([
                    'message' => 'Только владелец может приглашать пользователей',
                    'error_code' => 'CABINET_OWNER_REQUIRED'
                ], 403);
            }

            $inviteResult = $this->cabinetService->inviteUserToCabinet(
                $cabinet,
                $request->validated()['phone'],
                $request->validated()['role']
            );

            if ($inviteResult['success']) {
                // Fire event for audit logging
                UserInvited::dispatch(
                    $user,
                    $inviteResult['invited_user'],
                    $cabinet,
                    $request->validated()['role'],
                    $request->ip(),
                    $request->userAgent()
                );

                return response()->json([
                    'message' => $inviteResult['message'],
                    'cabinet_user' => new CabinetUserResource($inviteResult['cabinet_user'])
                ], 201);
            } else {
                return response()->json([
                    'message' => $inviteResult['message'],
                    'error_code' => $inviteResult['error_code'] ?? 'INVITE_FAILED'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Failed to invite user to cabinet', [
                'user_id' => $request->user()?->id,
                'cabinet_id' => $cabinet->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Ошибка приглашения пользователя',
                'error_code' => 'INVITE_ERROR'
            ], 500);
        }
    }

    /**
     * Remove user from cabinet.
     */
    public function remove(Request $request, Cabinet $cabinet, User $user): JsonResponse
    {
        try {
            $currentUser = $request->user();

            // Check if current user is owner of this cabinet
            if ($cabinet->owner_id !== $currentUser->id) {
                return response()->json([
                    'message' => 'Только владелец может удалять пользователей',
                    'error_code' => 'CABINET_OWNER_REQUIRED'
                ], 403);
            }

            // Check if trying to remove owner
            if ($cabinet->owner_id === $user->id) {
                return response()->json([
                    'message' => 'Нельзя удалить владельца кабинета',
                    'error_code' => 'CANNOT_REMOVE_OWNER'
                ], 400);
            }

            // Fire event for audit logging before removal
            UserRemoved::dispatch(
                $currentUser,
                $user,
                $cabinet,
                $request->ip(),
                $request->userAgent()
            );

            $this->cabinetService->removeUserFromCabinet($cabinet, $user);

            return response()->json([
                'message' => 'Пользователь успешно удален из кабинета'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to remove user from cabinet', [
                'user_id' => $request->user()?->id,
                'cabinet_id' => $cabinet->id,
                'target_user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Ошибка удаления пользователя',
                'error_code' => 'REMOVE_USER_ERROR'
            ], 500);
        }
    }

    /**
     * Transfer cabinet ownership.
     */
    public function transferOwnership(Request $request, Cabinet $cabinet): JsonResponse
    {
        try {
            $currentUser = $request->user();

            // Check if current user is owner of this cabinet
            if ($cabinet->owner_id !== $currentUser->id) {
                return response()->json([
                    'message' => 'Только владелец может передать права владения',
                    'error_code' => 'CABINET_OWNER_REQUIRED'
                ], 403);
            }

            $request->validate([
                'new_owner_phone' => 'required|string|regex:/^\+[1-9]\d{1,14}$/',
            ]);

            $transferResult = $this->cabinetService->transferOwnership(
                $cabinet,
                $request->input('new_owner_phone')
            );

            if ($transferResult['success']) {
                // Fire event for audit logging
                OwnershipTransferred::dispatch(
                    $currentUser,
                    $transferResult['new_owner'],
                    $cabinet,
                    $request->ip(),
                    $request->userAgent()
                );

                return response()->json([
                    'message' => $transferResult['message'],
                    'cabinet' => new CabinetUserResource($transferResult['cabinet_user'])
                ]);
            } else {
                return response()->json([
                    'message' => $transferResult['message'],
                    'error_code' => $transferResult['error_code'] ?? 'TRANSFER_FAILED'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Failed to transfer cabinet ownership', [
                'user_id' => $request->user()?->id,
                'cabinet_id' => $cabinet->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Ошибка передачи прав владения',
                'error_code' => 'TRANSFER_ERROR'
            ], 500);
        }
    }
}
