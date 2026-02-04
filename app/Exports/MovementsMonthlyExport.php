<?php

namespace App\Exports;

use App\Models\Movement;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class MovementsMonthlyExport implements FromView
{
    public function __construct(
        protected int $year,
        protected int $month,
        protected ?int $churchId = null
    ) {
    }

    public function view(): View
    {
        $query = Movement::query()
            ->whereYear('date', $this->year)
            ->whereMonth('date', $this->month)
            ->orderBy('date');

        // Usuarios globales pueden filtrar por iglesia
        if ($this->churchId) {
            $query->where('church_id', $this->churchId);
        }

        $movements = $query->get();

        $totalIncome = $movements->where('type', 'income')->sum('amount');
        $totalExpense = $movements->where('type', 'expense')->sum('amount');

        return view('exports.movements', [
            'movements' => $movements,
            'year' => $this->year,
            'month' => $this->month,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $totalIncome - $totalExpense,
        ]);
    }
}