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
            <a
                v-if="can['create']"
                href="https://youtu.be/niPKqWh-hJo"
                target="_blank"
                class="ant-btn ant-btn-sm ml-2"
                style="float: right"
                >Help</a
            >
            <div class="relative overflow-x-auto mt-2 ml-2 sm:rounded-2xl">
                <Table
                    :columns="columns"
                    :data-source="mapped_data"
                    :loading="loading"
                    :pagination="{
                        current: currentPage,
                        pageSize: pageSize,
                        total: total,  // Adjust total according to your data
                        showSizeChanger: true,       // Show option to change page size
                        pageSizeOptions: ['10', '20', '30', '50'],  // Page size options
                    }"
                    @change="handlePaginationChange"
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
import { Button, Table, Select, InputSearch, Modal } from "ant-design-vue";
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
        Modal,
        JetButton,
        Paginator,
        throttle,
        pickBy,
        Multiselect,
    },

    props: {
        data: Object,
        mapped_data: Object,
        filters: Object,
        total: Object,
        current_page: Object,
        per_page: Object,
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
            currentPage: this.current_page, 
            pageSize: this.per_page,
            total: this.total,
            search: "",
            selected: this.company.name,

            columns: [
                {
                    title: "Name of Account",
                    dataIndex: "name",
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
            },
        };
    },

    methods: {

        fetchData() {
            this.$inertia.get(route("accounts"), {
                search: this.search,
                page: this.currentPage,
                pageSize: this.pageSize,
            }, {
                replace: true,
                preserveState: true,
                onSuccess: (response) => {
                    this.currentPage = response.props.current_page;
                    this.pageSize = response.props.per_page;       
                    this.total = response.props.total;
                }
            });
        },

        onSearch() {
            this.currentPage = 1;  // Jab search ho, page 1 par le aayein
            this.fetchData();
        },

        handlePaginationChange(pagination) {
            this.currentPage = pagination.current;
            this.pageSize = pagination.pageSize;
            this.fetchData();
        },

        // onSearch() {
        //     this.$inertia.get(
        //         route("accounts"),
        //         {
        //             // select: select.value,
        //             // search: search.value
        //             search: this.search,
        //         },
        //         { replace: true, preserveState: true }
        //     );
        // },
        create() {
            this.$inertia.get(route("accounts.create"));
        },

        edit(id) {
            this.$inertia.get(route("accounts.edit", id));
        },

        destroy(id) {
            Modal.confirm({
                title: "Do you really want to delete Accounts?",
                onOk: () => {
                    this.$inertia.delete(route("accounts.destroy", id));
                },
                onCancel: () => {},
            });
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
