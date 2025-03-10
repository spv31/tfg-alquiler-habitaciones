
export type PropertyStatus = "available" | "unavailable" | "occupied" | "partially_occupied" | string;
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

export const statusLabel = (status: PropertyStatus): string => {
  switch (status) {
    case "available":
      return "Disponible";
    case "unavailable":
      return "No disponible";
    case "occupied":
      return "Ocupada";
    case "partially_occupied":
      return "Parcial";
    default:
      return "Desconocido";
  }
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

export const rentalTypeLabel = (type: RentalType): string => {
  switch (type) {
    case "full":
      return "Completa";
    case "per_room":
      return "Por habitaciones";
    default:
      return "Desconocido";
  }
};