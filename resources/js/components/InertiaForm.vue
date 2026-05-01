<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import TextInput from "./fields/TextInput.vue";
import SelectInput from "./fields/SelectInput.vue";
import { Button } from "@/components/ui/button";

interface Field {
    name: string;
    label: string;
    type: "text" | "select" | "checkbox" | "textarea";
    default: any;
    required: boolean;
    placeholder?: string;
    meta: Record<string, any>;
}

interface FormPayload {
    schema: Field[];
    defaults: Record<string, any>;
    action?: string;
    method?: string;
}

const props = defineProps<{
    formPayload: FormPayload;
    submitLabel?: string;
    method?: "post" | "put" | "patch";
    action?: string;
}>();

const form = useForm(props.formPayload.defaults);

const submit = () => {
    const finalMethod = (props.method || props.formPayload.method || "post").toLowerCase() as any;
    const finalAction = props.action || props.formPayload.action;

    if (!finalAction) {
        console.error("InertiaForm: No action URL provided.");
        return;
    }

    form.clearErrors();
    form.submit(finalMethod, finalAction, {
        preserveScroll: true,
        onSuccess: () => {
             // Optional success callback hook
        },
    });
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div v-for="field in formPayload.schema" :key="field.name">
            <template v-if="field.type === 'text'">
                <TextInput
                    v-model="form[field.name]"
                    :field="field"
                    :error="form.errors[field.name]"
                />
            </template>

            <template v-if="field.type === 'select'">
                <SelectInput
                    v-model="form[field.name]"
                    :field="field"
                    :error="form.errors[field.name]"
                />
            </template>
        </div>

        <div class="flex items-center justify-end gap-x-4 pt-4">
            <slot name="actions" :form="form">
                <Button type="submit" :disabled="form.processing">
                    <template v-if="form.processing">
                        <span class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-primary border-t-transparent"></span>
                    </template>
                    {{ submitLabel || "Save Changes" }}
                </Button>
            </slot>
        </div>
    </form>
</template>
