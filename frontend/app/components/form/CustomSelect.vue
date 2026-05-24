<script setup lang="ts">
import { computed, nextTick, onMounted, reactive, ref, watch } from 'vue';
import { useInfiniteScroll } from '@vueuse/core';
import { debounce } from 'lodash';
import { Search } from 'lucide-vue-next';
import InputText from 'primevue/inputtext';
import ProgressSpinner from 'primevue/progressspinner';
import { useAPI } from '~/api/base';
import { useToast } from 'primevue/usetoast';

interface SelectOption {
  id: any;
  [key: string]: any;
}

const props = withDefaults(
  defineProps<{
    modelValue?: any;
    url: string;
    optionLabel: string;
    selectedOptionData?: any;
    selectedOptionFetchUrl?: string;
    placeholder?: string;
    optionValue?: string;
    searchParam?: string;
    perPage?: number;
    allowEmpty?: boolean;
    emptyLabel?: string;
    disabled?: boolean;
    invalid?: boolean;
  }>(),
  {
    placeholder: 'Выберите',
    optionValue: 'id',
    perPage: 50,
    allowEmpty: false,
    emptyLabel: 'Не выбрано',
    disabled: false,
    invalid: false,
  },
);

const emit = defineEmits(['update:modelValue']);

const { apiFetch } = useAPI();
const toast = useToast();

const searchParam = props.searchParam ?? props.optionLabel;

const isOpen = ref(false);
const searchQuery = ref('');
const currentPage = ref(1);
const lastPage = ref(1);
const options = ref<SelectOption[]>([]);
const scrollContainer = ref();
const dropdownRef = ref();
const selectButtonRef = ref();
const overlayRef = ref();
const inputRef = ref();
const isInitialDataLoading = ref(true);
const isFilterDataLoading = ref(false);
const isSelectedOptionLoading = ref(false);

const filters = reactive({
  [searchParam]: null,
});

const allOptions = computed(() => {
  const data = [];

  if (props.allowEmpty) {
    data.push({
      [props.optionValue]: null,
      [props.optionLabel]: props.emptyLabel,
    } as SelectOption);
  }

  if (
    props.selectedOptionData &&
    !options.value.find(
      (option) => option[props.optionValue] === props.modelValue,
    )
  ) {
    data.push(props.selectedOptionData);
  }

  data.push(...options.value);

  return data;
});

const selectedOption = computed(() => {
  if (!props.modelValue) {
    return '';
  }
  return allOptions.value.find(
    (option) => Number(option[props.optionValue]) === Number(props.modelValue),
  );
});

const displayValue = computed(() => {
  return selectedOption.value ? selectedOption.value[props.optionLabel] : '';
});

const { isLoading: isPaginatedDataLoading } = useInfiniteScroll(
  scrollContainer,
  async () => {
    if (isInitialDataLoading.value || currentPage.value >= lastPage.value) {
      return;
    }

    currentPage.value++;

    const url = new URL(props.url, window.location.origin);
    url.searchParams.set('page', currentPage.value.toString());
    url.searchParams.set('per_page', props.perPage.toString());

    if (filters[props.searchParam]) {
      url.searchParams.set(props.searchParam, filters[props.searchParam]);
    }

    try {
      const response = await apiFetch(url.toString());

      if (response.success) {
        options.value.push(...response.data);
      } else {
        currentPage.value--;
        console.error('Error loading more data');
      }
    } catch (error) {
      currentPage.value--;
      console.error('Error loading more data:', error);
    }
  },
  {
    distance: 50,
    canLoadMore() {
      return currentPage.value < lastPage.value;
    },
  },
);

watch(
  filters,
  debounce(async (newFilters) => {
    isFilterDataLoading.value = true;
    currentPage.value = 1;

    const url = new URL(props.url, window.location.origin);

    Object.entries({ ...newFilters, per_page: props.perPage }).forEach(
      ([key, value]) => {
        if (!value) return;
        url.searchParams.set(key, value);
      },
    );

    const response = await apiFetch(url.toString());

    if (response.success) {
      scrollContainer.value.scrollTo(0, 0);
      options.value = response.data;
      lastPage.value = response.meta.last_page;
    } else {
      toast.add({
        severity: 'error',
        summary: 'Ошибка',
        detail: 'Произошла ошибка при подгрузке данных...',
        life: 3000,
      });
    }

    isFilterDataLoading.value = false;
  }, 300),
);

watch(isOpen, (newValue) => {
  if (newValue) {
    nextTick(() => {
      alignOverlay();
    });
  }
});

