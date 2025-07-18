import type { Owner } from "./owner";
import type { PropertyDetail } from "./propertyDetail";
import type { User } from "./user";

export interface PropertyCollection {
  data: Property[];
  links: any;
  meta: any;
}

export interface Property {
  id: number,
  address: string,
  cadastral_reference: string,
  description: string,
  rental_type: "full" | "per_room",
  status: "available" | "occupied" | "unavailable" | "partially_occupied",
  total_rooms: number,

  main_image_url: string;
  images: string[],

  details?: PropertyDetail,
  owner?: Owner,

  created_at: string,
  updated_at: string,
}

export interface CreatePropertyResponse {
  message_key: string;
  property: Property;
}
