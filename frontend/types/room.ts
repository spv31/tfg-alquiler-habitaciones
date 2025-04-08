import type { Tenant } from "./tenant";
import type { Invitation } from "./invitation";

export interface Room {
  id: number,
  property_id: number,
  room_number: number,
  description?: string,
  rental_price: number,
  status: "available" | "unavailable" | "occupied",

  main_image_url: string,
  images: string[],

  tenant?: Tenant,
  invitations?: Invitation[],

  created_at: string,
  updated_at: string,
}

export interface RoomsResponse {
  rooms: Room[];
  warning?: {
    key: string;
    parms?: any;
  };
}
