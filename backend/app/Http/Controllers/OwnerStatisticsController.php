<?php

namespace App\Http\Controllers;

use App\Services\StatisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OwnerStatisticsController extends Controller
{
  public function index(Request $request, StatisticsService $statistics)
  {
    $owner = $request->user();

    return response()->json(
      $statistics->getDashboardSummary($owner)
    );
  }

  public function fiscal(Request $request, StatisticsService $statistics)
  {
    $owner = $request->user();

    $from = $request->query('from', Carbon::create(null, 1, 1));
    $to   = $request->query('to', Carbon::create(null, 12, 31));

    return response()->json(
      $statistics->getFiscalData($owner, Carbon::parse($from), Carbon::parse($to))
    );
  }
}
