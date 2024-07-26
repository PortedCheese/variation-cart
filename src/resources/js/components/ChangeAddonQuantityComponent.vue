<template>
    <form class="cart-item__form">
        <div class="cart-item__form-info">
            <div class="rub-format cart-item__form-price">
                <span class="rub-format__value">
                    {{ priceFormatted }}
                </span>
                <svg class="rub-format__ico cart-item__rub">
                    <use xlink:href="#catalog-rub"></use>
                </svg>
            </div>
            <div class="rub-format cart-item__form-price cart-item__form-price_thin cart-item__form-price_old"
                  v-if="variation.sale">
                <span class="rub-format__value">
                    {{ saleFormatted }}
                </span>
                <svg class="rub-format__ico">
                    <use xlink:href="#catalog-rub"></use>
                </svg>
            </div>
            <span class="rub-format cart-item__form-price cart-item__form-price_discount"
                  v-if="variation.sale && showDiscount">
                <span class="rub-format__value">
                    Скидка -{{ discountFormatted }}
                </span>
                <svg class="rub-format__ico">
                    <use xlink:href="#catalog-rub"></use>
                </svg>
            </span>
        </div>

        <div class="cart-item__form-quantity">
            <div class="choose-quantity">
                <button class="btn choose-quantity__decrease"
                        type="button"
                        :disabled="quantity <= 1 || loading"
                        @click="decreaseQuantity">
                    <i class="fas fa-minus"></i>
                </button>
                <input type="number"
                       aria-label="Количество"
                       class="form-control choose-quantity__input"
                       min="1"
                       @change.self="sendQuantity"
                       v-model.lazy="quantity">
                <button class="btn choose-quantity__increase"
                        type="button"
                        :disabled="loading"
                        @click="increaseQuantity">
                    <i class="fas fa-plus"></i>
                </button>
            </div>

            <div class="rub-format cart-item__form-price cart-item__form-price_thin">
                <span class="rub-format__value">
                    {{ variation.human_price }}
                </span>
                <svg class="rub-format__ico">
                    <use xlink:href="#catalog-rub"></use>
                </svg>
                <span class="rub-format__value">
                     / {{ variation.short_measurement }}
                </span>
            </div>
        </div>
    </form>
</template>

<script>
import productVariationEventBus from '../category-product/categoryProductEventBus';
export default {
        name: "ChangeAddonQuantityComponent",

        props: {
            initQuantity: {
                type: Number,
                required: true
            },

            updateUrl: {
                type: String,
                required: true
            },

            initVariation: {
                type: Object,
                required: true
            },

            showDiscount: {
                type: Boolean,
                default: false
            }
        },

        data() {
            return {
                quantity: 1,
                loading: false,
                messages: [],
                errors: [],
                variation: {},
            }
        },

        created() {
            this.quantity = this.initQuantity;
            this.variation = this.initVariation;
        },

        computed: {
            priceFormatted() {
                let price = this.variation.price;
                return this.formatPrice(price);
            },

            saleFormatted() {
                let price = this.variation.sale_price;
                return this.formatPrice(price);
            },

            discountFormatted() {
                let price = this.variation.discount;
                return this.formatPrice(price);
            }
        },

        methods: {
            formatPrice(price) {
                return new Intl.NumberFormat("ru-RU").format(price * this.quantity);
            },

            increaseQuantity() {
                this.quantity++;
                this.sendQuantity();
            },

            decreaseQuantity() {
                this.quantity--;
                this.sendQuantity();
            },

            sendQuantity() {
                this.loading = true;
                this.errors = [];
                this.messages = [];
                axios
                    .put(this.updateUrl, {
                        quantity: this.quantity
                    })
                    .then(response => {
                        let result = response.data;
                        productVariationEventBus.$emit("change-cart", result.cart);
                        if (! result.success) {
                            this.errors.push([result.message]);
                        }
                        else {
                            this.variation = result.variation;
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