<template>
    <div class="card sticky-top cart-info" style="z-index: 900;">
        <div class="card-header cart-info__header">
            <h5 class="card-title cart-info__title-cover">
                <span class="cart-info__title">Ваша корзина</span>
                <span class="cart-info__title-count">{{ cart.count }} {{ productsText }}</span>
            </h5>
        </div>
        <div class="card-body cart-info__body">
            <div v-if="showDiscount" class="cart-info__discount">
                <div class="cart-info__cost-item">
                    <span class="cart-info__cost-title cart-info__cost-title_discount">Товары:</span>
                    <span class="rub-format cart-info__cost-value cart-info__cost-value_products">
                        <span class="rub-format__value">
                            {{ cart.humanSaleLess }}
                        </span>
                        <svg class="rub-format__ico cart-info__discount-ico cart-info__discount-ico_big">
                            <use xlink:href="#catalog-rub"></use>
                        </svg>
                    </span>
                </div>
                <div class="cart-info__cost-item">
                    <span class="cart-info__cost-title cart-info__cost-title_discount">Скидка:</span>
                    <span class="rub-format cart-info__cost-value cart-info__cost-value_danger">
                        <span class="rub-format__value">
                            - {{ cart.humanDiscount }}
                        </span>
                        <svg class="rub-format__ico cart-info__discount-ico">
                            <use xlink:href="#catalog-rub"></use>
                        </svg>
                    </span>
                </div>
            </div>
            <div class="cart-info__cost-item cart-info__cost-item_total">
                <span class="cart-info__cost-title">Стоимость</span>
                <span class="rub-format cart-info__cost-value">
                    <span class="rub-format__value">
                        {{ cart.humanTotal }}
                    </span>
                    <svg class="rub-format__ico cart-info__discount-ico cart-info__discount-ico_big">
                        <use xlink:href="#catalog-rub"></use>
                    </svg>
                </span>
            </div>
        </div>
        <div class="card-footer cart-info__footer">
            <a :href="checkoutUrl" class="btn btn-primary btn-block">
                <span class="d-block d-md-none d-xl-block">Перейти к оформлению</span>
                <span class="d-none d-md-block d-xl-none">Оформить</span>
            </a>
        </div>
    </div>
</template>

<script>
    export default {
        name: "CartInfoComponent",

        props: {
            initCart: {
                type: Object,
                required: true
            },

            showDiscount: {
                type: Boolean,
                default: false
            },

            checkoutUrl: {
                type: String,
                required: true
            }
        },

        data() {
            return {
                cart: {}
            }
        },

        mounted() {
            this.$root.$on("change-cart", this.changeCartData);
        },

        computed: {
            productsText() {
                let variables = ["товар", "товара", "товаров"];
                let number = Math.abs(this.cart.count) % 100;
                let second = number % 10;
                if (number > 10 && number < 20) return variables[2];
                if (second > 1 && second < 5) return variables[1];
                if (second === 1) return variables[0];
                return variables[2];
            }
        },

        created() {
            this.cart = this.initCart;
        },

        methods: {
            changeCartData(cart) {
                this.cart = cart;
            }
        }
    }
</script>

<style scoped>

</style>