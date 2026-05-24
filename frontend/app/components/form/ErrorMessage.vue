<script setup lang="ts">
defineProps<{
    message: string | undefined;
}>();
</script>

<template>
    <div v-if="message" class="text-sm text-red-500 dark:text-red-400">
        {{ message }}
    </div>
</template>
