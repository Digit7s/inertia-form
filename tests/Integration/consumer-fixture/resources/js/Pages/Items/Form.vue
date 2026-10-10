<script setup>
defineProps({ formPayload: Object, recordId: Number });
</script>

<template>
    <main>
        <h1>{{ recordId ? `Edit item ${recordId}` : 'Create item' }}</h1>
        <InertiaForm :form-payload="formPayload" submit-label="Save item" />
    </main>
</template>
