<script setup lang="ts">
import { nextTick, onMounted, reactive, ref, watch } from 'vue';
import { useConfirm } from 'primevue/useconfirm';
import { useInfiniteScroll } from '@vueuse/core';
import { debounce } from 'lodash';
import Dialog from 'primevue/dialog';
import ProgressSpinner from 'primevue/progressspinner';
import DataTable, { DataTableRowClickEvent } from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';

const props = withDefaults(
    defineProps<{
        visible: boolean;
        url: string;
        header: string;
        columnName: string;
        columnHeader: string;
        searchParam?: string;
        perPage?: number;
        confirmSelect?: boolean;
        confirmMessage?: string;
        allowEmpty?: boolean;
        closeModalOnSelect?: boolean;
    }>(),
    {
        perPage: 100,
        confirmSelect: false,
        confirmMessage: 'Вы уверены?',
        allowEmpty: false,
        closeModalOnSelect: true,
    },
);

const emit = defineEmits(['closeModal', 'onSelect']);

const searchParam = props.searchParam ?? props.columnName;

const isModalLoading = ref(true);
const isFilterDataLoading = ref(false);
const table = ref();
const tableScrollTarget = ref();
const currentPage = ref(1);
const lastPage = ref();

const data = ref([]);

const filters = reactive({
    [searchParam]: null,
});

const confirm = useConfirm();

const { isLoading: isPaginatedDataLoading } = useInfiniteScroll(
    tableScrollTarget,
    async () => {
        if (isModalLoading.value) {
            return;
        }

        currentPage.value++;

        const nonNullFilters = Object.fromEntries(Object.entries(filters).filter(([, value]) => value != null));

        const queryParams = new URLSearchParams({
            ...nonNullFilters,
            page: currentPage.value,
            per_page: props.perPage,
        }).toString();

        const response = await fetch(`${props.url}?${queryParams}`, {
            headers: {
                'Content-Type': 'application/json',
            },
        });
        const responseData = await response.json();

        if (responseData.success) {
            data.value.push(...responseData.data);
        } else {
            currentPage.value--;

            toast.add({
                severity: 'error',
                summary: 'Ошибка',
                detail: 'Произошла ошибка при подгрузке данных...',
                life: 3000,
            });
        }
    },
    {
        distance: 50,
        canLoadMore() {
            return currentPage.value !== lastPage.value;
        },
    },
);

watch(
    filters,
    debounce(async (newFilters) => {
        isFilterDataLoading.value = true;
        currentPage.value = 1;

        const queryParams = new URLSearchParams({ ...newFilters, per_page: props.perPage }).toString();

        const response = await fetch(`${props.url}?${queryParams}`, {
            headers: {
                'Content-Type': 'application/json',
            },
        });
        const responseData = await response.json();

        if (responseData.success) {
            tableScrollTarget.value.scrollTo(0, 0);
            data.value = responseData.data;
        } else {
            toast.add({
                severity: 'error',
                summary: 'Ошибка',
                detail: 'Произошла ошибка при подгрузке данных...',
                life: 3000,
            });
        }

        isFilterDataLoading.value = false;
    }, 500),
);

onMounted(async () => {
    if (props.allowEmpty) {
        data.value.push({
            id: null,
            [props.columnName]: 'Не выбрано',
        });
    }

    const queryParams = new URLSearchParams({ per_page: props.perPage }).toString();

    const response = await fetch(`${props.url}?${queryParams}`, {
        headers: {
            'Content-Type': 'application/json',
        },
    });
    const responseData = await response.json();

    if (responseData.success) {
        data.value.push(...responseData.data);
        lastPage.value = responseData.meta.last_page;
    }

    isModalLoading.value = false;

    await nextTick();
    tableScrollTarget.value = table.value?.$el.querySelector('.p-datatable-table-container');
});

function handleSelectButtonClick(event: DataTableRowClickEvent) {
    if (props.confirmSelect) {
        confirm.require({
            message: props.confirmMessage,
            header: 'Подтверждение действия',
            accept: () => {
                emit('onSelect', event.data);

                if (props.closeModalOnSelect) {
                    emit('closeModal');
                }
            },
        });
    } else {
        emit('onSelect', event.data);

        if (props.closeModalOnSelect) {
            emit('closeModal');
        }
    }
}
</script>

<template>
    <Dialog
        :visible="visible"
        :dismissable-mask="true"
        modal
        :header="header"
        :style="{ width: '40rem', height: '60em' }"
        :content-style="{ height: '100%' }"
        @update:visible="emit('closeModal')"
    >
        <div v-if="isModalLoading" class="flex h-full items-center justify-center">
            <ProgressSpinner
                class="h-[70px]! w-[70px]!"
                fill="transparent"
                animation-duration=".5s"
                aria-label="ProgressSpinner"
            />
        </div>

        <div v-show="!isModalLoading">
            <DataTable
                ref="table"
                :value="data"
                :rows="5"
                :class="{ blur: isFilterDataLoading }"
                filter-display="row"
                selection-mode="single"
                scrollable
                scroll-height="46em"
                @row-click="handleSelectButtonClick"
            >
                <template #empty>
                    <div class="p-3 text-center text-gray-500">Пусто</div>
                </template>

                <Column :header="columnHeader" :show-filter-menu="false" frozen>
                    <template #body="{ data }">
                        <slot name="row" :data="data">
                            {{ data[columnName] }}
                        </slot>
                    </template>
                    <template #filter>
                        <InputText v-model="filters[searchParam]" type="text" class="w-full" placeholder="Поиск..." />
                    </template>
                </Column>
            </DataTable>

            <div v-if="isPaginatedDataLoading" class="mt-4 flex justify-center">
                <ProgressSpinner class="h-[50px]! w-[50px]!" animation-duration=".5s" />
            </div>
        </div>
    </Dialog>
</template>
