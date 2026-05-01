<script setup lang="ts">
import { ref, computed } from "vue";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Eye, EyeOff } from "lucide-vue-next";

interface FieldMeta {
    input_type?: string;
    min_length?: number;
    max_length?: number;
    min_value?: number;
    max_value?: number;
    step?: number;
    prefix?: string;
    suffix?: string;
    disabled?: boolean;
    read_only?: boolean;
    autofocus?: boolean;
    autocomplete?: string;
}

interface Field {
    name: string;
    label: string;
    placeholder?: string;
    required?: boolean;
    meta: FieldMeta;
}

const props = defineProps<{
    modelValue: any;
    field: Field;
    error?: string;
}>();

defineEmits(["update:modelValue"]);

const isPasswordVisible = ref(false);

const inputType = computed(() => {
    if (props.field.meta.input_type === "password") {
        return isPasswordVisible.value ? "text" : "password";
    }
    return props.field.meta.input_type || "text";
});

const togglePasswordVisibility = () => {
    isPasswordVisible.value = !isPasswordVisible.value;
};
</script>

<template>
    <div class="space-y-2 text-left">
        <Label 
            :for="field.name" 
            class="flex items-center gap-x-1"
            :class="{ 'text-muted-foreground/70': field.meta.disabled }"
        >
            {{ field.label }}
            <span v-if="field.required" class="text-xs text-destructive">*</span>
        </Label>

        <div class="relative flex w-full items-center">
            <!-- Prefix -->
            <div 
                v-if="field.meta.prefix"
                class="flex h-10 items-center justify-center rounded-l-md border border-r-0 bg-muted px-3 text-sm text-muted-foreground"
            >
                {{ field.meta.prefix }}
            </div>

            <div class="relative w-full">
                <Input
                    :id="field.name"
                    :type="inputType"
                    :placeholder="field.placeholder"
                    :model-value="modelValue"
                    @update:model-value="$emit('update:modelValue', $event)"
                    :disabled="field.meta.disabled"
                    :readonly="field.meta.read_only"
                    :autofocus="field.meta.autofocus"
                    :autocomplete="field.meta.autocomplete"
                    :minlength="field.meta.min_length"
                    :maxlength="field.meta.max_length"
                    :min="field.meta.min_value"
                    :max="field.meta.max_value"
                    :step="field.meta.step"
                    class="transition-all"
                    :class="[
                        field.meta.prefix && 'rounded-l-none',
                        field.meta.suffix && 'rounded-r-none',
                        error && 'border-destructive ring-destructive shadow-[0_0_0_1px_rgba(239,68,68,1)]'
                    ]"
                />

                <!-- Password Reveal Toggle -->
                <button
                    v-if="field.meta.input_type === 'password' && !field.meta.disabled"
                    type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground transition-colors hover:text-foreground focus:outline-none"
                    @click="togglePasswordVisibility"
                >
                    <component :is="isPasswordVisible ? EyeOff : Eye" class="h-4 w-4" />
                </button>
            </div>

            <!-- Suffix -->
            <div 
                v-if="field.meta.suffix"
                class="flex h-10 items-center justify-center rounded-r-md border border-l-0 bg-muted px-3 text-sm text-muted-foreground"
            >
                {{ field.meta.suffix }}
            </div>
        </div>

        <p v-if="error" class="text-[0.8rem] font-medium text-destructive transition-all">
            {{ error }}
        </p>
    </div>
</template>
