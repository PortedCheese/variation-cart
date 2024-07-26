<template>
    <form v-if="variations.length">
        <product-variations :specifications="specifications" :variations="variations"
                            v-model="chosenVariation" @change="resetData"
                            :page-mode="true"
        >
        </product-variations>

        <div class="product-add-to-cart__addons" v-if="Object.keys(addonVariations).length">
            <div class="product-add-to-cart__addons-title " data-toggle="modal" data-target="#addonsModal">Дополнения к заказу:</div>
            <div class="rub-format variation-price__value">
                    <span class="rub-format__value">
                      {{ addonVariationsSumm.price }}
                    </span>
                <svg class="rub-format__ico">
                    <use xlink:href="#catalog-rub-bold"></use>
                </svg>
            </div>
            <div class="rub-format variation-price__value variation-price__value_thin" v-if="addonVariationsSumm.discount">
                    <span class="rub-format__value">
                        {{ addonVariationsSumm.sale_price  }}
                    </span>
                <svg class="rub-format__ico">
                    <use xlink:href="#catalog-rub"></use>
                </svg>
            </div>
        </div>

        <div class="product-add-to-cart">
            <div class="choose-quantity product-add-to-cart__quantity"  v-if="variationData && !toCart">
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
                <span v-if="! Object.keys(addonVariations).length">Купить</span>
                <span v-else>Купить комплект</span>
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

        <div class="modal fade" id="addonsModal" tabindex="-1" aria-labelledby="addonsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addonsModalLabel">Дополнения</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex flex-row flex-wrap">
                            <div class="card mr-2 mb-2" v-for="(item,index) in addonVariations">
                                <div class="card-header p-1">
                                    {{ item.description }}
                                    <button type="button" class="close" @click="removeThisAddon(item)">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="card-body p-1">
                                    <p class="mb-2">{{ item.human_price }}
                                        <svg class="rub-format__ico">
                                            <use xlink:href="#catalog-rub"></use>
                                        </svg>
                                        {{ item.short_measurement }}
                                    </p>
                                    <p class="mb-2 text-muted">
                                        <template v-for="(spec) in item.specifications">{{ spec.title }} : {{ spec.value }}</template>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

</template>

<script>

    import Variations from "../product-variation/ChooseProductVariationComponent";
    import productVariationEventBus from '../category-product/categoryProductEventBus';

    export default {
        name: "AddVariationsToCartComponent",

        components: {
            "product-variations": Variations,
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
                addonVariations: [],
                addonVariationsSumm: { price: 0, sale_price:0 , discount: 0},
                messages: [],
                errors: [],
                quantity: 1,
                toCart: false,
            }
        },
        mounted() {
            productVariationEventBus.$on("change-order-addons", this.changeOrderAddonsData);
            productVariationEventBus.$on("remove-order-addons", this.removeOrderAddonsData);
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
            },
        },

        methods: {
            resetData() {
                this.messages = [];
                this.errors = [];
                this.quantity = 1;
                this.toCart = false;
            },
            changeOrderAddonsData(data){
                this.addonVariations.push(data);
                this.addonVariationsSumm = this.addonsSumm();
            },
            removeOrderAddonsData(data){
                this.addonVariations.splice(this.addonVariations.indexOf(data),1);
                this.addonVariationsSumm = this.addonsSumm();
            },
            removeThisAddon(data){
                productVariationEventBus.$emit("remove-this-addon", data);
            },
            addonsSumm(){
                let summ = {
                    price: 0,
                    sale_price: 0,
                    discount: 0
                }
                for (let item of this.addonVariations){
                    summ.price += Number(item.human_price.replace(/[^0-9.-]+/g,""));
                    if (item.discount){
                        summ.sale_price += Number(item.human_sale_price.replace(/[^0-9.-]+/g,""))
                        summ.discount += Number(item.human_discount.replace(/[^0-9.-]+/g,""))
                    }
                    else {
                        summ.sale_price += Number(item.human_price.replace(/[^0-9.-]+/g,""));
                    }
                }
                return {
                    price: Intl.NumberFormat('ru-RU').format(summ.price),
                    sale_price: Intl.NumberFormat('ru-RU').format(summ.sale_price),
                    discount: summ.discount
                };
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
                let addonsArray = [];
                if (this.addonVariations.length){
                    for (let addon of this.addonVariations){
                        addonsArray.push({id: addon.id, quantity: 1})
                    }
                }
                axios
                    .put(this.variationData.addToCartUrl, {
                        quantity: this.quantity,
                        addons: addonsArray
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
                        productVariationEventBus.$emit("change-cart", result.cart);
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