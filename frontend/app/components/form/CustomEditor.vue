<script setup lang="ts">
import { ref, watch } from 'vue';
import Editor from 'primevue/editor';

const props = defineProps<{
    modelValue: string | null;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void
}>();

const content = ref(props.modelValue);

watch(content, (value) => emit('update:modelValue', value));
</script>

<template>
    <Editor v-model="content" class="w-full">
        <template #toolbar>
            <span class="ql-formats">
                <select class="ql-header">
                    <option selected></option>
                    <option value="1"></option>
                    <option value="2"></option>
                </select>
            </span>
            <span class="ql-formats">
                <button v-tooltip.bottom="'Bold'" class="ql-bold"></button>
                <button v-tooltip.bottom="'Italic'" class="ql-italic"></button>
                <button v-tooltip.bottom="'Underline'" class="ql-underline"></button>
                <button v-tooltip.bottom="'Strike'" class="ql-strike"></button>
            </span>
            <span class="ql-formats">
                <button v-tooltip.bottom="'Ordered List'" class="ql-list" value="ordered"></button>
                <button v-tooltip.bottom="'Bullet List'" class="ql-list" value="bullet"></button>
            </span>
            <span class="ql-formats">
                <button v-tooltip.bottom="'Align Left'" class="ql-align" value=""></button>
                <button v-tooltip.bottom="'Align Center'" class="ql-align" value="center"></button>
                <button v-tooltip.bottom="'Align Right'" class="ql-align" value="right"></button>
                <button v-tooltip.bottom="'Justify'" class="ql-align" value="justify"></button>
            </span>
            <span class="ql-formats">
                <select class="ql-color"></select>
                <select class="ql-background"></select>
            </span>
            <span class="ql-formats">
                <button v-tooltip.bottom="'Subscript'" class="ql-script" value="sub"></button>
                <button v-tooltip.bottom="'Superscript'" class="ql-script" value="super"></button>
            </span>
        </template>
    </Editor>
</template>