onMounted(async () => {
  await loadInitialData();

  window.addEventListener('scroll', alignOverlay, true);
  window.addEventListener('resize', alignOverlay);

  document.addEventListener('click', handleDocumentClick);
});

async function loadInitialData() {
  isInitialDataLoading.value = true;

  const url = new URL(props.url, window.location.origin);
  url.searchParams.set('per_page', props.perPage.toString());

  try {
    const response = await apiFetch(url.toString());

    if (response.success) {
      options.value.push(...response.data);
      lastPage.value = response.meta?.last_page || 1;

      if (
        props.modelValue &&
        !props.selectedOptionData &&
        !options.value.find(
          (option) => option[props.optionValue] === props.modelValue,
        )
      ) {
        await loadSelectedOption(props.modelValue);
      }
    }
  } catch (error) {
    console.error('Error loading initial data:', error);
  }

  isInitialDataLoading.value = false;
}

async function loadSelectedOption(value: any) {
  if (!props.selectedOptionFetchUrl || isSelectedOptionLoading.value || !value)
    return;

  isSelectedOptionLoading.value = true;

  try {
    const response = await apiFetch(props.selectedOptionFetchUrl);

    if (response.success && response.data) {
      const existingIndex = options.value.findIndex(
        (option) =>
          option[props.optionValue] === response.data[props.optionValue],
      );

      if (existingIndex === -1) {
        options.value.splice(1, 0, response.data);
      }
    }
  } catch (error) {
    console.error('Error loading selected option:', error);
  }

  isSelectedOptionLoading.value = false;
}

function alignOverlay() {
  if (!selectButtonRef.value || !overlayRef.value) return;

  const buttonRect = selectButtonRef.value.getBoundingClientRect();

  overlayRef.value.style.position = 'fixed';
  overlayRef.value.style.top = `${buttonRect.bottom + 2}px`;
  overlayRef.value.style.left = `${buttonRect.left}px`;
  overlayRef.value.style.width = `${buttonRect.width}px`;
  overlayRef.value.style.maxWidth = `${buttonRect.width}px`;
}

function toggleDropdown() {
  if (props.disabled) return;

  isOpen.value = !isOpen.value;

  if (isOpen.value) {
    nextTick(() => {
      document.body.appendChild(overlayRef.value);
      alignOverlay();
      inputRef.value?.$el?.focus();
    });
  } else {
    (document.activeElement as HTMLElement)?.blur();
  }
}

function handleOptionSelect(option: SelectOption) {
  emit('update:modelValue', option[props.optionValue]);
  isOpen.value = false;
}

function handleSearchInput(event: Event) {
  const value = (event.target as HTMLInputElement).value;
  filters[searchParam] = value || null;
}

function handleDocumentClick(event: MouseEvent) {
  if (!isOpen.value) return;

  const target = event.target as Node;
  const clickedButton = selectButtonRef.value?.contains(target);
  const clickedOverlay = overlayRef.value?.contains(target);

  if (!clickedButton && !clickedOverlay) {
    isOpen.value = false;
  }
}
</script>

