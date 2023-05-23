<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-lg text-white p-4">Company</h2>
        </template>



        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-2">
            <Button v-if="can['create']" @click="create" size="small" class="ml-2">Create</Button>
            <div class="relative overflow-x-auto mt-2 ml-2 sm:rounded-2xl">
                <Table :columns="columns" :data-source="mapped_data" :loading="loading" class="mt-2" size="small">
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.key === 'actions'">
                            <!-- v-if="can['edit'] || can['delete']" -->
                            <Button size="small" v-if="can['edit']" type="primary" @click="edit(record.id)"
                                class="mr-2">Edit</Button>
                            <!-- <Button
                size="small"
                v-if="record.delete"
                danger
                @click="destroy(record.id)"
                >Delete</Button
              > -->
                            <!-- v-if="item.delete && can['delete']" -->
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

export default {
    components: {
        AppLayout,
        FlashMessage,
        Button,
        Table,
        Select,
        InputSearch,
    },

    //   props: ["data"],
    props: {
        mapped_data: Object,
        filters: Object,
        can: Object,
        //FOR MULTI-SELECT
        cochange: Object,
    },

    data() {
        return {
            co_id: this.$page.props.co_id,
            // co_id: this.cochange,
            // options: this.companies,
            columns: [
                // {
                //   title: "ID",
                //   dataIndex: "id",
                //   // sorter: (a, b) => a.id - b.id,
                //   width: "10%",
                // },
                {
                    title: "Name",
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
                    title: "Address",
                    dataIndex: "address",
                },
                {
                    title: "Email",
                    dataIndex: "email",
                },
                {
                    title: "Website",
                    dataIndex: "website",
                },
                {
                    title: "Phone",
                    dataIndex: "phone",
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
                route("companies"),
                {
                    // select: select.value,
                    // search: search.value
                    search: this.search,
                },
                { replace: true, preserveState: true }
            );
        },
        create() {
            this.$inertia.get(route("companies.create"));
        },

        edit(id) {
            this.$inertia.get(route("companies.edit", id));
        },

        destroy(id) {
            this.$inertia.delete(route("companies.destroy", id));
        },

        coch() {
            this.$inertia.get(route("companies.coch", this.co_id));
        },

        sort(field) {
            this.params.field = field;
            this.params.direction = this.params.direction === "asc" ? "desc" : "asc";
        },
    },
};
</script>
