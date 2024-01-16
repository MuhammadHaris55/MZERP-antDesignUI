<template>
    <app-layout>
        <template #header>
            <div class="grid grid-cols-2">
                <h2 class="font-semibold text-lg text-white p-4">Voucher</h2>
                <div class="flex justify-end items-center">
                    <Select
                        v-model:value="selected_year"
                        :options="years"
                        :field-names="{ label: 'end', value: 'id' }"
                        filterOption="true"
                        optionFilterProp="name"
                        mode="single"
                        placeholder="Please select"
                        showArrow
                        @change="yrch"
                        class="w-1/2"
                    />
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
                >Create Voucher</Button
            >
            <a
                v-if="can['create']"
                href="https://youtu.be/oF1X7xJcO88"
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

    props: [
        "balances",
        "mapped_data",
        "companies",
        "company",
        "years",
        "year",
        "can",
    ],

    data() {
        return {
            // co_id: this.$page.props.co_id,
            co_id: this.company,
            options: this.companies,
            search: "",
            selected: this.company.name,
            years: this.years,
            selected_year: this.year.name,

            columns: [
                {
                    title: "Voucher Name",
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
                    title: "Prefix",
                    dataIndex: "prefix",
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
            this.$inertia.get(route("documenttypes.create"));
        },

        edit(id) {
            this.$inertia.get(route("documenttypes.edit", id));
        },

        destroy(id) {
            this.$inertia.delete(route("documenttypes.destroy", id));
        },
        coch(value) {
            this.$inertia.get(route("companies.coch", value));
        },
        yrch(value) {
            this.$inertia.get(route("years.yrch", value));
        },
    },
};
</script>