<template>
  <div ref="dropdownRef" class="relative">
    <button
      ref="selectButtonRef"
      type="button"
      class="inline-flex cursor-pointer relative select-none w-full bg-neutral-0 dark:bg-neutral-900 border border-neutral-300 dark:border-neutral-600 transition-all duration-200 rounded-lg outline-none shadow-none"
      :class="{
        'opacity-100 bg-neutral-200 dark:bg-neutral-700 cursor-default':
          disabled,
        'hover:border-neutral-400 dark:hover:border-neutral-500':
          !disabled && !isOpen,
        'border-primary-500 dark:border-primary-400 shadow-[0_0_0_0.2rem_rgba(59,130,246,0.5)] dark:shadow-[0_0_0_0.2rem_rgba(96,165,250,0.5)] outline-none':
          !disabled && isOpen,
        'border-red-400': invalid,
      }"
      :disabled="disabled"
      @click="toggleDropdown"
    >
      <span
        class="block whitespace-nowrap overflow-hidden flex-1 w-[1%] py-2 px-3 text-ellipsis cursor-pointer bg-transparent border-0 outline-none text-neutral-700 dark:text-white/80 text-start"
        :class="{ 'text-neutral-400 dark:text-neutral-500': !modelValue }"
      >
        <span
          v-if="isSelectedOptionLoading"
          class="inline-flex items-center gap-2"
        >
          <ProgressSpinner
            class="h-[16px]! w-[16px]!"
            stroke-width="4"
            animation-duration=".5s"
          />
        </span>
        <span v-else>
          <slot name="displayLabel" :selectedOption="selectedOption">
            {{ displayValue || placeholder }}
          </slot>
        </span>
      </span>
      <span
        class="flex items-center justify-center flex-shrink-0 bg-transparent text-neutral-500 dark:text-neutral-400 w-12 rounded-r-lg"
      >
        <svg
          width="14"
          height="14"
          viewBox="0 0 14 14"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
          class="w-[14px] h-[14px]"
          aria-hidden="true"
        >
          <path
            d="M7.01744 10.398C6.91269 10.3985 6.8089 10.378 6.71215 10.3379C6.61541 10.2977 6.52766 10.2386 6.45405 10.1641L1.13907 4.84913C1.03306 4.69404 0.985221 4.5065 1.00399 4.31958C1.02276 4.13266 1.10693 3.95838 1.24166 3.82747C1.37639 3.69655 1.55301 3.61742 1.74039 3.60402C1.92777 3.59062 2.11386 3.64382 2.26584 3.75424L7.01744 8.47394L11.769 3.75424C11.9189 3.65709 12.097 3.61306 12.2748 3.62921C12.4527 3.64535 12.6199 3.72073 12.7498 3.84328C12.8797 3.96582 12.9647 4.12842 12.9912 4.30502C13.0177 4.48162 12.9841 4.662 12.8958 4.81724L7.58083 10.1322C7.50996 10.2125 7.42344 10.2775 7.32656 10.3232C7.22968 10.3689 7.12449 10.3944 7.01744 10.398Z"
            fill="currentColor"
          />
        </svg>
      </span>
    </button>

    <Teleport to="body">
      <Transition name="p-connected-overlay">
        <div
          v-if="isOpen"
          ref="overlayRef"
          class="bg-white dark:bg-neutral-900 text-neutral-700 dark:text-white/80 border border-neutral-200 dark:border-neutral-700 rounded-lg shadow-lg min-w-0 absolute top-[calc(100%+0.5rem)] left-0 right-0"
          :style="{ zIndex: 9999 }"
        >
          <div class="overflow-auto">
            <div class="relative px-3 pt-3">
              <InputText
                ref="inputRef"
                v-model="searchQuery"
                type="text"
                class="w-full pr-10"
                placeholder="Поиск..."
                @input="handleSearchInput"
              />
              <Search
                class="absolute top-[60%] right-6 -mt-2 text-neutral-400 w-[14px] h-[14px]"
              />
            </div>

            <div
              v-if="isInitialDataLoading"
              class="flex items-center justify-center p-4"
            >
              <ProgressSpinner
                class="h-[40px]! w-[40px]!"
                stroke-width="4"
                animation-duration=".5s"
              />
            </div>

            <div
              v-else
              ref="scrollContainer"
              class="list-none max-h-[300px] overflow-y-auto mt-2"
            >
              <div
                v-if="allOptions.length === 0"
                class="p-3 text-neutral-500 text-center"
              >
                Нет данных
              </div>

              <div
                v-for="option in allOptions"
                :key="option[optionValue]"
                class="cursor-pointer font-normal text-sm whitespace-nowrap relative overflow-hidden flex items-center min-h-[2.3rem] px-3 py-3 border-0 text-neutral-700 dark:text-white/80 transition-all duration-200 leading-none hover:bg-neutral-100 dark:hover:bg-neutral-800"
                :class="{
                  'bg-neutral-800 hover:bg-neutral-900 text-white dark:bg-primary-400/10 text-primary-700 dark:text-primary-400':
                    Number(modelValue) === Number(option[optionValue]),
                }"
                @click="handleOptionSelect(option)"
              >
                <slot name="option" :option="option">
                  {{ option[optionLabel] }}
                </slot>
              </div>

              <div
                v-if="isPaginatedDataLoading"
                class="flex items-center justify-center p-3"
              >
                <ProgressSpinner
                  class="h-[30px]! w-[30px]!"
                  stroke-width="4"
                  animation-duration=".5s"
                />
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<style scoped>
.p-connected-overlay-enter-active {
  transition:
    transform 0.12s cubic-bezier(0, 0, 0.2, 1),
    opacity 0.12s;
}

.p-connected-overlay-enter-from {
  opacity: 0;
  transform: scaleY(0.8);
  transform-origin: center top;
}

.p-connected-overlay-enter-to {
  opacity: 1;
  transform: scaleY(1);
}

.p-connected-overlay-leave-active {
  transition: opacity 0.1s linear;
}

.p-connected-overlay-leave-to {
  opacity: 0;
}
</style>
