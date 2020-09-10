<template>
    <div class="card sticky-top" style="z-index: 900;">
        <div class="card-header">
            <h5 class="card-title">
                <span>Ваша корзина</span>
                <small>{{ cart.count }} {{ productsText }}</small>
            </h5>
        </div>
        <div class="card-body">
            <div v-if="showDiscount">
                Товары: {{ cart.humanSaleLess }}
                <br>
                Скидка: {{ cart.humanDiscount }}
            </div>
            <div>
                Общая стоимость {{ cart.humanTotal }}
            </div>
        </div>
        <div class="card-footer">
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