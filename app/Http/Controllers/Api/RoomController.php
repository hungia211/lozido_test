<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttachPriceItemRequest;
use App\Http\Requests\DetachPriceItemRequest;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Resources\RoomResource;
use App\Repositories\RoomRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;


class RoomController extends Controller
{
    protected $roomRepository;

    public function __construct(RoomRepositoryInterface $roomRepository)
    {
        $this->roomRepository = $roomRepository;
    }

    public function showAll(): JsonResponse
    {
        try {
            $rooms = $this->roomRepository->getAll();

            return response()->json([
                'message' => 'Get room list successfully',
                'data' => RoomResource::collection($rooms),
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to get room list',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $room = $this->roomRepository->findById((int) $id);

            if (!$room) {
                return response()->json([
                    'message' => 'Room not found',
                ], 404);
            }

            $room = $this->roomRepository->loadRelations($room);

            return new RoomResource($room);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to get room detail',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function store(StoreRoomRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            $room = $this->roomRepository->create($data);

            if (!empty($data['price_item_ids'])) {
                $this->roomRepository->attachPriceItems($room, $data['price_item_ids']);
            }

            $room = $this->roomRepository->loadRelations($room);

            DB::commit();

            return response()->json([
                'message' => 'Room created successfully',
                'data' => new RoomResource($room),
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create room',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $deleted = $this->roomRepository->softDelete((int) $id);

            if (!$deleted) {
                return response()->json([
                    'message' => 'Room not found',
                ], 404);
            }

            return response()->json([
                'message' => 'Room deleted successfully',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to delete room',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function attachPriceItem(AttachPriceItemRequest $request, $id): JsonResponse
    {
        try {
            $room = $this->roomRepository->findById((int) $id);

            if (!$room) {
                return response()->json([
                    'message' => 'Room not found',
                ], 404);
            }

            $this->roomRepository->attachOnePriceItem(
                $room,
                (int) $request->price_item_id
            );

            $room = $this->roomRepository->loadRelations($room);

            return response()->json([
                'message' => 'Price item attached successfully',
                'data' => new RoomResource($room),
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to attach price item',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function detachPriceItem(DetachPriceItemRequest $request, $id): JsonResponse
    {
        try {
            $room = $this->roomRepository->findById((int) $id);

            if (!$room) {
                return response()->json([
                    'message' => 'Room not found',
                ], 404);
            }

            $this->roomRepository->detachOnePriceItem(
                (int) $id,
                (int) $request->price_item_id
            );

            $room = $this->roomRepository->findById((int) $id);
            $room = $this->roomRepository->loadRelations($room);

            return response()->json([
                'message' => 'Price item detached successfully',
                'data' => new RoomResource($room),
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to detach price item',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
