<template>
    <form v-if="variations.length">
        <product-variations :specifications="specifications" :variations="variations"
                            v-model="chosenVariation" @change="resetData">
        </product-variations>

        <div class="product-add-to-cart">
            <div class="choose-quantity product-add-to-cart__quantity"  v-if="variationData">
                <button class="btn choose-quantity__decrease"
                        type="button"
                        :disabled="quantity <= 1"
                        @click="decreaseQuantity">
                    <i class="fas fa-minus"></i>
                </button>
                <input type="number"
                       aria-label="Количество"
                       class="form-control choose-quantity__input"
                       min="1"
                       v-model="quantity">
                <button class="btn choose-quantity__increase"
                        type="button"
                        @click="increaseQuantity">
                    <i class="fas fa-plus"></i>
                </button>
            </div>

            <a :href="cartUrl" v-if="toCart" class="btn btn-outline-primary">
                Перейти в корзину
            </a>
            <button type="button"
                    v-else
                    @click="addToCard"
                    :disabled="loading || quantity < 1 || ! variationData"
                    class="btn btn-primary product-add-to-cart__btn">
                Купить
            </button>
        </div>

        <div class="alert alert-danger" role="alert" v-if="Object.keys(errors).length">
            <template v-for="field in errors">
                <template v-for="error in field">
                    <span>{{ error }}</span>
                    <br>
                </template>
            </template>
        </div>
        <div class="alert alert-success" role="alert" v-if="Object.keys(messages).length">
            <template v-for="message in messages">
                <span>{{ message }}</span>
                <br>
            </template>
        </div>
    </form>
</template>

<script>
    import Variations from "../product-variation/ChooseProductVariationComponent";

    export default {
        name: "AddVariationsToCartComponent",

        components: {
            "product-variations": Variations
        },

        props: {
            variations: {
                type: Array,
                required: true
            },
            cartUrl: {
                type: String,
                required: true
            },
            specifications:{
                type: Object,
                required: false
            }
        },

        data() {
            return {
                loading: false,
                chosenVariation: "",
                messages: [],
                errors: [],
                quantity: 1,
                toCart: false,
            }
        },

        computed: {
            variationData() {
                let variation = false;
                for (let item in this.variations) {
                    if (this.variations.hasOwnProperty(item)) {
                        if (this.variations[item].id === this.chosenVariation) {
                            variation = this.variations[item];
                        }
                    }
                }
                return variation;
            }
        },

        methods: {
            resetData() {
                this.messages = [];
                this.errors = [];
                this.quantity = 1;
                this.toCart = false;
            },

            increaseQuantity() {
                this.quantity++;
                this.toCart = false;
                this.messages = [];
                this.errors = [];
            },

            decreaseQuantity() {
                this.quantity--;
                this.toCart = false;
                this.messages = [];
                this.errors = [];
            },

            addToCard() {
                this.loading = true;
                this.errors = [];
                this.messages = [];
                axios
                    .put(this.variationData.addToCartUrl, {
                        quantity: this.quantity
                    })
                    .then(response => {
                        let result = response.data;
                        if (result.success) {
                            this.quantity = 1;
                            this.messages.push(result.message);
                            this.toCart = true;
                        }
                        else {
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
        }
    }
</script>

<style scoped>

</style>