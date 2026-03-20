<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePriceItemRequest;
use App\Http\Resources\PriceItemResource;
use App\Repositories\PriceItemRepositoryInterface;
use Illuminate\Http\JsonResponse;


class PriceItemController extends Controller
{
    protected $priceItemRepository;

    public function __construct(PriceItemRepositoryInterface $priceItemRepository)
    {
        $this->priceItemRepository = $priceItemRepository;
    }

    public function show(): JsonResponse
    {
        try {
            $priceItems = $this->priceItemRepository->getAll();

            return response()->json([
                'message' => 'Get price item list successfully',
                'data' => PriceItemResource::collection($priceItems),
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to get price item list',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function store(StorePriceItemRequest $request): JsonResponse
    {
        $priceItem = $this->priceItemRepository->create($request->validated());

        return response()->json([
            'message' => 'Price item created successfully',
            'data' => new PriceItemResource($priceItem),
        ], 201);
    }

    public function destroy($id): JsonResponse
    {
        try {
            $priceItem = $this->priceItemRepository->findById((int) $id);

            if (!$priceItem) {
                return response()->json([
                    'message' => 'Price item not found',
                ], 404);
            }

            $usedRoomCount = $this->priceItemRepository->countRoomsUsingPriceItem((int) $id);

            if ($usedRoomCount > 0) {
                return response()->json([
                    'message' => 'Cannot delete this price item because some rooms are using it',
                ], 400);
            }

            $this->priceItemRepository->delete((int) $id);

            return response()->json([
                'message' => 'Price item deleted successfully',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to delete price item',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
