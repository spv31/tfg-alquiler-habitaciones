type ContractStatus = "draft" | "signed_by_owner" | "active" | "finished";
type UtilitiesPayer = "tenant" | "owner" | "shared";

export interface Contract {
  id: number;
  contract_template_id: number;
  property_id: number;
  room_id: number;
  tenant_id: number;
  type: string;
  price: number;
  deposit: number;
  utilities_included: boolean;
  utilities_payer: string;
  utilities_proportion: number;
  start_date: string;
  end_date: string | null;
  extension_date: string | null;
  status: "draft" | "signed_by_owner" | "active" | "finished";
  pdf_path: string | null;
  pdf_path_signed: string | null;
  final_content: string | null;
  token_values?: Record<string, any>;
  owner_iban?: string | null;
  tenant_iban?: string | null;
  stripe_payment_method_id?: string | null;
  pdf_path_signed_owner?: string | null;
  pdf_path_signed_tenant?: string | null;
  signed_by_owner_at?: string; // ISO timestamp
  signed_by_tenant_at?: string; // ISO timestamp
  created_at: string;
  updated_at: string;
}

export interface StoreContractPayload {
  contract_template_id: number;
  property_id?: number | null;
  room_id?: number | null;
  tenant_id: number;
  type?: string | null;
  price: number;
  deposit: number;
  utilities_included: boolean;
  utilities_payer?: UtilitiesPayer | null;
  utilities_proportion?: number | null;
  start_date: string;
  end_date?: string | null;
  extension_date?: string | null;
  status: ContractStatus;
  final_content: string;
  token_values: Record<string, string>;
  pdf_path?: string | null;
  pdf_path_signed?: string | null;
}
