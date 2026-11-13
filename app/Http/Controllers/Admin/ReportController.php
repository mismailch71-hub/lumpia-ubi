<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        $totalRevenue = 0;
        $completedOrdersCount = 0;
        $reports = collect();
        $monthlyRevenue = array_fill(1, 12, 0);

        if (Schema::hasTable('orders')) {
            $query = DB::table('orders')
                ->leftJoin('users as customers', 'orders.user_id', '=', 'customers.id')
                ->leftJoin('users as couriers', 'orders.courier_id', '=', 'couriers.id')
                ->select(
                    'orders.*',
                    'customers.name as customer_name',
                    'couriers.name as courier_name'
                );

            if ($startDate && $endDate) {
                $query->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }

            $reports = $query->orderBy('orders.created_at', 'desc')->get();

            // Hitung Total Pendapatan (Selesai + Dalam Pengiriman/Diproses agar statistik terisi)
            $totalRevenue = DB::table('orders')
                ->whereIn('status', ['selesai', 'dalam_pengiriman', 'diproses'])
                ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                    $q->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                })
                ->sum('total_price') ?? 0;

            // Hitung Total Transaksi Aktif/Selesai
            $completedOrdersCount = DB::table('orders')
                ->whereIn('status', ['selesai', 'dalam_pengiriman', 'diproses'])
                ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                    $q->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                })
                ->count();

            // Data Grafik Omset per Bulan
            $rawMonthlyData = DB::table('orders')
                ->select(DB::raw('MONTH(created_at) as month'), DB::raw('SUM(total_price) as total'))
                ->whereIn('status', ['selesai', 'dalam_pengiriman', 'diproses'])
                ->whereYear('created_at', date('Y'))
                ->groupBy(DB::raw('MONTH(created_at)'))
                ->pluck('total', 'month')
                ->toArray();

            foreach ($rawMonthlyData as $month => $total) {
                $monthlyRevenue[$month] = (float) $total;
            }
        }

        return view('admin.reports.index', compact(
            'totalRevenue',
            'completedOrdersCount',
            'reports',
            'startDate',
            'endDate',
            'monthlyRevenue'
        ));
    }
}