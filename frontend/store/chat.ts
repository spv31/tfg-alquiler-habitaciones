import { defineStore } from "pinia";
import { getCsrfToken } from "~/utils/auth";
import { tryCatch } from "~/utils/tryCatch";
import type { Conversation } from "~/types/conversation";
import type { Message } from "~/types/message";
import type Echo from "laravel-echo";

interface UserConnected {
  id: number;
  name: string;
}

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

    const usersConnected = ref<UserConnected[]>([]);
    const isPeerOnline = computed(() => {
      return usersConnected.value.length === 2;
    });

    /**
     * We subscribe to converstion and presence channel
     *
     * @param {number} conversationId
     */
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

      echo
        .join(`conversation.${conversationId}`)
        .here((users: Array<{ id: number; name: string }>) => {
          usersConnected.value = users;
        })
        .joining((user: { id: number; name: string }) => {
          usersConnected.value.push(user);
        })
        .leaving((user: { id: number; name: string }) => {
          usersConnected.value = usersConnected.value.filter(
            (userConnected) => userConnected.id !== user.id
          );
        });
    };

    /**
     * Fetch all conversations
     *
     * @async
     * @returns {*}
     */
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

    /**
     * Fetch messages related to conversation
     *
     * @async
     * @param {number} conversationId
     * @returns {*}
     */
    const fetchMessages = async (conversationId: number) => {
      const { data, error } = await tryCatch<{ data: Message[] }>(async () => {
        const csrf = await getCsrfToken();
        if (!csrf) throw new Error("Error getting CSRF Token");
        return $fetch<{ data: Message[] }>(
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
      messages.value = data.data;
    };

    // actives a conversation and charges its messages related
    const setActiveConversation = async (conversation: Conversation) => {
      activeConversation.value = conversation;
      await fetchMessages(conversation.id);
      subscribeToChannel(conversation.id);
    };

    /**
     * Sends a new message
     *
     * @async
     * @param {string} body
     * @returns {*}
     */
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

      const raw = (data as any).data ?? data;
      const msg: Message = {
        ...raw,
        sent_at: raw.sent_at ?? raw.updated_at,
      };
      messages.value.push(msg);
    };

    /**
     * Opens a conversation
     *
     * @async
     * @param {number} ownerId
     * @param {number} tenantId
     * @returns {unknown}
     */
    const openConversation = async (ownerId: number, tenantId: number) => {
      if (!alreadyLoaded.value) await fetchConversations();
      let convo = conversations.value.find(
        (c) => c.owner_id === ownerId && c.tenant_id === tenantId
      );

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

      await setActiveConversation(convo);
      return convo;
    };

    const reset = () => {
      conversations.value = [];
      activeConversation.value = null;
      messages.value = [];
      alreadyLoaded.value = false;
      usersConnected.value = [];
    };

    return {
      conversations,
      activeConversation,
      messages,
      loading,
      alreadyLoaded,
      isPeerOnline,
      fetchConversations,
      fetchMessages,
      setActiveConversation,
      sendMessage,
      openConversation,
      reset
    };
  },
  {
    persist: {
      storage: sessionStorage,
      pick: ["conversations", "activeConversation"],
    },
  }
);
