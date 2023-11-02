<template>
    <app-layout>
        <template #header>
            <div class="grid grid-cols-2">
                <h2 class="font-semibold text-lg text-white p-4">Reports</h2>
                <div class="justify-end">
                    <Select
                        v-model:value="selected_year"
                        :options="years"
                        :field-names="{ label: 'end', value: 'id' }"
                        filterOption="true"
                        optionFilterProp="name"
                        mode="single"
                        placeholder="Please select Multi Accounts"
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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-4">
            <div>
                <h2 class="text-2xl font-semibold">Trial Balance</h2>
            </div>

            <a-row>
                <a-col :span="12">
                    <label>As at: </label>
                    <form
                        target="_blank"
                        @submit.prevent="submit_trial_range"
                        v-bind:action="'trialbalance'"
                        ref="form_trial_range"
                    >
                        <a-input
                            :min="form.start"
                            :max="form.end"
                            v-model:value="form.date"
                            style="width: 70%"
                            type="date"
                            label="date"
                            placeholder="Enter Begin date:"
                            class="pr-2 ml-2 pb-2 rounded-md"
                            name="date"
                            required
                        />

                        <Button
                            type="primary"
                            :disabled="form.processing"
                            htmlType="submit"
                            >Trial Balance</Button
                        >
                        <!-- </div> -->
                    </form>
                </a-col>
            </a-row>
            <br />
            <div>
                <h2 class="text-2xl font-semibold">Financial Statements</h2>
            </div>
            <a-row>
                <a-col :span="12">
                    <label>As at: </label>
                    <form
                        target="_blank"
                        @submit.prevent="submit_bs_range"
                        v-bind:action="'bs'"
                        ref="form_bs_range"
                    >
                        <a-input
                            :min="form.start"
                            :max="form.end"
                            v-model:value="form.date"
                            style="width: 70%"
                            type="date"
                            label="date"
                            placeholder="Enter Begin date:"
                            class="pr-2 ml-2 pb-2 rounded-md"
                            name="date"
                            required
                        />

                        <Button
                            type="primary"
                            :disabled="form.processing"
                            htmlType="submit"
                            >Balance Sheet</Button
                        >
                    </form>
                </a-col>
                <a-col :span="12">
                    <form
                        target="_blank"
                        @submit.prevent="submit_pl_range"
                        v-bind:action="'pl'"
                        ref="form_pl_range"
                        class="inline-block"
                    >
                        <br />
                        <a-input
                            :min="form.start"
                            :max="form.end"
                            v-model:value="form.date"
                            style="width: 70%"
                            type="date"
                            label="date"
                            placeholder="Enter Begin date:"
                            class="pr-2 ml-2 pb-2 rounded-md"
                            name="date"
                            hidden
                            required
                        />

                        <Button
                            type="primary"
                            :disabled="form.processing"
                            htmlType="submit"
                            >Profit or Loss A/C</Button
                        >
                    </form>
                </a-col>
            </a-row>
            <br />
            <div>
                <h2 class="text-2xl font-semibold">Ledger</h2>
            </div>

            <!--   <div class="grid grid-cols-4 gap-1"> -->
            <div class="col-span-4">
                <form
                    @submit.prevent="submit_multi_ledger_range"
                    ref="form_multi_ledger_range"
                    v-bind:action="
                        'multi-ledger/' + JSON.stringify(this.form_multi_ledger)
                    "
                >
                    <div class="grid grid-cols-4 gap-1">
                        <div class="col-span-1">
                            <label>Head of Account(s):</label>
                            <Select
                                v-model:value="form_multi_ledger.account"
                                :options="accounts"
                                :field-names="{
                                    label: 'name',
                                    value: 'id',
                                }"
                                filterOption="true"
                                optionFilterProp="name"
                                mode="multiple"
                                placeholder="Please select Multi Accounts"
                                showArrow
                                class="w-full"
                            />
                        </div>
                        <div class="col-span-1">
                            <label> As at:</label>
                            <a-input
                                :min="form.start"
                                :max="form.end"
                                v-model:value="form_multi_ledger.date"
                                style="width: 100%"
                                type="date"
                                label="date"
                                placeholder="Enter Begin date:"
                                class="pr-2 ml-2 pb-2 rounded-md"
                                required
                            />
                        </div>
                        <div class="col-span-2">
                            <Button
                                type="primary"
                                :disabled="form.processing"
                                htmlType="submit"
                            >
                                In Excel</Button
                            >
                            <form
                                @submit.prevent="submit_multi_ledger_range_pdf"
                                ref="form_multi_ledger_range_pdf"
                                v-bind:action="
                                    'multi-ledger-pdf/' +
                                    JSON.stringify(this.form_multi_ledger)
                                "
                                class="inline-block ml-2"
                            >
                                <Select
                                    v-model:value="form_multi_ledger.account"
                                    :options="accounts"
                                    :field-names="{
                                        label: 'name',
                                        value: 'id',
                                    }"
                                    filterOption="true"
                                    optionFilterProp="name"
                                    mode="multiple"
                                    placeholder="Please select"
                                    showArrow
                                    class="w-full"
                                    hidden
                                />

                                <a-input
                                    :min="form.start"
                                    :max="form.end"
                                    v-model:value="form_multi_ledger.date"
                                    style="width: 100%"
                                    type="date"
                                    label="date"
                                    placeholder="Enter Begin date:"
                                    class="pr-2 ml-2 pb-2 rounded-md"
                                    hidden
                                />
                                <br />
                                <Button
                                    type="primary"
                                    :disabled="form.processing"
                                    htmlType="submit"
                                    >In PDF</Button
                                >
                                <!-- </div> -->
                            </form>
                        </div>
                    </div>
                </form>
            </div>
            <!--  </div> -->
        </div>
    </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import JetButton from "@/Jetstream/Button";
