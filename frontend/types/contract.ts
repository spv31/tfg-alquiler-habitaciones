export interface Contract {
	id: number,
	contract_template_id: number,
	property_id: number,
	room_id: number,
	tenant_id: number,
	type: string,
	price: number,
	deposit: number,
	utilities_included: boolean,
	utilities_payer: string,
	utilities_proportion: number,
	start_date: string
  end_date: string | null
  extension_date: string | null
  status: 'pending_signature' | 'active' | 'finished'
  pdf_path: string | null
  pdf_path_signed: string | null
  final_content: string | null
  token_values?: Record<string, any>
  created_at: string
  updated_at: string
}