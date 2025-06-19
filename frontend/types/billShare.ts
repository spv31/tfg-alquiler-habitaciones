import type { Tenant } from "./tenant";

export interface BillShare {
  id: number;
  tenant: Tenant | null;
  amount: number;
  status: "pending" | "paid" | "cancelled";
  stripe_payment_intent_id: string | null;
  stripe_checkout_session_id: string | null;
  paid_at: string | null;
  created_at: string;
  updated_at: string;
  utility_bill_id?: number;
}
