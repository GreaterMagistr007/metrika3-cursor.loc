<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Events\CabinetCreated;
use App\Events\CabinetDeleted;
use App\Events\CabinetUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CabinetRequest;
use App\Http\Resources\CabinetResource;
use App\Http\Resources\CabinetUserResource;
use App\Models\Cabinet;
use App\Models\User;
use App\Services\CabinetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

final class CabinetController extends Controller
{
    public function __construct(
        private readonly CabinetService $cabinetService
    ) {}

    /**
     * Get list of user's cabinets.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $cabinets = $user->cabinets()->with('owner')->get();

            return response()->json([
                'cabinets' => CabinetResource::collection($cabinets)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch cabinets', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Ошибка получения списка кабинетов',
                'error_code' => 'CABINETS_FETCH_FAILED'
            ], 500);
        }
    }

    /**
     * Create a new cabinet.
     */
    public function store(CabinetRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $cabinet = $this->cabinetService->createCabinet(
                $user,
                $request->validated()['name'],
                $request->validated()['description'] ?? null
            );

            // Fire event for audit logging
            CabinetCreated::dispatch(
                $user,
                $cabinet,
                $request->ip(),
                $request->userAgent()
            );

            return response()->json([
                'message' => 'Кабинет успешно создан',
                'cabinet' => new CabinetResource($cabinet)
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create cabinet', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Ошибка создания кабинета',
                'error_code' => 'CABINET_CREATE_FAILED'
            ], 500);
        }
    }

    /**
     * Get cabinet details.
     */
    public function show(Request $request, Cabinet $cabinet): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if user has access to this cabinet
            if (!$user->cabinets()->where('cabinets.id', $cabinet->id)->exists()) {
                return response()->json([
                    'message' => 'Доступ к кабинету запрещен',
                    'error_code' => 'CABINET_ACCESS_DENIED'
                ], 403);
            }

            $cabinet->load('owner');
            $cabinet->load(['cabinetUsers' => function ($query) {
                $query->with(['user', 'permissions']);
            }]);

            return response()->json([
                'cabinet' => new CabinetResource($cabinet),
                'users' => CabinetUserResource::collection($cabinet->cabinetUsers)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch cabinet details', [
                'user_id' => $request->user()?->id,
                'cabinet_id' => $cabinet->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Ошибка получения данных кабинета',
                'error_code' => 'CABINET_FETCH_FAILED'
            ], 500);
        }
    }

    /**
     * Update cabinet.
     */
    public function update(CabinetRequest $request, Cabinet $cabinet): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if user is owner of this cabinet
            if ($cabinet->owner_id !== $user->id) {
                return response()->json([
                    'message' => 'Только владелец может изменять кабинет',
                    'error_code' => 'CABINET_OWNER_REQUIRED'
                ], 403);
            }

            // Store original values for audit
            $originalData = $cabinet->only(['name', 'description']);
            
            $cabinet->update([
                'name' => $request->validated()['name'],
                'description' => $request->validated()['description'] ?? null,
            ]);

            // Calculate changes for audit
            $changes = [];
            foreach ($originalData as $key => $value) {
                if ($cabinet->$key !== $value) {
                    $changes[$key] = [
                        'old' => $value,
                        'new' => $cabinet->$key
                    ];
                }
            }

            // Fire event for audit logging
            if (!empty($changes)) {
                CabinetUpdated::dispatch(
                    $user,
                    $cabinet,
                    $changes,
                    $request->ip(),
                    $request->userAgent()
                );
            }

            return response()->json([
                'message' => 'Кабинет успешно обновлен',
                'cabinet' => new CabinetResource($cabinet)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update cabinet', [
                'user_id' => $request->user()?->id,
                'cabinet_id' => $cabinet->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Ошибка обновления кабинета',
                'error_code' => 'CABINET_UPDATE_FAILED'
            ], 500);
        }
    }

    /**
     * Delete cabinet.
     */
    public function destroy(Request $request, Cabinet $cabinet): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if user is owner of this cabinet
            if ($cabinet->owner_id !== $user->id) {
                return response()->json([
                    'message' => 'Только владелец может удалить кабинет',
                    'error_code' => 'CABINET_OWNER_REQUIRED'
                ], 403);
            }

            // Fire event for audit logging before deletion
            CabinetDeleted::dispatch(
                $user,
                $cabinet,
                $request->ip(),
                $request->userAgent()
            );

            $this->cabinetService->deleteCabinet($cabinet);

            return response()->json([
                'message' => 'Кабинет успешно удален'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete cabinet', [
                'user_id' => $request->user()?->id,
                'cabinet_id' => $cabinet->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Ошибка удаления кабинета',
                'error_code' => 'CABINET_DELETE_FAILED'
            ], 500);
        }
    }
}
