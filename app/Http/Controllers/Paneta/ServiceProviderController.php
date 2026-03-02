<?php

namespace App\Http\Controllers\Paneta;

use App\Http\Controllers\Controller;
use App\CrossBorderTransactionIntent;
use Illuminate\Http\Request;

class ServiceProviderController extends Controller
{
    public function reports()
    {
        $providerId = auth()->user()->fx_provider_id ?? 8; // Get from authenticated user
        
        $growthRate = $this->calculateGrowthRate($providerId);
        $tradesByMonth = $this->getTradesByMonth($providerId);
        
        return view('paneta.service-provider.reports', compact('growthRate', 'tradesByMonth'));
    }

    private function calculateGrowthRate($providerId): float
    {
        $currentMonth = CrossBorderTransactionIntent::where('fx_provider_id', $providerId)
            ->where('status', 'executed')
            ->whereMonth('created_at', now()->month)
            ->sum('source_amount'); // Fixed: changed from 'amount' to 'source_amount'

        $lastMonth = CrossBorderTransactionIntent::where('fx_provider_id', $providerId)
            ->where('status', 'executed')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('source_amount'); // Fixed: changed from 'amount' to 'source_amount'

        return $lastMonth > 0 ? round((($currentMonth - $lastMonth) / $lastMonth) * 100, 2) : 0;
    }

    private function getTradesByMonth($providerId): array
    {
        $trades = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $total = CrossBorderTransactionIntent::where('fx_provider_id', $providerId)
                ->where('status', 'executed')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('source_amount'); // Fixed: changed from 'amount' to 'source_amount'
            
            $trades[] = [
                'month' => $month->format('M Y'),
                'total' => $total
            ];
        }
        
        return $trades;
    }
}
