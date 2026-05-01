<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from "vue";
import { Label } from "@/components/ui/label";
import { Badge } from "@/components/ui/badge";
import { Check, ChevronsUpDown, Search, X } from "lucide-vue-next";

interface SelectOption {
    label: string;
    value: string | number;
}

interface FieldMeta {
    options?: Record<string, any>;
    multiple?: boolean;
    searchable?: boolean;
    disabled?: boolean;
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

const emit = defineEmits(["update:modelValue"]);

const isOpen = ref(false);
const searchQuery = ref("");
const dropdownRef = ref<HTMLElement | null>(null);

// Flattened options for easier manipulation
const allOptions = computed(() => {
    const options: SelectOption[] = [];
    const source = props.field.meta.options || {};

    Object.entries(source).forEach(([key, value]) => {
        if (value && typeof value === "object") {
            // Grouped options
            Object.entries(value).forEach(([subKey, subValue]) => {
                options.push({ label: String(subValue), value: subKey });
            });
        } else {
            options.push({ label: String(value), value: key });
        }
    });

    return options;
});

// Grouped options for rendering
const groupedOptions = computed(() => {
    const groups: { label: string | null; options: SelectOption[] }[] = [];
    const source = props.field.meta.options || {};
    const query = searchQuery.value.toLowerCase();

    Object.entries(source).forEach(([key, value]) => {
        if (value && typeof value === "object") {
            const filtered = Object.entries(value)
                .map(([subKey, subValue]) => ({ label: String(subValue), value: subKey }))
                .filter(opt => opt.label.toLowerCase().includes(query));

            if (filtered.length > 0) {
                groups.push({ label: key, options: filtered });
            }
        } else {
            if (String(value).toLowerCase().includes(query)) {
                let ungrouped = groups.find(g => g.label === null);
                if (!ungrouped) {
                    ungrouped = { label: null, options: [] };
                    groups.push(ungrouped);
                }
                ungrouped.options.push({ label: String(value), value: key });
            }
        }
    });

    return groups;
});

const isSelected = (value: string | number) => {
    if (props.field.meta.multiple) {
        return Array.isArray(props.modelValue) && props.modelValue.includes(String(value));
    }
    return String(props.modelValue) === String(value);
};

const toggleOption = (value: string | number) => {
    if (props.field.meta.disabled) return;

    if (props.field.meta.multiple) {
        const current = Array.isArray(props.modelValue) ? [...props.modelValue] : [];
        const index = current.indexOf(String(value));
        if (index > -1) {
            current.splice(index, 1);
        } else {
            current.push(String(value));
        }
        emit("update:modelValue", current);
    } else {
        emit("update:modelValue", String(value));
        isOpen.value = false;
    }
};

const activeLabel = computed(() => {
    const selected = allOptions.value.find(opt => String(opt.value) === String(props.modelValue));
    return selected ? selected.label : props.field.placeholder || "Select an option...";
});

const handleClickOutside = (event: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
        isOpen.value = false;
    }
};

onMounted(() => document.addEventListener("mousedown", handleClickOutside));
onUnmounted(() => document.removeEventListener("mousedown", handleClickOutside));
</script>

<template>
    <div class="space-y-2 text-left" ref="dropdownRef">
        <Label 
            :for="field.name" 
            class="flex items-center gap-x-1"
            :class="{ 'text-muted-foreground/70': field.meta.disabled }"
        >
            {{ field.label }}
            <span v-if="field.required" class="text-xs text-destructive">*</span>
        </Label>
        
        <div class="relative">
            <!-- Trigger -->
            <button
                type="button"
                @click="isOpen = !isOpen"
                :disabled="field.meta.disabled"
                class="flex min-h-10 w-full items-center justify-between rounded-md border bg-background px-3 py-2 text-sm ring-offset-background transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                :class="!!error ? 'border-destructive' : 'border-input'"
            >
                <div class="flex flex-wrap gap-1 text-left">
                    <template v-if="field.meta.multiple && Array.isArray(modelValue) && modelValue.length > 0">
                        <Badge 
                            v-for="val in modelValue" 
                            :key="val" 
                            variant="secondary" 
                            class="flex items-center gap-1.5 px-1.5 py-0.5"
                        >
                            {{ allOptions.find(o => String(o.value) === String(val))?.label || val }}
                            <X 
                                v-if="!field.meta.disabled" 
                                @click.stop="toggleOption(val)" 
                                class="h-3 w-3 cursor-pointer hover:text-destructive" 
                            />
                        </Badge>
                    </template>
                    <template v-else-if="!field.meta.multiple && modelValue">
                        {{ activeLabel }}
                    </template>
                    <span v-else class="text-muted-foreground">
                        {{ field.placeholder || 'Select an option...' }}
                    </span>
                </div>
                <ChevronsUpDown class="h-4 w-4 shrink-0 opacity-50" />
            </button>

            <!-- Dropdown -->
            <div 
                v-if="isOpen" 
                class="absolute z-50 mt-1 max-h-64 w-full overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md animate-in fade-in zoom-in-95 duration-100"
            >
                <!-- Search -->
                <div v-if="field.meta.searchable" class="flex items-center border-b px-3">
                    <Search class="mr-2 h-4 w-4 shrink-0 opacity-50" />
                    <input 
                        v-model="searchQuery" 
                        class="flex h-10 w-full rounded-md bg-transparent py-3 text-sm outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50"
                        placeholder="Search..."
                        @click.stop
                    />
                </div>

                <!-- Options -->
                <div class="max-h-52 overflow-y-auto p-1 py-1.5">
                    <div v-for="group in groupedOptions" :key="group.label || 'default'">
                        <div v-if="group.label" class="select-none px-2 py-1.5 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                            {{ group.label }}
                        </div>
                        <div 
                            v-for="option in group.options" 
                            :key="option.value"
                            @click="toggleOption(option.value)"
                            class="relative flex w-full cursor-pointer select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none transition-colors hover:bg-accent hover:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50"
                            :class="{ 'bg-accent/50 text-accent-foreground': isSelected(option.value) }"
                        >
                            <span class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center">
                                <Check v-if="isSelected(option.value)" class="h-4 w-4" />
                            </span>
                            {{ option.label }}
                        </div>
                    </div>
                    <div v-if="groupedOptions.length === 0" class="px-2 py-4 text-center text-sm text-muted-foreground">
                        No results found.
                    </div>
                </div>
            </div>
        </div>

        <p v-if="error" class="text-xs font-medium text-destructive">
            {{ error }}
        </p>
    </div>
</template>
