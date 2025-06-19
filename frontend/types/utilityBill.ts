import type { BillShare } from "./billShare";
import type { Owner } from "./owner";
import type { Property } from "./property";
import type { Room } from "./room";

export interface UtilityBill {
  id: number;
  property: Property | null;
  room: Room | null;
  owner: Owner | null;
  issue_date: string;      // YYYY‑MM‑DD
  due_date: string;        // YYYY‑MM‑DD
  period_start: string | null;
  period_end: string | null;
  total_amount: number;
  category: "utility" | "general" | "tax";
  remit_to_tenants: boolean,      
  description: string | null;
  attachment_url: string | null;
  status: "pending" | "split" | "settled";
  bill_shares?: BillShare[];
  created_at: string;      
  updated_at: string;     
}

export type UtilityBillFilters = Partial<Pick<UtilityBill, "status" | "category">> & {
  property_id?: number;
  room_id?: number;
  from?: string;
  to?: string;
};