export type PropertyStatus =
  | "available"
  | "unavailable"
  | "occupied"
  | "partially_occupied"
  | string;
export type RentalType = "full" | "per_room" | string;

export const statusBadgeClasses = (status: PropertyStatus): string => {
  switch (status) {
    case "available":
      return "bg-green-100 text-green-800";
    case "unavailable":
      return "bg-red-100 text-red-800";
    case "occupied":
      return "bg-yellow-100 text-yellow-800";
    case "partially_occupied":
      return "bg-blue-100 text-blue-800";
    default:
      return "bg-gray-200 text-gray-700";
  }
};

export const statusLabel = (
  status: PropertyStatus,
  t: (key: string, fallback?: string) => string
): string => {
  return t(`badges.status.${status}`, "Desconocido");
};

export const rentalBadgeClasses = (type: RentalType): string => {
  switch (type) {
    case "per_room":
      return "bg-accent text-dark";
    case "full":
      return "bg-purple text-dark";
    default:
      return "bg-gray-300 text-black";
  }
};

export const rentalTypeLabel = (
  type: RentalType,
  t: (key: string, fallback?: string) => string
): string => {
  return t(`badges.rental_type.${type}`, "Desconocido");
};

export type ContractType = 
  | "vivienda_habitual" 
  | "habitacional_temporal" 
  | "habitacion" 
  | "vacacional" 
  | "turistico" 
  | string;

export const contractTypeBadgeClasses = (type: ContractType): string => {
  switch (type) {
    case "vivienda_habitual":
      return "bg-green-100 text-green-800";
    case "habitacional_temporal":
      return "bg-blue-100 text-blue-800";
    case "habitacion":
      return "bg-purple-100 text-purple-800";
    case "vacacional":
      return "bg-yellow-100 text-yellow-800";
    case "turistico":
      return "bg-indigo-100 text-indigo-800";
    default:
      return "bg-gray-200 text-gray-700";
  }
};

export const contractTypeLabel = (type: ContractType): string => {
  const labels: Record<string, string> = {
    "vivienda_habitual": "Vivienda habitual",
    "habitacional_temporal": "Temporal",
    "habitacion": "Habitación",
    "vacacional": "Vacacional",
    "turistico": "Turístico"
  };
  return labels[type] || "Otro tipo";
};