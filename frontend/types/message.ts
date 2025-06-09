export interface Message {
  id: number;
  body: string;
  sender_id: number;
  sent_at: string; 
  sender_name: string; 
  metadata?: Record<string, any> | null;
  read_at?: string | null;
}
