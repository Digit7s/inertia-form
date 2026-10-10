<script setup lang="ts">
import type { FormField } from "../fieldTypes";

defineProps<{ field: FormField }>();
</script>

<template>
    <div class="rounded-md border border-red-600 p-3 text-sm text-red-700" role="alert">
        Unsupported form field type: <code>{{ field.type }}</code> ({{ field.name }})
    </div>
</template>
