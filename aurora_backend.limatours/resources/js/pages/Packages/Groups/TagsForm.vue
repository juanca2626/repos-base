<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="tag_name">{{
                            $t("tags.tag_name")
                        }}</label>
                        <div class="col-sm-5">
                            <input
                                :class="{
                                    'form-control': true,
                                    'is-valid': validError,
                                    'is-invalid': invalidError,
                                }"
                                id="tag_name"
                                name="tag_name"
                                type="text"
                                v-model="
                                    form.translations[currentLang].tag_name
                                "
                                v-validate="'required'"
                            />
                            <div
                                class="bg-danger"
                                style="margin-top: 3px; border-radius: 2px"
                                v-show="errors.has('tag_name')"
                            >
                                <font-awesome-icon
                                    :icon="['fas', 'exclamation-circle']"
                                    style="margin-left: 5px"
                                />
                                <span>{{ errors.first("tag_name") }}</span>
                            </div>
                        </div>
                        <select
                            class="col-sm-1 form-control"
                            id="lang"
                            required
                            size="0"
                            v-model="currentLang"
                        >
                            <option
                                v-bind:value="language.id"
                                v-for="language in languages"
                            >
                                {{ language.iso }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <sketch-picker v-model="color" name="color" />
                </div>
            </form>
        </div>
        <div class="col-sm-6">
            <div slot="footer">
                <img src="/images/loading.svg" v-if="loading" width="40px" />
                <button
                    @click="validateBeforeSubmit"
                    class="btn btn-success"
                    type="submit"
                    v-if="!loading"
                >
                    <font-awesome-icon :icon="['fas', 'dot-circle']" />
                    {{ $t("global.buttons.submit") }}
                </button>
                <router-link :to="{ name: 'Tags' }" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{ $t("global.buttons.cancel") }}
                    </button>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
import { API } from "./../../../api";
import { Sketch } from "vue-color";

export default {
    components: {
        "sketch-picker": Sketch,
    },
    data: () => {
        return {
            loading: false,
            formAction: "post",
            invalidError: false,
            languages: [],
            currentLang: "1",
            color: "#EA5656",
            form: {
                translations: {
                    1: {
                        id: "",
                        tag_name: "",
                    },
                },
            },
        };
    },
    mounted() {
        API.get("/languages/").then((result) => {
            this.languages = result.data.data;
            this.currentLang = result.data.data[0].id;

            let form = {
                translations: {},
            };

            let languages = this.languages;

            languages.forEach((value) => {
                form.translations[value.id] = {
                    id: "",
                    tag_name: "",
                };
            });
            if (this.$route.params.tag_id !== undefined) {
                API.get(
                    "/packages/groups/" +
                        this.$route.params.group_id +
                        "/tags/" +
                        this.$route.params.tag_id +
                        "?lang=" +
                        localStorage.getItem("lang")
                )
                    .then((result) => {
                        this.formAction = "put";
                        let arrayTranslations = result.data.translations;
                        this.color = "#" + result.data.color;
                        arrayTranslations.forEach((translation) => {
                            form.translations[translation.language_id] = {
                                id: translation.id,
                                tag_name: translation.value,
                            };
                        });
                    })
                    .catch(() => {
                        this.$notify({
                            group: "main",
                            type: "error",
                            title: this.$t("zones.error.messages.name"),
                            text: this.$t(
                                "zones.error.messages.connection_error"
                            ),
                        });
                    });
            }

            this.form = form;
        });
    },
    computed: {
        validError: function () {
            if (
                this.errors.has("tag_name") === false &&
                this.form.translations[1].tag_name !== ""
            ) {
                this.invalidError = false;
                this.countError += 1;
                return true;
            } else if (this.countError > 0) {
                this.invalidError = true;
            }
            return false;
        },
    },
    methods: {
        validateBeforeSubmit() {
            this.$validator.validateAll().then((result) => {
                if (result) {
                    this.submit();
                } else {
                    this.$notify({
                        group: "main",
                        type: "error",
                        title: this.$t("global.modules.tags"),
                        text: this.$t(
                            "zones.error.messages.information_complete"
                        ),
                    });

                    this.loading = false;
                }
            });
        },
        submit() {
            this.loading = true;
            let color =
                typeof this.color.hex === "undefined"
                    ? this.color
                    : this.color.hex;
            this.form.color = color.substr(1);
            API({
                method: this.formAction,
                url:
                    "/packages/groups/" +
                    this.$route.params.group_id +
                    "/tags" +
                    (this.$route.params.tag_id !== undefined
                        ? `/${this.$route.params.tag_id}`
                        : ""),
                data: this.form,
            }).then((result) => {
                if (result.data.success === true) {
                    this.$router.push(
                        "/packages/groups/tags/" + this.$route.params.group_id
                    );
                } else {
                    this.$notify({
                        group: "main",
                        type: "error",
                        title: this.$t("global.modules.groups"),
                        text: this.$t("zones.error.messages.information_error"),
                    });

                    this.loading = false;
                }
            });
        },
    },
};
</script>

<style lang="stylus"></style>
