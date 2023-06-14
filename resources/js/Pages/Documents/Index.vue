<template>
    <app-layout>
        <template #header>
            <div class="grid grid-cols-2">
                <h2 class="font-semibold text-lg text-white p-4">
                    Transactions
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

            <Button
                v-if="yearclosed && can['create']"
                size="small"
                @click="create"
                class="ml-2"
                >Create</Button
            >

            <InputSearch
                size="small"
                class="ml-2"
                v-model:value="search"
                placeholder="input search text"
                style="width: 200px"
                @search="onSearch"
            />
            <input
                hidden
                id="selected"
                @click="checkAll()"
                v-model="form_delete_transaction.selected_arr"
            />
            <label
                v-if="isCheckAll"
                class="ant-btn ant-btn-sm ml-2"
                for="selected"
            >
                Un-Select All</label
            >
            <label v-else class="ant-btn ant-btn-sm ml-2" for="selected">
                Select All</label
            >
            <Button danger ghost @click="selete()" class="ml-2" size="small"
                >Delete</Button
            >

            <!-- <div v-if="can['create']" class="mt-2">
                <form @submit.prevent="submit">
                    <a-input
                        style="width: 30%; font-size: 12px"
                        size="small"
                        type="file"
                        placeholder="Upload Excel Sheet"
                        title="Upload Excel Sheet"
                        v-on:change="onFileChange"
                        accept=".xlsx"
                    />
                    <div
                        class="ml-2 bg-red-100 border border-red-400 text-red-700 px-4 py-1 rounded inline-block"
                        role="alert"
                        v-if="errors.file"
                    >
                        {{ errors.file }}
                    </div>
                    <Button
                        class="ml-2"
                        size="small"
                        :disabled="form.processing"
                        type="button"
                        htmlType="submit"
                        >Upload Sales Sheet</Button
                    >

                    <a
                        class="ant-btn ant-btn-sm ml-2"
                        type="primary"
                        ghost
                        :href="'documents/downloadFile'"
                    >
                        Download Sales Format
                    </a>
                    <a
                        class="ant-btn ant-btn-sm ml-2"
                        type="primary"
                        target="_blank"
                        ghost
                        :href="'documents/Accountpdf'"
                    >
                        Download Accounts
                    </a>
                </form>
            </div> -->
            <a
                class="ant-btn ant-btn-sm ml-2"
                type="primary"
                target="_blank"
                ghost
                :href="'documents/Accountpdf'"
            >
                Download Accounts
            </a>
            <div class="relative overflow-x-auto mt-2 ml-2 sm:rounded-2xl">
                <Table
                    :columns="columns"
                    :data-source="mapped_data"
                    :loading="loading"
                    class="mt-2"
                    size="small"
                >
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.key === 'delete'">
                            <!-- <Checkbox
                v-if="record.approve"
                v-model:value="record.name"
                checked
              />
              <Checkbox
                v-else
                v-model:value="record.name"
                v-model:checked="unchecked"
                @change="updateCheckall()"
              /> -->

                            <input
                                class="focus:ring-green-500 checkbox-item"
                                type="checkbox"
                                @change="updateCheckall(record.id)"
                            />
                            <!--
                                v-bind:value="record.id"
                                v-model="form.selected_arr"
                            name="selected_arr"-->
                        </template>
                        <template v-if="column.key === 'actions'">
                            <!-- v-if="can['edit'] || can['delete']" -->
                            <Button
                                size="small"
                                v-if="yearclosed && can['edit']"
                                type="primary"
                                @click="edit(record.id)"
                                class="mr-2"
                                >Edit</Button
                            >
                            <Button
                                size="small"
                                v-else
                                type="primary"
                                @click="edit(record.id)"
                                class="mr-2"
                                >Show</Button
                            >
                            <Button
                                size="small"
                                v-if="record.delete && can['delete']"
                                danger
                                @click="destroy(record.id)"
                                >Delete</Button
                            >
                            <a
                                :href="'pd/' + record.id"
                                target="_blank"
                                class="ant-btn ant-btn-sm"
                                >Voucher in PDF</a
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
import {
    Button,
    Form,
    Input,
    Table,
    Select,
    InputSearch,
    Anchor,
    Checkbox,
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
    },

    setup() {
        const form = useForm({
            avatar: null,
        });
        const form_delete_transaction = useForm({
            selected_arr: [],
        });
        return { form, form_delete_transaction };
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

            isCheckAll: false,

            columns: [
                // {
                //   title: "ID",
                //   dataIndex: "id",
                //   // sorter: (a, b) => a.id - b.id,
                //   width: "10%",
                // },
                {
                    title: "Delete",
                    dataIndex: "approve",
                    key: "delete",
                },
                {
                    title: "Reference",
                    dataIndex: "ref",
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
                    title: "Date",
                    dataIndex: "date",
                },
                {
                    title: "Description",
                    dataIndex: "description",
                },
                {
                    title: "Actions",
                    dataIndex: "actions",
                    key: "actions",
                },
            ],

            params: {
                search: this.filters.search,
                field: this.filters.field,
                direction: this.filters.direction,
            },
        };
    },

    methods: {
        onSearch() {
            this.$inertia.get(
                route("documents"),
                {
                    // select: select.value,
                    // search: search.value
                    search: this.search,
                },
                { replace: true, preserveState: true }
            );
        },
        //   for file upload
        submit() {
            if (this.form.avatar) {
                this.form.post(route("sales.trial.read"));
            } else {
                alert("Please select file first");
            }
        },
        downloadFormat() {
            this.$inertia.get(route("documents.downloadFile"));
        },

        onFileChange(e) {
            var files = e.target.files || e.dataTransfer.files;
            if (!files.length) return;
            this.form.avatar = files[0];
        },
        // file upload end
        create() {
            this.$inertia.get(route("documents.create"));
        },

        edit(id) {
            this.$inertia.get(route("documents.edit", id));
        },

        destroy(id) {
            this.$inertia.delete(route("documents.destroy", id));
        },

        coch(value) {
            this.$inertia.get(route("companies.coch", value));
        },
        // yrch() {
        //   this.$inertia.get(route("companies.yrch", this.yr_id));
        // },
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

        checkAll: function () {
            this.isCheckAll = !this.isCheckAll;
            this.form_delete_transaction.selected_arr = [];
            var checkboxes = document.getElementsByClassName("checkbox-item");
            if (this.isCheckAll) {
                // Check all
                for (var key in this.mapped_data) {
                    // checkboxes[key].checked = true;
                    this.form_delete_transaction.selected_arr.push(
                        this.mapped_data[key].id
                    );
                }
                for (var i = 0; i < checkboxes.length; i++) {
                    checkboxes[i].checked = true;
                }
            } else {
                for (var i = 0; i < checkboxes.length; i++) {
                    checkboxes[i].checked = false;
                }
            }
        },
        updateCheckall: function (id) {
            if (this.form_delete_transaction.selected_arr.includes(id)) {
                console.log("include" + id);
                const index =
                    this.form_delete_transaction.selected_arr.indexOf(id);
                if (index !== -1) {
                    this.form_delete_transaction.selected_arr.splice(index, 1); // Array se value ko nikalna
                }
            } else {
                this.form_delete_transaction.selected_arr.push(id);
            }
            if (
                this.form_delete_transaction.selected_arr.length ==
                this.mapped_data.length
            ) {
                console.log("all checked true");
                this.isCheckAll = true;
            } else {
                this.isCheckAll = false;
            }
        },
        selete: function () {
            if (this.form_delete_transaction.selected_arr.length >> 0) {
                this.$inertia.post(
                    route("delete_transactions", this.form_delete_transaction)
                );
                // this.selected_arr = [];
            } else {
                alert("Please select file");
            }
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
