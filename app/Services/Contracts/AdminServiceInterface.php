<?php

namespace App\Services\Contracts;

interface AdminServiceInterface {
    public function listUsers();
    public function listRestaurants();
    public function listRiders();
    public function listOrders();
    public function getAnalytics();
}
