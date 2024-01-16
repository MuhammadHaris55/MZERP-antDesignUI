<template>
    <app-layout>
        <template #header>
            <div class="grid grid-cols-2">
                <h2 class="font-semibold text-lg text-white p-4">Dashboard</h2>
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
        <div class="container mx-auto">
            <a
                href="https://youtu.be/c0PZvl0c1lI"
                target="_blank"
                class="ant-btn ant-btn-sm ml-2"
                style="float: right"
                >Help</a
            >
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="(item, index) in dashboard_variables"
                    :key="index"
                    class="flex justify-center text-sm border-2 border-blue-300 rounded-xl shadow-lg p-2 bg-blue-100"
                >
                    {{ item.name }}
                    <br />
                    PKR. {{ item.amount }}
                </div>
            </div>
            <br />
            <div>
                <div id="chart">
                    <apexchart
                        type="line"
                        height="350"
                        :options="chartOptions"
                        :series="series"
                    ></apexchart>
                </div>
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- <div
                    class="bg-gray-800 text-white text-center text-3xl p-4 overflow-hidden shadow-xl sm:rounded-lg"
                >
                    Welcome to SA-accounting
                </div> -->

                <!-- <welcome /> -->
                <div
                    v-if="can['edit'] && user.email == 'haris@gmail.com'"
                    class="inline-flex py-2 px-4 bg-gray-800 text-white m-4 rounded-lg shadow-lg overflow-auto md:w-1/2"
                >
                    <form
                        @submit.prevent="
                            form.post(route('dashboard.roleassign'))
                        "
                    >
                        <div class="flex-col m-2">
                            <h3>Assign usage rights to another user</h3>
                        </div>
                        <div class="flex-col m-2">
                            <label
                                for="email"
                                class="inline-flex text-white mb-2 w-20"
                                >Email:</label
                            >
                            <input
                                type="text"
                                id="email"
                                v-model="form.email"
                                class="bg-gray-600 text-white rounded focus:outline-none focus:shadow-outline px-1 hover:text-blue-200 w-52"
                                label="email"
                                placeholder="Enter Email of User"
                            />
                            <div v-if="errors.email">{{ errors.email }}</div>
                        </div>
                        <div class="p-2 mr-2 mb-2 mt-4 ml-6 flex flex-wrap">
                            <input
                                v-model="form.role"
                                id="manager"
                                name="manager"
                                type="radio"
                                value="manager"
                                class="pr-2 mt-1 pb-2 rounded-md placeholder-indigo-300"
                            />
                            <label
                                for="manager"
                                class="mb-2 ml-4 mr-5 text-right w-38 font-bold"
                                >Manager
                            </label>

                            <input
                                v-model="form.role"
                                id="user"
                                name="user"
                                type="radio"
                                value="user"
                                class="pr-2 mt-1 pb-2 rounded-md placeholder-indigo-300"
                            />
                            <label
                                for="user"
                                class="mb-2 ml-2 mr-5 text-right w-38 font-bold"
                                >User
                            </label>
                            <div v-if="errors.role">{{ errors.role }}</div>
                        </div>

                        <div style="min-width: 25%" class="flex-1 float-right">
                            <multiselect
                                class="rounded-md border border-black"
                                placeholder="Select Company."
                                v-model="form.company_id"
                                track-by="id"
                                label="name"
                                :options="all_companies"
                            >
                            </multiselect>
                            <div v-if="errors.company_id">
                                {{ errors.company_id }}
                            </div>
                        </div>
                        <div class="flex-col m-2">
                            <button
                                class="flex-wrap mb-2 ml-20 px-4 py-1 rounded-lg bg-gray-600 text-white hover:bg-gray-700 focus:outline-none focus:shadow-outline"
                                type="submit"
                                :disabled="form.processing"
                            >
                                Assign
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import Welcome from "@/Jetstream/Welcome";
import { useForm } from "@inertiajs/inertia-vue3";
import Multiselect from "@suadelabs/vue3-multiselect";
import VueApexCharts from "vue3-apexcharts";
import { Select } from "ant-design-vue";

export default {
    components: {
        AppLayout,
        Welcome,
        Select,
        apexchart: VueApexCharts,
        Multiselect,
    },

    props: {
        errors: Object,
        // roles: Object,
        can: Object,
        all_companies: Object,
        companies: Object,
        years: Object,
        year: Object,
        company: Object,
        user: Object,
        monthly_revenue_value: Array,
        monthly_revenue_month: Array,
        dashboard_variables: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
            all_companies: this.all_companies,
            series: [
                {
                    name: "Profit/Loss",
                    data: this.monthly_revenue_value,
                },
            ],
            chartOptions: {
                chart: {
                    height: 350,
                    type: "line",
                    zoom: {
                        enabled: false,
                    },
                },
                dataLabels: {
                    enabled: false,
                },
                stroke: {
                    curve: "straight",
                },
                title: {
                    text: "Profit / Loss by Month",
                    align: "left",
                },
                grid: {
                    row: {
                        colors: ["#f3f3f3", "transparent"], // takes an array which will be repeated on columns
                        opacity: 0.5,
                    },
                },
                xaxis: {
                    categories: this.monthly_revenue_month,
                },
            },
            options: this.companies,
            selected: this.company ? this.company.name : "",
            selected_year: this.year ? this.year.name : "",
            years: this.years,
        };
    },
    setup(props) {
        const form = useForm({
            email: null,
            role: "user",
            // company_id: null,
            company_id: props.companies[0],
            // role: props.roles[0].id,
        });

        return { form };
    },

    methods: {
        coch(value) {
            this.$inertia.get(route("companies.coch", value));
        },
        yrch(value) {
            this.$inertia.get(route("years.yrch", value));
        },
    },
};
</script>
