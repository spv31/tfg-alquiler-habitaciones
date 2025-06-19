import type { BillShare } from "./billShare";
import type { RentPayment } from "./rentPayment";

export interface Payment {
  id: number;
  amount: number;
  method: "stripe" | "manual_transfer";
  stripe_payment_intent_id: string | null;
  paid_at: string | null;
  bill_share?: BillShare;
  rent_payment?: RentPayment;
  created_at: string;
  updated_at: string;
}

export type PaymentFilters = {
  property_id?: number;
  status?: "pending" | "paid" | "processing" | "manual";
  from?: string;
  to?: string;
};
