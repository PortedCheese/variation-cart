<template>
    <div class="col-12">
        <form>
            <product-variations :variations="variations" v-model="chosenVariation" @change="resetData"></product-variations>

            <div>
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

                <button class="btn btn-outline-secondary"
                        type="button"
                        :disabled="quantity <= 1"
                        @click="decreaseQuantity">
                    <i class="fas fa-minus"></i>
                </button>
                <input type="number"
                       aria-label="Количество"
                       class="form-control"
                       min="1"
                       v-model="quantity">
                <button class="btn btn-outline-secondary"
                        type="button"
                        @click="increaseQuantity">
                    <i class="fas fa-plus"></i>
                </button>

                <button type="button"
                        @click="addToCard"
                        :disabled="loading || quantity < 1 || ! variationData"
                        class="btn btn-primary">
                    Добавить в корзину
                </button>
            </div>
        </form>
    </div>
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
            }
        },

        data() {
            return {
                loading: false,
                chosenVariation: "",
                messages: [],
                errors: [],
                quantity: 1,
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
            },

            increaseQuantity() {
                this.quantity++;
            },

            decreaseQuantity() {
                this.quantity--;
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
                        }
                        else {
                            this.errors.push([result.message]);
                        }
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