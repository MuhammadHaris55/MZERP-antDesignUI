<template>
    <app-layout>
        <template #header>
            <div class="grid grid-cols-2">
                <h2 class="font-semibold text-lg text-white p-4">Accounts</h2>
                <div class="flex justify-end items-center">
                    <Select
                        v-model:value="selected"
                        :options="options"
                        :field-names="{ label: 'name', value: 'id' }"
                        filterOption="true"
                        optionFilterProp="name"
                        mode="single"
                        placeholder="Please select"
                        showArrow
                        @change="coch"
                        class="w-1/2"
                    />
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-2">
            <Button
                v-if="can['create']"
                size="small"
                @click="create"
                class="ml-2"
                >Create Account</Button
            >

            <InputSearch
                class="ml-2"
                size="small"
                v-model:value="search"
                placeholder="Search here"
                style="width: 200px"
                @search="onSearch"
            />

            <div class="relative overflow-x-auto mt-2 ml-2 sm:rounded-2xl">
                <Table
                    :columns="columns"
                    :data-source="mapped_data"
                    :loading="loading"
                    class="mt-2"
                    size="small"
                >
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.key === 'actions'">
                            <!-- v-if="can['edit'] || can['delete']" -->
                            <Button
                                size="small"
                                v-if="can['edit']"
                                type="primary"
                                @click="edit(record.id)"
                                class="mr-2"
                                >Edit</Button
                            >
                            <Button
                                size="small"
                                v-if="record.delete && can['delete']"
                                danger
                                @click="destroy(record.id)"
                                >Delete</Button
                            >
                        </template>
                    </template>
                </Table>
            </div>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import FlashMessage from "@/Layouts/FlashMessage";
import { Button, Table, Select, InputSearch } from "ant-design-vue";
import "ant-design-vue/dist/antd.css";

import JetButton from "@/Jetstream/Button";
import Paginator from "@/Layouts/Paginator";
import { pickBy } from "lodash";
import { throttle } from "lodash";
import Multiselect from "@suadelabs/vue3-multiselect";

export default {
    components: {
        AppLayout,
        FlashMessage,
        Button,
        Table,
        Select,
        InputSearch,

        JetButton,
        Paginator,
        throttle,
        pickBy,
        Multiselect,
    },

    props: {
        data: Object,
        balances: Object,
        mapped_data: Object,
        filters: Object,
        can: Object,
        companies: Array,
        company: Object,
    },

    data() {
        return {
            // co_id: this.$page.props.co_id,
            co_id: this.company,
            options: this.companies,
            search: "",
            selected: this.company.name,

            search: "",
            selected: this.company.name,

            columns: [
                {
                    title: "Name of Account",
                    dataIndex: "name",
                    // sorter: (a, b) => {
                    //     const nameA = a.name.toUpperCase();
                    //     const nameB = b.name.toUpperCase();
                    //     if (nameA < nameB) {
                    //         return -1;
                    //     }
                    //     if (nameA > nameB) {
                    //         return 1;
                    //     }
                    //     return 0;
                    //     },
                    width: "20%",
                },
                {
                    title: "Group of Account",
                    dataIndex: "group_name",
                },
                {
                    title: "Actions",
                    dataIndex: "actions",
                    key: "actions",
                },
            ],

            params: {
                search: this.filters.search,
                // field: this.filters.field,
                // direction: this.filters.direction,
            },
        };
    },

    methods: {
        onSearch() {
            this.$inertia.get(
                route("accounts"),
                {
                    // select: select.value,
                    // search: search.value
                    search: this.search,
                },
                { replace: true, preserveState: true }
            );
        },
        create() {
            this.$inertia.get(route("accounts.create"));
        },

        edit(id) {
            this.$inertia.get(route("accounts.edit", id));
        },

        destroy(id) {
            this.$inertia.delete(route("accounts.destroy", id));
        },
        coch(value) {
            this.$inertia.get(route("companies.coch", value));
        },

        sort(field) {
            this.params.field = field;
            this.params.direction =
                this.params.direction === "asc" ? "desc" : "asc";
        },
        search_data() {
            let params = pickBy(this.params);
            this.$inertia.get(this.route("accounts"), params, {
                replace: true,
                preserveState: true,
            });
        },
    },
    watch: {
        params: {
            handler: throttle(function () {
                let params = pickBy(this.params);
                if (params.search == null) {
                    this.$inertia.get(this.route("accounts"), params, {
                        replace: true,
                        preserveState: true,
                    });
                }
            }, 150),
            deep: true,
        },
    },
};
</script>
