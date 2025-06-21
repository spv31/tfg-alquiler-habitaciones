import { useI18n } from "#imports";

export type FlashMessageType = "success" | "error" | "info" | "warning";

export interface FlashMessage {
  message: string;
  type: FlashMessageType;
}

export const getFlashMessage = (
  msg: string | undefined,
  t: (key: string) => string
): FlashMessage | null => {
  if (!msg) return null;

  switch (msg) {
    case "property_created":
      return { message: t("properties.created"), type: "success" };
    case "property_updated":
      return {
        message: t("properties.update_success_message"),
        type: "success",
      };
    case "property_deleted":
      return { message: t("properties.detail.propertyDeleted"), type: "info" };
    case "room_created":
      return { message: t("api.success.room_created"), type: "success" };
    case "room_updated":
      return {
        message: t("properties.detail.rooms.roomUpdated"),
        type: "success",
      };
    case "room_deleted":
      return {
        message: t("properties.detail.rooms.roomDeleted"),
        type: "info",
      };
    case "template_created":
      return { message: t("templates.created"), type: "success" };
    case "template_updated":
      return { message: t("templates.updated"), type: "success" };
    case "template_deleted":
      return { message: t("templates.deleted"), type: "info" };
    case "contract_created":
      return {
        message: t("contracts.create_success_message"),
        type: "success",
      };
    case "contract_updated":
      return {
        message: t("contracts.update_success_message"),
        type: "success",
      };
    case "contract_create_failed":
      return { message: t("errors.contractCreateFailed"), type: "error" };
    case "tenant_reassigned":
      return { message: t("tenants.reassigned"), type: "success" };
    case "payment_success":
      return {
        message: t("payments.success_message"),
        type: "success",
      };
    case "payment_cancelled":
      return {
        message: t("payments.cancelled_message"),
        type: "info",
      };
    default:
      return null;
  }
};
