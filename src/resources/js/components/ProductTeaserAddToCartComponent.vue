<template>
    <div class="variation-price__btn-wrapper" v-if="variation">
        <div class="alert alert-danger" role="alert" v-if="Object.keys(errors).length">
            <template v-for="field in errors">
                <template v-for="error in field">
                    <span>{{ error }}</span>
                    <br>
                </template>
            </template>
        </div>

        <button type="button"
                @click="addToCart"
                :disabled="loading"
                class="btn btn-primary btn-block variation-price__btn"
                v-if="! success">
            Купить
        </button>
        <a :href="cartUrl" class="btn btn-outline-primary btn-block variation-price__btn" v-else>
            К корзине
        </a>
    </div>
</template>

<script>
    export default {
        name: "ProductTeaserAddToCartComponent",

        props: {
            variation: {
                type: Object,
                required: true
            },
            cartUrl: {
                type: String,
                required: true
            }
        },

        data() {
            return {
                loading: false,
                errors: [],
                messages: [],
                success: false,
            }
        },

        methods: {
            addToCart() {
                this.loading = true;
                this.errors = [];
                this.success = false;
                axios
                    .put(this.variation.addToCartUrl, {
                        quantity: 1
                    })
                    .then(response => {
                        let result = response.data;
                        if (result.success) {
                            this.success = true;
                        } else {
                            this.errors.push([result.message]);
                        }
                        this.$root.$emit("change-cart", result.cart);
                    })
                    .catch(error => {
                        let response = error.response;
                        let data = response.data;
                        if (response.status === 404) {
                            this.errors.push(["404 Не найдено"]);
                        }
                        else {
                            if (data.hasOwnProperty("errors")) {
                                this.errors = data.errors;
                            }
                            else {
                                this.errors.push([data.message]);
                            }
                        }
                    })
                    .finally(() => {
                        this.loading = false;
                    })
            }
        },
    }
</script>

<style scoped>

</style>