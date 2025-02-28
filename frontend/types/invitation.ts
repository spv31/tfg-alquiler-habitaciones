export interface Invitation {
  id: number,
  email: string,
  status: "pending" | "accepted" | "expired",
  created_at: string,
  updated_at: string,
}