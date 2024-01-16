<template>
    <app-layout>
        <template #header>
            <div class="grid grid-cols-2">
                <h2 class="font-semibold text-lg text-white p-4">Ledgers</h2>
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
                        show-search
                        filterOption="true"
                        optionFilterProp="name"
                        :options="options"
                        :field-names="{ label: 'name', value: 'id' }"
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
            <form
                target="_blank"
                @submit.prevent="submit_range"
                v-bind:action="'range/' + form.account_id"
                ref="form_range"
            >
                <a-select
                    style="width: 40%"
                    v-model:value="form.account_id"
                    :options="option"
                    show-search
                    :field-names="{ label: 'name', value: 'id' }"
                    filterOption="true"
                    optionFilterProp="name"
                    mode="single"
                    placeholder="Select Account"
                    @change="getledger"
                    showArrow
                />
                <a-input
                    style="width: 20%"
                    v-model:value="form.date_start"
                    :min="form.start"
                    type="date"
                    :max="form.end"
                    @change="getledger"
                    name="date_start"
                />
                <div v-if="errors.date_start">{{ errors.date_start }}</div>

                <a-input
                    style="width: 20%"
                    v-model:value="form.date_end"
                    :min="form.start"
                    :max="form.end"
                    type="date"
                    placeholder="Enter End date:"
                    @change="getledger"
                    name="date_end"
                />

                <div v-if="errors.date_end">{{ errors.date_end }}</div>
                <Button html-type="submit" type="primary" ghost class="ml-2"
                    >Generate Ledger</Button
                >

                <a
                    href="https://youtu.be/jkxwzkwKA5E"
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
            </form>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import FlashMessage from "@/Layouts/FlashMessage";
import { Button, Table, Select, Input } from "ant-design-vue";
import "ant-design-vue/dist/antd.css";

import JetButton from "@/Jetstream/Button";
import { useForm } from "@inertiajs/inertia-vue3";
import Multiselect from "@suadelabs/vue3-multiselect";

export default {
    components: {
        AppLayout,
        FlashMessage,
        Button,
        Table,
        Select,
        JetButton,
        Multiselect,
        "a-input": Input,
        "a-button": Button,
        "a-select": Select,
    },

    props: {
        errors: Object,
        data: Object,
        companies: Object,
        company: Object,
        years: Object,
        year: Object,
        accounts: Array,
        account_first: Object,
        entries: Object,
        debits: Object,
        credits: Object,
        balance: Object,
        prebal: Object,
        date_start: Object,
        date_end: Object,
        min_start: Object,
        max_end: Object,
        mapped_data: Object,
    },

    data() {
        return {
            // co_id: this.$page.props.co_id,
            co_id: this.company,
            options: this.companies,
            search: "",
            selected: this.company.name,
            years: this.years,
            selected_year: this.year.name,
            option: this.accounts,

            columns: [
                {
                    title: "Reference",
                    dataIndex: "ref",
                    width: "20%",
                },
                {
                    title: "Date",
                    dataIndex: "date",
                },
                {
                    title: "Description",
                    dataIndex: "description",
                },
                {
                    title: "Debit",
                    dataIndex: "debit",
                },
                {
                    title: "Credit",
                    dataIndex: "credit",
                },
                {
                    title: "Balance",
                    dataIndex: "balance",
                },
            ],

            form: {
                account_id: this.account_first
                    ? this.account_first["id"]
                    : this.accounts,
                date_start: this.date_start ? this.date_start : this.min_start,
                date_end: this.date_end ? this.date_end : this.max_end,
                start: this.min_start
                    ? this.min_start
                    : new Date().toISOString().substr(0, 10),
                end: this.max_end
                    ? this.max_end
                    : new Date().toISOString().substr(0, 10),
            },
        };
    },

    methods: {
        // ------------------------- To generate PDF ------------------------
        submit_range: function () {
            this.$refs.form_range.submit();
        },
        // ----------------------- To generate on screen ledger -----------------------
        getledger() {
            this.$inertia.get(route("ledgers", this.form));
        },
        meth() {
            this.form.get(route("range"));
        },
        //TO GENERATE AN PDF WITH BUTTON
        submit: function () {
            this.$refs.form.submit();
        },

        create() {
            this.$inertia.get(route("years.create"));
        },

        route() {
            this.$inertia.get(route("pd"));
        },

        route() {
            this.$inertia.get(route("trialbalance"));
        },

        edit(id) {
            this.$inertia.get(route("years.edit", id));
        },

        destroy(id) {
            this.$inertia.delete(route("years.destroy", id));
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
