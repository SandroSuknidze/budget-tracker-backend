<?php

namespace App\Http\Controllers;

use App\Imports\CurrencyImport;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    public function index(): JsonResponse
    {
        $data = Excel::toArray(new CurrencyImport(), storage_path('app/currencies.xlsx'));

        return response()->json($data);
    }
}