import { useForm } from "@inertiajs/inertia-vue3";
import Multiselect from "@suadelabs/vue3-multiselect";
import {
    Table,
    Select,
    InputSearch,
    Form,
    Input,
    Button,
    Row,
    Col,
} from "ant-design-vue";

export default {
    components: {
        AppLayout,
        Button,
        Table,
        Select,
        InputSearch,
        JetButton,
        Multiselect,
        "a-form": Form,
        "a-form-item": Form.Item,
        "a-input": Input,
        "a-textarea": Input.TextArea,
        "a-button": Button,
        "a-select": Select,
        "a-row": Row,
        "a-col": Col,
    },

    props: {
        errors: Object,
        data: Object,
        companies: Object,
        company: Object,
        accounts: Object,
        account_first: Object,
        years: Object,
        year: Object,
        min_start: Object,
        max_end: Object,
    },

    data() {
        return {
            co_id: this.company,
            options: this.companies,
            accounts: this.accounts,
            selected: this.company.name,

            yr_id: this.$page.props.yr_id,
            years: this.years,
            selected_year: this.year.name,

            form: {
                date: this.date ? this.date : this.max_end,
                start: this.min_start
                    ? this.min_start
                    : new Date().toISOString().substr(0, 10),
                end: this.max_end
                    ? this.max_end
                    : new Date().toISOString().substr(0, 10),
            },
            //   form: this.$inertia.form({
            //     account_id: this.account_first.id,
            //     date_start: null,
            //     date_end: null,
            //   }),

            form_multi_ledger: {
                account: [],
                date: this.date ? this.date : this.max_end,
            },

            //   form: {
            //     account_id: this.account_first.id,
            //     date_start: null,
            //     date_end: null,
            //     // begin: null,
            //     // end: null,
            //   },
        };
    },
    //   setup(props) {
    //     const form = useForm({
    //       account_id: props.account_first.id,
    //       date_start: null,
    //       date_end: "",
    //     });
    //     return { form };
    //   },

    methods: {
        submit_trial_range: function () {
            this.$refs.form_trial_range.submit();
        },
        submit_bs_range: function () {
            this.$refs.form_bs_range.submit();
        },
        submit_pl_range: function () {
            this.$refs.form_pl_range.submit();
        },

        submit_multi_ledger_range: function () {
            // this.$inertia.get(route("multi_ledger", this.form_multi_ledger));
            this.$refs.form_multi_ledger_range.submit();
        },

        submit_multi_ledger_range_pdf: function () {
            this.$refs.form_multi_ledger_range_pdf.submit();
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
