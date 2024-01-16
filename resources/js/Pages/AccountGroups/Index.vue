<template>
    <app-layout>
        <template #header>
            <div class="grid grid-cols-2">
                <h2 class="font-semibold text-lg text-white p-4">
                    Account Groups
                </h2>
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

        <!-- <FlashMessage /> -->

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-2">
            <Button
                v-if="can['create']"
                @click="create"
                size="small"
                class="ml-2"
                >Create Groups</Button
            >
            <Button
                v-if="exists && can['create']"
                size="small"
                @click="generate"
                class="ml-2"
                >Auto Generate Groups</Button
            >

            <a
                v-if="can['create']"
                href="https://youtu.be/ZOZOKJc37b0"
                target="_blank"
                class="ant-btn ant-btn-sm ml-2"
                style="float: right"
                >Help</a
            >

            <InputSearch
                size="small"
                class="ml-2"
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

import { ref } from "vue";
import { Inertia } from "@inertiajs/inertia";

export default {
    components: {
        AppLayout,
        FlashMessage,
        Button,
        Table,
        Select,
        InputSearch,
    },

    props: {
        filters: Object,
        companies: Object,
        company: Object,
        exists: Object,
        can: Object,
        mapped_data: Object,
    },

    data() {
        return {
            co_id: this.$page.props.co_id,
            co_id: this.company,
            options: this.companies,
            search: "",
            selected: this.company.name,

            columns: [
                // {
                //   title: "ID",
                //   dataIndex: "id",
                //   // sorter: (a, b) => a.id - b.id,
                //   width: "10%",
                // },
                {
                    title: "Group Name",
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
                    title: "Group Type",
                    dataIndex: "type_name",
                },
                {
                    title: "Company Name",
                    dataIndex: "company_name",
                },
                {
                    title: "Actions",
                    dataIndex: "actions",
                    key: "actions",
                },
            ],

            // const options = [{
            //     value: 'name',
            //     label: 'Name',
            //   }, {
            //     value: 'email',
            //     label: 'Email',
            // }],

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
                route("accountgroups"),
                {
                    // select: select.value,
                    // search: search.value
                    search: this.search,
                },
                { replace: true, preserveState: true }
            );
        },
        create() {
            this.$inertia.get(route("accountgroups.create"));
        },

        edit(id) {
            this.$inertia.get(route("accountgroups.edit", id));
        },

        destroy(id) {
            this.$inertia.delete(route("accountgroups.destroy", id));
        },

        generate() {
            this.$inertia.get(route("accountgroups.generate"));
        },

        coch(value) {
            this.$inertia.get(route("companies.coch", value));
        },

        sort(field) {
            this.params.field = field;
            this.params.direction =
                this.params.direction === "asc" ? "desc" : "asc";
        },

        check() {
            console.log("click");

            setTimeout(() => {
                console.log("timer");
                // this.postRecordSolo('clientStore/UPDATE_RECORDS_NO_TAB', this.endPoint, true)
            }, 1000);
        },

        // addRecord () {
        //   setTimeout(() => {
        //     this.postRecordSolo('clientStore/UPDATE_RECORDS_NO_TAB', this.endPoint, true)
        //   }, 1000)
        // }
    },
};
</script>
