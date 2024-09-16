<template>
    <a :href="cartUrl" :class="baseClass" class="cart-state">
        <span class="cart-state__cover">
            <svg class="cart-state__ico">
                <use xlink:href="#cart-ico"></use>
            </svg>
            <span v-if="count > 0" class="badge bg-primary cart-state__count">{{ count }}</span>
        </span>
        <span class="cart-state__title">
            Корзина
        </span>
    </a>
</template>

<script>
import productVariationEventBus from '../category-product/categoryProductEventBus';

    export default {
        name: "CartStateComponent",

        props: {
            baseClass: {
                type: String,
                required: false,
                default: "nav-link"
            },

            cartData: {
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
                total: 0,
                count: 0,
            }
        },

        created() {
            this.total = this.cartData.total;
            this.count = this.cartData.count;
        },

        mounted() {
            productVariationEventBus.$on("change-cart", this.changeCartData);
        },

        methods: {
            changeCartData(cart) {
                this.total = cart.total;
                this.count = cart.count;
            }
        }
    }
</script>

<style scoped>

</style>