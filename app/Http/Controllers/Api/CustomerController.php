<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Room;
use App\Repositories\CustomerRepositoryInterface;
use App\Repositories\RoomRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    protected $customerRepository;
    protected $roomRepository;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        RoomRepositoryInterface $roomRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->roomRepository = $roomRepository;
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            $customer = $this->customerRepository->create($data);

            $this->roomRepository->updateStatus(
                (int) $data['room_id'],
                Room::STATUS_OCCUPIED
            );

            DB::commit();

            return response()->json([
                'message' => 'Customer created successfully',
                'data' => new CustomerResource($customer),
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create customer',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
