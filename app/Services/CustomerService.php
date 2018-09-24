<?php

namespace App\Services;

use App\Customer;
use App\Report;

class CustomerService
{
    public function getCustomers()
    {
        return Customer::query()->select(['id', 'name'])->get();
    }

    public function postCustomer($name)
    {
        $customer = new Customer();
        $customer->name = $name;
        $customer->save();
    }

    public function getCustomer($customer_id)
    {
        return Customer::query()
            ->where('id', '=', $customer_id)
            ->select(['id', 'name'])
            ->first();
    }

    public function updateCustomer($customer_id, $name)
    {
        /** @var Customer $customer */
        $customer = Customer::query()->find($customer_id);
        $customer->name = $name;
        $customer->save();
    }

    public function exists($customer_id)
    {
        return Customer::query()->where('id', '=', $customer_id)->exists();
    }

    public function deleteCustomer($customer_id)
    {
        Customer::query()->where('id', '=', $customer_id)->delete();
    }

    public function hasReports($customer_id)
    {
        return Report::query()->where('customer_id', '=', $customer_id)->exists();
    }
}
