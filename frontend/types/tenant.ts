import type { Contract } from "./contract";

export interface Tenant {
  id: number,
  name: string;
  email: string;
  user_type: "individual" | "company";
  profile_picture: string;
  phone_number: string;
  property_id: number;
  room_id?: number | null;
  contract?: Contract | null,
}

export interface TenantCollection {
  data: Tenant[];
}