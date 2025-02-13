<template>
    <app-layout>
        <template #header>
            <div class="grid grid-cols-2">
                <h2 class="font-semibold text-lg text-white p-4">
                    Transactions Detail
                </h2>
                <div class="justify-end">
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
                        class="w-1/2 ml-2"
                    />
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-2">
            <!-- <div class="p-2 mr-2 mb-2 ml-2 flex flex-wrap"> -->
            <label class="pl-2">Search : </label>
            <a-input
                size="Medium"
                class="ml-2"
                v-model:value="search"
                placeholder="Search here"
                style="width: 200px"
                @change="onSearch"
            />
            <label class="pl-2">From : </label>
            <a-input
                style="width: 20%"
                v-model:value="d_start"
                :min="start"
                type="date"
                :max="end"
                @change="onSearch"
                name="date_start"
            />
            <div v-if="errors.date_start">{{ errors.date_start }}</div>
            <label class="pl-2">To : </label>
            <a-input
                style="width: 20%"
                v-model:value="d_end"
                :min="start"
                :max="end"
                type="date"
                placeholder="Enter End date:"
                @change="onSearch"
                name="date_end"
            />

            <a
                href="javascript:void(0)"
                class="ant-btn ant-btn-sm ml-2"
                style="float: right"
                target="_blank"
                @click="exportData"
                >Export</a
            >

            <div v-if="errors.date_end">{{ errors.date_end }}</div>

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
                    <template #bodyCell="{ column, record }"> </template>
                    <template #description="{ text }">
                        <a-tooltip placement="topLeft" title="Prompt Text">
                            <p>{{ text }}</p>
                        </a-tooltip>
                    </template>
                </Table>
            </div>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import {
    Button,
    Form,
    Input,
    Table,
    Select,
    InputSearch,
    Anchor,
    Checkbox,
    Modal,
    Tooltip,
} from "ant-design-vue";
import JetButton from "@/Jetstream/Button";
import Paginator from "@/Layouts/Paginator";
import moment from "moment";
import { pickBy } from "lodash";
import { throttle } from "lodash";
import Multiselect from "@suadelabs/vue3-multiselect";
import { useForm } from "@inertiajs/inertia-vue3";

export default {
    components: {
        AppLayout,
        Button,
        Table,
        Select,
        InputSearch,
        Checkbox,
        Modal,
        JetButton,
        Paginator,
        throttle,
        pickBy,
        moment,
        Multiselect,
        useForm,
        "a-form": Form,
        "a-form-item": Form.Item,
        "a-input": Input,
        "a-anchor-link": Anchor,
        "a-tooltip": Tooltip,
    },

    props: {
        mapped_data: Object,
        errors: Object,
        data: Object,
        filters: Object,
        companies: Object,
        company: Object,
        years: Object,
        year: Object,
        yearclosed: Object,
        can: Object,
        date_start: Object,
        date_end: Object,
        min_start: Object,
        max_end: Object,
        total: Object,
        current_page: Object,
        per_page: Object,				

    },

    data() {
        return {
            // co_id: this.$page.props.co_id,
            co_id: this.company,
            options: this.companies,
            search: "",
            selected: this.company.name,
            yr_id: this.$page.props.yr_id,
            years: this.years,
            selected_year: this.year.name,
            d_start: this.date_start ? this.date_start : this.min_start,
            d_end: this.date_end ? this.date_end : this.max_end,
            start: this.min_start
                ? this.min_start
                : new Date().toISOString().substr(0, 10),
            end: this.max_end
                ? this.max_end
                : new Date().toISOString().substr(0, 10),
            currentPage: this.current_page, 
            pageSize: this.per_page,
            total: this.total,
            columns: [
                // {
                //   title: "ID",
                //   dataIndex: "id",
                //   // sorter: (a, b) => a.id - b.id,
                //   width: "10%",
                // },
                // {
                //     title: "Delete",
                //     dataIndex: "approve",
                //     key: "delete",
                // },

                {
                    title: "Date (Y-M-D)",
                    dataIndex: "date",
                    width: "15%",
                },
                {
                    title: "Reference",
                    dataIndex: "ref",
                    width: "10%",
                },
                {
                    title: "Description",
                    dataIndex: "description",
                    width: "25%",
                },
                {
                    title: "Accounts",
                    dataIndex: "account",
                    key: "account",
                    width: "25%",
                },
                {
                    title: "Debit",
                    dataIndex: "debit",
                    key: "debit",
                    width: "15%",
                },
                {
                    title: "Credit",
                    dataIndex: "credit",
                    key: "credit",
                    width: "15%",
                },
            ],

            params: {
                search: this.filters.search,
                date_start: this.filters.date_start,
                date_end: this.filters.date_end,
                field: this.filters.field,
                direction: this.filters.direction,
            },
        };
    },

    methods: {
        fetchData() {
            this.$inertia.get(route("documents_detail"), {
                search: this.search,
                date_start: this.d_start,
                date_end: this.d_end,
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
        exportData() {
            const params = new URLSearchParams({
                search: this.search || '',
                date_start: this.d_start || '',
                date_end: this.d_end || '',
            }).toString();

            window.location.href = route("transactions_export") + "?" + params;
        },

        onSearch() {
            this.currentPage = 1;
            this.fetchData();
        },

        handlePaginationChange(pagination) {
            this.currentPage = pagination.current;
            this.pageSize = pagination.pageSize;
            this.fetchData();
        },


        // onSearch() {
        //     this.$inertia.get(
        //         route("documents_detail"),
        //         {
        //             search: this.search,
        //             date_start: this.d_start,
        //             date_end: this.d_end,
        //         },
        //         { replace: true, preserveState: true }
        //     );
        // },

        coch(value) {
            this.$inertia.get(route("companies.coch", value));
        },
        yrch(value) {
            this.$inertia.get(route("years.yrch", value));
        },

        sort(field) {
            this.params.field = field;
            this.params.direction =
                this.params.direction === "asc" ? "desc" : "asc";
        },
        search_data() {
            let params = pickBy(this.params);
            this.$inertia.get(this.route("documents"), params, {
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
                    this.$inertia.get(this.route("documents"), params, {
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
