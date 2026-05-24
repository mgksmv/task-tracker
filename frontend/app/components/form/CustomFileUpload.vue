<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useConfirm } from 'primevue/useconfirm';
import Image from 'primevue/image';
import { useToast } from 'primevue/usetoast';
import ProgressSpinner from 'primevue/progressspinner';
import { useAPI } from '~/api/base';
import FormErrorMessage from './FormErrorMessage.vue';

interface UploadedFile {
  id?: string | number;
  path?: string;
  url?: string;
  is_new?: boolean;
  file?: File;
}

interface Props {
  canEdit?: boolean;
  files?: UploadedFile[];
  fileSrc?: string;
  fileThumbnailSrc?: string;
  accept?: string;
  multiple?: boolean;
  apiUploadFn?: () => string;
  apiDeleteFn?: (fileId: string | number) => string;
  formDataBuilder?: (files: File[], existingData?: any) => FormData;
  errors?: Record<string, any>;
  existingData?: any;
  gridCols?: number;
  addFileButtonTitle?: string;
}

const props = withDefaults(defineProps<Props>(), {
  canEdit: true,
  files: () => [],
  fileSrc: 'url',
  fileThumbnailSrc: 'url',
  accept: '*',
  multiple: false,
  gridCols: 4,
  addFileButtonTitle: 'Добавить файл',
});

const emit = defineEmits<{
  fileAdded: [files: File[]];
  fileDeleted: [index: number];
  fileUpdated: [files: UploadedFile[]];
}>();

const uploadedFiles = ref<UploadedFile[]>([]);
const isLoading = ref(false);

const toast = useToast();
const confirm = useConfirm();

const { apiFetch } = useAPI();

onMounted(() => {
  if (props.files) {
    uploadedFiles.value = [...props.files];
  }
});

const showAddButton = computed(
  () => props.canEdit && (props.multiple || uploadedFiles.value.length === 0),
);

async function processFiles(fileList: FileList | null) {
  if (!fileList) return;

  const filesArray = Array.from(fileList);

  if (!props.existingData?.id) {
    for (const file of filesArray) {
      const reader = new FileReader();
      reader.onload = (loadEvent) => {
        const result = loadEvent.target?.result;
        if (result) {
          uploadedFiles.value.push({
            [props.fileSrc]: result as string,
            [props.fileThumbnailSrc]: result as string,
            path: result as string,
            is_new: true,
            file: file,
          });
          emit('fileUpdated', uploadedFiles.value);
        }
      };
      reader.onerror = () => {
        console.error('Error reading file:', file.name);
      };
      reader.readAsDataURL(file);
    }
    emit('fileAdded', filesArray);
  } else if (props.apiUploadFn) {
    const formData = props.formDataBuilder
      ? props.formDataBuilder(filesArray, props.existingData)
      : createDefaultFormData(filesArray);

    isLoading.value = true;

    try {
      const response = await apiFetch(props.apiUploadFn(), {
        method: 'post',
        body: formData,
      });

      if (response.success && response.data) {
        uploadedFiles.value.push(...response.data);
        emit('fileUpdated', uploadedFiles.value);
        toast.add({
          severity: 'success',
          summary: 'Успех',
          detail: 'Файл загружен',
          life: 3000,
        });
      } else {
        toast.add({
          severity: 'error',
          summary: 'Ошибка',
          detail: 'Не удалось загрузить файл',
          life: 3000,
        });
      }
    } catch (error) {
      console.error('File upload failed:', error);
    }

    isLoading.value = false;
  }
}

async function handleFileUpload(event: Event) {
  if (!props.canEdit) return;

  try {
    const fileInput = event.target as HTMLInputElement;
    await processFiles(fileInput.files);
    fileInput.value = '';
  } catch (error) {
    console.error('File upload error:', error);
    if (error instanceof Error) {
      console.error('Error message:', error.message);
      console.error('Stack:', error.stack);
    }
    throw error;
  }
}

function handleDragOver(event: DragEvent) {
  event.preventDefault();
  event.stopPropagation();
}

function handleDrop(event: DragEvent) {
  event.preventDefault();
  event.stopPropagation();
  processFiles(event.dataTransfer?.files || null);
}

function createDefaultFormData(files: File[]): FormData {
  const formData = new FormData();
  for (const file of files) {
    formData.append('files[]', file);
  }
  return formData;
}

