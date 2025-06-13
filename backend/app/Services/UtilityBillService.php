<?php

namespace App\Services;

use App\Models\UtilityBill;
use Auth;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UtilityBillService
{
	public function __construct(private UploadFilesService $files) {}

	public function list(array $filters = []): Collection
	{
		$q = UtilityBill::query()
			->with(['property', 'room', 'billShares'])
			->where('owner_id', Auth::id());

		$q->when($filters['property_id'] ?? null, fn($q, $v) => $q->where('property_id', $v));
		$q->when($filters['room_id'] ?? null, fn($q, $v) => $q->where('room_id', $v));
		$q->when($filters['status'] ?? null, fn($q, $v) => $q->where('status', $v));
		$q->when($filters['from'] ?? null, fn($q, $v) => $q->whereDate('issue_date', '>=', $v));
		$q->when($filters['to'] ?? null, fn($q, $v) => $q->whereDate('issue_date', '<=', $v));

		return $q->orderByDesc('issue_date')->get();
	}

	/**
	 * Creates a new utility bill and upload attachment if provided
	 */
	public function create(array $data): UtilityBill
	{
		DB::beginTransaction();
		try {
			if (($data['category'] ?? null) === 'utility') {
				$issue = Carbon::parse($data['issue_date']);
				$data['period_start'] = $data['period_start']
					?? $issue->copy()->startOfMonth()->toDateString();
				$data['period_end']   = $data['period_end']
					?? $issue->copy()->endOfMonth()->toDateString();
			}

			if (!empty($data['attachment'])) {
				$data['attachment_path'] = $this->files->storeFile(
					$data['attachment'],
					'utility-bills',
					$data['attachment_extension'] ?? 'pdf'
				);
			}

			$data['owner_id'] = Auth::id();
			$data['status']   = 'pending';

			$bill = UtilityBill::create($data);
			app(BillShareService::class)->autoSplit(bill: $bill);

			DB::commit();
			return $bill->load('billShares');
		} catch (Exception $e) {
			DB::rollBack();
			throw $e;
		}
	}

	/**
	 * Retrieve a utility bill with its shares, ensuring ownership
	 */
	public function find(int $id): UtilityBill
	{
		$bill = UtilityBill::with('billShares')->find($id);
		if (!$bill || $bill->owner_id !== Auth::id()) {
			throw new ModelNotFoundException();
		}

		return $bill;
	}

	/**
	 * Updates an existing utility bill and replace attachment if new one provided
	 */
	public function update(UtilityBill $bill, array $data): UtilityBill
	{
		DB::beginTransaction();
		try {
			if ((isset($data['category']) && $data['category'] === 'utility')
				|| isset($data['issue_date'])
			) {
				$issueDate = isset($data['issue_date'])
					? Carbon::parse($data['issue_date'])
					: $bill->issue_date;
				$data['period_start'] = $data['period_start']
					?? $issueDate->copy()->startOfMonth()->toDateString();
				$data['period_end']   = $data['period_end']
					?? $issueDate->copy()->endOfMonth()->toDateString();
			}

			if (!empty($data['attachment'])) {
				if ($bill->attachment_path) {
					$this->files->deleteFile($bill->attachment_path);
				}
				$data['attachment_path'] = $this->files->storeFile(
					$data['attachment'],
					'utility-bills',
					$data['attachment_extension'] ?? 'pdf'
				);
			}

			$bill->update($data);

			DB::commit();
			return $bill->refresh();
		} catch (Exception $e) {
			DB::rollBack();
			throw $e;
		}
	}

	/**
	 * Delete a utility bill and its attachment if exists
	 *
	 * @param \App\Models\UtilityBill $bill
	 * @return void
	 */
	public function delete(UtilityBill $bill): void
	{
		DB::beginTransaction();
		try {
			if ($bill->attachment_path) {
				$this->files->deleteFile($bill->attachment_path);
			}

			$bill->delete();
			DB::commit();
		} catch (Exception $e) {
			DB::rollBack();
			throw $e;
		}
	}

	/**
	 * Method for statistics (amount associated to owner and tenant)
	 */

	/**
	 * Summary of breakdown
	 * 
	 * @param \App\Models\UtilityBill $bill
	 * @return array{owner_share: float|int, tenant_share: mixed}
	 */
	public function breakdown(UtilityBill $bill): array
	{
		$tenantTotal = $bill->billShares()->sum('amount');
		$ownerTotal  = $bill->total_amount - $tenantTotal;

		return [
			'tenant_share' => $tenantTotal,
			'owner_share'  => $ownerTotal,
		];
	}
}
