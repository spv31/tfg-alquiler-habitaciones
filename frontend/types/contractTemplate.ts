export interface ContractTemplate {
	id: number,
	name: string,
	content: string,
	type: string | null,
	is_default: boolean,
	user_id: number,
	preview_path?: string | null
	created_at: string,
	updated_at: string,
}