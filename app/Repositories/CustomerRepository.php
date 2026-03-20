<?php

namespace App\Repositories;

use App\Models\Customer;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function create(array $data)
    {
        return Customer::create([
            'room_id' => $data['room_id'],
            'name' => $data['name'],
            'phone' => $data['phone'],
        ]);
    }
}
