<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyDetail;
use App\Models\RentPayment;
use App\Models\Room;
use App\Models\User;
use App\Models\UtilityBill;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatisticsService
{

  protected function mapToFiscalType(string|null $raw): string
  {
    return match ($raw) {
      'full', 'vivienda_habitual', null => 'vivienda_habitual',
      'per_room', 'habitacion' => 'habitacion',
      'turistica' => 'turistica',
      'temporal' => 'temporal',
      default => 'vivienda_habitual',
    };
  }

  /**
   *  Includes KPIs and monthly revenue/expense series.
   *
   * @param User $owner
   * @return array
   */
  public function getDashboardSummary(User $owner): array
  {
    return [
      'propertyCount' => $this->countProperties($owner),
      'rentedProperties' => $this->countRentedProperties($owner),
      'roomTotal' => $this->countRooms($owner),
      'roomRented' => $this->countRentedRooms($owner),
      'netWorth' => $this->calculateNetWorth($owner),
      'incomeMonthly' => $this->getMonthlyIncome($owner),
      'expenseMonthly' => $this->getMonthlyExpenses($owner),
    ];
  }

  /**
   * Returns days rented, income and expenses broken down.   
   * 
   * @param User $owner
   * @param Carbon $from
   * @param Carbon $to
   * @return array
   */
  public function getFiscalData(User $owner, Carbon $from, Carbon $to): array
  {
    return [
      'daysByType' => $this->calculateDaysRentedByType($owner, $from, $to),
      'incomeByType' => $this->calculateIncomeByType($owner, $from, $to),
      'expensesByCat' => $this->calculateExpensesByCategory($owner, $from, $to),
    ];
  }

  protected function countProperties(User $owner): int
  {
    return $owner->properties()->count();
  }

  protected function countRentedProperties(User $owner): int
  {
    return $owner->properties()->whereHas('tenants')->count();
  }

  protected function countRooms(User $owner): int
  {
    return Room::whereHas('property', fn($q) => $q->where('user_id', $owner->id))->count();
  }

  protected function countRentedRooms(User $owner): int
  {
    return Room::whereHas('tenant')
      ->whereHas('property', fn($q) => $q->where('user_id', $owner->id))
      ->count();
  }

  protected function calculateNetWorth(User $owner): float
  {
    return (float) PropertyDetail::whereHas('property', fn($q) => $q->where('user_id', $owner->id))
      ->sum('estimated_value');
  }

  /**
   * Returns income (rent payments) per month.
   *    
   * @param User $owner
   * @return array
   */
  protected function getMonthlyIncome(User $owner): array
  {
    return RentPayment::selectRaw('DATE_FORMAT(period_start, "%Y-%m") as period, SUM(amount) as total')
      ->where('status', 'paid')
      ->whereHas('contract.property', fn($q) => $q->where('user_id', $owner->id))
      ->groupBy('period')
      ->orderBy('period')
      ->pluck('total', 'period')
      ->map(fn($v) => (float)$v)
      ->toArray();
  }


  /**
   * Return expenses per month
   *
   * @param User $owner
   * @return array
   */
  protected function getMonthlyExpenses(User $owner): array
  {
    return UtilityBill::selectRaw('DATE_FORMAT(issue_date, "%Y-%m") as period, SUM(total_amount) as total')
      ->where('owner_id', $owner->id)
      ->groupBy('period')
      ->orderBy('period')
      ->pluck('total', 'period')
      ->map(fn($v) => (float) $v)
      ->toArray();
  }

  /**
   * Days rented
   *
   * @param User $owner
   * @param Carbon $from
   * @param Carbon $to
   * @return array
   */
  protected function calculateDaysRentedByType(User $owner, Carbon $from, Carbon $to): array
  {
    $contracts = Contract::with('property')
      ->whereHas('property', fn($q) => $q->where('user_id', $owner->id))
      ->where(function ($q) use ($from, $to) {
        $q->whereBetween('start_date', [$from, $to])
          ->orWhereBetween('end_date', [$from, $to])
          ->orWhere(fn($q) => $q->where('start_date', '<=', $from)->where('end_date', '>=', $to));
      })
      ->get();

    $daysByType = [];

    foreach ($contracts as $c) {
      $type  = $this->mapToFiscalType($c->type);
      $start = $c->start_date->greaterThan($from) ? $c->start_date : $from;
      $end   = $c->end_date && $c->end_date->lessThan($to) ? $c->end_date : $to;
      $days  = $start->diffInDays($end) + 1;
      $daysByType[$type] = ($daysByType[$type] ?? 0) + $days;
    }

    return $daysByType;
  }

  /**
   * Income by type of rental
   *
   * @param User $owner
   * @param Carbon $from
   * @param Carbon $to
   * @return array
   */
  protected function calculateIncomeByType(User $owner, Carbon $from, Carbon $to): array
  {
    $rentPayments = RentPayment::with('contract.property')
      ->where('status', 'paid')
      ->whereBetween('period_start', [$from, $to])
      ->whereHas('contract.property', fn($q) => $q->where('user_id', $owner->id))
      ->get()
      ->groupBy(fn($rp) => $this->mapToFiscalType($rp->contract->type ?? null));

    return $rentPayments->map(fn($group) => (float) $group->sum('amount'))->toArray();
  }

  /**
   * Returns expenses by category
   *
   * @param User $owner
   * @param Carbon $from
   * @param Carbon $to
   * @return array
   */
  protected function calculateExpensesByCategory(User $owner, Carbon $from, Carbon $to): array
  {
    $bills = UtilityBill::with('property')
      ->where(function ($q) use ($from, $to) {
        $q->whereBetween('period_start', [$from, $to])
          ->orWhereBetween('period_end',   [$from, $to])
          ->orWhere(
            fn($qq) =>
            $qq->where('period_start', '<=', $from)
              ->where('period_end',   '>=', $to)
          );
      })->get();

    $daysByType = $this->calculateDaysRentedByType($owner, $from, $to);

    $expenses = [];

    foreach ($bills as $bill) {
      $type = $this->mapToFiscalType($bill->property->rental_type);

      $daysPeriod = $bill->period_start->diffInDays($bill->period_end) + 1;
      $daysRented = $daysByType[$type] ?? array_sum($daysByType);
      $proportion = $daysPeriod > 0 ? min(1, $daysRented / $daysPeriod) : 0;

      $amountImputed = (float) $bill->total_amount * $proportion;
      $expenses[$bill->category][$type] = ($expenses[$bill->category][$type] ?? 0) + $amountImputed;
    }

    foreach ($expenses as $category => &$data) {
      $totalCat = array_sum($data);

      $updated = [];
      foreach ($data as $type => $amount) {
        $updated[$type] = [
          'amount'     => round($amount, 2),
          'percentage' => $totalCat > 0 ? round($amount / $totalCat * 100, 2) : 0.0,
        ];
      }

      $data = $updated;
    }

    return $expenses;
  }
}
