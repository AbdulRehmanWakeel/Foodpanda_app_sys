<?php

namespace App\Services\Contracts;

interface AdminServiceInterface
{
     
    public function listUsers(array $filters = []);
    public function listRestaurants(array $filters = []);
    public function listRiders(array $filters = []);
    public function listOrders(array $filters = []);
    public function getAnalytics();

     
    public function createUser(array $data);
    public function updateUser(int $id, array $data);
    public function deleteUser(int $id);

    
    public function createRestaurant(array $data);
    public function updateRestaurant(int $id, array $data);
    public function deleteRestaurant(int $id);
    public function approveRestaurant(int $id);
    public function rejectRestaurant(int $id);

     
    public function createRider(array $data);
    public function updateRider(int $id, array $data);
    public function deleteRider(int $id);
    public function verifyRider(int $id);
    public function rejectRider(int $id);
}
