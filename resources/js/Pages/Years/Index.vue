<template>
    <app-layout>
        <template #header>
            <div class="grid grid-cols-2">
                <h2 class="font-semibold text-lg text-white p-4">Years</h2>
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
                >Add Year</Button
            >
            <a
                v-if="can['create']"
                href="https://youtu.be/XgEW4fnNVN0"
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
                    class="mt-2"
                    size="small"
                >
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.key === 'actions'">
                            <!-- v-if="can['edit'] || can['delete']" -->
                            <Button
                                hidden
                                size="small"
                                v-if="can['edit']"
                                type="primary"
                                @click="edit(record.id)"
                                class="mr-2"
                                >Edit</Button
                            >
                            <Button
                                class="mr-2"
                                size="small"
                                v-if="record.delete && can['delete']"
                                danger
                                @click="destroy(record.id)"
                                >Delete</Button
                            >
                            <Button
                                size="small"
                                v-if="record.closed"
                                type="primary"
                                ghost
                                @click="close(record.id)"
                                >Close Year</Button
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
import Multiselect from "@suadelabs/vue3-multiselect";

export default {
    components: {
        AppLayout,
        FlashMessage,
        Button,
        Table,
        Select,
        InputSearch,
        Multiselect,
    },

    // props: ["data", "companies", "company"],
    props: {
        companies: Object,
        company: Object,
        can: Object,
        mapped_data: Object,
    },

    data() {
        return {
            // co_id: this.$page.props.co_id,
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
                    title: "Company",
                    dataIndex: "company_name",
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
                    title: "Year Start",
                    dataIndex: "begin",
                },
                {
                    title: "Year End",
                    dataIndex: "end",
                },
                {
                    title: "Actions",
                    dataIndex: "actions",
                    key: "actions",
                },
            ],
        };
    },

    methods: {
        create() {
            this.$inertia.get(route("years.create"));
        },

        edit(id) {
            this.$inertia.get(route("years.edit", id));
        },

        destroy(id) {
            this.$inertia.delete(route("years.destroy", id));
        },

        close(id) {
            this.$inertia.get(route("years.close", id));
        },

        coch(value) {
            // this.$inertia.get(route("companies.coch", this.co_id));
            this.$inertia.get(route("companies.coch", value));
        },
    },
};
</script>
