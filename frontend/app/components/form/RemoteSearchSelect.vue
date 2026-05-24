<script setup lang="ts">
import { ref } from 'vue';
import { Select, type SelectFilterEvent } from 'primevue';
import ProgressSpinner from 'primevue/progressspinner';

withDefaults(
    defineProps<{
        id: string;
        currentValue: any;
        options: object[];
        isLoading: boolean;
        optionLabel: string;
        required?: boolean;
        optionValue?: string;
        placeholder?: string;
    }>(),
    {
        required: false,
        optionValue: undefined,
        placeholder: 'Не выбрано'
    }
);

const emit = defineEmits(['onFilter', 'onChange']);

const currentFilterText = ref('');

function handleFilter(event: SelectFilterEvent) {
    currentFilterText.value = event.value;
    emit('onFilter', event);
}
</script>

<template>
    <Select
        :id="id"
        :model-value="currentValue"
        :options="options"
        :option-label="optionLabel"
        :option-value="optionValue"
        empty-message=" "
        empty-filter-message=" "
        filter
        show-clear
        auto-filter-focus
        placeholder="Не выбрано"
        :required="required"
        @filter="handleFilter($event)"
        @change="emit('onChange', $event)"
    >
        <template #footer="{ options }">
            <div v-if="isLoading" class="p-4 flex justify-center">
                <ProgressSpinner style="width: 50px; height: 50px" />
            </div>
            <p v-else-if="!options.length && currentFilterText" class="p-select-empty-message">
                Ничего не найдено...
            </p>
            <p v-else-if="!options.length" class="p-select-empty-message">
                Начните печатать для поиска...
            </p>
        </template>
    </Select>
</template>

<style scoped>
.p-select-empty-message {
    margin-top: -20px;
}
</style>
