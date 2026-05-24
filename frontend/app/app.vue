<script setup lang="ts">
import '~/assets/css/app.css';
import '~/assets/scss/app.scss';
import '~/assets/scss/primevue.scss';
import 'primeicons/primeicons.css';
import LoaderSpinner from './components/app/LoaderSpinner.vue';
</script>

<template>
  <NuxtLoadingIndicator color="#3F3F46" />

  <Suspense>
    <template #default>
      <NuxtPage />
    </template>
    <template #fallback>
      <LoaderSpinner />
    </template>
  </Suspense>

  <Toast position="bottom-right" />
</template>
