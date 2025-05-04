import type { Contract } from "./contract";

export interface Tenant {
  id: number,
  name: string;
  email: string;
  user_type: "individual" | "company";
  profile_picture: string;
  phone_number: string;
  room_id?: string | null,
  contract?: Contract | null,
}

export interface TenantCollection {
  data: Tenant[];
}