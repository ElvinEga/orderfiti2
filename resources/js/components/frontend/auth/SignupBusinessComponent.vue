<template>
    <LoadingComponent :props="loading" />
    <section class="pt-8 pb-16">
        <div class="container max-w-[550px] py-6 p-4 sm:px-6 shadow-xs rounded-2xl bg-white">
            <h2 class="capitalize mb-6 text-center text-[22px] font-semibold leading-[34px] text-heading">
                {{ $t('label.create_business') }}
            </h2>
            <form @submit.prevent="save">
                <div class="form-row">
                    <div class="form-col-12 sm:form-col-6">
                        <label for="name" class="db-field-title required">{{
                                $t("label.name")
                            }}</label>
                        <input v-model="props.form.name" v-bind:class="errors.name ? 'invalid' : ''" type="text"
                               id="name" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.name">{{
                                errors.name[0]
                            }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-6">
                        <label class="db-field-title" for="latitude">{{ $t("label.latitude") }}/{{
                                $t("label.longitude")
                            }}</label>
                        <div class="db-multiple-field">
                            <input v-model="props.form.latitude" v-bind:class="errors.latitude ? 'invalid' : ''
                                    " type="text" id="latitude" />
                            <input v-model="props.form.longitude" v-bind:class="errors.longitude ? 'invalid' : ''
                                    " type="text" id="longitude" />
                            <button @click="add" v-on:click="isMap = true" type="button"
                                    class="fa-solid fa-map-location-dot" data-modal="#branchMap"></button>
                        </div>

                        <small class="db-field-alert" v-if="errors.latitude">{{ errors.latitude[0] }}</small>
                        <small class="db-field-alert" v-if="errors.longitude">{{ errors.longitude[0] }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-6">
                        <label for="email" class="db-field-title">{{
                                $t("label.email")
                            }}</label>
                        <input v-model="props.form.email" v-bind:class="errors.email ? 'invalid' : ''" type="email"
                               id="email" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.email">{{
                                errors.email[0]
                            }}</small>
                    </div>
                    <div class="form-col-12 sm:form-col-6">
                        <label for="phone" class="db-field-title">{{
                                $t("label.phone")
                            }}</label>
                        <input v-model="props.form.phone" v-bind:class="errors.phone ? 'invalid' : ''" type="text"
                               id="phone" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.phone">{{
                                errors.phone[0]
                            }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-6">
                        <label for="city" class="db-field-title required">{{
                                $t("label.city")
                            }}</label>
                        <input v-model="props.form.city" v-bind:class="errors.city ? 'invalid' : ''" type="text"
                               id="city" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.city">{{
                                errors.city[0]
                            }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-6">
                        <label for="state" class="db-field-title required">{{ $t("label.state") }}</label>
                        <input v-model="props.form.state" v-bind:class="errors.state ? 'invalid' : ''" type="text"
                               id="state" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.state">{{
                                errors.state[0]
                            }}</small>
                    </div>

                    <div class="form-col-12 sm:form-col-6">
                        <label for="zip_code" class="db-field-title required">{{ $t("label.zip_code") }}</label>
                        <input v-model="props.form.zip_code" v-bind:class="errors.zip_code ? 'invalid' : ''" type="text"
                               id="zip_code" class="db-field-control" />
                        <small class="db-field-alert" v-if="errors.zip_code">{{ errors.zip_code[0] }}</small>
                    </div>

                    <div class="form-col-12">
                        <label for="address" class="db-field-title required">{{ $t("label.address") }}</label>
                        <textarea v-model="props.form.address" v-bind:class="errors.address ? 'invalid' : ''"
                                  id="address" class="db-field-control"></textarea>
                        <small class="db-field-alert" v-if="errors.address">{{ errors.address[0] }}</small>
                    </div>

                    <div class="col-12">
                        <button type="submit"
                                class="w-full h-12 text-center capitalize font-medium rounded-3xl text-white bg-primary">
                            {{ $t('button.sign_up') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</template>

<script>
// import SmModalCreateComponent from "../../components/buttons/SmModalCreateComponent";
import LoadingComponent from "../components/LoadingComponent";
import statusEnum from "../../../enums/modules/statusEnum";
import alertService from "../../../services/alertService";
// import appService from "../../../../services/appService";
import MapComponent from "../../frontend/components/MapComponent";

export default {
    name: "SignupBusinessComponent",
    components: { LoadingComponent, MapComponent },
    props: ["props"],
    data() {
        return {
            loading: {
                isActive: false,
            },
            isMap: false,
            address: "",
            errors: {},
        };
    },
    computed: {
        addButton: function () {
            return { title: this.$t('button.sign_up') };
        }
    },
    methods: {
        add: function () {
            // appService.modalShow('#branchMap');
        },
        location: function (e) {
            this.address = e.address;
            this.props.form.latitude = e.location.lat;
            this.props.form.longitude = e.location.lng;
            this.props.form.city = e.other.city;
            this.props.form.state = e.other.state;
            this.props.form.zip_code = e.other.zipCode;
            this.props.form.address = e.address;
        },
        reset: function () {
            // appService.modalHide();
            this.$store.dispatch("branch/reset").then().catch();
            this.errors = {};
            this.$props.props.form = {
                name: "",
                email: "",
                phone: "",
                latitude: "",
                longitude: "",
                city: "",
                state: "",
                zip_code: "",
                address: "",
                status: statusEnum.ACTIVE,
            };
        },


        save: function () {
            try {
                const tempId = this.$store.getters["branch/temp"].temp_id;
                this.loading.isActive = true;
                this.$store.dispatch("branch/save", this.props).then((res) => {
                    this.loading.isActive = false;
                    alertService.successFlip(
                        tempId === null ? 0 : 1,
                        this.$t("menu.branches")
                    );
                    this.props.form = {
                        name: "",
                        email: "",
                        phone: "",
                        latitude: "",
                        longitude: "",
                        city: "",
                        state: "",
                        zip_code: "",
                        address: "",
                        status: statusEnum.ACTIVE,
                    };
                    this.errors = {};
                    this.$router.push({ name: "frontend.home" });
                }).catch((err) => {
                    this.loading.isActive = false;
                    this.errors = err.response.data.errors;
                });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err);
            }
        },
    },
};
</script>
