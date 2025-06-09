import { defineStore } from "pinia";
import { getCsrfToken } from "~/utils/auth";
import { tryCatch } from "~/utils/tryCatch";
import type { Conversation } from "~/types/conversation";
import type { Message } from "~/types/message";
import type Echo from "laravel-echo";

export const useChatStore = defineStore(
  "chat",
  () => {
    const config = useRuntimeConfig();
    const apiBaseUrl = config.app.apiBaseURL;

    const conversations = ref<Conversation[]>([]);
    const activeConversation = ref<Conversation | null>(null);
    const messages = ref<Message[]>([]);

    const loading = ref(false);
    const alreadyLoaded = ref(false);

    const { $echo } = useNuxtApp();
    const echo = $echo as Echo<any>;

    const subscribeToChannel = (conversationId: number) => {
      echo
        .private(`conversation.${conversationId}`)
        .listen(".message.sent", (e: Message) => {
          if (activeConversation.value?.id === conversationId) {
            messages.value.push(e);
          }
          const idx = conversations.value.findIndex(
            (c) => c.id === conversationId
          );
          if (idx !== -1) {
            conversations.value[idx].updated_at = e.sent_at;
          }
        });
    };

    const fetchConversations = async () => {
      if (alreadyLoaded.value || loading.value) return;
      const { data, error } = await tryCatch<Conversation[]>(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");
        return $fetch<Conversation[]>(`${apiBaseUrl}/conversations`, {
          method: "GET",
          credentials: "include",
          headers: { "X-XSRF-TOKEN": csrf, Accept: "application/json" },
        });
      }, loading);
      if (error) throw error;
      if (!data) throw new Error("No data received");
      conversations.value = data;
      alreadyLoaded.value = true;
      conversations.value.forEach((c) => subscribeToChannel(c.id));
    };

    // Carga mensajes de una conversación dada
    const fetchMessages = async (conversationId: number) => {
      const { data, error } = await tryCatch<Message[]>(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");
        return $fetch<Message[]>(
          `${apiBaseUrl}/conversations/${conversationId}/messages`,
          {
            method: "GET",
            credentials: "include",
            headers: { "X-XSRF-TOKEN": csrf, Accept: "application/json" },
          }
        );
      }, loading);
      if (error) throw error;
      if (!data) throw new Error("No data received");
      messages.value = data;
    };

    // Activa una conversación y carga sus mensajes
    const setActiveConversation = async (conversation: Conversation) => {
      activeConversation.value = conversation;
      await fetchMessages(conversation.id);
      subscribeToChannel(conversation.id);
    };

    // Envia un mensaje a la conversación activa
    const sendMessage = async (body: string) => {
      if (!activeConversation.value) return;
      const { data, error } = await tryCatch<Message>(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");
        return $fetch<Message>(
          `${apiBaseUrl}/conversations/${activeConversation.value!.id}/messages`,
          {
            method: "POST",
            credentials: "include",
            body: { body },
            headers: { "X-XSRF-TOKEN": csrf, Accept: "application/json" },
          }
        );
      }, loading);
      if (error) throw error;
      if (!data) throw new Error("No data received");
      messages.value.push(data);
    };

    // Abre (get or create) una conversación y la activa
    const openConversation = async (ownerId: number, tenantId: number) => {
      // Cargar conversaciones si es primera vez
      if (!alreadyLoaded.value) await fetchConversations();
      // Buscar conversación existente
      let convo = conversations.value.find(
        (c) => c.owner_id === ownerId && c.tenant_id === tenantId
      );
      // Crear si no existe
      if (!convo) {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");
        convo = await $fetch<Conversation>(`${apiBaseUrl}/conversations`, {
          method: "POST",
          credentials: "include",
          body: { owner_id: ownerId, tenant_id: tenantId },
          headers: { "X-XSRF-TOKEN": csrf, Accept: "application/json" },
        });
        conversations.value.push(convo);
      }
      // Activar conversación
      await setActiveConversation(convo);
      return convo;
    };

    return {
      conversations,
      activeConversation,
      messages,
      loading,
      alreadyLoaded,
      fetchConversations,
      fetchMessages,
      setActiveConversation,
      sendMessage,
      openConversation,
    };
  },
  {
    persist: {
      storage: sessionStorage,
      pick: ["conversations", "activeConversation"],
    },
  }
);
