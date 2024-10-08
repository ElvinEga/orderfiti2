<template>
    <LoadingComponent :props="loading" />
    <SmModalCreateComponent :props="addButton" />


    <div id="modal" class="modal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t("menu.templates") }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500"
                        @click="reset"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="save">
                    <div class="form-row">

                        <div class="form-col-12 sm:form-col-12">
                            <label for="template_id" class="db-field-title required">{{ $t("label.select_template") }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="template_id"
                                        v-bind:class="errors.template_id ? 'invalid' : ''" v-model="props.form.template_id"
                                        :options="templates" label-by="name" value-by="id" :closeOnSelect="true" :searchable="true"
                                        :clearOnClose="true" placeholder="--" search-placeholder="--" />
                            <small class="db-field-alert" v-if="errors.template_id">{{
                                    errors.template_id[0]
                                }}</small>
                        </div>

                        <div class="col-12">
                            <div class="flex flex-wrap gap-3 mt-4">
                                <button type="submit" class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-save"></i>
                                    <span>{{ $t("button.select") }}</span>
                                </button>
                                <button type="button" class="modal-btn-outline modal-close" @click="reset">
                                    <i class="lab lab-close"></i>
                                    <span>{{ $t("button.close") }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
<script>
import SmModalCreateComponent from "../components/buttons/SmModalCreateComponent";
import LoadingComponent from "../components/LoadingComponent";
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";
import statusEnum from "../../../enums/modules/statusEnum";

export default {
    name: "TemplateSelectComponent",
    components: { SmModalCreateComponent, LoadingComponent },
    props: ['props'],
    data() {
        return {
            loading: {
                isActive: false
            },
            errors: {},
        }
    },
    computed: {
        addButton: function () {
            return { title: this.$t('button.select_template') };
        },
        templates: function () {
            return this.$store.getters['template/lists'];
        },
    },
    mounted() {
        this.loading.isActive = true;
        this.$store.dispatch('template/lists', {
            status: statusEnum.ACTIVE
        });
        this.loading.isActive = false;
    },
    methods: {
        reset: function () {
            appService.modalHide();
            this.errors = {};
        },
        save: function () {
            try {
                const fd = new FormData();
                fd.append('template_id', this.props.form.template_id);
                // fd.append('branch_id', this.props.form.price);
                // const tempId = this.$store.getters['item/temp'].temp_id;
                this.loading.isActive = true;
                this.$store.dispatch('template/save', {
                    form: fd
                }).then((res) => {
                    appService.modalHide();
                    this.loading.isActive = false;
                    alertService.successFlip((1), this.$t('menu.items'));
                    location.reload();
                }).catch((err) => {
                    this.loading.isActive = false;
                    this.errors = err.response.data.errors;
                })
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err)
            }
        }
    }
}
</script>
