import type { Contract } from "./contract";
import type { Payment } from "./payment";

export interface RentPayment {
  id: number;
  contract: Contract | null;
  period_start: string; // YYYY‑MM‑DD
  period_end: string; // YYYY‑MM‑DD
  due_date: string; // YYYY‑MM‑DD
  amount: number;
  status: "pending" | "paid" | "processing" | "overdue";
  stripe_payment_intent_id: string | null;
  stripe_mandate_id: string | null;
  stripe_checkout_session_id: string | null;
  paid_at: string | null;
  payments?: Payment[];
  created_at: string;
  updated_at: string;
}

export type RentPaymentFilters = Partial<Pick<RentPayment, "status">> & {
  property_id?: number;
  room_id?: number;
  tenant_id?: number;
  from?: string;
  to?: string;
};
