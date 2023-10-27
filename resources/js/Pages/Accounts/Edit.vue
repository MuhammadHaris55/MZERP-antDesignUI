<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-lg text-white p-4">Edit Accounts</h2>
        </template>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-4">
            <div class="">
                <a-form
                    :form="form"
                    @submit.prevent="submit"
                    :label-col="{ span: 4 }"
                    :wrapper-col="{ span: 14 }"
                >
                    <a-form-item label="Name">
                        <a-input
                            v-model:value="form.name"
                            placeholder="Enter your name"
                        />

                        <div
                            class="text-red-700 px-4 py-2"
                            role="alert"
                            v-if="errors.name"
                        >
                            {{ errors.name }}
                        </div>
                    </a-form-item>

                    <a-form-item label="Group">
                        <a-tree-select
                            v-model:value="form.group"
                            :disabled="true"
                            :dropdown-style="{
                                maxHeight: '400px',
                                overflow: 'auto',
                            }"
                            :tree-data="option"
                            :field-names="{ label: 'name', value: 'id' }"
                            :treeDataSimpleMode="{ pID: 'parent_id' }"
                            placeholder="Please select Group"
                            tree-default-expand-all
                        >
                        </a-tree-select>
                    </a-form-item>
                    <a-form-item class="text-right">
                        <a-button type="primary" @click="submit()"
                            >Update Account</a-button
                        >
                    </a-form-item>
                </a-form>
            </div>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import { Form, Input, Button, TreeSelect } from "ant-design-vue";

export default {
    components: {
        AppLayout,
        "a-tree-select": TreeSelect,
        "a-form": Form,
        "a-form-item": Form.Item,
        "a-input": Input,
        "a-button": Button,
    },

    props: {
        errors: Object,
        account: Object,
        groups: Array,
        group_first: Object,
        // groups: Object,
    },

    data() {
        return {
            option: this.groups,
            form: this.$inertia.form({
                name: this.account.name,
                number: this.account.number,
                // group: this.groups[this.account.group_id],
                group: this.account.group_id,
            }),
        };
    },

    methods: {
        submit() {
            this.$inertia.put(
                route("accounts.update", this.account.id),
                this.form
            );
        },
    },
};
</script>
