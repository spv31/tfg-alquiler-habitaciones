export interface InvitationCollection {
  data: Invitation[],
  links: any,
  meta: any,
}

export interface Invitation {
  id: number,
  email: string,
  status: "pending" | "accepted" | "expired",
  created_at: string,
  updated_at: string,
}

export interface CreateInvitationResponse {
  message_key: string;
  invitation: Invitation;
}