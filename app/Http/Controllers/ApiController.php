<?php

namespace App\Http\Controllers;

use App\Services\CustomerService; // 忘れずにuse
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApiController extends Controller
{
    public function getCustomers(CustomerService $customer_service)
    {
        return response()->json($customer_service->getCustomers());
    }

    public function postCustomer(Request $request, CustomerService $customer_service)
    {
        $this->validate($request, ['name' => 'required']);
        $customer_service->postCustomer($request->json('name'));
    }

    public function getCustomer($customer_id, CustomerService $customer_service)
    {
        if (!$customer_service->exists($customer_id)) {
            abort(Response::HTTP_NOT_FOUND);
        }
        return response()->json($customer_service->getCustomer($customer_id));
    }

    public function putCustomer($customer_id, Request $request, CustomerService $customer_service)
    {
        if (!$customer_service->exists($customer_id)) {
            abort(Response::HTTP_NOT_FOUND);
        }
        $customer_service->updateCustomer($customer_id, $request->json('name'));
    }

    public function deleteCustomer($customer_id, CustomerService $customer_service)
    {
        if (!$customer_service->exists($customer_id)) {
            abort(Response::HTTP_NOT_FOUND);
        }
        if ($customer_service->hasReports($customer_id)) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $customer_service->deleteCustomer($customer_id);
    }

    public function getReports(ReportService $report_service)
    {
        return response()->json($report_service->getReports());
    }

    public function postReport(Request $request, ReportService $report_service)
    {
        $rules = [
            'visit_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'detail' => 'required',
        ];
        $this->validate($request, $rules);
        $report_service->postReport($request->only('visit_date', 'customer_id', 'detail'));
    }

    public function getReport($report_id, ReportService $report_service)
    {
        if (!$report_service->exists($report_id)) {
            abort(Response::HTTP_NOT_FOUND);
        }
        return $report_service->getReport($report_id);
    }

    public function putReport($report_id, Request $request, ReportService $report_service)
    {
        if (!$report_service->exists($report_id)) {
            abort(Response::HTTP_NOT_FOUND);
        }
        $report_service->updateReport($report_id, $request->only('visit_date', 'customer_id', 'detail'));
    }

    public function deleteReport($report_id, ReportService $report_service)
    {
        $report_service->deleteReport($report_id);
    }
}
