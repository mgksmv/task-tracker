<script setup lang="ts">
import Message from 'primevue/message';

defineProps<{
  messages?: string[];
}>();
</script>

<template>
  <template v-if="messages?.length">
    <Message
      v-for="message in messages"
      :key="message"
      severity="error"
      variant="simple"
      size="small"
    >
      {{ message }}
    </Message>
  </template>
</template>
