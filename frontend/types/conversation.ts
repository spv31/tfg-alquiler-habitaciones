export interface Conversation {
  id: number;
  owner_id: number;
  tenant_id: number;
  property_id: number | null;
  created_at: string;
  updated_at: string;
}
