<script setup lang="ts">
import type { BreadcrumbItem } from '@/types';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import HeadingSmall from '@/components/app/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import FormLabel from '@/components/form/FormLabel.vue';
import { useUserStore } from '~/stores/user';
import { useForm } from '~/composables/useForm';
import { useUserAPI } from '~/api/user';
import { useToast } from 'primevue/usetoast';
import FormErrorMessage from '~/components/form/FormErrorMessage.vue';

useHead({
  title: 'Профиль',
});

const router = useRouter();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Настройки профиля',
    href: router.resolve({ name: 'settings-profile' }).fullPath,
  },
];

const userStore = useUserStore();
const { authUser } = storeToRefs(userStore);

const { form, data, errors, setErrors, processing } = useForm({
  name: authUser.value.name,
  email: authUser.value.email,
});

const toast = useToast();

const userAPI = useUserAPI();

async function handleSubmit() {
  const response = await userAPI.updateCurrentUser(data());

  if (response.success) {
    userStore.setUserData(response.data);

    toast.add({
      severity: 'success',
      summary: 'Успех',
      detail: 'Сохранено',
      life: 3000,
    });
  } else {
    setErrors(response.errors);

    toast.add({
      severity: 'error',
      summary: 'Ошибка',
      detail: 'Не удалось сохранить',
      life: 3000,
    });
  }
};
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <SettingsLayout>
      <div class="flex flex-col space-y-6">
        <HeadingSmall title="Профиль" description="Изменение данных профиля" />

        <form class="space-y-6" @submit.prevent="handleSubmit">
          <div class="grid gap-2">
            <FormLabel required> Имя </FormLabel>
            <InputText
              id="name"
              v-model="form.name"
              :invalid="!!errors?.name"
              type="text"
              autocomplete="name"
              placeholder="Введите имя"
              required
            />
            <FormErrorMessage :messages="errors?.name" />
          </div>

          <div class="grid gap-2">
            <FormLabel required> Email </FormLabel>
            <InputText
              id="email"
              v-model="form.email"
              :invalid="!!errors?.email"
              type="email"
              autocomplete="email"
              placeholder="Введите email"
              required
              fluid
            />
            <FormErrorMessage :messages="errors?.email" />
          </div>

          <div class="flex items-center gap-4">
            <Button :disabled="processing" type="submit"> Сохранить </Button>
          </div>
        </form>
      </div>
    </SettingsLayout>
  </AppLayout>
</template>