async function handleFileDelete(file: UploadedFile, index: number) {
  if (!props.canEdit) return;

  confirm.require({
    message: 'Вы уверены, что хотите удалить файл?',
    header: 'Подтверждение действия',
    accept: async () => {
      if (!props.existingData?.id) {
        const fileToDelete = uploadedFiles.value[index];
        uploadedFiles.value.splice(index, 1);

        if (fileToDelete?.is_new) {
          emit('fileDeleted', index);
        } else {
          emit('fileUpdated', uploadedFiles.value);
        }
      } else if (props.apiDeleteFn) {
        try {
          const response = await apiFetch(props.apiDeleteFn(file.id!), {
            method: 'delete',
          });

          if (response.success) {
            uploadedFiles.value.splice(index, 1);
            emit('fileUpdated', uploadedFiles.value);
            toast.add({
              severity: 'success',
              summary: 'Успех',
              detail: 'Файл удалён',
              life: 3000,
            });
          }
        } catch (error) {
          console.error('File delete failed:', error);
        }
      }
    },
  });
}

function getFiles(): UploadedFile[] {
  return uploadedFiles.value;
}

function getNewFiles(): UploadedFile[] {
  return uploadedFiles.value.filter((f) => f.is_new);
}

defineExpose({
  getFiles,
  getNewFiles,
});
</script>

<template>
  <div
    :style="{
      display: 'grid',
      gap: '1rem',
      width: '100%',
      marginTop: '1rem',
      gridTemplateColumns: `repeat(${gridCols}, minmax(0, 1fr))`,
    }"
  >
    <div
      v-for="(file, index) in uploadedFiles"
      :key="file.id || index"
      class="relative w-[160px] h-[160px]"
    >
      <Image
        v-if="file[fileThumbnailSrc]"
        :src="file[fileSrc]"
        class="shadow-md rounded-lg w-full h-full"
        image-class="rounded-lg object-cover w-full h-full"
        alt="Файл"
        preview
      >
        <template #image>
          <img
            :src="file[fileThumbnailSrc]"
            class="rounded-lg object-cover w-full h-full"
            alt="Превью"
          />
        </template>
      </Image>
      <a v-else :href="file[fileSrc]">
        <img
          src="/images/file.png"
          class="border rounded-lg object-cover w-full h-full"
          alt="Превью"
        />
      </a>
      <button
        v-if="canEdit"
        class="delete-button"
        @click.prevent="handleFileDelete(file, index)"
      >
        <svg
          width="26"
          height="26"
          viewBox="0 0 1024 1024"
          xmlns="http://www.w3.org/2000/svg"
          fill="#000000"
        >
          <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
          <g
            id="SVGRepo_tracerCarrier"
            stroke-linecap="round"
            stroke-linejoin="round"
          ></g>
          <g id="SVGRepo_iconCarrier">
            <path
              fill="#b93434"
              d="M512 64a448 448 0 1 1 0 896 448 448 0 0 1 0-896zM288 512a38.4 38.4 0 0 0 38.4 38.4h371.2a38.4 38.4 0 0 0 0-76.8H326.4A38.4 38.4 0 0 0 288 512z"
            ></path>
          </g>
        </svg>
      </button>
    </div>

    <label
      v-if="showAddButton"
      class="add-file-button"
      :class="{ disabled: isLoading }"
      @dragover="handleDragOver"
      @drop="handleDrop"
    >
      <ProgressSpinner
        v-if="isLoading"
        class="h-[70px]! w-[70px]!"
        fill="transparent"
        animation-duration=".5s"
        aria-label="ProgressSpinner"
      />
      <template v-else>
        <svg
          width="24"
          height="24"
          viewBox="0 0 25 24"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M21.5 10.5H14V3H11V10.5H3.5V13.5H11V21H14V13.5H21.5V10.5Z"
            fill="#000000"
          ></path>
        </svg>
        <span>{{ addFileButtonTitle }}</span>
        <input
          @change="handleFileUpload"
          :accept="accept"
          class="hidden"
          type="file"
          :multiple="multiple"
        />
      </template>
    </label>
  </div>

  <FormErrorMessage :messages="errors?.files" />
</template>

<style scoped>
.add-file-button {
  width: 160px;
  height: 160px;
  border-radius: 8px;
  background: rgba(161, 161, 161, 0.1);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.delete-button {
  position: absolute;
  display: flex;
  justify-content: center;
  align-items: center;
  top: -13px;
  right: -13px;
  z-index: 2;
  width: 28px;
  height: 28px;
  background: #fff;
  border-radius: 50%;
  cursor: pointer;
}

.delete-button:hover {
  opacity: 0.8;
}
</style>
